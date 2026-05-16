{{-- resources/public/partials/lot_cards.blade.php --}}
@foreach($lots as $lot)
    @php
        $product      = $lot->product;
        $images       = $product?->images ?? collect();
        $primaryImage = $images->first();
        $title        = trim(($product->brand ?? '').' '.($product->model ?? ''));

        $status     = $lot->runtime_status; 
        $currentBid = $lot->highest_bid ?? 0;

        $isLive     = $status === 'ACTIVE';
        $isUpcoming = $status === 'SCHEDULED';
        $isEnded    = !$isLive && !$isUpcoming; 

        if ($isLive) {
            $badgeText      = 'Live';
            $badgeClass     = 'bg-emerald-600/95';
            $ctaText        = 'Bid Sekarang';
            $ctaClass       = 'bg-slate-900 text-white hover:bg-slate-800';
            $showHighestBid = true;
            $priceLabel     = 'Bid Terakhir';
            $countdownLabel = null;
            $countTarget    = 'end';
        } elseif ($isUpcoming) {
            $badgeText      = 'Segera Dimulai';
            $badgeClass     = 'bg-yellow-500/95';
            $ctaText        = 'Lihat Detail';
            $ctaClass       = 'bg-white text-slate-900 ring-1 ring-slate-200 hover:bg-slate-50';
            $showHighestBid = false;
            $priceLabel     = null;
            $countdownLabel = null;
            $countTarget    = 'start';
        } else {
            $badgeText      = 'Selesai';
            $badgeClass     = 'bg-red-700/95';
            $ctaText        = 'Lihat Hasil';
            $ctaClass       = 'bg-slate-100 text-slate-700 hover:bg-slate-200';
            $showHighestBid = true;
            $priceLabel     = 'Harga Akhir';
            $countdownLabel = 'Lelang telah berakhir';
            $countTarget    = null;
        }

        $startIso = optional($lot->start_at)->toIso8601String();
        $endIso   = optional($lot->end_at)->toIso8601String();
    @endphp
    @php
        $isWatchlisted = isset($watchlistLotIds)
            ? in_array($lot->id, $watchlistLotIds)
            : false;
    @endphp


    <div x-data="{ openQuick:false, active:0 }"
        data-lot-card="{{ $lot->id }}"
        class="auction-card group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-md transition"
        data-title="{{ $title }}"
        data-brand="{{ $product->brand ?? '' }}"
        data-current="{{ $currentBid }}"
        data-start="{{ $startIso }}"
        data-end="{{ $endIso }}"
        data-status="{{ $status }}"
        data-target="{{ $countTarget }}">

        <div class="relative">
            <img
                src="{{ $primaryImage?->public_url ?? asset('tempus/placeholder.jpg') }}"
                class="h-56 w-full object-cover"
                alt="{{ $title }}"
            >

            {{-- Badge status kiri --}}
            <span class="absolute top-3 left-3 rounded-full {{ $badgeClass }} text-white text-xs font-semibold px-2 py-1">
                {{ $badgeText }}
            </span>

            {{-- ICON QUICK VIEW kanan atas --}}
            <button type="button"
                    class="absolute top-3 right-3 inline-flex items-center justify-center
                        rounded-full bg-white/90 text-slate-700 hover:bg-white
                        w-6 lg:w-8 h-6 lg:h-8 shadow-sm border border-slate-200"
                    @click="openQuick = true">
                <svg class="w-4 lg:w-5 h-4 lg:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                        d="M2.25 12s2.7-6 9.75-6 9.75 6 9.75 6-2.7 6-9.75 6-9.75-6-9.75-6z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                <span class="sr-only">Quick view</span>
            </button>
        </div>

        <div class="p-4 flex flex-col flex-1">
            <div class="min-h-[48px]">
                <h3 class="text-base font-bold text-slate-900 group-hover:text-slate-700 line-clamp-2 leading-snug">
                    {{ $title }}
                </h3>
            </div>

            <dl class="mt-2 space-y-1.5 text-sm">
                @if($isUpcoming)
                    <div class="flex justify-between">
                        <dt class="text-slate-500 text-xs lg:text-sm">Harga Awal</dt>
                        <dd class="font-semibold text-xs lg:text-sm">
                            Rp {{ number_format($lot->start_price, 0, ',', '.') }}
                        </dd>
                    </div>
                @endif

                @if($showHighestBid && $priceLabel)
                    <div class="flex justify-between">
                        <dt class="text-slate-500 text-xs lg:text-sm">{{ $priceLabel }}</dt>
                        <dd class="font-semibold text-xs lg:text-sm {{ $isLive ? 'text-emerald-700' : 'text-slate-900' }}">
                            Rp {{ number_format($currentBid, 0, ',', '.') }}
                        </dd>
                    </div>
                @endif
            </dl>

            <div class="mt-3">
                @if($countdownLabel)
                    <div class="countdown flex justify-center">
                        <span class="inline-flex items-center rounded-full bg-red-700 text-white mt-9 px-2 lg:px-3 py-1 text-xs font-semibold">
                            {{ $countdownLabel }}
                        </span>
                    </div>
                @else
                    @if($isUpcoming)
                        <p class="text-xs font-semibold text-amber-700 mb-2">
                            Dimulai dalam
                        </p>
                    @elseif($isLive)
                        <p class="text-xs font-semibold text-emerald-700 mb-2">
                            Berakhir dalam
                        </p>
                    @endif

                    <div class="countdown flex w-full gap-1 font-semibold text-[11px]">
                        <div class="flex-1 flex flex-col items-center py-1.5 rounded-md bg-slate-100 text-slate-800">
                            <span data-part="days" class="font-mono text-sm">00</span>
                            <span class="uppercase tracking-tight text-[9px]">Hari</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center py-1.5 rounded-md bg-slate-100 text-slate-800">
                            <span data-part="hours" class="font-mono text-sm">00</span>
                            <span class="uppercase tracking-tight text-[9px]">Jam</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center py-1.5 rounded-md bg-slate-100 text-slate-800">
                            <span data-part="minutes" class="font-mono text-sm">00</span>
                            <span class="uppercase tracking-tight text-[9px]">Menit</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center py-1.5 rounded-md bg-slate-100 text-slate-800">
                            <span data-part="seconds" class="font-mono text-sm">00</span>
                            <span class="uppercase tracking-tight text-[9px]">Detik</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-auto pt-3 flex items-center gap-2">
                {{-- Tombol utama: Bid Sekarang / Lihat Detail --}}
                <a href="{{ route('lots.show', ['lot' => $lot, 'from' => 'home']) }}"
                class="flex-1 block text-center rounded-lg py-1.5 text-[14px] font-semibold {{ $ctaClass }}">
                    {{ $ctaText }}
                </a>

                {{-- Tombol Watchlist di kanan --}}
                @auth
                    @php
                        $u = auth()->user();

                        $isBidder    = method_exists($u, 'isBidder') ? $u->isBidder() : (($u->role ?? null) === 'BIDDER');
                        $isSuspended = method_exists($u,'isSuspended') && $u->isSuspended();
                        $isVerified  = method_exists($u,'hasVerifiedEmail') ? $u->hasVerifiedEmail() : !is_null($u->email_verified_at);

                        $canWatchlist = $isBidder && $isVerified && ! $isSuspended;
                    @endphp

                    @if($canWatchlist)
                        {{-- Bidder + verified + not suspended -> AJAX toggle --}}
                        <button type="button"
                            data-watchlist-button
                            data-url="{{ route('watchlist.toggle', $lot) }}"
                            data-watchlisted="{{ $isWatchlisted ? '1' : '0' }}"
                            class="inline-flex items-center justify-center rounded-full
                                w-8 h-8 border
                                {{ $isWatchlisted
                                        ? 'border-slate-900 bg-slate-900 text-white'
                                        : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50' }}"
                            title="{{ $isWatchlisted ? 'Hapus dari Watchlist' : 'Tambahkan ke Watchlist' }}">

                            {{-- Heart outline --}}
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4 icon-heart-outline {{ $isWatchlisted ? 'hidden' : '' }}"
                                viewBox="0 0 16 16" fill="currentColor">
                                <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815
                                        2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385
                                        C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1
                                        .176-.17C12.72-3.042 23.333 4.867 8 15"/>
                            </svg>

                            {{-- Heart filled --}}
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4 icon-heart-fill {{ $isWatchlisted ? '' : 'hidden' }}"
                                viewBox="0 0 16 16" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314"/>
                            </svg>
                        </button>

                    @elseif($isBidder && ! $isVerified)
                        {{-- Bidder tapi belum verifikasi -> arahkan ke halaman verifikasi --}}
                        <a href="{{ route('verification.notice') }}"
                            class="inline-flex items-center justify-center rounded-full
                                w-8 h-8 border border-slate-200 bg-white text-slate-500 hover:bg-slate-50"
                            title="Verifikasi email terlebih dahulu untuk menggunakan watchlist">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4"
                                viewBox="0 0 16 16" fill="currentColor">
                                <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815
                                        2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385
                                        C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1
                                        .176-.17C12.72-3.042 23.333 4.867 8 15"/>
                            </svg>
                        </a>

                    @else
                        {{-- Suspended atau Admin -> disabled --}}
                        <button type="button"
                            disabled
                            class="inline-flex items-center justify-center rounded-full
                                w-8 h-8 border border-slate-200 bg-slate-50 text-slate-400 cursor-not-allowed"
                            title="{{ $isSuspended ? 'Akun sedang ditangguhkan' : 'Admin tidak dapat menggunakan watchlist' }}">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4"
                                viewBox="0 0 16 16" fill="currentColor">
                                <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815
                                        2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385
                                        C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1
                                        .176-.17C12.72-3.042 23.333 4.867 8 15"/>
                            </svg>
                        </button>
                    @endif
                @else
                    {{-- belum login -> arahkan ke login --}}
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center justify-center rounded-full
                            w-9 h-9 border border-slate-200 bg-white text-slate-500 hover:bg-slate-50"
                        title="Login untuk menambahkan ke watchlist">

                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 icon-heart-outline"
                            viewBox="0 0 16 16" fill="currentColor" >
                            <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815
                                    2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385
                                    C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1
                                    .176-.17C12.72-3.042 23.333 4.867 8 15"/>
                        </svg>
                    </a>
                @endauth
            </div>

        </div>

        {{-- QUICK VIEW MODAL --}}
        <div x-show="openQuick"
            x-cloak
            class="fixed inset-0 z-50 flex items-start sm:items-center justify-center 
                    bg-black/40 px-2 sm:px-3 pt-4 sm:pt-0 pb-4 sm:pb-0">
            <div class="bg-white rounded-2xl shadow-2xl
                    w-full                 {{-- mobile: penuh --}}
                    sm:w-11/12            {{-- tablet / laptop kecil: 91% --}}
                    lg:w-3/4              {{-- desktop: 75% --}}
                    xl:w-1/2              {{-- desktop lebar: 50% --}}
                    max-w-2xl             {{-- batas maksimal px --}}
                    mx-auto max-h-[90vh]
                    overflow-y-auto overflow-x-hidden"
            @click.outside="openQuick = false">


                {{-- HEADER --}}
                <div class="flex items-start justify-between 
                    px-4 sm:px-6 pt-4 pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-lg sm:text-xl font-semibold text-slate-900">
                            {{ $title }}
                        </h2>
                        <span
                            class="inline-flex items-center mt-2 px-3 py-1 rounded-full 
                                text-xs font-semibold text-white {{ $badgeClass }}">
                            {{ $badgeText }}
                        </span>
                    </div>

                    <button
                        class="ml-3 mt-1 text-slate-400 hover:text-slate-600 text-xl leading-none"
                        @click="openQuick = false">
                        ✕
                    </button>
                </div>

                {{-- BODY --}}
                <div class="px-4 sm:px-6 pb-5 pt-4 space-y-4">

                    {{-- ===== BARIS 1: GALLERY + DETAIL ===== --}}
                    <div class="grid gap-5 lg:grid-cols-2 lg:gap-8">

                        {{-- GALLERY (kiri) --}}
                        <div class="space-y-3 min-w-0">

                            {{-- gambar utama --}}
                            <div class="rounded-2xl bg-slate-100 overflow-hidden lg:aspect-[4/3]">
                                @forelse($images as $idx => $image)
                                    <img
                                        x-show="active === {{ $idx }}"
                                        x-cloak
                                        src="{{ $image->public_url ?? asset('tempus/placeholder.jpg') }}"
                                        alt="{{ $title }} - {{ $idx + 1 }}"
                                        class="w-full h-auto object-contain lg:h-full lg:object-cover"
                                    >
                                @empty
                                    <img
                                        src="{{ asset('tempus/placeholder.jpg') }}"
                                        alt="No image"
                                        class="w-full h-auto object-contain lg:h-full lg:object-cover"
                                    >
                                @endforelse
                            </div>

                            {{-- thumbnail --}}
                            @if($images->count() > 1)
                                <div class="flex gap-2 pb-1 overflow-x-auto max-w-full min-w-0">
                                    @foreach($images as $idx => $image)
                                        <button
                                            type="button"
                                            @click="active = {{ $idx }}"
                                            class="relative flex-none basis-1/4 sm:basis-auto
                                                sm:w-20 sm:h-20 aspect-square
                                                rounded-lg overflow-hidden bg-slate-100 border
                                                transition ring-offset-2"
                                            :class="active === {{ $idx }}
                                                ? 'ring-2 ring-slate-900 border-slate-900'
                                                : 'border-slate-200'">
                                            <img
                                                src="{{ $image->public_url ?? asset('tempus/placeholder.jpg') }}"
                                                alt="Thumb {{ $idx + 1 }}"
                                                class="w-full h-full object-cover"
                                            >
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- DETAIL (kanan) --}}
                        <div class="space-y-3 text-sm">

                            {{-- Periode (paling atas) --}}
                            <div>
                                <div class="text-slate-500 mb-0.5">Periode</div>
                                <div class="font-medium text-slate-900">
                                    {{ optional($lot->start_at)->format('d M Y H:i') }}
                                    &mdash;
                                    {{ optional($lot->end_at)->format('d M Y H:i') }}
                                </div>
                            </div>

                            {{-- Brand / Model --}}
                            <div>
                                <div class="text-slate-500">Brand / Model</div>
                                <div class="font-semibold text-slate-900">
                                    {{ $product->brand ?? '-' }} {{ $product->model ?? '' }}
                                </div>
                            </div>

                            {{-- Harga / Bid / Increment --}}
                            <div class="grid grid-cols-3 lg:grid-cols-2 gap-2 sm:gap-3 text-xs sm:text-sm">

                                {{-- Harga Awal (selalu ada) --}}
                                <div>
                                    <div class="text-slate-500">Harga Awal</div>
                                    <div class="font-semibold text-slate-900">
                                        Rp {{ number_format($lot->start_price, 0, ',', '.') }}
                                    </div>
                                </div>

                                {{-- Kelipatan Bid (untuk live & upcoming) --}}
                                @if($isLive || $isUpcoming)
                                    <div>
                                        <div class="text-slate-500">Kelipatan Bid</div>
                                        <div class="font-semibold text-slate-900">
                                            Rp {{ number_format($lot->increment, 0, ',', '.') }}
                                        </div>
                                    </div>
                                @endif

                                {{-- Kolom ke-3: Bid Terakhir (live) / Harga Akhir (ended) --}}
                                @if($isLive)
                                    <div>
                                        <div class="text-slate-500">Bid Terakhir</div>
                                        <div class="font-semibold text-emerald-700">
                                            Rp {{ number_format($currentBid, 0, ',', '.') }}
                                        </div>
                                    </div>
                                @elseif(! $isUpcoming)
                                    <div>
                                        <div class="text-slate-500">Harga Akhir</div>
                                        <div class="font-semibold text-slate-900">
                                            Rp {{ number_format($currentBid, 0, ',', '.') }}
                                        </div>
                                    </div>
                                @endif

                            </div>

                            {{-- Tahun / Kondisi / Berat / Kategori --}}
                            <div class="grid grid-cols-3 lg:grid-cols-2 gap-2 sm:gap-3 text-xs sm:text-sm">
                                <div>
                                    <div class="text-slate-500">Tahun</div>
                                    <div class="font-medium text-slate-900">
                                        {{ $product->year ?? '-' }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-slate-500">Kondisi</div>
                                    <div class="font-medium text-slate-900">
                                        {{ $product->condition ?? '-' }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-slate-500">Berat (gram)</div>
                                    <div class="font-medium text-slate-900">
                                        {{ $product->weight_grams ?? '-' }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-slate-500">Kategori</div>
                                    <div class="font-medium text-slate-900">
                                        {{ $product->category ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== BARIS 2: DESKRIPSI FULL WIDTH ===== --}}
                    @if($product?->description)
                        <div class="border-t border-slate-100 pt-3">
                            <div class="text-slate-500 mb-2">Deskripsi</div>
                            <div class="prose prose-sm max-w-none text-slate-700">
                                {!! nl2br(e($product->description)) !!}
                            </div>
                        </div>
                    @endif

                </div>

                {{-- FOOTER --}}
                <div class="px-4 sm:px-6 pb-4 pt-3 flex justify-end gap-2 border-t border-slate-100">
                    <button class="px-4 py-2 rounded-lg ring-1 ring-slate-300 text-sm font-medium hover:bg-slate-50"
                            @click="openQuick = false">
                        Tutup
                    </button>
                    <a href="{{ route('lots.show', ['lot' => $lot, 'from' => 'home']) }}"
                    class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800">
                        Lihat Detail & Bid
                    </a>
                </div>
            </div>
        </div>
    </div>
@endforeach
