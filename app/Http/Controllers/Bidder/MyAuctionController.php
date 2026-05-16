<?php

namespace App\Http\Controllers\Bidder;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Bid;
use App\Models\AuctionLot;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class MyAuctionController extends Controller
{
    public function index(Request $request)
    {
        $user    = Auth::user();
        $profile = $user->bidderProfile;
        $perPage = 12;

        if (!$profile) {
            $watchlistLots   = collect();
            $watchlistLotIds = [];
            $watchlistTotal  = 0;
            $bidHistory      = collect();
            $runningBidCount   = 0;
            $endedBidCount     = 0;
        } else {
            // query dasar watchlist
            $watchlistQuery = $profile->watchlistLots()
                ->with(['product.images' => function ($q) {
                    $q->orderBy('sort_order');
                }])
                ->withMax('bids as highest_bid', 'amount')
                ->notCancelled()
                ->orderByRaw("
                    CASE
                        WHEN start_at <= ? AND end_at > ? THEN 1  -- LIVE
                        WHEN start_at > ? THEN 2                  -- UPCOMING
                        ELSE 3                                    -- ENDED
                    END
                ", [now(), now(), now()])
                ->orderBy('start_at', 'desc');

            // total semua lot di watchlist (buat kotak statistik atas)
            $watchlistTotal = (clone $watchlistQuery)->count();

            // paginate 12 per halaman
            $watchlistLots = $watchlistQuery->paginate($perPage);
            $watchlistLotIds = $watchlistLots->pluck('id')->all();

            // RIWAYAT BID – grup per lot, ambil bid TERAKHIR + riwayat lain
            $rawBids = Bid::with(['lot.bids', 'lot.product.images', 'lot.payment'])
                ->where('bidder_profile_id', $profile->id)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get();

            $bidHistory = $rawBids
                ->groupBy('lot_id')
                ->map(function ($bidsForLot) {
                    // semua bid SAYA di lot ini, dari terbaru ke lama
                    $sortedMyBids = $bidsForLot
                        ->sortByDesc('created_at')
                        ->sortByDesc('id')
                        ->values();

                    /** @var \App\Models\Bid $latest */
                    $latest = $sortedMyBids->first();
                    $lot    = $latest->lot;

                    $latest->amount_formatted = 'Rp ' . number_format($latest->amount, 0, ',', '.');
                    $totalMyBids = $sortedMyBids->count();

                    // kalau lot sudah hilang (harusnya jarang)
                    if (! $lot) {
                        $latest->rank   = null;
                        $latest->status = 'LOST'; // fallback aja

                        return [
                            'latest'        => $latest,
                            'lot'           => null,
                            'previous_bids' => collect(),
                            'total_my_bids' => $totalMyBids,
                        ];
                    }

                    // urutkan SEMUA bid di lot itu (semua peserta) utk hitung rank
                    $sortedAllBids = $lot->bids->sortBy([
                        ['amount', 'desc'],
                        ['created_at', 'asc'],
                    ])->values();

                    $rankIndex = $sortedAllBids->search(fn ($b) => $b->id === $latest->id);
                    $rank      = $rankIndex === false ? null : $rankIndex + 1;
                    $latest->rank = $rank;

                    // tentukan status utk bid TERAKHIR saya
                    $runtimeStatus = $lot->runtime_status;
                    $status        = null;

                    if ($runtimeStatus === 'ACTIVE') {
                        // lelang masih jalan → LEADING / OUTBID
                        $status = $rank === 1 ? 'LEADING' : 'OUTBID';

                    } elseif ($runtimeStatus === 'CANCELLED') {
                        // lelang dibatalkan admin/sistem
                        $status = 'CANCELLED';

                    } else {
                        // selain ACTIVE & CANCELLED kita anggap sudah berakhir
                        // (ENDED, AWARDED, UNSOLD, PENDING invoice, dll)

                        if ($lot->winner_bid_id) {
                            $status = ($latest->id === $lot->winner_bid_id) ? 'WON' : 'LOST';
                        } elseif ($rank === 1) {
                            // belum sempat set winner_bid_id tapi saya bid tertinggi
                            $status = 'WON';
                        } else {
                            $status = 'LOST';
                        }
                    }

                    $latest->status = $status;

                    return [
                        'latest'        => $latest,                 // bid terakhir saya
                        'lot'           => $lot,                    // objek lot
                        'previous_bids' => $sortedMyBids->slice(1), // bid2 saya yg lebih lama
                        'total_my_bids' => $totalMyBids,
                    ];
                })
                // urutkan lot berdasarkan kapan terakhir saya bid
                ->sortByDesc(fn ($item) => $item['latest']->created_at)
                ->values();
        }

        // total semua lot yang pernah dibid
        $totalBids = $bidHistory->count();

        // pisah: running (LEADING / OUTBID) vs ended (lainnya)
        $runningCollection = $bidHistory->filter(function ($item) {
            $status = $item['latest']->status ?? null;
            return in_array($status, ['LEADING', 'OUTBID'], true);
        })->values();

        $endedCollection = $bidHistory->reject(function ($item) {
            $status = $item['latest']->status ?? null;
            return in_array($status, ['LEADING', 'OUTBID'], true);
        })->values();

        $runningBidCount = $runningCollection->count();
        $endedBidCount   = $endedCollection->count();

        // ====== PAGINATION 10 PER HALAMAN UNTUK RIWAYAT BID ======
        $perBidPage   = 10;

        // RUNNING
        $runningPage   = max((int) $request->get('bids_running_page', 1), 1);
        $runningOffset = ($runningPage - 1) * $perBidPage;
        $runningItems  = $runningCollection->slice($runningOffset, $perBidPage)->values();

        $bidHistoryRunning = new LengthAwarePaginator(
            $runningItems,
            $runningBidCount,
            $perBidPage,
            $runningPage,
            [
                'path'     => route('my.auctions'),
                'pageName' => 'bids_running_page',
            ]
        );

        // ENDED
        $endedPage   = max((int) $request->get('bids_ended_page', 1), 1);
        $endedOffset = ($endedPage - 1) * $perBidPage;
        $endedItems  = $endedCollection->slice($endedOffset, $perBidPage)->values();

        $bidHistoryEnded = new LengthAwarePaginator(
            $endedItems,
            $endedBidCount,
            $perBidPage,
            $endedPage,
            [
                'path'     => route('my.auctions'),
                'pageName' => 'bids_ended_page',
            ]
        );

        // ==== RESPONSE AJAX UNTUK LOAD MORE WATCHLIST ====
        if ($request->ajax() && $request->boolean('watchlist')) {
            $html = view('public.partials.lot_cards', [
                'lots'            => $watchlistLots,
                'watchlistLotIds' => $watchlistLotIds,
            ])->render();

            return response()->json([
                'html'      => $html,
                'next_page' => $watchlistLots->currentPage() < $watchlistLots->lastPage()
                    ? $watchlistLots->currentPage() + 1
                    : null,
            ]);
        }

        // ==== HALAMAN BIASA ====
        return view('bidder.auctions.index', [
            'watchlistLots'     => $watchlistLots,
            'watchlistLotIds'   => $watchlistLotIds ?? [],
            'watchlistTotal'    => $watchlistTotal ?? 0,
            'bidHistory'        => $bidHistory,          
            'bidHistoryRunning' => $bidHistoryRunning,   // paginator running
            'bidHistoryEnded'   => $bidHistoryEnded,     // paginator ended
            'runningBidCount'   => $runningBidCount ?? 0,
            'endedBidCount'     => $endedBidCount ?? 0,
            'totalBids'         => $totalBids ?? 0,
        ]);
    }

    public function poll(Request $request)
    {
        $user    = $request->user();
        $profile = $user->bidderProfile;

        if (! $profile) {
            return response()->json([
                'updates' => [],
            ]);
        }

        $now = now();

        // Ambil hanya lot yang MASIH ACTIVE dan ada bid dari bidder ini
        $lots = AuctionLot::notCancelled()
            ->where('start_at', '<=', $now)
            ->where('end_at', '>', $now)
            ->whereHas('bids', function ($q) use ($profile) {
                $q->where('bidder_profile_id', $profile->id);
            })
            ->with('bids')
            ->get();

        $updates = [];

        foreach ($lots as $lot) {
            // urutkan semua bid di lot tsb: amount desc, created_at asc
            $sortedBids = $lot->bids->sortBy([
                ['amount', 'desc'],
                ['created_at', 'asc'],
            ])->values();

            foreach ($sortedBids as $index => $bid) {
                if ($bid->bidder_profile_id !== $profile->id) {
                    continue;
                }

                $rank = $index + 1;
                $status = $rank === 1 ? 'LEADING' : 'OUTBID';

                $updates[] = [
                    'bid_id' => $bid->id,
                    'lot_id' => $lot->id,
                    'rank'   => $rank,
                    'status' => $status,
                ];
            }
        }

        return response()->json([
            'updates' => $updates,
        ]);
    }

    public function toggle(Request $request, AuctionLot $lot)
    {
        $user = $request->user();

        // pastikan punya bidder_profile
        $profile = $user->bidderProfile;

        if (! $profile) {
            // fallback aman
            $msg = 'Profil bidder belum tersedia.';
            return $request->expectsJson()
                ? response()->json(['message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $existing = Watchlist::where('bidder_profile_id', $profile->id)
            ->where('lot_id', $lot->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $action = 'removed';
            $flash  = 'Lot dihapus dari watchlist Anda.';
        } else {
            Watchlist::create([
                'bidder_profile_id' => $profile->id,
                'lot_id'            => $lot->id,
            ]);
            $action = 'added';
            $flash  = 'Lot ditambahkan ke watchlist Anda.';
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'ok',
                'action'  => $action,
                'message' => $flash,
            ]);
        }

        return back()->with('success', $flash);
    }
}
