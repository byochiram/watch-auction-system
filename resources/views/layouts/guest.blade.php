{{-- resources/views/layouts/guest.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth overscroll-y-none">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Tempus Auctions</title>

  {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('tempus/simbol3.png') }}">

  @vite(['resources/css/app.css','resources/js/app.js'])
  
  @livewireStyles

  <!-- Canonical -->
  <link rel="canonical" href="https://demo.themesberg.com/landwind/" />

  <!-- Meta SEO -->
  <meta name="title" content="Landwind - Tailwind CSS Landing Page" />
  <meta name="description" content="Get started with a free and open-source landing page built with Tailwind CSS and the Flowbite component library." />
  <meta name="robots" content="index, follow" />
  <meta name="language" content="English" />
  <meta name="author" content="Themesberg" />

  <!-- Social share -->
  <meta property="og:title" content="Landwind - Tailwind CSS Landing Page" />
  <meta property="og:site_name" content="Themesberg" />
  <meta property="og:url" content="https://demo.themesberg.com/landwind/" />
  <meta property="og:description" content="Landing page for Tailwind CSS + Flowbite." />
  <meta property="og:image" content="https://themesberg.s3.us-east-2.amazonaws.com/public/github/landwind/og-image.png" />
  <meta name="twitter:card" content="summary" />
  <meta name="twitter:site" content="@themesberg" />
  <meta name="twitter:creator" content="@themesberg" />

  <link
        rel="preload"
        as="image"
        href="{{ asset('tempus/hero2.webp') }}"
        fetchpriority="high"
    >
</head>

<body class="bg-white text-slate-800 antialiased overflow-x-hidden">
    @php
        $currentRoute = request()->route()->getName();
        $currentPath  = request()->path();

        $isProfilePage      = $currentRoute === 'profile.show';
        $isMyAuctionsPage   = str_starts_with($currentPath, 'my/auctions');
        $isTransactionsPage = str_starts_with($currentPath, 'my/transactions');
    @endphp

  <!-- HEADER -->
  <header class="fixed top-0 inset-x-0 z-50 w-full bg-slate-900/90 backdrop-blur border-b border-slate-700">
    <nav>
      <div class="mx-auto max-w-screen-xl px-4
                  h-16 md:h-20   {{-- tinggi header fix --}}
                  flex items-center justify-between">

        {{-- LOGO --}}
        <a href="{{ url('/') }}" class="flex items-center gap-3 group">
            <img src="{{ asset('tempus/logo2.png') }}"
                  class="h-20 sm:h-20 md:h-20 lg:h-24 w-auto object-contain"
                  alt="Tempus Auctions Logo" />
        </a>

        {{-- CTA kanan --}}
        <div class="flex items-center lg:order-2 gap-2">
          @auth
            {{-- Avatar + Dropdown (desktop) --}}
            <div class="relative hidden lg:block">
              <button
                  id="user-menu-button"
                  type="button"
                  class="flex items-center gap-2"
              >
                <img src="{{ Auth::user()->profile_photo_url }}"
                    class="w-9 h-9 rounded-full ring-2 ring-white/10"
                    alt="Avatar">

                <span class="text-white">
                  {{ Str::limit(Auth::user()->name, 18) }}
                </span>

                <svg class="w-4 h-4 text-gray-200"
                    viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd"
                        d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                        clip-rule="evenodd" />
                </svg>
              </button>

              <div
                  id="user-menu-dropdown"
                  class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow border border-slate-100 overflow-hidden z-50"
              >
                @if(! Auth::user()->isAdmin())
                  <a href="{{ route('profile.show') }}"
                    class="block px-4 py-2.5
                            {{ $isProfilePage
                                    ? 'bg-slate-100 text-slate-900 font-semibold'
                                    : 'hover:bg-slate-50 text-slate-700' }}">
                        Profil Akun
                    </a>

                    <a href="{{ url('/my/auctions') }}"
                    class="block px-4 py-2.5
                            {{ $isMyAuctionsPage
                                    ? 'bg-slate-100 text-slate-900 font-semibold'
                                    : 'hover:bg-slate-50 text-slate-700' }}">
                        Lelang Saya
                    </a>

                    <a href="{{ url('/my/transactions') }}"
                    class="block px-4 py-2.5
                            {{ $isTransactionsPage
                                    ? 'bg-slate-100 text-slate-900 font-semibold'
                                    : 'hover:bg-slate-50 text-slate-700' }}">
                        Transaksi Saya
                    </a>
                  <div class="border-t"></div>
                @else
                  <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 hover:bg-slate-50">Admin Dashboard</a>
                  <div class="border-t"></div>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button class="w-full text-left px-4 py-2.5 hover:bg-slate-50">Keluar</button>
                </form>
              </div>
            </div>
          @else
            {{-- Belum login --}}
            <a href="{{ route('login') }}"
              class="inline-flex items-center justify-center rounded-full
                      border border-white/10 bg-slate-900 text-[#F6C453]
                      px-4 py-1.5 text-sm font-medium tracking-wide
                      hover:bg-[#F6C453] hover:text-slate-900
                      hover:shadow-[0_0_18px_rgba(246,196,83,0.45)]
                      transition-all duration-300">
                Masuk
            </a>
            <a href="{{ route('register') }}"
              class="inline-flex items-center justify-center rounded-full
                      bg-[#F6C453] border border-[#F6C453]
                      px-4 py-1.5 text-sm font-semibold text-slate-900 tracking-wide
                      hover:bg-[#e5b652] hover:border-[#e5b652]
                      hover:shadow-[0_0_20px_rgba(246,196,83,0.55)]
                      transition-all duration-300">
                Daftar
            </a>
          @endauth
  
          {{-- Toggle mobile (hamburger) --}}
          <button data-collapse-toggle="mobile-menu"
                  type="button"
                  class="inline-flex items-center p-2 ml-1 text-sm text-gray-300 rounded-lg lg:hidden
                        hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-600"
                  aria-controls="mobile-menu" aria-expanded="false">
            <span class="sr-only">Open main menu</span>
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd"
                    d="M3 5h14a1 1 0 010 2H3a1 1 0 010-2zm0 4h14a1 1 0 010 2H3a1 1 0 010-2zm0 4h14a1 1 0 010 2H3a1 1 0 010-2z"
                    clip-rule="evenodd"></path>
            </svg>
          </button>
        </div>

        {{-- MENU DESKTOP --}}
        @php
            $current = request()->route()->getName();
        @endphp
        <ul class="hidden lg:flex lg:items-center lg:gap-8 font-medium">
            <li>
                <a href="{{ route('home') }}"
                  class="block py-2  {{ $current === 'home'
                        ? 'text-yellow-500'
                        : 'text-gray-200 border-transparent hover:text-yellow-500 transition' }}">
                    Beranda
                </a>
            </li>

            <li>
                <a href="{{ route('rules') }}"
                  class="block py-2  {{ $current === 'rules'
                        ? 'text-yellow-500'
                        : 'text-gray-200 border-transparent hover:text-yellow-500 transition' }}">
                    Panduan dan Aturan
                </a>
            </li>

            <li>
                <a href="{{ route('about') }}"
                  class="block py-2  {{ $current === 'about'
                        ? 'text-yellow-500'
                        : 'text-gray-200 border-transparent hover:text-yellow-500 transition' }}">
                    Tentang Kami
                </a>
            </li>
        </ul>

        {{-- MENU MOBILE --}}
        <div id="mobile-menu"
            class="hidden lg:hidden absolute left-0 right-0 top-full bg-slate-900/95 border-t border-slate-700 shadow-2xl">
          <nav class="px-4 py-4">
              {{-- HEADER AKUN KECIL DI ATAS --}}
              @auth
                <div class="mb-4 flex items-center gap-3 rounded-xl bg-slate-800/80 px-3 py-3">
                    <img src="{{ Auth::user()->profile_photo_url }}"
                        class="w-9 h-9 rounded-full ring-2 ring-yellow-400/60"
                        alt="Avatar">
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-white">
                            {{ Str::limit(Auth::user()->name, 22) }}
                        </span>
                        <span class="text-[11px] uppercase tracking-[0.15em] text-slate-400">
                            {{ Auth::user()->isAdmin() ? 'Administrator' : 'Bidder' }}
                        </span>
                    </div>
                </div>
              @endauth

              <ul class="space-y-1 text-base font-medium">

                  {{-- NAV UTAMA --}}
                  <li class="text-[11px] uppercase tracking-[0.2em] text-slate-500 px-1 mb-1">
                      Navigasi
                  </li>

                  <li>
                      <a href="{{ route('home') }}"
                        class="flex items-center justify-between rounded-lg px-3 py-3
                        {{ $current === 'home'
                              ? 'bg-slate-700/80 text-yellow-400'
                              : 'text-gray-100 hover:bg-slate-800 hover:text-yellow-400' }} transition">
                          <span>Beranda</span>
                      </a>
                  </li>

                  <li>
                      <a href="{{ route('rules') }}"
                        class="flex items-center justify-between rounded-lg px-3 py-3
                        {{ $current === 'rules'
                              ? 'bg-slate-700/80 text-yellow-400'
                              : 'text-gray-100 hover:bg-slate-800 hover:text-yellow-400' }} transition">
                          <span>Panduan dan Aturan</span>
                      </a>
                  </li>

                  <li>
                      <a href="{{ route('about') }}"
                        class="flex items-center justify-between rounded-lg px-3 py-3
                        {{ $current === 'about'
                              ? 'bg-slate-700/80 text-yellow-400'
                              : 'text-gray-100 hover:bg-slate-800 hover:text-yellow-400' }} transition">
                          <span>Tentang Kami</span>
                      </a>
                  </li>

                  {{-- MENU AKUN (MOBILE) --}}
                  @auth
                      <li class="pt-4 text-[11px] uppercase tracking-[0.2em] text-slate-500 px-1">
                          Akun
                      </li>

                      @if(! Auth::user()->isAdmin())
                          <li>
                            <a href="{{ route('profile.show') }}"
                            class="flex items-center justify-between rounded-lg px-3 py-3
                                    {{ $isProfilePage
                                            ? 'bg-slate-700/80 text-yellow-400'
                                            : 'text-gray-100 hover:bg-slate-800 hover:text-yellow-400' }} transition">
                                <span>Profil Akun</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ url('/my/auctions') }}"
                            class="flex items-center justify-between rounded-lg px-3 py-3
                                    {{ $isMyAuctionsPage
                                            ? 'bg-slate-700/80 text-yellow-400'
                                            : 'text-gray-100 hover:bg-slate-800 hover:text-yellow-400' }} transition">
                                <span>Lelang Saya</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ url('/my/transactions') }}"
                            class="flex items-center justify-between rounded-lg px-3 py-3
                                    {{ $isTransactionsPage
                                            ? 'bg-slate-700/80 text-yellow-400'
                                            : 'text-gray-100 hover:bg-slate-800 hover:text-yellow-400' }} transition">
                                <span>Transaksi Saya</span>
                            </a>
                        </li>
                      @else
                          <li>
                              <a href="{{ route('admin.dashboard') }}"
                                class="flex items-center justify-between rounded-lg px-3 py-3
                                        text-gray-100 hover:bg-slate-800 hover:text-yellow-400 transition">
                                  <span>Admin Dashboard</span>
                              </a>
                          </li>
                      @endif

                      <li>
                          <form method="POST" action="{{ route('logout') }}">
                              @csrf
                              <button
                                  class="w-full text-left rounded-lg px-3 py-3 text-gray-100 hover:bg-red-500/10 hover:text-red-400 transition">
                                  Keluar
                              </button>
                          </form>
                      </li>
                  @endauth
              </ul>
          </nav>
        </div>
      </div>
    </nav>
  </header>

  @auth
    @php
        $user = Auth::user();

        // Suspend AUTO (unpaid) -> ada tenggat
        $isAutoSuspended = $user->status === 'SUSPENDED'
            && $user->suspended_until
            && now()->lt($user->suspended_until);

        // Suspend MANUAL admin -> tanpa tenggat (suspended_until NULL)
        $isManualSuspended = $user->status === 'SUSPENDED'
            && ! $user->suspended_until;

        // Banner tampil untuk keduanya (kecuali admin)
        $showSuspendedBanner = ($isAutoSuspended || $isManualSuspended) && ! $user->isAdmin();

        // Text berbeda biar jelas
        $suspendTitle = $isAutoSuspended
            ? 'Akun Anda sedang ditangguhkan hingga'
            : 'Akun Anda sedang ditangguhkan oleh Admin';

        $suspendSubtitle = $isAutoSuspended
            ? 'Anda tetap dapat melihat lelang dan transaksi, namun tidak dapat mengikuti lelang baru selama masa penangguhan.'
            : 'Akun Anda dibatasi sementara. Silakan hubungi Admin untuk aktivasi kembali.';
    @endphp

    @if($showSuspendedBanner)
        <div
            id="suspended-banner"
            class="fixed left-0 right-0 top-16 md:top-20 z-40 bg-amber-50 border-b border-amber-200">

            <div class="max-w-screen-xl mx-auto px-4 py-2.5
                        flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 sm:gap-3
                        text-amber-800 text-xs sm:text-sm">

                <div class="flex items-start gap-2">
                    <div class="mt-0.5 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l6.518 11.59A1.75 1.75 0 0 1 16.768 18H3.232a1.75 1.75 0 0 1-1.493-3.311l6.518-11.59zM10 7a.75.75 0 0 0-.75.75v3.5a.75.75 0 0 0 1.5 0v-3.5A.75.75 0 0 0 10 7zm0 7a.9.9 0 1 0 0-1.8.9.9 0 0 0 0 1.8z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>

                    <div class="space-y-0.5">
                        <p class="font-semibold">
                            {{ $suspendTitle }}
                            @if($isAutoSuspended)
                                <span class="font-bold">
                                    {{ $user->suspended_until->format('d M Y, H:i') }}
                                </span>
                            @endif
                        </p>

                        <p class="text-[11px] sm:text-xs text-amber-900/90">
                            {{ $suspendSubtitle }}
                        </p>
                    </div>
                </div>

                <div class="shrink-0">
                    <a href="{{ route('rules') }}#penangguhan"
                    class="inline-flex items-center rounded-full border border-amber-300 px-3 py-1
                            text-[11px] font-medium text-amber-900 hover:bg-amber-100">
                        Lihat ketentuan
                    </a>
                </div>
            </div>
        </div>
    @endif
    @endauth

  {{-- CONTENT --}}
    @php
    $user = Auth::user();

    $isSuspendedForPadding = $user
        && $user->status === 'SUSPENDED'
        && (
            // auto suspend (unpaid)
            ($user->suspended_until && now()->lt($user->suspended_until))
            // manual suspend (admin)
            || (! $user->suspended_until)
        );
    @endphp

    <main class="mx-auto max-w-7xl px-4 {{ $isSuspendedForPadding ? 'pt-[158px]' : 'pt-28' }} pb-16">
        {{ $slot }}
    </main>

  {{-- FOOTER --}}
  <footer class="border-t border-slate-800 bg-slate-900 text-slate-200">
      <div class="max-w-screen-xl mx-auto px-4 py-4 space-y-6">

          {{-- TOP: BRAND + LINK UTAMA --}}
          <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
              {{-- BRAND + TAGLINE --}}
              <div class="space-y-2 max-w-md">
                  <div class="flex items-center gap-2">
                      <img src="{{ asset('tempus/logo2.png') }}"
                          class="h-20 w-auto object-contain"
                          alt="Tempus Auctions Logo">
                  </div>
                  <p class="text-sm text-slate-300">
                      Tempat terbaik untuk menemukan dan memenangkan jam tangan impian Anda.
                  </p>
              </div>

              {{-- NAVIGASI UTAMA + ICON KONTAK --}}
              <div class="flex flex-col gap-3 items-start lg:items-end lg:text-right md:mt-10">
                  {{-- NAVIGASI UTAMA (tetap) --}}
                  <nav class="flex flex-wrap gap-4 text-sm">
                      <a href="{{ route('home') }}"
                        class="hover:text-yellow-400 transition">
                          Beranda
                      </a>
                      <a href="{{ route('rules') }}"
                        class="hover:text-yellow-400 transition">
                          Panduan &amp; Aturan
                      </a>
                      <a href="{{ route('rules') }}#faq"
                        class="hover:text-yellow-400 transition">
                          FAQ
                      </a>
                      <a href="{{ route('about') }}"
                        class="hover:text-yellow-400 transition">
                          Tentang Kami
                      </a>
                  </nav>

                  {{-- ICON WA / IG / MAIL + EMAIL --}}
                  <div class="flex items-center gap-3 md:justify-end md:mt-3 text-sm text-slate-300">
                      {{-- WhatsApp --}}
                      <a href="https://wa.me/628995080305"
                        target="_blank" rel="noopener"
                        class="hover:text-yellow-400 transition"
                        title="WhatsApp">
                          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                              fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                              <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                          </svg>
                      </a>

                      {{-- Instagram --}}
                      <a href="https://www.instagram.com/tempus.collective"
                        target="_blank" rel="noopener"
                        class="hover:text-yellow-400 transition"
                        title="Instagram">
                          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                              fill="currentColor" class="bi bi-instagram" viewBox="0 0 16 16">
                              <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334"/>
                          </svg>
                      </a>

                      {{-- Email --}}
                      <a href="mailto:halo@tempuscollective.com"
                        class="hover:text-yellow-400 transition flex items-center gap-2"
                        title="Email">
                          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                              fill="currentColor" class="bi bi-envelope-at" viewBox="0 0 16 16">
                              <path d="M2 2a2 2 0 0 0-2 2v8.01A2 2 0 0 0 2 14h5.5a.5.5 0 0 0 0-1H2a1 1 0 0 1-.966-.741l5.64-3.471L8 9.583l7-4.2V8.5a.5.5 0 0 0 1 0V4a2 2 0 0 0-2-2zm3.708 6.208L1 11.105V5.383zM1 4.217V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v.217l-7 4.2z"/>
                              <path d="M14.247 14.269c1.01 0 1.587-.857 1.587-2.025v-.21C15.834 10.43 14.64 9 12.52 9h-.035C10.42 9 9 10.36 9 12.432v.214C9 14.82 10.438 16 12.358 16h.044c.594 0 1.018-.074 1.237-.175v-.73c-.245.11-.673.18-1.18.18h-.044c-1.334 0-2.571-.788-2.571-2.655v-.157c0-1.657 1.058-2.724 2.64-2.724h.04c1.535 0 2.484 1.05 2.484 2.326v.118c0 .975-.324 1.39-.639 1.39-.232 0-.41-.148-.41-.42v-2.19h-.906v.569h-.03c-.084-.298-.368-.63-.954-.63-.778 0-1.259.555-1.259 1.4v.528c0 .892.49 1.434 1.26 1.434.471 0 .896-.227 1.014-.643h.043c.118.42.617.648 1.12.648m-2.453-1.588v-.227c0-.546.227-.791.573-.791.297 0 .572.192.572.708v.367c0 .573-.253.744-.564.744-.354 0-.581-.215-.581-.8Z"/>
                          </svg>
                          <span class="text-slate-200">
                              halo@tempuscollective.com
                          </span>
                      </a>
                  </div>
              </div>
          </div>

          {{-- BOTTOM: LEGAL + KONTEN KECIL --}}
          <div class="border-t border-slate-800 pb-1 pt-4 flex flex-col gap-2 sm:flex-row md:flex-row sm:items-center md:items-center sm:justify-between md:justify-between">
              <p class="text-sm text-slate-400">
                  &copy; {{ date('Y') }} Tempus Auctions. Seluruh hak cipta dilindungi.
              </p>
              <div class="flex flex-wrap gap-4 text-sm text-slate-400">
                  <a href="{{ route('terms.show') }}" class="hover:text-yellow-400 transition">
                      Syarat &amp; Ketentuan
                  </a>
                  <span class="md:inline-block text-slate-500">•</span>
                  <a href="{{ route('policy.show') }}" class="hover:text-yellow-400 transition">
                      Kebijakan Privasi
                  </a>
              </div>
          </div>
      </div>
  </footer>

  <script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('reset-identity-checkbox', () => {
            const checkbox = document.querySelector('input[type="checkbox"][wire\\:model="state.confirm_identity"]');
            if (checkbox) checkbox.checked = false;
        });
    });
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
        const button   = document.getElementById('user-menu-button');
        const dropdown = document.getElementById('user-menu-dropdown');

        if (!button || !dropdown) return;

        // toggle saat avatar diklik
        button.addEventListener('click', (event) => {
            event.stopPropagation();
            dropdown.classList.toggle('hidden');
        });

        // klik di luar dropdown -> tutup
        document.addEventListener('click', () => {
            dropdown.classList.add('hidden');
        });
    });
  </script>

  <script>
      document.addEventListener('DOMContentLoaded', () => {
          function pad(num) {
              return num.toString().padStart(2, '0');
          }

          function tickAuctionCountdown() {
              const now   = Date.now();
              const cards = document.querySelectorAll('.auction-card');

              cards.forEach(card => {
                  const status    = card.dataset.status;   // ACTIVE / SCHEDULED / ENDED
                  const targetKey = card.dataset.target;   // 'start' atau 'end'
                  const container = card.querySelector('.countdown');

                  if (!container || !targetKey || status === 'ENDED') return;

                  const iso = card.dataset[targetKey];
                  if (!iso) return;

                  const targetTime = new Date(iso).getTime();
                  let msDiff       = targetTime - now;

                  if (msDiff <= 0) {
                      container.innerHTML = `
                          <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-3 py-1 text-xs font-semibold">
                              Sedang diproses...
                          </span>
                      `;
                      return;
                  }

                  const totalSeconds = Math.floor(msDiff / 1000);
                  const days    = Math.floor(totalSeconds / (60 * 60 * 24));
                  const hours   = Math.floor((totalSeconds % (60 * 60 * 24)) / (60 * 60));
                  const minutes = Math.floor((totalSeconds % (60 * 60)) / 60);
                  const seconds = totalSeconds % 60;

                  const daysEl    = container.querySelector('[data-part="days"]');
                  const hoursEl   = container.querySelector('[data-part="hours"]');
                  const minutesEl = container.querySelector('[data-part="minutes"]');
                  const secondsEl = container.querySelector('[data-part="seconds"]');

                  if (daysEl)    daysEl.textContent    = pad(days);
                  if (hoursEl)   hoursEl.textContent   = pad(hours);
                  if (minutesEl) minutesEl.textContent = pad(minutes);
                  if (secondsEl) secondsEl.textContent = pad(seconds);
              });
          }

          // simpan ke window kalau suatu saat mau dipanggil manual
          window.tickAuctionCountdown = tickAuctionCountdown;

          tickAuctionCountdown();
          setInterval(tickAuctionCountdown, 1000);
      });
  </script>

    @php
        $flash = [
            'success' => session('success'),
            'error'   => session('error'),
            'status'  => session('status'),
        ];

        if (! $flash['error']) {
            // hanya saat di halaman lot detail DAN error terkait bid
            if (request()->routeIs('lots.show') && ($errors->has('amount') || $errors->has('bid_amount'))) {
                $flash['error'] = $errors->first('amount')
                    ?? $errors->first('bid_amount')
                    ?? 'Bid gagal, silakan cek kembali isian Anda.';
            }
        }
    @endphp

    @if($flash['success'] || $flash['error'] || $flash['status'])
        <script>
            window._flash = {
                success: @json($flash['success']),
                error:   @json($flash['error']),
                status:  @json($flash['status']),
            };
        </script>
    @endif

  {{-- stack modals Jetstream (delete account, dll) --}}

    <!-- ===== Toast Stack (guest layout) ===== -->
    <div x-data class="fixed z-[110] right-4 top-4 space-y-2">
        <template x-for="t in $store.toast.items" :key="t.id">
            <div class="rounded-lg shadow px-4 py-2 text-sm text-white"
                :class="{
                    'bg-emerald-600': t.type === 'success',
                    'bg-red-600': t.type === 'error',
                    'bg-slate-800': t.type === 'info',
                    'bg-yellow-500': t.type === 'warn',
                }">
                <div class="flex items-start gap-3">
                    <span x-text="t.text"></span>
                    <button class="ml-2 opacity-80 hover:opacity-100"
                            @click="$store.toast.remove(t.id)">✕</button>
                </div>
            </div>
        </template>
    </div>

  @stack('modals')

  @livewireScripts

</body>
</html>
