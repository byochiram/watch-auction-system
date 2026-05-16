<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;   
use App\Models\AuctionLot;
use App\Models\Bid;      
use App\Models\Product;
use App\Models\NewsletterSubscription;
use App\Models\Watchlist;
use Illuminate\Http\Request;           
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 12;
        $now     = now();

        // ambil parameter filter dari query string
        $search    = $request->input('q');
        $brand     = $request->input('brand');
        $category  = $request->input('category');
        $minPrice  = $request->input('min');
        $maxPrice  = $request->input('max');
        $status    = $request->input('status');   // live | upcoming | ended
        $sort      = $request->input('sort', 'new'); // new | ending | highest | lowest

        $query = AuctionLot::with(['product.images' => function ($q) {
                $q->orderBy('sort_order');
            }])
            ->withMax('bids as highest_bid', 'amount')
            ->notCancelled();

        // ====== FILTER: Search (brand / model / kategori) ======
        if ($search) {
            $search = trim($search);
            $like   = '%' . $search . '%';

            $query->whereHas('product', function ($q) use ($like) {
                $q->where(function ($sub) use ($like) {
                    $sub->where('brand', 'like', $like)
                        ->orWhere('model', 'like', $like)
                        ->orWhere('category', 'like', $like);
                });
            });
        }

        // ====== FILTER: Brand ======
        if ($brand) {
            $query->whereHas('product', function ($q) use ($brand) {
                $q->where('brand', $brand);
            });
        }

        // ====== FILTER: Kategori ======
        if ($category) {
            $query->whereHas('product', function ($q) use ($category) {
                $q->where('category', 'like', '%' . $category . '%');
            });
        }

        // ====== FILTER: Harga Min–Maks (pakai COALESCE current_price / start_price) ======
        if ($minPrice !== null && $minPrice !== '' && $maxPrice !== null && $maxPrice !== '' && (float)$minPrice > (float)$maxPrice) {
            $msg = 'Rentang harga tidak valid. Nilai minimum tidak boleh lebih besar dari maksimum.';

            if ($request->ajax()) {
                return response()->json([
                    'error' => $msg,
                ], 422);
            }

            return redirect()->route('home')->with('error', $msg);
        }

        // baru apply filter min/max
        if ($minPrice !== null && $minPrice !== '') {
            $query->whereRaw('COALESCE(current_price, start_price) >= ?', [$minPrice]);
        }
        if ($maxPrice !== null && $maxPrice !== '') {
            $query->whereRaw('COALESCE(current_price, start_price) <= ?', [$maxPrice]);
        }

        // ====== FILTER: Status ======
        if ($status === 'live') {
            $query->where('start_at', '<=', $now)
                ->where('end_at', '>', $now);
        } elseif ($status === 'upcoming') {
            $query->where('start_at', '>', $now);
        } elseif ($status === 'ended') {
            $query->where('end_at', '<=', $now);
        }

        // ====== SORTING ======
        switch ($sort) {
            case 'ending':   // Segera berakhir
                $query->orderBy('end_at', 'asc');
                break;

            case 'highest':  // Tawaran tertinggi
                $query->orderByRaw('COALESCE(current_price, start_price) DESC');
                break;

            case 'lowest':   // Tawaran terendah
                $query->orderByRaw('COALESCE(current_price, start_price) ASC');
                break;

            case 'new':
            default:         
                // Default: Live dulu, lalu upcoming, lalu ended
                $query->orderByRaw("
                    CASE
                        WHEN start_at <= ? AND end_at > ? THEN 1  -- LIVE
                        WHEN start_at > ? THEN 2                  -- UPCOMING
                        ELSE 3                                    -- ENDED
                    END
                ", [$now, $now, $now])
                ->orderBy('start_at', 'desc'); // di dalam masing2 grup, yang terbaru dulu
                break;
        }

        $lots = $query->paginate($perPage)->appends($request->query());

        // ambil daftar brand & kategori untuk dropdown
        $brands = Product::whereNotNull('brand')
            ->distinct()->orderBy('brand')
            ->pluck('brand');

        $rawCategories = Product::whereNotNull('category')->pluck('category');

        // pecah "Dress Watch,Field" jadi ["Dress Watch", "Field"], trim, unik & sort
        $categories = $rawCategories
            ->flatMap(function ($value) {
                return collect(explode(',', $value))
                    ->map(function ($item) {
                        return trim($item);
                    });
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // lot yang ada di watchlist user (kalau login)
        $watchlistLotIds = [];
        if (Auth::check() && Auth::user()->bidderProfile) {
            $watchlistLotIds = Watchlist::where('bidder_profile_id', Auth::user()->bidderProfile->id)
                ->pluck('lot_id')
                ->toArray();
        }

        if ($request->ajax()) {
            $html = view('public.partials.lot_cards', [
                'lots'            => $lots,
                'watchlistLotIds' => $watchlistLotIds,
            ])->render();

            return response()->json([
                'html'      => $html,
                'next_page' => $lots->currentPage() < $lots->lastPage()
                    ? $lots->currentPage() + 1
                    : null,
            ]);
        }

        return view('public.home', [
            'lots'             => $lots,
            'brands'           => $brands,
            'categories'       => $categories,
            'watchlistLotIds'  => $watchlistLotIds,
        ]);
    }

    public function show(AuctionLot $lot)
    {
        // load relasi yg dibutuhkan buat halaman detail
        $lot->load([
            'product.images' => function ($q) {
                $q->orderBy('sort_order');
            },
        ]);

        // riwayat bid pakai pagination 15 / halaman
        $bids = $lot->bids()
            ->with(['bidderProfile.user' => function ($q) {
                $q->select('id', 'name', 'username');
            }])
            ->orderByDesc('created_at')
            ->paginate(12);

        $now     = now();
        $product = $lot->product;

        // ====== REKOMENDASI LELANG LAINNYA ======
        $relatedLots = AuctionLot::with([
                'product.images' => function ($q) {
                    $q->orderBy('sort_order');
                },
            ])
            ->notCancelled()
            ->where('id', '!=', $lot->id)
            ->when($product, function ($q) use ($product) {
                $q->whereHas('product', function ($qp) use ($product) {
                    $qp->where('brand', $product->brand)
                    ->orWhere('category', $product->category);
                });
            })
            ->orderByRaw("
                CASE
                    WHEN start_at <= ? AND end_at > ? THEN 1  -- LIVE
                    WHEN start_at > ? THEN 2                  -- UPCOMING
                    ELSE 3                                    -- ENDED
                END
            ", [$now, $now, $now])
            ->orderBy('start_at', 'desc')
            ->take(4)
            ->get();
        
        // status watchlist untuk user login
        $isWatchlisted = false;
        if (auth()->check() && auth()->user()->bidderProfile) {
            $isWatchlisted = Watchlist::where('bidder_profile_id', auth()->user()->bidderProfile->id)
                ->where('lot_id', $lot->id)
                ->exists();
        }

        return view('public.show', compact('lot', 'bids', 'relatedLots', 'isWatchlisted'));
    }

    public function bid(Request $request, AuctionLot $lot)
    {
        $user = $request->user();

        // Hard guard jika akun sedang ditangguhkan
        if ($user && method_exists($user, 'isSuspended') && $user->isSuspended()) {
            $msg = 'Akun Anda sedang ditangguhkan. Anda tidak dapat melakukan bid.';

            // Kalau AJAX / fetch
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'message' => $msg,
                ], 403);
            }

            // Kalau submit biasa (non-AJAX)
            return back()->withErrors([
                'amount' => $msg,
            ]);
        }
        
        // pastikan user punya bidder_profile
        $bidderProfile = $user->bidderProfile;
        if (!$bidderProfile) {
            // untuk jaga-jaga kalau ada user lama sebelum kita auto-create
            $bidderProfile = $user->bidderProfile()->create([]);
        }

        // pastikan lot masih ACTIVE
        if ($lot->runtime_status !== 'ACTIVE') {
            return back()->withErrors([
                'amount' => 'Lelang ini sudah tidak aktif, Anda tidak dapat melakukan bid.',
            ]);
        }

        // validasi dasar
        $request->validate([
            'amount' => ['required', 'numeric'],
        ]);

        $amount = (float) $request->input('amount');

        // Konfigurasi anti-sniping (dalam detik)
        $windowSeconds = (int) config('auction.anti_sniping_window', 120);  // misal 2 menit terakhir
        $extendSeconds = (int) config('auction.anti_sniping_extend', 120);  // perpanjang 2 menit
        $antiSnipingExtended = false;

        // transaksi + row lock untuk hindari race condition
        DB::transaction(function () use (
            $lot,
            $bidderProfile,
            $amount,
            $windowSeconds,
            $extendSeconds,
            &$antiSnipingExtended
        ) {
            // refresh + lock baris lot
            $lot->refresh();
            $lot = AuctionLot::whereKey($lot->id)->lockForUpdate()->first();

            // re-check runtime status di dalam lock (hindari late bid)
            if ($lot->runtime_status !== 'ACTIVE') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'amount' => 'Lelang ini sudah tidak aktif, Anda tidak dapat melakukan bid.',
                ]);
            }

            // hitung base price & minimum berikutnya
            $base      = (float) ($lot->current_price ?? $lot->start_price);
            $increment = (float) $lot->increment;

            $minNext = $base + $increment;

            // 1) cek minimal: harus >= base + increment
            if ($amount < $minNext) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'amount' => "Minimal bid berikutnya adalah Rp " . number_format($minNext, 0, ',', '.'),
                ]);
            }

            // 2) cek kelipatan increment
            //    (pakai satuan rupiah x100 agar aman desimal)
            $step = (int) round($increment * 100);
            $diff = (int) round(($amount - $base) * 100);

            if ($step > 0 && $diff % $step !== 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'amount' => "Bid harus kelipatan Rp " . number_format($increment, 0, ',', '.')
                        . " di atas harga saat ini (Rp " . number_format($base, 0, ',', '.') . ").",
                ]);
            }

            // 3) Anti-sniping: jika bid masuk di menit/detik terakhir → perpanjang end_at
            if ($windowSeconds > 0 && $extendSeconds > 0 && $lot->end_at) {
                $nowTs   = now()->getTimestamp();
                $endAtTs = $lot->end_at->getTimestamp();
                $remaining = $endAtTs - $nowTs; // sisa waktu dalam detik

                // contoh: kalau sisa waktu 0 < t ≤ 120s → extend 120s
                if ($remaining > 0 && $remaining <= $windowSeconds) {
                    $lot->end_at = $lot->end_at->copy()->addSeconds($extendSeconds);
                    $antiSnipingExtended = true;
                }
            }

            // 4) simpan bid
            Bid::create([
                'lot_id'            => $lot->id,
                'bidder_profile_id' => $bidderProfile->id,
                'amount'            => $amount,
            ]);

            // 5) update current_price lot
            $lot->current_price = $amount;
            $lot->save();

            // 6) update statistik bidder_profile (bid_count, last_bid_at)
            $bidderProfile->increment('bid_count');
            $bidderProfile->last_bid_at = now();
            $bidderProfile->save();
        });

        // hitung ulang state setelah bid disimpan & (mungkin) end_at diperpanjang
        $lot->refresh();

        $latestBid = $lot->bids()
            ->with(['bidderProfile.user' => function ($q) {
                $q->select('id', 'username');
            }])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        $totalBids = $lot->bids()->count();

        $base      = (float) ($lot->current_price ?? $lot->start_price);
        $increment = (float) $lot->increment;
        $nextMin   = $base + $increment;

        if ($request->wantsJson()) {
            return response()->json([
                'message'               => 'Bid Anda berhasil direkam.',
                'last_bid_id'           => $latestBid->id ?? null,
                'last_amount'           => (float) ($latestBid->amount ?? $amount),
                'last_bidder'           => $latestBid->bidderProfile->user->username ?? $user->username ?? 'Anda',
                'total_bids'            => $totalBids,
                'next_min'              => $nextMin,
                'created_at'            => optional($latestBid->created_at)->format('d M Y H:i:s'),
                'runtime_status'        => $lot->runtime_status,
                'is_me'                 => true,
                'end_at_iso'            => optional($lot->end_at)->toIso8601String(),
                'anti_sniping_extended' => $antiSnipingExtended,
            ]);
        }

        return redirect()
            ->route('lots.show', $lot)
            ->with('success', 'Bid Anda berhasil direkam.');
    }

    // public function subscribeNewsletter(Request $request)
    // {
    //     $data = $request->validate([
    //         'newsletter_email' => ['required', 'email', 'max:191', 'unique:newsletter_subscriptions,email'],
    //     ], [
    //         'newsletter_email.required' => 'Email wajib diisi.',
    //         'newsletter_email.email'    => 'Format email tidak valid.',
    //         'newsletter_email.unique'   => 'Email ini sudah terdaftar.',
    //     ]);

    //     NewsletterSubscription::create([
    //         'email' => $data['newsletter_email'],
    //     ]);

    //     // kalau AJAX → balas JSON
    //     if ($request->expectsJson()) {
    //         return response()->json([
    //             'status'  => 'ok',
    //             'message' => 'Terima kasih, email Anda sudah terdaftar.',
    //         ]);
    //     }

    //     return back()->with('subscribed', true);
    // }

    public function poll(Request $request, AuctionLot $lot)
    {
        // id bid terakhir yang diketahui client (optional, untuk hemat bandwidth)
        $lastBidId = $request->query('last_bid_id');

        // refresh dari DB (end_at bisa berubah karena anti-sniping / admin)
        $lot->refresh();

        // ambil bid terbaru
        $latestBid = $lot->bids()
            ->with(['bidderProfile.user' => function ($q) {
                $q->select('id', 'username');
            }])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        $totalBids = $lot->bids()->count();
        $endAtIso  = optional($lot->end_at)->toIso8601String();

        $perPage = 12; 
        $paginationHtml = view('public.partials.bid_pagination', [
            'lot'         => $lot,
            'totalBids'   => $totalBids,
            'perPage'     => $perPage,
            'currentPage' => (int) $request->query('page', 1),
        ])->render();

        // kalau belum ada bid sama sekali
        if (! $latestBid) {
            return response()->json([
                'changed'        => false,
                'total_bids'     => $totalBids,
                'last_bid_id'    => null,
                'last_amount'    => null,
                'next_min'       => (float) $lot->start_price + (float) $lot->increment,
                'runtime_status' => $lot->runtime_status,
                'end_at_iso'     => $endAtIso,
                'pagination_html'=> $paginationHtml,
            ]);
        }

        // cek apakah ada bid baru dibanding yang diketahui client
        $changed = $lastBidId != $latestBid->id;

        if (! $changed) {
            return response()->json([
                'changed'        => false,
                'last_bid_id'    => $latestBid->id,
                'total_bids'     => $totalBids,
                'runtime_status' => $lot->runtime_status,
                'end_at_iso'     => $endAtIso,
                'pagination_html'=> $paginationHtml,
            ]);
        }

        $base      = (float) ($lot->current_price ?? $lot->start_price);
        $increment = (float) $lot->increment;
        $nextMin   = $base + $increment;

        $currentUserId = auth()->id();
        $isMe = $currentUserId
            && $latestBid->bidderProfile
            && $latestBid->bidderProfile->user_id == $currentUserId;

        return response()->json([
            'changed'        => true,
            'last_bid_id'    => $latestBid->id,
            'last_amount'    => (float) $latestBid->amount,
            'last_bidder'    => $latestBid->bidderProfile->user->username ?? 'Anonim',
            'total_bids'     => $totalBids,
            'next_min'       => $nextMin,
            'runtime_status' => $lot->runtime_status,
            'created_at'     => optional($latestBid->created_at)->format('d M Y H:i:s'),
            'is_me'         => $isMe,
            'end_at_iso'     => $endAtIso,
            'pagination_html'=> $paginationHtml,
        ]);
    }
}


