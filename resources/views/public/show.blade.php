{{-- resources/public/show.blade.php --}}
<x-guest-layout>
    <div class="max-w-screen-xl mx-auto px-4 pb-5">
        {{-- BAR ATAS: Kembali (kiri) + Watchlist (kanan) --}}
        @php
            $from = request('from');
            $backUrl   = route('home');
            $backLabel = 'Kembali';

            if ($from === 'my-auctions') {
                $backUrl   = route('my.auctions');
                $backLabel = 'Kembali ke lelang saya';
            } elseif ($from === 'transactions') {
                $backUrl   = route('transactions.index');
                $backLabel = 'Kembali ke transaksi saya';
            } elseif ($from === 'home') {
                $backUrl   = route('home');
                $backLabel = 'Kembali ke beranda';
            }
        @endphp
        <div class="flex items-center justify-between mb-6">
            {{-- Tombol Kembali --}}
            <a href="{{ $backUrl }}"
            class="inline-flex items-center gap-2
                    text-slate-700 hover:text-slate-900
                    bg-slate-50 border border-slate-200
                    px-3 py-1.5 rounded-full
                    hover:bg-slate-100 hover:border-slate-300
                    transition-all duration-150">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-3.5 w-3.5 stroke-[3]" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 19l-7-7 7-7" />
                </svg>
                <span class="text-[12px] font-semibold tracking-tight">{{ $backLabel }}</span>
            </a>

            {{-- Tombol Watchlist di ujung kanan (AJAX) --}}
            @guest
                {{-- Belum login -> arahkan ke login --}}
                <a href="{{ route('login') }}"
                    class="inline-flex items-center gap-1.5
                        rounded-full px-3 py-1.5
                        text-xs font-semibold border transition
                        bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100"
                    title="Login untuk menambahkan ke watchlist">

                    <span data-heart-pill
                        class="inline-flex h-5 w-5 items-center justify-center rounded-full
                                bg-white text-slate-500 border border-slate-200">
                        {{-- Heart outline --}}
                        <svg xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 16 16"
                            class="w-3 h-3">
                            <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15"
                                fill="currentColor"/>
                        </svg>
                    </span>

                    <span>Tambah ke watchlist</span>
                </a>
            @endguest

            @auth
                @php
                    $u = auth()->user();

                    $isBidder    = method_exists($u, 'isBidder') ? $u->isBidder() : ($u->role ?? null) === 'BIDDER';
                    $isAdmin     = ! $isBidder; // asumsi semua non-bidder = admin/superadmin
                    $isSuspended = method_exists($u,'isSuspended') && $u->isSuspended();
                    $isVerified  = method_exists($u,'hasVerifiedEmail') ? $u->hasVerifiedEmail() : !is_null($u->email_verified_at);

                    $isWatchlisted = $isWatchlisted ?? false;

                    $canWatchlist = $isBidder && $isVerified && ! $isSuspended;
                @endphp

                @if($canWatchlist)
                    {{-- Bidder + verified + not suspended -> AJAX toggle --}}
                    <button type="button"
                            data-watchlist-button
                            data-url="{{ route('watchlist.toggle', $lot) }}"
                            data-watchlisted="{{ $isWatchlisted ? '1' : '0' }}"
                            class="inline-flex items-center gap-1.5
                                rounded-full px-3 py-1.5
                                text-xs font-semibold border transition
                                {{ $isWatchlisted
                                        ? 'bg-rose-50 border-rose-200 text-rose-600 hover:bg-rose-100'
                                        : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">

                        <span data-heart-pill
                            class="inline-flex h-5 w-5 items-center justify-center rounded-full
                                    {{ $isWatchlisted
                                            ? 'bg-rose-500 text-white'
                                            : 'bg-white text-slate-500 border border-slate-200' }}">
                            {{-- Heart outline --}}
                            <svg xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 16 16"
                                class="w-3 h-3 icon-heart-outline {{ $isWatchlisted ? 'hidden' : '' }}">
                                <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15"
                                    fill="currentColor"/>
                            </svg>

                            {{-- Heart fill --}}
                            <svg xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 16 16"
                                class="w-3 h-3 icon-heart-fill {{ $isWatchlisted ? '' : 'hidden' }}">
                                <path fill-rule="evenodd"
                                    d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314"
                                    fill="currentColor"/>
                            </svg>
                        </span>

                        <span data-watchlist-label>
                            {{ $isWatchlisted ? 'Di watchlist' : 'Tambah ke watchlist' }}
                        </span>
                    </button>

                @elseif($isBidder && ! $isVerified)
                    {{-- Bidder tapi belum verifikasi -> arahkan ke halaman verifikasi --}}
                    <a href="{{ route('verification.notice') }}"
                        class="inline-flex items-center gap-1.5
                            rounded-full px-3 py-1.5
                            text-xs font-semibold border transition
                            bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100"
                        title="Verifikasi email dulu untuk menggunakan watchlist">

                        <span data-heart-pill
                            class="inline-flex h-5 w-5 items-center justify-center rounded-full
                                    bg-white text-slate-500 border border-slate-200">
                            {{-- Heart outline --}}
                            <svg xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 16 16"
                                class="w-3 h-3">
                                <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15"
                                    fill="currentColor"/>
                            </svg>
                        </span>

                        <span>Tambah ke watchlist</span>
                    </a>

                @else
                    {{-- Suspended atau Admin -> disabled --}}
                    <button type="button"
                        disabled
                        class="inline-flex items-center gap-1.5
                            rounded-full px-3 py-1.5
                            text-xs font-semibold border transition
                            bg-slate-50 border-slate-200 text-slate-400 cursor-not-allowed"
                        title="{{ $isSuspended ? 'Akun sedang ditangguhkan' : 'Admin tidak dapat menggunakan watchlist' }}">

                        <span data-heart-pill
                            class="inline-flex h-5 w-5 items-center justify-center rounded-full
                                    bg-white text-slate-400 border border-slate-200">
                            {{-- Heart outline --}}
                            <svg xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 16 16"
                                class="w-3 h-3">
                                <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15"
                                    fill="currentColor"/>
                            </svg>
                        </span>

                        <span>Tambah ke watchlist</span>
                    </button>
                @endif
            @endauth
        </div>

        @php
            $product = $lot->product;
            $images  = $product?->images ?? collect();

            // cek apakah sudah ada bid
            $hasBid = isset($bids) ? $bids->total() > 0 : ($lot->current_price !== null);

            // harga dasar untuk hitung minimal bid berikutnya
            $basePrice = $hasBid ? ($lot->current_price ?? $lot->start_price)
                                : $lot->start_price;

            // nilai yang ditampilkan sebagai "Bid Terakhir"
            $lastBidDisplay = $hasBid ? ($lot->current_price ?? $lot->start_price) : 0;

            // minimal bid berikutnya
            $nextMin = $basePrice + $lot->increment;
        @endphp

        <div class="grid lg:grid-cols-2 gap-10">
            {{-- ================== GALERI GAMBAR ================== --}}
            <div x-data="{ active: 0, zoom: false }" class="space-y-3">
                <div
                    class="aspect-[4/3] rounded-2xl overflow-hidden bg-slate-100 relative cursor-zoom-in"
                    @click="if({{ $images->count() }}) zoom = true"
                >
                    @forelse($images as $idx => $image)
                        <img
                            x-show="active === {{ $idx }}"
                            x-cloak
                            src="{{ $image->public_url ?? asset('tempus/placeholder.jpg') }}"
                            alt="{{ $product->brand }} {{ $product->model }}"
                            class="w-full h-full object-cover"
                        >
                    @empty
                        <img
                            src="{{ asset('tempus/placeholder.jpg') }}"
                            alt="No image"
                            class="w-full h-full object-cover"
                        >
                    @endforelse

                    @if($images->count() > 0)
                        <div class="absolute right-2 bottom-2 rounded-full bg-black/60 text-white text-[11px] px-2 py-0.5">
                            Klik untuk perbesar
                        </div>
                    @endif
                </div>

                {{-- THUMBNAIL BAR --}}
                @if($images->count() > 1)
                    <div class="flex gap-2 overflow-x-auto pb-1 lg:pb-8 max-w-full min-w-0">
                        @foreach($images as $idx => $image)
                            <button
                                type="button"
                                @click="active = {{ $idx }}"
                                class="relative flex-none
                                    basis-1/4 md:basis-1/5 lg:basis-auto   {{-- < lg: 4–5 thumb per layar, sisanya scroll --}}
                                    aspect-square                           {{-- selalu kotak --}}
                                    lg:w-20 lg:h-20                         {{-- baru di layar besar fixed 80px --}}
                                    rounded-lg overflow-hidden bg-slate-100 border
                                    transition ring-offset-2"
                                :class="active === {{ $idx }}
                                    ? 'ring-2 ring-slate-900 border-slate-900'
                                    : 'border-slate-200'"
                            >
                                <img
                                    src="{{ $image->public_url ?? asset('tempus/placeholder.jpg') }}"
                                    alt="Thumb {{ $idx + 1 }}"
                                    class="w-full h-full object-cover"
                                >
                            </button>
                        @endforeach
                    </div>
                @endif

                {{-- OVERLAY ZOOM FULLSCREEN --}}
                @if($images->count() > 0)
                    <div
                        x-show="zoom"
                        x-cloak
                        @keydown.escape.window="zoom = false"
                        class="fixed inset-0 z-[999] bg-black/80 flex flex-col items-center justify-center px-4"
                    >
                        <button
                            type="button"
                            class="absolute top-4 right-4 h-8 w-8 rounded-full bg-white/90 text-slate-800 flex items-center justify-center text-sm hover:bg-white"
                            @click="zoom = false"
                        >
                            ✕
                        </button>

                        <div class="max-w-4xl w-full max-h-[80vh]">
                            @foreach($images as $idx => $image)
                                <img
                                    x-show="active === {{ $idx }}"
                                    x-cloak
                                    src="{{ $image->public_url ?? asset('tempus/placeholder.jpg') }}"
                                    class="w-full h-full object-contain rounded-xl bg-black/10"
                                >
                            @endforeach
                        </div>

                        @if($images->count() > 1)
                            <div class="mt-4 flex gap-2 overflow-x-auto max-w-full">
                                @foreach($images as $idx => $image)
                                    <button
                                        type="button"
                                        @click="active = {{ $idx }}"
                                        class="flex-none h-14 w-14 rounded-lg overflow-hidden border
                                            {{ $loop->first ? 'border-yellow-400' : 'border-slate-200' }}"
                                    >
                                        <img
                                            src="{{ $image->public_url ?? asset('tempus/placeholder.jpg') }}"
                                            class="w-full h-full object-cover"
                                        >
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Detail Produk (khusus layar besar, di bawah gambar) --}}
                <div class="hidden lg:block border border-slate-200 rounded-xl p-4">
                    <h2 class="text-sm font-semibold text-slate-700 mb-3 uppercase tracking-wide">
                        Detail Produk
                    </h2>
                    <dl class="grid grid-cols-2 gap-y-2 text-sm">
                        <div>
                            <dt class="text-slate-500">Brand</dt>
                            <dd class="font-medium text-slate-900">{{ $product->brand ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Model</dt>
                            <dd class="font-medium text-slate-900">{{ $product->model ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Tahun</dt>
                            <dd class="font-medium text-slate-900">{{ $product->year ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Kondisi</dt>
                            <dd class="font-medium text-slate-900">{{ $product->condition ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Berat (gram)</dt>
                            <dd class="font-medium text-slate-900">{{ $product->weight_grams ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Kategori</dt>
                            <dd class="font-medium text-slate-900">{{ $product->category ?? '-' }}</dd>
                        </div>
                    </dl>

                    @if($product?->description)
                        <div class="mt-3 text-sm">
                            <dt class=" text-slate-500 mb-2">Deskripsi</dt>
                            <div class="font-medium prose prose-sm max-w-none text-slate-900">
                                {!! nl2br(e($product->description)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ================== DETAIL & BID SECTION ================== --}}
            <div class="space-y-6">
                {{-- Judul --}}
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mb-1">
                            {{ $product->brand ?? '-' }} {{ $product->model ?? '' }}
                        </h1>
                        <p class="text-sm text-slate-500">
                            Lot #{{ $lot->id }}
                        </p>
                    </div>
                </div>

                {{-- Detail Produk (mobile & tablet) --}}
                <div class="border border-slate-200 rounded-xl p-4 lg:hidden">
                    <h2 class="text-sm font-semibold text-slate-700 mb-3 uppercase tracking-wide">
                        Detail Produk
                    </h2>
                    <dl class="grid grid-cols-2 gap-y-2 text-sm">
                        <div>
                            <dt class="text-slate-500">Brand</dt>
                            <dd class="font-medium text-slate-900">{{ $product->brand ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Model</dt>
                            <dd class="font-medium text-slate-900">{{ $product->model ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Tahun</dt>
                            <dd class="font-medium text-slate-900">{{ $product->year ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Kondisi</dt>
                            <dd class="font-medium text-slate-900">{{ $product->condition ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Berat (gram)</dt>
                            <dd class="font-medium text-slate-900">{{ $product->weight_grams ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Kategori</dt>
                            <dd class="font-medium text-slate-900">{{ $product->category ?? '-' }}</dd>
                        </div>
                    </dl> 
                    @if($product?->description)
                        <div class="mt-3 text-sm">
                            <dt class=" text-slate-500 mb-2">Deskripsi</dt>
                            <div class="font-medium prose prose-sm max-w-none text-slate-900">
                                {!! nl2br(e($product->description)) !!}
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Detail Lelang + Form Bid --}}
                <div class="border border-slate-200 rounded-xl p-4 space-y-4">
                    <h2 class="text-sm font-semibold text-slate-700 mb-3 uppercase tracking-wide">
                        Detail Lelang
                    </h2>

                    @php
                        $runtime     = $lot->runtime_status;
                        $isLive     = $lot->runtime_status === 'ACTIVE';
                        $isUpcoming = $lot->runtime_status === 'SCHEDULED';
                        $isCancelled = $runtime === 'CANCELLED';
                        
                        $label = match (true) {
                            $isLive      => 'Live',
                            $isUpcoming  => 'Segera',
                            $isCancelled => 'Dibatalkan',
                            default      => 'Selesai',
                        };

                        $labelClass = match (true) {
                            $isLive      => 'bg-emerald-600/95',
                            $isUpcoming  => 'bg-yellow-500/95',
                            $isCancelled => 'bg-slate-700/95',
                            default      => 'bg-red-700/95',
                        };

                        // target waktu untuk countdown
                        $targetIso = $isCancelled
                            ? null
                            : ($isUpcoming
                                ? optional($lot->start_at)->toIso8601String()
                                : optional($lot->end_at)->toIso8601String());
                    @endphp

                    {{-- STATUS + PERIODE + COUNTDOWN --}}
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            <span id="lot-status-badge"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold text-white {{ $labelClass }}">
                                <span id="lot-status-text">{{ $label }}</span>
                            </span>
                            <span class="text-[13px] sm:text-sm text-slate-500">
                                Mulai: {{ optional($lot->start_at)->format('d M Y H:i') }} •
                                Berakhir:
                                <span id="lot-end-label">
                                    {{ optional($lot->end_at)->format('d M Y H:i') }}
                                </span>
                            </span>
                        </div>

                        <p id="lot-countdown-label"
                            class="text-[11px] sm:text-xs font-semibold {{ $isUpcoming ? 'text-amber-700' : ($isLive ? 'text-emerald-700' : 'hidden') }}">
                                {{ $isUpcoming ? 'Dimulai dalam' : ($isLive ? 'Berakhir dalam' : '') }}
                        </p>

                        {{-- COUNTDOWN --}}
                        @if($lot->runtime_status === 'CANCELLED')
                            <div id="lot-countdown"
                                data-status="{{ $lot->runtime_status }}"
                                class="grid grid-cols-4 gap-1 text-center text-[11px] font-semibold">
                                <div class="col-span-4 flex items-center justify-center py-2 rounded-md bg-slate-900 text-white text-xs font-semibold">
                                    Lelang dibatalkan
                                </div>
                            </div>
                        @else
                            <div id="lot-countdown"
                                data-time="{{ $targetIso }}"
                                data-status="{{ $lot->runtime_status }}"
                                class="grid grid-cols-4 gap-1 text-center text-[11px] font-semibold">
                                <div class="flex flex-col items-center py-2 rounded-md bg-slate-100 text-slate-800">
                                    <span data-part="days" class="font-mono text-base">00</span>
                                    <span class="uppercase tracking-tight text-[9px]">Hari</span>
                                </div>
                                <div class="flex flex-col items-center py-2 rounded-md bg-slate-100 text-slate-800">
                                    <span data-part="hours" class="font-mono text-base">00</span>
                                    <span class="uppercase tracking-tight text-[9px]">Jam</span>
                                </div>
                                <div class="flex flex-col items-center py-2 rounded-md bg-slate-100 text-slate-800">
                                    <span data-part="minutes" class="font-mono text-base">00</span>
                                    <span class="uppercase tracking-tight text-[9px]">Menit</span>
                                </div>
                                <div class="flex flex-col items-center py-2 rounded-md bg-slate-100 text-slate-800">
                                    <span data-part="seconds" class="font-mono text-base">00</span>
                                    <span class="uppercase tracking-tight text-[9px]">Detik</span>
                                </div>
                            </div>
                        @endif
                        @if($lot->runtime_status === 'CANCELLED' && $lot->cancel_reason)
                            <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                                    Alasan Pembatalan
                                </p>

                                <p class="text-sm text-slate-700 leading-relaxed">
                                    {{ $lot->cancel_reason }}
                                </p>

                                <p class="mt-2 text-xs text-slate-500 leading-relaxed">
                                    Kami mohon maaf atas ketidaknyamanan yang ditimbulkan.
                                    Seluruh bid yang telah masuk dibatalkan dan tidak diproses lebih lanjut.
                                    Silakan hubungi admin apabila Anda memerlukan bantuan.
                                </p>
                            </div>
                        @endif
                    </div>

                    <dl class="grid grid-cols-2 gap-y-2 text-sm mb-3">
                        <div>
                            <dt class="text-slate-500">Harga Awal</dt>
                            <dd class="font-semibold text-slate-900">
                                Rp {{ number_format($lot->start_price, 0, ',', '.') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Kelipatan Bid</dt>
                            <dd class="font-semibold text-slate-900">
                                Rp {{ number_format($lot->increment, 0, ',', '.') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Bid Terakhir</dt>
                            <dd class="font-semibold text-emerald-700">
                                <span id="last-bid-amount">
                                    Rp {{ number_format($lastBidDisplay, 0, ',', '.') }}
                                </span>
                            </dd>
                        </div>    
                        <div>
                            <dt class="text-slate-500">Minimal Bid Berikutnya</dt>
                            <dd class="font-semibold text-slate-900">
                                <span id="next-min-amount">
                                    Rp {{ number_format($nextMin, 0, ',', '.') }}
                                </span>
                            </dd>
                        </div>
                    </dl>

                    @php
                        $runtime    = $lot->runtime_status;    
                        $isLive     = $lot->runtime_status === 'ACTIVE';
                        $isUpcoming = $lot->runtime_status === 'SCHEDULED';
                        $isCancelled  = $runtime === 'CANCELLED';
                        $isEnded      = ! $isLive && ! $isUpcoming && ! $isCancelled;

                        $statusMessage = null;
                        $statusColor   = '';

                        if ($isUpcoming) {
                            $statusMessage = 'Lelang belum dimulai. Anda dapat memasukkan nominal nanti saat lelang sudah Live.';
                            $statusColor   = 'text-amber-700';
                        } elseif ($isCancelled) {
                            $statusMessage = null;
                        } elseif ($isEnded) {
                            $statusMessage = 'Lelang ini telah berakhir. Anda tidak dapat lagi melakukan bid.';
                            $statusColor   = 'text-red-700';
                        }

                        $userHasBid    = false;
                        $userIsLeading = false;
                        $userWon       = false;
                        $userLost      = false;

                        if (auth()->check() && auth()->user()->isBidder()) {
                            $profile = auth()->user()->bidderProfile;

                            if ($profile) {
                                $userHasBid = \App\Models\Bid::where('lot_id', $lot->id)
                                    ->where('bidder_profile_id', $profile->id)
                                    ->exists();

                                if ($userHasBid && $isLive) {
                                    $topBid = \App\Models\Bid::where('lot_id', $lot->id)
                                        ->orderByDesc('amount')
                                        ->orderBy('created_at')
                                        ->first();

                                    if ($topBid && $topBid->bidder_profile_id === $profile->id) {
                                        $userIsLeading = true;
                                    }
                                }

                                if ($userHasBid && $isEnded) {
                                    $winningBid = $lot->winner_bid_id
                                        ? \App\Models\Bid::find($lot->winner_bid_id)
                                        : \App\Models\Bid::where('lot_id', $lot->id)
                                            ->orderByDesc('amount')
                                            ->orderBy('created_at')
                                            ->first();

                                    if ($winningBid && $winningBid->bidder_profile_id === $profile->id) {
                                        $userWon  = true;
                                        $userLost = false;
                                    } else {
                                        $userWon  = false;
                                        $userLost = true;
                                    }
                                }
                            }
                        }
                    @endphp

                    @auth
                        @if(auth()->user()->isBidder())
                            @php
                                $bidder           = auth()->user()->bidderProfile;
                                $user             = auth()->user();
                                $isSuspended      = $user && method_exists($user, 'isSuspended') && $user->isSuspended();
                                
                                // lelang live + user TIDAK suspended
                                $canBidAuction = $isLive && ! $isSuspended;
                                $canBidForUser = $canBidAuction;
                            @endphp

                            {{-- Bungkus form + modal dengan Alpine state --}}
                            <div>
                                <form action="{{ route('lots.bid', $lot) }}" method="POST" class="space-y-3" data-bid-form>
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">
                                            Masukkan Tawaran Anda
                                        </label>
                                        <div class="flex gap-2">
                                            <div class="relative flex-1">
                                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-slate-500">
                                                    Rp
                                                </span>

                                                {{-- input yang kelihatan user --}}
                                                <input
                                                    type="text"
                                                    name="amount_formatted"
                                                    id="amount-visible"
                                                    class="w-full rounded-lg border-slate-300 pl-10 pr-3 py-2 text-sm
                                                        focus:border-slate-400 focus:ring-slate-300
                                                        {{ ! $canBidAuction ? 'bg-slate-50 text-slate-400 cursor-not-allowed' : '' }}"
                                                    autocomplete="off"
                                                    @if(! $canBidAuction) disabled @endif
                                                    required
                                                >

                                                {{-- hidden input yang dikirim ke backend --}}
                                                <input type="hidden" name="amount" id="amount-hidden">
                                            </div>

                                            {{-- Tombol Bid --}}
                                            @if($canBidForUser)
                                                {{-- Lelang live & akun tidak suspended -> submit normal --}}
                                                <button type="submit"
                                                        class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold
                                                            bg-slate-900 text-white hover:bg-slate-800">
                                                    Bid Sekarang
                                                </button>
                                            @else
                                                {{-- Lelang belum mulai / sudah berakhir / suspended --}}
                                                <button type="button"
                                                        class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold
                                                            bg-slate-200 text-slate-500 cursor-not-allowed">
                                                    Bid Sekarang
                                                </button>
                                            @endif
                                        </div>

                                        @error('amount')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror

                                        <p id="lot-bid-status-message"
                                            class="mt-1 text-xs font-medium {{ $statusMessage ? $statusColor : 'hidden' }}">
                                                {{ $statusMessage ?? '' }}
                                        </p>
                                    </div>
                                </form>
                            </div>
                        @else
                            {{-- Admin / Superadmin tidak boleh bid --}}
                            <p class="text-sm text-slate-600">
                                Anda saat ini login sebagai akun admin.
                                Akun admin tidak dapat mengikuti lelang.
                                Jika ingin melakukan bid, silakan gunakan akun bidder.
                            </p>
                        @endif
                    @else
                        <p class="text-sm text-slate-600">
                            Untuk melakukan bid, silakan
                            <a href="{{ route('login') }}" class="text-slate-900 font-semibold underline">login</a>
                            atau
                            <a href="{{ route('register') }}" class="text-slate-900 font-semibold underline">daftar akun</a>.
                        </p>
                    @endauth

                    {{-- Info status: kamu lagi leading / sudah ter-outbid / menang / kalah --}}
                    @if(auth()->check() && auth()->user()->isBidder())
                        <div id="my-bid-status-wrapper" class="mt-3">
                            @if($userHasBid)
                                @if($isLive)
                                    {{-- Lelang masih berjalan --}}
                                    <div
                                        id="my-bid-status-pill"
                                        class="inline-flex items-center rounded-full px-3 py-1 text-[12px] font-medium
                                            {{ $userIsLeading ? 'bg-emerald-50 text-emerald-800' : 'bg-amber-50 text-amber-800' }}">
                                        @if($userIsLeading)
                                            Kamu sedang memimpin lelang dengan bid tertinggi.
                                        @else
                                            Kamu sudah ter-outbid. Peserta lain memiliki bid yang lebih tinggi.
                                        @endif
                                    </div>
                                @elseif($isEnded)
                                    {{-- Lelang sudah berakhir --}}
                                    <div id="my-bid-status-pill"
                                        class="inline-flex flex-col sm:flex-row sm:flex-wrap items-start sm:items-center gap-1
                                            rounded-full sm:rounded-xl px-3 py-2
                                            text-[12px] font-medium
                                            {{ $userWon ? 'bg-emerald-50 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">

                                        @if($userWon)
                                            <span class="leading-snug block">
                                                Selamat! Anda memenangkan lelang ini.
                                            </span>

                                            <a href="{{ route('transactions.index') }}"
                                            class="underline font-semibold leading-snug block">
                                                 Selesaikan pembayaran segera.
                                            </a>
                                        @else
                                            <span class="leading-snug block">
                                                Lelang ini telah berakhir dan Anda tidak memenangkan lot ini.
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            @endif
                        </div>
                    @else
                        <div id="my-bid-status-wrapper" class="mt-3"></div>
                    @endif
                </div>

                {{-- ================== RIWAYAT BID ================== --}}
                <div class="mt-10 lg:pt-5">
                    @php
                        /** @var \Illuminate\Pagination\LengthAwarePaginator $bids */
                        $totalBids = $bids->total();
                    @endphp

                    <div class="flex items-center justify-between mb-3" id="bid-history-header">
                        <div class="space-y-1">
                            <h2 class="text-lg font-semibold text-slate-900">Riwayat Bid</h2>
                        </div>

                        @if($totalBids > 0)
                            <span id="total-bids-pill"
                                class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-[12px] font-medium text-slate-700">
                                {{ $totalBids }} tawaran masuk
                            </span>
                        @endif
                    </div>

                    {{-- Empty state (hanya tampak kalau belum ada bid) --}}
                    <div id="bid-history-empty" class="{{ $totalBids === 0 ? '' : 'hidden' }}">
                        <div class="border border-dashed border-slate-300 rounded-xl bg-slate-50 px-4 py-6 flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v6l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Belum ada tawaran.</p>
                                <p class="text-xs text-slate-500">Jadilah yang pertama melakukan bid.</p>
                            </div>
                        </div>
                    </div>
                    {{-- Table riwayat bid (selalu ada, tapi bisa hidden) --}}
                    <div id="bid-history-table-container"
                        class="overflow-x-auto border border-slate-200 rounded-xl shadow-sm bg-white {{ $totalBids === 0 ? 'hidden' : '' }}">
                        <table class="min-w-full">
                            <thead class="bg-slate-50/80">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs md:text-sm font-semibold text-slate-600">Waktu</th>
                                    <th class="px-4 py-2 text-left text-xs md:text-sm font-semibold text-slate-600">Pengguna</th>
                                    <th class="px-4 py-2 text-right text-xs md:text-sm font-semibold text-slate-600">Nominal</th>
                                </tr>
                            </thead>
                            <tbody id="bid-history-body">
                            @foreach($bids as $bid)
                                <tr class="
                                    border-t border-slate-100
                                    hover:bg-slate-50 transition-colors
                                    {{ $loop->first && $bids->currentPage() === 1 ? 'latest-bid-row bg-emerald-50' : '' }}"
                                    data-bid-id="{{ $bid->id }}"
                                >
                                    <td class="px-4 py-2 text-xs lg:text-sm text-slate-700 whitespace-nowrap">
                                        {{ optional($bid->created_at)->format('d M Y H:i:s') }}
                                    </td>

                                    <td class="px-4 py-2 text-xs lg:text-sm text-slate-700">
                                        @php
                                            $authId   = auth()->id();
                                            $isMe     = $authId && $bid->bidderProfile && $bid->bidderProfile->user_id == $authId;
                                            $username = $bid->bidderProfile->user->username ?? 'Anonim';
                                        @endphp

                                        @if($isMe)
                                            {{-- username kamu dibikin chip khusus --}}
                                            <span class="inline-flex items-center rounded-full bg-slate-900 text-white px-2 py-0.5 text-[11px]">
                                                {{ $username }}
                                            </span>
                                        @else
                                            <span class="text-slate-700">
                                                {{ $username }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-2 text-xs lg:text-sm text-right whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-900">
                                            Rp {{ number_format($bid->amount, 0, ',', '.') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @php
                        $perPage = $bids->perPage();
                    @endphp
                    <div id="bid-history-pagination" class="mt-4 flex justify-end {{ $bids->hasPages() ? '' : 'hidden' }}">
                        @include('public.partials.bid_pagination', [
                            'lot'         => $lot,
                            'totalBids'   => $totalBids,
                            'perPage'     => $perPage,
                            'currentPage' => $bids->currentPage(),
                        ])
                    </div>
                </div>
            </div>
        </div>

        {{-- ================== REKOMENDASI LELANG LAINNYA ================== --}}
        @if(isset($relatedLots) && $relatedLots->isNotEmpty())
            <div class="mt-8 border-t border-slate-200 pt-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-900">
                        Lelang lainnya yang mungkin Anda suka
                    </h2>

                    {{-- Desktop / tablet: tombol di kanan atas --}}
                    <a href="{{ route('home') }}"
                        class="hidden sm:inline-flex items-center gap-2
                                text-slate-700 hover:text-slate-900
                                bg-slate-50 border border-slate-200
                                px-3 py-1.5 rounded-full text-[12px] font-semibold tracking-tight
                                hover:bg-slate-100 hover:border-slate-300
                                transition-all duration-150">
                        <span>Lihat semua lelang</span>
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-3.5 w-3.5 stroke-[3]" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($relatedLots as $related)
                        @php
                            $p           = $related->product;
                            $img         = optional($p?->images->first())->public_url ?? asset('tempus/placeholder.jpg');
                            $displayPrice = $related->current_price ?? $related->start_price;

                            // status badge biar konsisten dengan halaman detail
                            $isLive     = $related->runtime_status === 'ACTIVE';
                            $isUpcoming = $related->runtime_status === 'SCHEDULED';
                            $label      = $isLive ? 'Live' : ($isUpcoming ? 'Segera Dimulai' : 'Selesai');
                            $badgeClass = $isLive
                                ? 'bg-emerald-600/95'
                                : ($isUpcoming ? 'bg-yellow-500/95' : 'bg-red-700/95');
                        @endphp

                        <a href="{{ route('lots.show', $related) }}"
                        class="group rounded-xl border border-slate-200 bg-white overflow-hidden
                                hover:shadow-md hover:border-slate-300 transition">
                            <div class="relative aspect-[4/3] bg-slate-100 overflow-hidden">
                                <img src="{{ $img }}"
                                    alt="{{ $p->brand ?? '' }} {{ $p->model ?? '' }}"
                                    class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform">

                                {{-- Badge status kecil di pojok kiri atas --}}
                                <span class="absolute left-2 top-2 inline-flex items-center px-2 py-0.5
                                            rounded-full text-[10px] font-semibold text-white {{ $badgeClass }}">
                                    {{ $label }}
                                </span>
                            </div>

                            <div class="p-3 space-y-1.5">
                                <p class="text-[11px] uppercase tracking-wide text-slate-400">
                                    {{ $p->brand ?? 'Unknown Brand' }}
                                </p>
                                <p class="text-sm font-semibold text-slate-900">
                                    {{ $p->model ?? '-' }}
                                </p>
                                @if($p?->category)
                                    <p class="text-xs text-slate-500">
                                        {{ $p->category }}
                                    </p>
                                @endif
                                <p class="pt-1 text-xs text-slate-600">
                                    Harga saat ini
                                    <span class="font-semibold text-slate-900">
                                        Rp {{ number_format($displayPrice, 0, ',', '.') }}
                                    </span>
                                </p>
                            </div>
                        </a>
                    @endforeach

                </div>
                {{-- Mobile: tombol di kanan bawah grid --}}
                <div class="mt-4 flex justify-end sm:hidden">
                    <a href="{{ route('home') }}"
                    class="inline-flex items-center gap-2
                            text-slate-700 hover:text-slate-900
                            bg-slate-50 border border-slate-200
                            px-3 py-1.5 rounded-full text-[12px] font-semibold tracking-tight
                            hover:bg-slate-100 hover:border-slate-300
                            transition-all duration-150">
                        <span>Lihat semua lelang</span>
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-3.5 w-3.5 stroke-[3]" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        @endif

        <div id="bid-poll-meta"
            data-url="{{ route('lots.poll', $lot) }}"
            data-initial-last-bid-id="{{ $bids->first()->id ?? '' }}"
            data-current-page="{{ $bids->currentPage() }}">
        </div>

        <div id="my-bid-state"
            data-has-bid="{{ $userHasBid ? '1' : '0' }}"
            data-is-leading="{{ $userIsLeading ? '1' : '0' }}"
            data-is-won="{{ $userWon ? '1' : '0' }}"
            data-transactions-url="{{ route('transactions.index') }}">
        </div>
    </div>

    <script>
        // Helper: sinkronisasi badge, label countdown, dan form bid dengan status runtime lot
        window.TempusLotUi = {
            syncStatus(status) {
                const s          = (status || '').toUpperCase();
                const isLive     = s === 'ACTIVE';
                const isUpcoming = s === 'SCHEDULED';
                const isCancelled = s === 'CANCELLED';
                const isEnded     = !isLive && !isUpcoming && !isCancelled;

                const badge      = document.getElementById('lot-status-badge');
                const badgeText  = document.getElementById('lot-status-text');
                const labelEl    = document.getElementById('lot-countdown-label');
                const msgEl      = document.getElementById('lot-bid-status-message');
                const form       = document.querySelector('[data-bid-form]');
                const submitBtn  = form ? form.querySelector('button[type="submit"]') : null;
                const amountInput = document.getElementById('amount-visible');

                // Badge status: Live / Segera / Selesai
                if (badge && badgeText) {
                    badge.classList.remove('bg-emerald-600/95', 'bg-yellow-500/95', 'bg-red-700/95', 'bg-slate-700/95');

                    if (isLive) {
                        badge.classList.add('bg-emerald-600/95');
                        badgeText.textContent = 'Live';
                    } else if (isUpcoming) {
                        badge.classList.add('bg-yellow-500/95');
                        badgeText.textContent = 'Segera';
                    } else if (isCancelled) {
                        badge.classList.add('bg-slate-700/95');
                        badgeText.textContent = 'Dibatalkan';
                    } else {
                        badge.classList.add('bg-red-700/95');
                        badgeText.textContent = 'Selesai';
                    }
                }

                // Label di atas countdown
                if (labelEl) {
                    labelEl.classList.remove('text-amber-700', 'text-emerald-700', 'hidden');

                    if (isUpcoming) {
                        labelEl.textContent = 'Dimulai dalam';
                        labelEl.classList.add('text-amber-700');
                    } else if (isLive) {
                        labelEl.textContent = 'Berakhir dalam';
                        labelEl.classList.add('text-emerald-700');
                    } else {
                        labelEl.textContent = '';
                        labelEl.classList.add('hidden');
                    }
                }

                // Pesan status di bawah input
                if (msgEl) {
                    msgEl.classList.remove('text-amber-700', 'text-red-700', 'text-slate-700', 'hidden');

                    if (isUpcoming) {
                        msgEl.textContent = 'Lelang belum dimulai. Anda dapat memasukkan nominal nanti saat lelang sudah Live.';
                        msgEl.classList.add('text-amber-700');
                    } else if (isCancelled) {
                        msgEl.textContent = '';
                        msgEl.classList.add('hidden');
                    } else if (isEnded) {
                        msgEl.textContent = 'Lelang ini telah berakhir. Anda tidak dapat lagi melakukan bid.';
                        msgEl.classList.add('text-red-700');
                    } else {
                        msgEl.textContent = '';
                        msgEl.classList.add('hidden');
                    }
                }

                // Enable / disable form bid – hanya berlaku kalau ada tombol submit
                if (form && submitBtn && amountInput) {
                    if (isLive) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('bg-slate-200', 'text-slate-500', 'cursor-not-allowed');
                        submitBtn.classList.add('bg-slate-900', 'text-white', 'hover:bg-slate-800');

                        amountInput.disabled = false;
                        amountInput.classList.remove('bg-slate-50', 'text-slate-400', 'cursor-not-allowed');
                    } else {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('bg-slate-200', 'text-slate-500', 'cursor-not-allowed');
                        submitBtn.classList.remove('bg-slate-900', 'text-white', 'hover:bg-slate-800');

                        amountInput.disabled = true;
                        amountInput.classList.add('bg-slate-50', 'text-slate-400', 'cursor-not-allowed');
                    }
                }

                // === Status pribadi: menang / kalah ketika lelang berakhir ===
                const myBidState  = document.getElementById('my-bid-state');
                const myBidWrapper = document.getElementById('my-bid-status-wrapper');

                if (isCancelled && myBidState && myBidWrapper) {
                    // Untuk status CANCELLED:
                    // pesan sudah ditampilkan di blok "Alasan Pembatalan"
                    // jadi jangan tampilkan status pribadi tambahan
                    const existing = document.getElementById('my-bid-status-pill');
                    if (existing) existing.remove();
                    return;
                }

                if (isEnded && myBidState && myBidWrapper) {
                    const hasBid   = myBidState.dataset.hasBid === '1';
                    if (!hasBid) {
                        // kalau nggak pernah bid, hapus pill kalau ada
                        const pillExisting = document.getElementById('my-bid-status-pill');
                        if (pillExisting) pillExisting.remove();
                    } else {
                        const isWonFlag   = myBidState.dataset.isWon === '1';
                        const wasLeading  = myBidState.dataset.isLeading === '1';
                        const transactionsUrl = myBidState.dataset.transactionsUrl;

                        // kalau server belum set is_won, pakai fallback: lagi leading = menang
                        const isWon = isWonFlag || wasLeading;

                        let pill = document.getElementById('my-bid-status-pill');
                        if (!pill) {
                            pill = document.createElement('div');
                            pill.id = 'my-bid-status-pill';
                            pill.className = 'inline-flex flex-wrap items-center gap-1 rounded-full px-3 py-1 text-[11px] font-medium';
                            myBidWrapper.appendChild(pill);
                        }

                        pill.classList.remove(
                            'bg-emerald-50','text-emerald-800',
                            'bg-amber-50','text-amber-800',
                            'bg-slate-100','text-slate-700'
                        );

                        if (isWon) {
                            pill.classList.add('bg-emerald-50','text-emerald-800');
                            if (transactionsUrl) {
                                pill.innerHTML =
                                    'Selamat! Anda memenangkan lelang ini. ' +
                                    `<a href="${transactionsUrl}" class="underline font-semibold">Selesaikan pembayaran segera.`;
                            } else {
                                pill.textContent = 'Selamat! Anda memenangkan lelang ini.';
                            }
                        } else {
                            pill.classList.add('bg-slate-100','text-slate-700');
                            pill.textContent = 'Lelang ini telah berakhir dan Anda tidak memenangkan lot ini.';
                        }
                    }
                }
            }
        };

        // Jalankan sekali di awal
        document.addEventListener('DOMContentLoaded', () => {
            const box = document.getElementById('lot-countdown');
            if (box && box.dataset.status && window.TempusLotUi) {
                window.TempusLotUi.syncStatus(box.dataset.status);
            }
        });
    </script>

    {{-- Countdown script --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const box = document.getElementById('lot-countdown');
            if (!box) return;

            const pad = (n) => n.toString().padStart(2, '0');

            function renderFinished(status) {
                const s = (status || '').toUpperCase();
                let msg;
                if (s === 'SCHEDULED') {
                    msg = 'Lelang akan segera dimulai';
                } else if (s === 'CANCELLED') {
                    msg = 'Lelang dibatalkan';
                } else {
                    msg = 'Lelang telah berakhir';
                }

                box.innerHTML = `
                    <div class="col-span-4 flex items-center justify-center py-2 rounded-md bg-slate-900 text-white text-xs font-semibold">
                        ${msg}
                    </div>
                `;
            }

            function tick() {
                const timeIso = box.dataset.time;
                let status    = (box.dataset.status || '').toUpperCase();

                if (!timeIso) return;

                const targetTime = new Date(timeIso).getTime();
                const now        = Date.now();
                const diff       = targetTime - now;

                if (diff <= 0) {
                    // Untuk status ACTIVE (lelang live) → anggap selesai di sisi UI
                    if (status !== 'SCHEDULED' && status !== 'CANCELLED') {
                        status = 'ENDED';
                        box.dataset.status = status;
                        if (window.TempusLotUi && typeof window.TempusLotUi.syncStatus === 'function') {
                            window.TempusLotUi.syncStatus(status);
                        }
                    }

                    renderFinished(status);
                    return;
                }

                const totalSeconds = Math.floor(diff / 1000);
                const days    = Math.floor(totalSeconds / (60 * 60 * 24));
                const hours   = Math.floor((totalSeconds % (60 * 60 * 24)) / (60 * 60));
                const minutes = Math.floor((totalSeconds % (60 * 60)) / 60);
                const seconds = totalSeconds % 60;

                const daysEl    = box.querySelector('[data-part="days"]');
                const hoursEl   = box.querySelector('[data-part="hours"]');
                const minutesEl = box.querySelector('[data-part="minutes"]');
                const secondsEl = box.querySelector('[data-part="seconds"]');

                if (!daysEl || !hoursEl || !minutesEl || !secondsEl) return;

                daysEl.textContent    = pad(days);
                hoursEl.textContent   = pad(hours);
                minutesEl.textContent = pad(minutes);
                secondsEl.textContent = pad(seconds);
            }

            tick();
            setInterval(tick, 1000);
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const visible = document.getElementById('amount-visible');
            const hidden  = document.getElementById('amount-hidden');

            if (!visible || !hidden) return;

            const formatter = new Intl.NumberFormat('id-ID');

            // nilai awal dari server (next minimal bid)
            const initialRaw = '{{ old('amount', $nextMin) }}';

            function setFromRaw(raw) {
                const cleaned = (raw || '').toString().replace(/\D/g, '');
                hidden.value  = cleaned;
                visible.value = cleaned ? formatter.format(cleaned) : '';
            }

            // set awal (langsung muncul 503.500.000 dll)
            setFromRaw(initialRaw);

            visible.addEventListener('input', () => {
                const raw = visible.value.replace(/\./g, '');
                setFromRaw(raw);
            });

            visible.addEventListener('blur', () => {
                const raw = visible.value.replace(/\./g, '');
                setFromRaw(raw);
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrf) return;

            document.addEventListener('click', async (event) => {
                const btn = event.target.closest('[data-watchlist-button]');
                if (!btn) return;

                event.preventDefault();

                const url  = btn.dataset.url;
                if (!url) return;

                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (!res.ok) throw new Error('Request gagal');

                    const data = await res.json();
                    const added = data.action === 'added';

                    // update state data
                    btn.dataset.watchlisted = added ? '1' : '0';

                    // elemen-elemen di dalam tombol
                    const pill        = btn.querySelector('[data-heart-pill]');
                    const iconOutline = btn.querySelector('.icon-heart-outline');
                    const iconFill    = btn.querySelector('.icon-heart-fill');
                    const label       = btn.querySelector('[data-watchlist-label]');

                    // update kelas tombol utama
                    btn.classList.toggle('bg-rose-50', added);
                    btn.classList.toggle('border-rose-200', added);
                    btn.classList.toggle('text-rose-600', added);

                    btn.classList.toggle('bg-slate-50', !added);
                    btn.classList.toggle('border-slate-200', !added);
                    btn.classList.toggle('text-slate-600', !added);

                    // update pill icon
                    if (pill) {
                        pill.className =
                            'inline-flex h-5 w-5 items-center justify-center rounded-full ' +
                            (added
                                ? 'bg-rose-500 text-white'
                                : 'bg-white text-slate-500 border border-slate-200');
                    }

                    if (iconOutline && iconFill) {
                        iconOutline.classList.toggle('hidden', added);
                        iconFill.classList.toggle('hidden', !added);
                    }

                    if (label) {
                        label.textContent = added ? 'Di watchlist' : 'Tambah ke watchlist';
                    }
                } catch (err) {
                    console.error(err);
                    // kalau mau, bisa tambahkan toast kecil/console log saja, tanpa alert besar
                }
            });
        });
    </script>

    <script>
        // Helper: pastikan tabel riwayat bid muncul ketika sudah ada bid
        window.TempusBidHistory = {
            ensureTableVisible(totalBids) {
                const tableWrapper = document.getElementById('bid-history-table-container');
                const emptyState   = document.getElementById('bid-history-empty');
                const header       = document.getElementById('bid-history-header');
                const tbody        = document.getElementById('bid-history-body');

                if (!tableWrapper || !tbody) return;

                if (totalBids > 0 && tableWrapper.classList.contains('hidden')) {
                    tableWrapper.classList.remove('hidden');
                }

                if (totalBids > 0 && emptyState && !emptyState.classList.contains('hidden')) {
                    emptyState.classList.add('hidden');
                }

                if (header && totalBids > 0) {
                    let pill = document.getElementById('total-bids-pill');
                    if (!pill) {
                        pill = document.createElement('span');
                        pill.id = 'total-bids-pill';
                        pill.className =
                            'inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-[12px] font-medium text-slate-700';
                        header.appendChild(pill);
                    }
                    pill.textContent = totalBids + ' tawaran masuk';
                }
            }
        };
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const meta = document.getElementById('bid-poll-meta');
            if (!meta) return;

            const url           = meta.dataset.url;
            let lastBidId       = meta.dataset.initialLastBidId || null;
            const currentPage   = parseInt(meta.dataset.currentPage || '1', 10) || 1;

            const lastBidEl     = document.getElementById('last-bid-amount');
            const nextMinEl     = document.getElementById('next-min-amount');
            // const totalBidsPill = document.getElementById('total-bids-pill');
            const tbody         = document.getElementById('bid-history-body');
            const paginationBox = document.getElementById('bid-history-pagination');

            const visibleInput  = document.getElementById('amount-visible');
            const hiddenInput   = document.getElementById('amount-hidden');

            const formatter     = new Intl.NumberFormat('id-ID');

            const myBidStateEl   = document.getElementById('my-bid-state');
            const myBidWrapperEl = document.getElementById('my-bid-status-wrapper');

            const countdownBox   = document.getElementById('lot-countdown');
            const endLabel     = document.getElementById('lot-end-label');

            async function poll() {
                try {
                    if (document.visibilityState === 'hidden') {
                        return; // jangan spam server kalau tabnya tidak aktif
                    }

                    const query = new URLSearchParams();
                    if (lastBidId) {
                        query.set('last_bid_id', lastBidId);
                    }
                    if (currentPage) {
                        query.set('page', String(currentPage));
                    }

                    const res = await fetch(url + (query.toString() ? '?' + query.toString() : ''), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });

                    if (!res.ok) return;

                    const data = await res.json();

                    // sinkron end time & status countdown (support anti-sniping)
                    if (countdownBox) {
                        if (data.end_at_iso) {
                            countdownBox.dataset.time = data.end_at_iso;
                        }
                        if (data.runtime_status) {
                            countdownBox.dataset.status = data.runtime_status;
                        }
                    }

                    // UPDATE TEKS "Berakhir: …"
                    if (endLabel && data.end_at_iso) {
                        const dt = new Date(data.end_at_iso);
                        endLabel.textContent = dt.toLocaleString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: false,
                        });
                    }

                    // update UI status (badge, label, form) kalau ada helper
                    if (window.TempusLotUi && typeof window.TempusLotUi.syncStatus === 'function' && data.runtime_status) {
                        window.TempusLotUi.syncStatus(data.runtime_status);
                    }

                    // kalau lelang sudah tidak ACTIVE, hentikan polling
                    if (data.runtime_status && data.runtime_status !== 'ACTIVE') {
                        if (pollTimer) {
                            clearInterval(pollTimer);
                            pollTimer = null;
                        }
                    }

                    // update jumlah total bids + tampilkan tabel kalau perlu
                    if (typeof data.total_bids !== 'undefined' &&
                        window.TempusBidHistory &&
                        typeof window.TempusBidHistory.ensureTableVisible === 'function') {
                        window.TempusBidHistory.ensureTableVisible(data.total_bids);
                    }

                    // update pagination jika server kirim HTML baru
                    if (paginationBox && typeof data.pagination_html !== 'undefined') {
                        paginationBox.innerHTML = data.pagination_html;
                        const hasContent = data.pagination_html.trim().length > 0;
                        paginationBox.classList.toggle('hidden', !hasContent);
                    }

                    if (!data.changed) {
                        return;
                    }

                    // ====== update banner "kamu leading / ter-outbid" ======
                    if (myBidStateEl && myBidWrapperEl) {
                        let hasBid    = myBidStateEl.dataset.hasBid === '1';
                        let isLeading = myBidStateEl.dataset.isLeading === '1';

                        if (data.is_me) {
                            // bid baru milik kita → kita pasti leading sekarang
                            hasBid    = true;
                            isLeading = true;
                            myBidStateEl.dataset.hasBid    = '1';
                            myBidStateEl.dataset.isLeading = '1';

                            let pill = document.getElementById('my-bid-status-pill');
                            if (!pill) {
                                pill = document.createElement('div');
                                pill.id = 'my-bid-status-pill';
                                pill.className = 'inline-flex items-center rounded-full px-3 py-1 text-[11px] font-medium';
                                myBidWrapperEl.appendChild(pill);
                            }
                            pill.textContent = 'Kamu sedang memimpin lelang dengan bid tertinggi.';
                            pill.classList.remove('bg-amber-50', 'text-amber-800');
                            pill.classList.add('bg-emerald-50', 'text-emerald-800');
                        } else if (hasBid && isLeading) {
                            // sebelumnya kita leading, sekarang ada bid baru dari orang lain → kita ter-outbid
                            isLeading = false;
                            myBidStateEl.dataset.isLeading = '0';

                            let pill = document.getElementById('my-bid-status-pill');
                            if (!pill) {
                                pill = document.createElement('div');
                                pill.id = 'my-bid-status-pill';
                                pill.className = 'inline-flex items-center rounded-full px-3 py-1 text-[11px] font-medium';
                                myBidWrapperEl.appendChild(pill);
                            }
                            pill.textContent = 'Kamu sudah ter-outbid. Peserta lain memiliki bid yang lebih tinggi.';
                            pill.classList.remove('bg-emerald-50', 'text-emerald-800');
                            pill.classList.add('bg-amber-50', 'text-amber-800');

                            // toast khusus outbid
                            if (window.Alpine && Alpine.store && Alpine.store('toast')) {
                                Alpine.store('toast').push({
                                    type: 'warn',
                                    text: 'Bid Anda sudah ter-outbid di lot ini.',
                                    timeout: 6000,
                                });
                            }
                        }
                    }

                    // Notifikasi umum: setiap ada bid baru dari orang lain
                    if (!data.is_me && window.Alpine && Alpine.store && Alpine.store('toast')) {
                        Alpine.store('toast').push({
                            type: 'warn',
                            text: 'Ada bid baru masuk. Periksa kembali posisi Anda.',
                            timeout: 4000,
                        });
                    }

                    // ada bid baru
                    lastBidId = data.last_bid_id;

                    // update Bid Terakhir
                    if (lastBidEl && data.last_amount != null) {
                        lastBidEl.textContent = 'Rp ' + formatter.format(data.last_amount);
                        lastBidEl.classList.add('bg-emerald-100', 'px-1', 'rounded');
                        setTimeout(() => {
                            lastBidEl.classList.remove('bg-emerald-100', 'px-1', 'rounded');
                        }, 1200);
                    }

                    // update Minimal Bid Berikutnya
                    if (nextMinEl && data.next_min != null) {
                        nextMinEl.textContent = 'Rp ' + formatter.format(data.next_min);
                    }

                    // update default nilai di input (kalau user belum ngetik apa-apa)
                    if (visibleInput && hiddenInput) {
                        if (!document.activeElement || document.activeElement !== visibleInput) {
                            const raw = Math.round(parseFloat(data.next_min || 0));
                            hiddenInput.value  = raw;
                            visibleInput.value = raw ? formatter.format(raw) : '';
                        }
                    }

                    // ====== prepend baris baru ke Riwayat Bid (tanpa duplikat) ======
                    if (tbody && data.created_at && data.last_bidder && data.last_amount != null) {

                        // kalau row untuk bid ini sudah ada, jangan bikin baru
                        const existing = tbody.querySelector(
                            `tr[data-bid-id="${data.last_bid_id}"]`
                        );

                        // html username (chip hitam kalau "Anda")
                        const usernameHtml = data.is_me
                            ? `<span class="inline-flex items-center rounded-full bg-slate-900 text-white px-2 py-0.5 text-[11px]">
                                    ${data.last_bidder}
                            </span>`
                            : `<span class="text-slate-700">
                                    ${data.last_bidder}
                            </span>`;

                        if (existing) {
                            // update isi kolom username & nominal (biar konsisten)
                            const cells = existing.querySelectorAll('td');
                            if (cells[0]) {
                                cells[0].textContent = data.created_at;
                            }
                            if (cells[1]) {
                                cells[1].innerHTML = usernameHtml;
                            }
                            if (cells[2]) {
                                cells[2].innerHTML = `
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-900">
                                        Rp ${formatter.format(data.last_amount)}
                                    </span>
                                `;
                            }

                            // pindahkan ke paling atas + kasih highlight lagi
                            tbody.querySelectorAll('.latest-bid-row').forEach(el => {
                                el.classList.remove('latest-bid-row', 'bg-emerald-50');
                            });

                            existing.classList.add('latest-bid-row', 'bg-emerald-50');
                            if (tbody.firstChild !== existing) {
                                tbody.insertBefore(existing, tbody.firstChild);
                            }
                            return;
                        }

                        // buang highlight lama dulu (kalau ada)
                        tbody.querySelectorAll('.latest-bid-row').forEach(el => {
                            el.classList.remove('latest-bid-row', 'bg-emerald-50');
                        });

                        const tr = document.createElement('tr');
                        tr.className = 'border-t border-slate-100 hover:bg-slate-50 transition-colors latest-bid-row bg-emerald-50 animate-pulse';
                        tr.setAttribute('data-bid-id', data.last_bid_id);
                        tr.innerHTML = `
                            <td class="px-4 py-2 text-xs lg:text-sm text-slate-700 whitespace-nowrap">
                                ${data.created_at}
                            </td>
                            <td class="px-4 py-2 text-xs lg:text-sm text-slate-700">
                                ${usernameHtml}
                            </td>
                            <td class="px-4 py-2 text-xs lg:text-sm text-right whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-900">
                                    Rp ${formatter.format(data.last_amount)}
                                </span>
                            </td>
                        `;

                        if (tbody.firstChild) {
                            tbody.insertBefore(tr, tbody.firstChild);
                        } else {
                            tbody.appendChild(tr);
                        }

                        setTimeout(() => tr.classList.remove('animate-pulse'), 1000);
                    }

                } catch (e) {
                    console.error('Polling error', e);
                }
            }

            // expose ke global supaya bisa dipanggil dari script AJAX submit
            window.TempusBidPoll = { poll };

            // langsung jalan sekali
            poll();

            let pollTimer = setInterval(poll, 2000);
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('[data-bid-form]');
            if (!form) return;

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrf) return;

            const submitBtn   = form.querySelector('button[type="submit"]');
            const visibleInput = document.getElementById('amount-visible');
            const endLabel     = document.getElementById('lot-end-label');

            form.addEventListener('submit', async (e) => {
                // kalau tombolnya disabled (lelang belum live / user belum verif), biarkan default
                if (submitBtn && submitBtn.disabled) {
                    return;
                }

                e.preventDefault();

                const action  = form.action;
                const formData = new FormData(form);

                // state loading
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
                }

                try {
                    const res = await fetch(action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    // kalau user suspended / tidak berhak -> 403
                    if (res.status === 403) {
                        const json = await res.json().catch(() => ({}));
                        const msg = json.message || 'Anda tidak dapat melakukan bid saat ini.';

                        if (window.Alpine && Alpine.store && Alpine.store('toast')) {
                            Alpine.store('toast').push({
                                type: 'error',
                                text: msg,
                                timeout: 5000,
                            });
                        }

                        return;
                    }

                    if (res.status === 422) {
                        const json = await res.json();
                        const errors = json.errors || {};
                        const first = (errors.amount && errors.amount[0])
                            ? errors.amount[0]
                            : 'Bid gagal, silakan cek kembali isian Anda.';

                        if (window.Alpine && Alpine.store && Alpine.store('toast')) {
                            Alpine.store('toast').push({
                                type: 'error',
                                text: first,
                                timeout: 5000,
                            });
                        }
                        return;
                    }

                    if (res.status === 429) {
                        const json = await res.json().catch(() => ({}));
                        const msg = json.message || 'Terlalu banyak percobaan bid. Coba lagi sebentar lagi.';

                        if (window.Alpine && Alpine.store && Alpine.store('toast')) {
                            Alpine.store('toast').push({
                                type: 'warn',
                                text: msg,
                                timeout: 5000,
                            });
                        }

                        return;
                    }

                    if (!res.ok) {
                        throw new Error('Bid gagal');
                    }

                    const json = await res.json();

                    // === UPDATE countdown bila ada end_at baru (anti-sniping / admin extend) ===
                    const countdownBox = document.getElementById('lot-countdown');
                    if (countdownBox) {
                        if (json.end_at_iso) {
                            countdownBox.dataset.time = json.end_at_iso;
                        }
                        if (json.runtime_status) {
                            countdownBox.dataset.status = json.runtime_status;
                        }
                    }

                    // UPDATE LABEL "Berakhir: …" SETELAH USER SENDIRI BID
                    if (endLabel && json.end_at_iso) {
                        const dt = new Date(json.end_at_iso);
                        endLabel.textContent = dt.toLocaleString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: false,
                        });
                    }

                    // Sinkronkan status UI (badge, label, form)
                    if (window.TempusLotUi && typeof window.TempusLotUi.syncStatus === 'function' && json.runtime_status) {
                        window.TempusLotUi.syncStatus(json.runtime_status);
                    }

                    // === TOAST SUKSES ===
                    if (window.Alpine && Alpine.store && Alpine.store('toast')) {
                        Alpine.store('toast').push({
                            type: 'success',
                            text: json.message || 'Bid Anda berhasil direkam.',
                            timeout: 4000,
                        });

                        // kalau anti-sniping aktif untuk bid ini → kasih info ke bidder
                        if (json.anti_sniping_extended) {
                            Alpine.store('toast').push({
                                type: 'info',
                                text: 'Waktu lelang diperpanjang otomatis karena ada bid di detik-detik akhir.',
                                timeout: 6000,
                            });
                        }
                    }

                    // === UPDATE LAYAR LANGSUNG DARI RESPONSE ===
                    const formatter = new Intl.NumberFormat('id-ID');

                    const lastBidEl     = document.getElementById('last-bid-amount');
                    const nextMinEl     = document.getElementById('next-min-amount');
                    // const totalBidsPill = document.getElementById('total-bids-pill');
                    const tbody         = document.getElementById('bid-history-body');
                    const myBidStateEl  = document.getElementById('my-bid-state');
                    const myBidWrapper  = document.getElementById('my-bid-status-wrapper');

                    // 1) Bid terakhir
                    if (lastBidEl && json.last_amount != null) {
                        lastBidEl.textContent = 'Rp ' + formatter.format(json.last_amount);
                    }

                    // 2) Minimal bid berikutnya
                    if (nextMinEl && json.next_min != null) {
                        nextMinEl.textContent = 'Rp ' + formatter.format(json.next_min);
                    }

                    // 3) Total bid + pastikan tabel muncul
                    if (typeof json.total_bids !== 'undefined' &&
                        window.TempusBidHistory &&
                        typeof window.TempusBidHistory.ensureTableVisible === 'function') {
                        window.TempusBidHistory.ensureTableVisible(json.total_bids);
                    }

                    // 4) Update input default next_min (kalau user gak lagi ngetik)
                    if (visibleInput && document.activeElement !== visibleInput && json.next_min != null) {
                        const raw = Math.round(parseFloat(json.next_min));
                        document.getElementById('amount-hidden').value  = raw;
                        visibleInput.value = raw ? formatter.format(raw) : '';
                    }

                    // 5) Update banner "kamu memimpin"
                    if (myBidStateEl && myBidWrapper) {
                        myBidStateEl.dataset.hasBid    = '1';
                        myBidStateEl.dataset.isLeading = '1';

                        let pill = document.getElementById('my-bid-status-pill');
                        if (!pill) {
                            pill = document.createElement('div');
                            pill.id = 'my-bid-status-pill';
                            pill.className = 'inline-flex items-center rounded-full px-3 py-1 text-[11px] font-medium';
                            myBidWrapper.appendChild(pill);
                        }
                        pill.textContent = 'Kamu sedang memimpin lelang dengan bid tertinggi.';
                        pill.classList.remove('bg-amber-50', 'text-amber-800');
                        pill.classList.add('bg-emerald-50', 'text-emerald-800');
                    }

                    // 6) Prepend baris baru ke riwayat bid (kalau tbody ada)
                    if (tbody && json.last_bid_id && json.created_at && json.last_bidder) {
                        const existing = tbody.querySelector(`tr[data-bid-id="${json.last_bid_id}"]`);

                        const usernameHtml = `
                            <span class="inline-flex items-center rounded-full bg-slate-900 text-white px-2 py-0.5 text-[11px]">
                                ${json.last_bidder}
                            </span>
                        `;

                        const rowHtml = `
                            <td class="px-4 py-2 text-xs lg:text-sm text-slate-700 whitespace-nowrap">
                                ${json.created_at}
                            </td>
                            <td class="px-4 py-2 text-xs lg:text-sm text-slate-700">
                                ${usernameHtml}
                            </td>
                            <td class="px-4 py-2 text-xs lg:text-sm text-right whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-900">
                                    Rp ${formatter.format(json.last_amount)}
                                </span>
                            </td>
                        `;

                        let tr;
                        if (existing) {
                            tr = existing;
                            tr.innerHTML = rowHtml;
                        } else {
                            tr = document.createElement('tr');
                            tr.setAttribute('data-bid-id', json.last_bid_id);
                            tr.className = 'border-t border-slate-100 hover:bg-slate-50 transition-colors latest-bid-row bg-emerald-50';
                            tr.innerHTML = rowHtml;
                        }

                        // reset highlight lama
                        tbody.querySelectorAll('.latest-bid-row').forEach(el => {
                            el.classList.remove('latest-bid-row', 'bg-emerald-50');
                        });

                        tr.classList.add('latest-bid-row', 'bg-emerald-50');

                        if (tbody.firstChild !== tr) {
                            tbody.insertBefore(tr, tbody.firstChild);
                        }
                    }

                    // 7) Optional: tetap panggil polling untuk sinkron dengan user lain
                    if (window.TempusBidPoll && typeof window.TempusBidPoll.poll === 'function') {
                        window.TempusBidPoll.poll();
                    }

                } catch (err) {
                    console.error(err);
                    if (window.Alpine && Alpine.store && Alpine.store('toast')) {
                        Alpine.store('toast').push({
                            type: 'error',
                            text: 'Terjadi kesalahan saat mengirim bid. Silakan coba lagi.',
                            timeout: 5000,
                        });
                    }
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
                    }
                }
            });
        });
    </script>

</x-guest-layout>
