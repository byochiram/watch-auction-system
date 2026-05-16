<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LotStoreRequest;
use App\Http\Requests\LotUpdateRequest;
use App\Notifications\AuctionCancelledNotification;
use App\Models\AuctionLot;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LotController extends Controller
{
    public function index(Request $r)
    {
        $per = in_array((int) $r->per, [10, 25, 50]) ? (int) $r->per : 10;

        $sort = $r->input('sort');
        if (! in_array($sort, ['start_asc','start_desc','end_asc','end_desc'], true)) {
            $sort = null;
        }

        $endStatus = $r->input('end_status');
        if (! in_array($endStatus, ['ENDED','PENDING','AWARDED','UNSOLD'], true)) {
            $endStatus = null;
        }

        $base = AuctionLot::query()
            ->with([
                'product.images' => fn ($q) => $q->orderBy('sort_order'),
                'payment',
            ])
            ->when($r->search, function ($q, $s) {
                $q->whereHas('product', function ($p) use ($s) {
                    $p->where('brand', 'like', "%$s%")
                      ->orWhere('model', 'like', "%$s%");
                });
            })
            ->when($r->date_from, fn ($q, $from) => $q->where('start_at', '>=', $from))
            ->when($r->date_to, fn ($q, $to) => $q->where('end_at', '<=', $to));

        // helper sort
        $applySort = function ($q, array $default) use ($sort) {
            switch ($sort) {
                case 'start_asc':
                    $q->orderBy('start_at', 'asc');
                    break;
                case 'start_desc':
                    $q->orderBy('start_at', 'desc');
                    break;
                case 'end_asc':
                    $q->orderBy('end_at', 'asc');
                    break;
                case 'end_desc':
                    $q->orderBy('end_at', 'desc');
                    break;
                default:
                    foreach ($default as $col => $dir) {
                        $q->orderBy($col, $dir);
                    }
            }
        };

        $scheduledLots = (clone $base)
            ->scheduled()
            ->tap(fn ($q) => $applySort($q, ['start_at' => 'asc']))
            ->paginate($per, ['*'], 'scheduled_page')
            ->withQueryString();

        $activeLots = (clone $base)
            ->active()
            ->tap(fn ($q) => $applySort($q, ['start_at' => 'asc']))
            ->paginate($per, ['*'], 'active_page')
            ->withQueryString();

        $cancelledLots = (clone $base)
            ->cancelled()
            ->tap(fn ($q) => $applySort($q, ['cancelled_at' => 'desc']))
            ->paginate($per, ['*'], 'cancelled_page')
            ->withQueryString();

        $endedLots = (clone $base)
            ->ended()
            ->when($endStatus, function ($q, $status) {
                if ($status === 'ENDED') {
                    $q->whereNull('winner_bid_id')
                      ->whereDoesntHave('payment');
                } elseif ($status === 'PENDING') {
                    $q->whereHas('payment', fn ($p) => $p->where('status', 'PENDING'));
                } elseif ($status === 'AWARDED') {
                    $q->whereHas('payment', fn ($p) => $p->where('status', 'PAID'));
                } elseif ($status === 'UNSOLD') {
                    $q->whereHas('payment', fn ($p) => $p->whereIn('status', ['EXPIRED', 'CANCELLED']));
                }
            })
            ->tap(fn ($q) => $applySort($q, ['end_at' => 'desc']))
            ->paginate($per, ['*'], 'ended_page')
            ->withQueryString();

        // LIST UNTUK CREATE
        $productsAvailable = Product::with([
                'auctionLots' => function ($q) {
                    $q->with(['payment', 'bids'])
                      ->orderByDesc('id')
                      ->limit(1);
                },
            ])
            ->get()
            ->filter(function ($product) {
                $lastLot = $product->auctionLots->first();

                if (! $lastLot) {
                    return true; // belum pernah dilelang
                }

                if ($lastLot->cancelled_at) {
                    return true; // lot terakhir dibatalkan
                }

                if (now()->lt($lastLot->end_at)) {
                    return false; // masih berjalan
                }

                $payment = $lastLot->payment;

                if (! $lastLot->winner_bid_id && ! $payment) {
                    return true; // ENDED tanpa pemenang
                }

                if ($payment && in_array($payment->status, ['EXPIRED','CANCELLED'], true)) {
                    return true; // UNSOLD
                }

                return false; // PENDING / PAID → masih reserved / terjual
            })
            ->sortBy(function ($product) {
                return trim(($product->brand ?? '') . ' ' . ($product->model ?? ''));
            })
            ->values();

        // LIST UNTUK EDIT
        $editProducts = Product::orderBy('brand')->orderBy('model')->get();

        // ===== STATISTIK LELANG =====
        $lotStats = [
            'scheduled' => AuctionLot::scheduled()->count(),
            'active'    => AuctionLot::active()->count(),
            'cancelled' => AuctionLot::cancelled()->count(),
        ];

        // ended + breakdown
        $endedBase = AuctionLot::ended();

        $lotStats['ended_total'] = (clone $endedBase)->count();
        $lotStats['ended_plain'] = (clone $endedBase)
            ->whereNull('winner_bid_id')
            ->whereDoesntHave('payment')
            ->count(); // ENDED (tanpa pemenang & payment)

        $lotStats['pending'] = (clone $endedBase)
            ->whereHas('payment', fn ($p) => $p->where('status', 'PENDING'))
            ->count();

        $lotStats['awarded'] = (clone $endedBase)
            ->whereHas('payment', fn ($p) => $p->where('status', 'PAID'))
            ->count();

        $lotStats['unsold'] = (clone $endedBase)
            ->whereHas('payment', fn ($p) => $p->whereIn('status', ['EXPIRED','CANCELLED']))
            ->count();

        return view('admin.lots.index', compact(
            'scheduledLots',
            'activeLots',
            'cancelledLots',
            'endedLots',
            'productsAvailable',
            'editProducts',
            'lotStats',
        ));
    }

    public function store(LotStoreRequest $req)
    {
        DB::transaction(function () use ($req, &$lot) {
            $lot = AuctionLot::create([
                'product_id'    => $req->product_id,
                'start_price'   => $req->start_price,
                'increment'     => $req->increment,
                'current_price' => $req->start_price,
                'start_at'      => $req->start_at,
                'end_at'        => $req->end_at,
            ]);
        });

        return back()->with('success', "Lot #{$lot->id} dibuat.");
    }

    public function update(LotUpdateRequest $req, AuctionLot $lot)
    {
        $status = $lot->runtime_status;

        if ($status === 'ACTIVE') {
            $data = $req->only('end_at');
        } elseif (in_array($status, ['ENDED','CANCELLED'], true)) {
            return redirect()
                ->route('lots.index', ['tab' => $req->input('tab', 'scheduled')])
                ->with('error', 'Lot yang sudah berakhir / dibatalkan tidak dapat diubah.');
        } else {
            $data = $req->validated();
        }

        $lot->update($data);

        return redirect()
            ->route('lots.index', ['tab' => $req->input('tab', 'scheduled')])
            ->with('success', "Lot #{$lot->id} diperbarui.");
    }

    public function destroy(Request $req, AuctionLot $lot)
    {
        if ($lot->runtime_status === 'ACTIVE') {
            return redirect()
                ->route('lots.index', ['tab' => $req->input('tab', 'scheduled')])
                ->with('error', 'Lot yang sedang berjalan tidak boleh dihapus.');
        }

        $lot->delete();

        return redirect()
            ->route('lots.index', ['tab' => $req->input('tab', 'scheduled')])
            ->with('success', 'Lot dihapus.');
    }

    public function cancel(Request $request, AuctionLot $lot)
    {
        $status = $lot->runtime_status;

        if ($status !== 'ACTIVE') {
            return redirect()
                ->route('lots.index', ['tab' => $request->input('tab', 'active')])
                ->with('error', 'Hanya lot yang sedang berjalan yang dapat dibatalkan.');
        }

        if ($lot->cancelled_at) {
            return redirect()
                ->route('lots.index', ['tab' => $request->input('tab', 'active')])
                ->with('error', 'Lot ini sudah dibatalkan.');
        }

        $request->validate([
            'cancel_reason' => ['required', 'string', 'max:255'],
        ]);

        $lot->update([
            'cancelled_at'  => now(),
            'cancel_reason' => $request->cancel_reason,
        ]);

        $users = $lot->bids()
            ->with('bidderProfile.user')
            ->get()
            ->pluck('bidderProfile.user')
            ->filter()
            ->unique('id');

        foreach ($users as $user) {
            $user->notify(new AuctionCancelledNotification($lot));
        }

        return redirect()
            ->route('lots.index', ['tab' => $request->input('tab', 'active')])
            ->with('success', 'Lot dibatalkan.');
    }

    public function show(AuctionLot $lot)
    {
        $lot->load([
            'product.images',
            'bids.bidderProfile.user',
            'payment', // ← tambah ini
        ]);

        $highestBid = $lot->bids->sortByDesc('amount')->first();
        $bids       = $lot->bids->sortByDesc('created_at');

        return view('admin.lots.show', [
            'lot'        => $lot,
            'highestBid' => $highestBid,
            'bids'       => $bids,
        ]);
    }
}
