{{-- resources/views/auth/login.blade.php --}}
<x-auth-layout>
    <div class="min-h-screen w-full grid grid-cols-1 md:grid-cols-5">

        {{-- PANEL KIRI --}}
        <div class="hidden md:flex md:col-span-2 flex-col justify-between
                    px-8 py-8 lg:px-12 lg:py-10
                    bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800">
            <div class="flex items-center gap-3">
                <x-authentication-card-logo class="h-9 w-9" />
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">
                        Tempus Auctions
                    </p>
                    <p class="text-sm text-slate-200">
                        Member Area
                    </p>
                </div>
            </div>

            <div class="mt-10 space-y-4 max-w-md">
                <h1 class="text-2xl lg:text-3xl font-semibold leading-snug">
                    Masuk dan mulai
                    <span class="text-amber-300">menawar</span> jam impian Anda.
                </h1>
                <p class="text-sm text-slate-300">
                    Simpan lot favorit, pantau bid secara real-time, dan selesaikan transaksi
                    dengan pengalaman lelang yang dirancang khusus untuk pecinta jam tangan.
                </p>

                <div class="mt-6 grid grid-cols-2 gap-4 text-xs text-slate-200/90">
                    <div class="flex items-start gap-2">
                        <span class="mt-1 h-5 w-5 rounded-full bg-amber-400/10 text-amber-300
                                     flex items-center justify-center text-[11px]">1</span>
                        <div>
                            <p class="font-medium">Pantau harga live</p>
                            <p class="text-[11px] text-slate-400">
                                Lihat pergerakan tawaran secara langsung di setiap lot.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="mt-1 h-5 w-5 rounded-full bg-amber-400/10 text-amber-300
                                     flex items-center justify-center text-[11px]">2</span>
                        <div>
                            <p class="font-medium">Notifikasi kemenangan</p>
                            <p class="text-[11px] text-slate-400">
                                Dapatkan info ketika Anda menjadi pemenang lelang.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <p class="mt-10 text-[11px] text-slate-500 max-w-sm">
                Keamanan akun Anda dilindungi. Kami tidak membagikan data login kepada pihak ketiga.
            </p>
        </div>

        {{-- PANEL FORM LOGIN (KANAN) --}}
        <div class="md:col-span-3 bg-slate-50 text-slate-50 
                    md:bg-white md:text-slate-900
                    px-4 py-8 sm:px-10 sm:py-10
                    flex items-center">

            <div class="w-full max-w-md mx-auto
                        bg-white rounded-2xl shadow-lg p-6 sm:p-8
                        md:max-w-xl md:mx-0
                        md:bg-transparent md:shadow-none md:rounded-none md:p-0">

                {{-- LOGO DI MOBILE --}}
                <div class="flex items-center justify-between mb-6 md:hidden">
                    <div class="flex items-center gap-3">
                        <x-authentication-card-logo class="h-9 w-9" />
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">
                                Tempus Auctions
                            </p>
                            <p class="text-sm text-slate-800">
                                Masuk ke akun Anda
                            </p>
                        </div>
                    </div>
                </div>

                <x-validation-errors class="mb-4" />

                @session('status')
                    <div class="mb-4 font-medium text-sm text-emerald-600 bg-emerald-50
                                border border-emerald-100 rounded-xl px-4 py-2.5">
                        {{ $value }}
                    </div>
                @endsession

                <div class="mb-6">
                    <h2 class="text-xl sm:text-2xl font-semibold text-slate-900">
                        Selamat datang kembali 👋
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Masuk untuk melanjutkan aktivitas lelang Anda.
                    </p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-label for="email" value="{{ __('Email') }}" required />
                        <x-input id="email"
                                 class="block mt-1 w-full rounded-xl border-slate-200
                                        focus:border-slate-500 focus:ring-slate-300"
                                 type="email"
                                 name="email"
                                 :value="old('email')"
                                 required autofocus autocomplete="username" />
                    </div>

                    {{-- FIELD PASSWORD --}}
                    <div>
                        <x-label for="password" value="{{ __('Password') }}" required />
                        <x-input id="password"
                                class="block mt-1 w-full rounded-xl border-slate-200
                                        focus:border-slate-500 focus:ring-slate-300"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />
                    </div>

                    {{-- REMEMBER + LUPA KATA SANDI DI SATU BARIS --}}
                    <div class="flex items-center justify-between pt-1">
                        <label for="remember_me" class="flex items-center gap-2">
                            <x-checkbox id="remember_me" name="remember" />
                            <span class="text-xs text-gray-600">
                                {{ __('Remember me') }}
                            </span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-xs font-medium text-slate-500 hover:text-slate-700"
                            href="{{ route('password.request') }}">
                                {{ __('Lupa kata sandi?') }}
                            </a>
                        @endif
                    </div>

                    <div class="pt-2">
                        <x-button class="w-full justify-center rounded-xl bg-slate-900 hover:bg-slate-800">
                            {{ __('Masuk') }}
                        </x-button>
                    </div>
                </form>

                <div class="mt-6">
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <span class="flex-1 h-px bg-slate-200"></span>
                        <span>atau</span>
                        <span class="flex-1 h-px bg-slate-200"></span>
                    </div>

                    <div class="mt-3 text-sm text-center text-slate-600">
                        Belum punya akun?
                        <a href="{{ route('register') }}"
                           class="font-semibold text-slate-900 hover:text-slate-700
                                  underline-offset-2 hover:underline">
                            Daftar sekarang
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-auth-layout>
