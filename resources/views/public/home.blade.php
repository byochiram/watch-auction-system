{{-- resources/public/home.blade.php --}}
<x-guest-layout>
    {{-- ===================== HERO ===================== --}}
    @php
        $user = Auth::user();
        $isSuspended = $user && $user->suspended_until && now()->lt($user->suspended_until);
    @endphp
    <section class="relative {{ $isSuspended ? '-mt-20' : '-mt-28' }} left-1/2 right-1/2 -mx-[50vw] w-screen min-h-[68vh] md:min-h-[70vh] lg:min-h-[80vh] overflow-hidden text-white">
        <!-- Background image full -->
        <div class="absolute inset-0 -z-10 bg-center bg-no-repeat bg-cover" style="background-image:url('{{ asset('tempus/hero2.png') }}')"></div>
        <!-- (opsional) overlay biar teks kontras -->
        <div class="absolute inset-0 -z-10 bg-black/70"></div>

        <!-- konten -->
        <div class="relative mx-auto max-w-screen-xl px-6 md:pt-12 lg:pt-24 flex flex-col items-center justify-center text-center min-h-[70vh]">
            <h1 class="max-w-2xl mb-5 mt-5 text-3xl md:text-4xl lg:text-5xl font-extrabold leading-tight">
                Timeless Pieces <br>Exclusive Bids
            </h1>

            <p class="max-w-xl mb-8 text-white/90 md:text-lg lg:text-xl">
                Tempus Auctions brings you authentic watches — curated, verified, and ready for your bid.
            </p>

            <div class="flex flex-wrap gap-3 justify-center">
                <a href="#auctions"
                   class="inline-flex items-center rounded-xl bg-white/60 px-4 py-2 text-slate-900 font-semibold hover:bg-white">
                    Lihat Lelang
                </a>
            </div>
        </div>

        <!-- wave bawah -->
        <!-- <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 160" xmlns="http://www.w3.org/2000/svg" class="w-full" fill="none">
                <path d="M0 80c120 40 240 60 360 60s240-20 360-60 240-60 360-60 240 20 360 60v80H0V80z"
                      class="fill-white"/>
            </svg>
        </div> -->
    </section>
    {{-- ===================== END HERO ===================== --}}

    {{-- ===================== DAFTAR LELANG ===================== --}}
    <section id="auctions" class="relative">
        <div class="mx-auto max-w-screen-xl py-10 lg:py-14">

            {{-- PANEL FILTER: Search • Filter • Sort --}}
            <div class="relative -mt-32 sm:-mt-32 lg:-mt-36 z-20">
                <div
                    class="max-w-5xl mx-auto rounded-3xl
                        bg-gradient-to-br from-white via-white to-slate-50/95
                        border border-slate-200/90
                        shadow-[0_14px_40px_rgba(15,23,42,0.14)]
                        px-4 py-5 sm:px-7 sm:py-6">

                    {{-- HEADER FILTER --}}
                    <div class="flex items-start justify-between gap-2 mb-4">
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold tracking-[0.18em] uppercase text-slate-500">
                                Lelang Aktif
                            </p>
                            <h2 class="text-sm sm:text-base font-semibold text-slate-900">
                                Temukan jam yang ingin Anda menangkan
                            </h2>
                        </div>

                        <button id="resetBtn"
                            class="shrink-0 inline-flex items-center gap-1.5
                                text-[11px] font-medium text-slate-500
                                border border-slate-300/90 rounded-full px-3 py-1.5
                                bg-white/80
                                hover:bg-slate-100 hover:text-slate-700 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                                <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
                            </svg>
                            Reset filter
                        </button>
                    </div>

                    {{-- GRID FILTER (compact & rapi) --}}
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">

                        {{-- Cari --}}
                        <div class="col-span-2 md:col-span-2">
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">
                                Cari jam
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m21 21-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z"/>
                                    </svg>
                                </span>
                                <input id="searchInput" type="text" placeholder="Cari nama / model…"
                                    value="{{ request('q') }}"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/80
                                            text-sm text-slate-700 placeholder:text-slate-400
                                            pl-9 pr-3 py-2.5
                                            focus:border-slate-500 focus:ring-2 focus:ring-slate-200">
                            </div>
                        </div>

                        {{-- Brand --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">
                                Brand
                            </label>
                            <div class="relative">
                                <select id="brandSelect"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50/80
                                            text-sm text-slate-700 px-3 py-2.5
                                            appearance-none pr-8
                                            focus:border-slate-500 focus:ring-2 focus:ring-slate-200">
                                    <option value="">Semua brand</option>
                                    @foreach($brands as $b)
                                        <option value="{{ $b }}" {{ request('brand') === $b ? 'selected' : '' }}>
                                            {{ $b }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-2.5 flex items-center text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.085l3.71-3.855a.75.75 0 1 1 1.08 1.04l-4.24 4.4a.75.75 0 0 1-1.08 0l-4.24-4.4a.75.75 0 0 1 .02-1.06Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </div>
                        </div>

                        {{-- Kategori --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">
                                Kategori
                            </label>
                            <div class="relative">
                                <select id="categorySelect"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50/80
                                            text-sm text-slate-700 px-3 py-2.5
                                            appearance-none pr-8
                                            focus:border-slate-500 focus:ring-2 focus:ring-slate-200">
                                    <option value="">Semua kategori</option>
                                    @foreach($categories as $c)
                                        <option value="{{ $c }}" {{ request('category') === $c ? 'selected' : '' }}>
                                            {{ $c }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-2.5 flex items-center text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.085l3.71-3.855a.75.75 0 1 1 1.08 1.04l-4.24 4.4a.75.75 0 0 1-1.08 0l-4.24-4.4a.75.75 0 0 1 .02-1.06Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </div>
                        </div>

                        {{-- Min harga --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">
                                Min harga
                            </label>
                            <input id="minInput" type="number" placeholder="Min"
                                value="{{ request('min') }}"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/80
                                        text-sm text-slate-700 px-3 py-2.5
                                        focus:border-slate-500 focus:ring-2 focus:ring-slate-200">
                        </div>

                        {{-- Maks harga --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">
                                Maks harga
                            </label>
                            <input id="maxInput" type="number" placeholder="Maks"
                                value="{{ request('max') }}"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/80
                                        text-sm text-slate-700 px-3 py-2.5
                                        focus:border-slate-500 focus:ring-2 focus:ring-slate-200">
                            <p id="priceError"
                                class="hidden mt-2 text-xs text-red-600 font-medium">
                            </p>
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">
                                Status
                            </label>
                            <div class="relative">
                                <select id="statusSelect"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50/80
                                            text-sm text-slate-700 px-3 py-2.5
                                            appearance-none pr-8
                                            focus:border-slate-500 focus:ring-2 focus:ring-slate-200">
                                    <option value="">Semua status</option>
                                    <option value="live"     {{ request('status') === 'live' ? 'selected' : '' }}>Live</option>
                                    <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Segera dimulai</option>
                                    <option value="ended"    {{ request('status') === 'ended' ? 'selected' : '' }}>Selesai</option>
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-2.5 flex items-center text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.085l3.71-3.855a.75.75 0 1 1 1.08 1.04l-4.24 4.4a.75.75 0 0 1-1.08 0l-4.24-4.4a.75.75 0 0 1 .02-1.06Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </div>
                        </div>

                        {{-- Urutkan --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">
                                Urutkan
                            </label>
                            <div class="relative">
                                <select id="sortSelect"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50/80
                                            text-sm text-slate-700 px-3 py-2.5
                                            appearance-none pr-8
                                            focus:border-slate-500 focus:ring-2 focus:ring-slate-200">
                                    <option value="new"     {{ request('sort','new') === 'new' ? 'selected' : '' }}>Terbaru</option>
                                    <option value="ending"  {{ request('sort') === 'ending' ? 'selected' : '' }}>Segera berakhir</option>
                                    <option value="highest" {{ request('sort') === 'highest' ? 'selected' : '' }}>Tawaran tertinggi</option>
                                    <option value="lowest"  {{ request('sort') === 'lowest' ? 'selected' : '' }}>Tawaran terendah</option>
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-2.5 flex items-center text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.085l3.71-3.855a.75.75 0 1 1 1.08 1.04l-4.24 4.4a.75.75 0 0 1-1.08 0l-4.24-4.4a.75.75 0 0 1 .02-1.06Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- SPASI KECIL ANTARA FILTER & GRID --}}
            <div class="mt-10 md:mt-12 lg:mt-14"></div>

            {{-- GRID --}}
            @if($lots->count())
                <div id="auctionGrid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 lg:gap-6">
                    @include('public.partials.lot_cards', ['lots' => $lots])
                </div>
            @else
                <p class="text-center text-slate-500">
                    Belum ada lelang aktif saat ini.
                </p>
            @endif

            {{-- LOAD MORE --}}
            <div id="loadMoreWrapper">
                @if($lots->hasMorePages())
                    <div class="mt-8 flex justify-center">
                        <button id="loadMoreBtn"
                                data-next-page="{{ $lots->currentPage() + 1 }}"
                                class="inline-flex items-center rounded-lg bg-slate-900 text-white px-5 py-2.5 text-sm font-semibold hover:bg-slate-800">
                            Tampilkan Lainnya
                        </button>
                    </div>
                @endif
            </div>

            <!-- {{-- CTA NEWSLETTER / NOTIFIKASI --}}
            <div class="mt-16">
                <div
                    class="rounded-3xl border border-slate-200/80 bg-slate-50/80
                        px-5 py-6 sm:px-8 sm:py-7
                        flex flex-col md:flex-row md:items-center md:justify-between gap-5">

                    {{-- TEXT --}}
                    <div class="flex items-start gap-3 max-w-xl">
                        <div
                            class="mt-1 flex h-9 w-9 items-center justify-center rounded-full
                                bg-slate-900 text-white shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 6.5 11.2 11a1.5 1.5 0 0 0 1.6 0L20 6.5M5 19h14a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 mb-1">
                                Newsletter
                            </p>
                            <h2 class="text-lg sm:text-xl font-semibold text-slate-900">
                                Ingin update saat ada lelang jam baru?
                            </h2>
                            <p class="mt-2 text-sm text-slate-600">
                                Kami kirim ringkasan lelang, highlight lot menarik, dan info jadwal berikutnya.
                                Frekuensi ringan, bisa berhenti kapan saja.
                            </p>
                        </div>
                    </div>

                    {{-- FORM --}}
                    <div class="w-full md:w-auto">
                        <form method="POST"
                            action="{{ route('newsletter.subscribe') }}"
                            id="newsletterForm"
                            class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                            @csrf

                            <input type="email"
                                name="newsletter_email"
                                required
                                placeholder="Masukkan email Anda"
                                class="flex-1 sm:flex-none
                                        sm:w-72 md:w-80 lg:w-96
                                        rounded-full border border-slate-300 bg-white px-4 py-2.5
                                        text-sm text-slate-900 placeholder:text-slate-400
                                        focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-500">

                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-full
                                        bg-slate-900 text-white px-5 py-2.5 text-sm font-semibold
                                        hover:bg-slate-800 transition">
                                Ikuti Update
                            </button>
                        </form>

                        {{-- container pesan sukses & error (bisa dipakai AJAX & non-AJAX) --}}
                        <p id="newsletterSuccess" class="mt-2 text-xs text-emerald-700">
                            @if(session('subscribed'))
                                Terima kasih, email Anda sudah terdaftar.
                            @endif
                        </p>

                        <p id="newsletterError" class="mt-2 text-xs text-red-600">
                            @if($errors->has('newsletter_email'))
                                {{ $errors->first('newsletter_email') }}
                            @endif
                        </p>

                        <p class="mt-2 text-[11px] text-slate-500">
                            Kami sertakan link untuk berhenti berlangganan di setiap email.
                        </p>
                    </div>

                </div>
            </div> -->
        </div>
    </section>

    {{-- ===================== SCRIPT ===================== --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput    = document.getElementById('searchInput');
            const brandSelect    = document.getElementById('brandSelect');
            const categorySelect = document.getElementById('categorySelect');
            const minInput       = document.getElementById('minInput');
            const maxInput       = document.getElementById('maxInput');
            const statusSelect   = document.getElementById('statusSelect');
            const sortSelect     = document.getElementById('sortSelect');
            const resetBtn       = document.getElementById('resetBtn');

            const grid            = document.getElementById('auctionGrid');
            const loadMoreWrapper = document.getElementById('loadMoreWrapper');
            const baseUrl         = `{{ route('home') }}`;

            // state query aktif (untuk load more)
            let currentParams = new URLSearchParams(window.location.search);

            // ====== Helper: debounce ======
            function debounce(fn, delay = 400) {
                let t;
                return (...args) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn(...args), delay);
                };
            }

            // ====== Build params dari UI ======
            function buildParamsFromUI() {
                const params = new URLSearchParams();

                if (searchInput.value.trim() !== '') {
                    params.set('q', searchInput.value.trim());
                }
                if (brandSelect.value) {
                    params.set('brand', brandSelect.value);
                }
                if (categorySelect && categorySelect.value) {
                    params.set('category', categorySelect.value);
                }
                if (minInput.value) {
                    params.set('min', minInput.value);
                }
                if (maxInput.value) {
                    params.set('max', maxInput.value);
                }
                if (statusSelect.value) {
                    params.set('status', statusSelect.value);
                }
                if (sortSelect.value) {
                    params.set('sort', sortSelect.value);
                }

                return params;
            }

            function showPriceError(message) {
                const el = document.getElementById('priceError');
                if (!el) return;

                el.textContent = message;
                el.classList.remove('hidden');

                // kasih border merah di input
                minInput?.classList.add('border-red-500');
                maxInput?.classList.add('border-red-500');
            }

            function clearPriceError() {
                const el = document.getElementById('priceError');
                if (!el) return;

                el.textContent = '';
                el.classList.add('hidden');

                minInput?.classList.remove('border-red-500');
                maxInput?.classList.remove('border-red-500');
            }

            // ====== Attach Load More untuk state params tertentu ======
            function attachLoadMore() {
                const loadMoreBtn = document.getElementById('loadMoreBtn');
                if (!loadMoreBtn || !grid) return;

                // reset handler biar nggak dobel kalau dipanggil lagi
                loadMoreBtn.onclick = async () => {
                    const nextPage = loadMoreBtn.dataset.nextPage;
                    if (!nextPage) return;

                    loadMoreBtn.disabled = true;
                    loadMoreBtn.textContent = 'Memuat...';

                    try {
                        const params = new URLSearchParams(currentParams.toString());
                        params.set('page', nextPage);

                        const res = await fetch(`${baseUrl}?${params.toString()}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });

                        if (!res.ok) throw new Error('Gagal memuat data');

                        const data = await res.json();

                        if (data.html) {
                            grid.insertAdjacentHTML('beforeend', data.html);
                            // kalau kamu punya init ulang countdown card, panggil di sini
                            // tick();
                        }

                        if (data.next_page) {
                            loadMoreBtn.dataset.nextPage = data.next_page;
                            loadMoreBtn.disabled = false;
                            loadMoreBtn.textContent = 'Tampilkan Lainnya';
                        } else {
                            loadMoreWrapper.innerHTML = '';
                        }
                    } catch (err) {
                        console.error(err);
                        loadMoreBtn.disabled = false;
                        loadMoreBtn.textContent = 'Coba lagi';
                    }
                };
            }

            attachLoadMore(); // initial

            // ====== Apply filter via AJAX ======
            const applyFilters = debounce(async () => {
                const minVal = minInput?.value !== '' ? Number(minInput.value) : null;
                const maxVal = maxInput?.value !== '' ? Number(maxInput.value) : null;

                if (minVal !== null && maxVal !== null && minVal > maxVal) {
                    showPriceError('Rentang harga tidak valid. Nilai minimum tidak boleh lebih besar dari maksimum.');
                    return;
                }

                clearPriceError();

                const params = buildParamsFromUI();
                currentParams = new URLSearchParams(params.toString()); // update state

                // update URL (tanpa reload)
                const qs = params.toString();
                const newUrl = qs ? `${baseUrl}?${qs}` : baseUrl;
                window.history.replaceState({}, '', newUrl);

                try {
                    const res = await fetch(newUrl, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });

                    if (res.status === 422) {
                        const data = await res.json();
                        showPriceError(data.error ?? 'Rentang harga tidak valid.');
                        return;
                    }

                    if (!res.ok) throw new Error('Gagal memuat data');

                    const data = await res.json();

                    if (data.html) {
                        grid.innerHTML = data.html;
                        //tick();
                    } else {
                        grid.innerHTML = '<p class="text-center text-slate-500 col-span-4">Tidak ada data.</p>';
                    }

                    if (data.next_page) {
                        loadMoreWrapper.innerHTML = `
                            <div class="mt-8 flex justify-center">
                                <button id="loadMoreBtn"
                                        data-next-page="${data.next_page}"
                                        class="inline-flex items-center rounded-lg bg-slate-900 text-white px-5 py-2.5 text-sm font-semibold hover:bg-slate-800">
                                    Tampilkan Lainnya
                                </button>
                            </div>
                        `;
                        attachLoadMore();
                    } else {
                        loadMoreWrapper.innerHTML = '';
                    }
                } catch (err) {
                    console.error(err);
                }
            }, 400);

            // ====== Event listeners: auto filter ======
            if (searchInput) {
                searchInput.addEventListener('input', applyFilters);
            }
            [brandSelect, categorySelect, statusSelect, sortSelect].forEach(el => {
                if (el) el.addEventListener('change', applyFilters);
            });
            if (minInput) minInput.addEventListener('input', applyFilters);
            if (maxInput) maxInput.addEventListener('input', applyFilters);

            // ====== Reset ======
            if (resetBtn) {
                resetBtn.addEventListener('click', () => {
                    // reset UI
                    if (searchInput) searchInput.value = '';
                    if (brandSelect) brandSelect.value = '';
                    if (categorySelect) categorySelect.value = '';
                    if (minInput) minInput.value = '';
                    if (maxInput) maxInput.value = '';
                    if (statusSelect) statusSelect.value = '';
                    if (sortSelect) sortSelect.value = 'new';

                    currentParams = new URLSearchParams();
                    applyFilters();
                });
            }
        });
    </script>
    <!-- <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tokenEl = document.querySelector('meta[name="csrf-token"]');
            if (!tokenEl) return;
            const csrf = tokenEl.getAttribute('content');

            function setWatchlistButtonState(btn, active) {
                btn.dataset.watchlisted = active ? '1' : '0';

                // border + background
                btn.classList.toggle('border-slate-900', active);
                btn.classList.toggle('bg-slate-900', active);
                btn.classList.toggle('text-white', active);

                btn.classList.toggle('border-slate-200', !active);
                btn.classList.toggle('bg-white', !active);
                btn.classList.toggle('text-slate-500', !active);

                // icon
                const outline = btn.querySelector('.icon-heart-outline');
                const fill    = btn.querySelector('.icon-heart-fill');
                if (outline && fill) {
                    outline.classList.toggle('hidden', active);
                    fill.classList.toggle('hidden', !active);
                }

                // title tooltip
                btn.title = active
                    ? 'Hapus dari Watchlist'
                    : 'Tambahkan ke Watchlist';
            }

            // Event delegation: berlaku juga untuk card yang di-load via AJAX
            document.addEventListener('click', async (e) => {
                const btn = e.target.closest('[data-watchlist-button]');
                if (!btn) return;

                e.preventDefault();
                if (btn.dataset.loading === '1') return;
                btn.dataset.loading = '1';

                try {
                    const url = btn.dataset.url;
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (!res.ok) {
                        throw new Error('HTTP ' + res.status);
                    }

                    const data = await res.json();

                    if (data.action === 'added') {
                        setWatchlistButtonState(btn, true);
                    } else if (data.action === 'removed') {
                        setWatchlistButtonState(btn, false);
                    }
                } catch (err) {
                    console.error(err);
                    alert('Gagal mengubah watchlist. Silakan coba lagi.');
                } finally {
                    btn.dataset.loading = '0';
                }
            });
        });
        </script> -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

            document.body.addEventListener('click', async (e) => {
                const btn = e.target.closest('[data-watchlist-button]');
                if (!btn) return;

                e.preventDefault();

                const url  = btn.getAttribute('data-url');
                const isWatchlisted = btn.getAttribute('data-watchlisted') === '1';

                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    const data = await res.json();
                    if (data.status !== 'ok') return;

                    const nowWatchlisted = data.action === 'added';
                    btn.setAttribute('data-watchlisted', nowWatchlisted ? '1' : '0');

                    // toggle icon & style
                    const outline = btn.querySelector('.icon-heart-outline');
                    const fill    = btn.querySelector('.icon-heart-fill');

                    if (nowWatchlisted) {
                        outline?.classList.add('hidden');
                        fill?.classList.remove('hidden');
                        btn.classList.remove('border-slate-200', 'bg-white', 'text-slate-500', 'hover:bg-slate-50');
                        btn.classList.add('border-slate-900', 'bg-slate-900', 'text-white');
                        btn.title = 'Hapus dari Watchlist';
                    } else {
                        outline?.classList.remove('hidden');
                        fill?.classList.add('hidden');
                        btn.classList.remove('border-slate-900', 'bg-slate-900', 'text-white');
                        btn.classList.add('border-slate-200', 'bg-white', 'text-slate-500', 'hover:bg-slate-50');
                        btn.title = 'Tambahkan ke Watchlist';
                    }

                    // kalau sedang di halaman watchlist, dan action = removed → hapus kartu
                    const watchlistPage = btn.closest('[data-watchlist-page]');
                    if (watchlistPage && data.action === 'removed') {
                        const card = btn.closest('[data-lot-card]');
                        if (card) card.remove();
                    }

                } catch (err) {
                    console.error('Watchlist toggle failed', err);
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form   = document.getElementById('newsletterForm');
            if (!form) return;

            const csrf         = document.querySelector('meta[name="csrf-token"]')?.content;
            const emailInput   = form.querySelector('input[name="newsletter_email"]');
            const successEl    = document.getElementById('newsletterSuccess');
            const errorEl      = document.getElementById('newsletterError');

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                // reset pesan
                if (successEl) successEl.textContent = '';
                if (errorEl)   errorEl.textContent   = '';

                const formData = new FormData(form);

                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    // kalau validasi gagal → Laravel balas 422 + JSON errors
                    if (res.status === 422) {
                        const data = await res.json();
                        const firstError = data.errors?.newsletter_email?.[0]
                            ?? 'Terjadi kesalahan validasi.';
                        if (errorEl) errorEl.textContent = firstError;
                        return;
                    }

                    if (!res.ok) {
                        throw new Error('HTTP ' + res.status);
                    }

                    const data = await res.json();
                    if (data.status === 'ok') {
                        if (successEl) {
                            successEl.textContent = data.message
                                ?? 'Terima kasih, email Anda sudah terdaftar.';
                        }
                        if (emailInput) {
                            emailInput.value = '';
                        }
                    }
                } catch (err) {
                    console.error('Newsletter AJAX failed', err);
                    if (errorEl) {
                        errorEl.textContent = 'Gagal mendaftarkan email. Silakan coba lagi.';
                    }
                }
            });
        });
    </script>
</x-guest-layout>
