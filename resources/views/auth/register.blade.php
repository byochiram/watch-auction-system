{{-- resources/views/auth/register.blade.php --}}
<x-auth-layout>
    {{-- wrapper untuk state modal + layout split --}}
    <div x-data="{ showTerms: false, showPolicy: false }"
         class="min-h-screen w-full grid grid-cols-1 md:grid-cols-5">

        {{-- PANEL BRAND / INFO (KIRI) --}}
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
                        Buat akun baru
                    </p>
                </div>
            </div>

            <div class="mt-10 space-y-4 max-w-md">
                <h1 class="text-2xl lg:text-3xl font-semibold leading-snug">
                    Daftar dan mulai
                    <span class="text-amber-300">mengoleksi</span> jam impian Anda.
                </h1>
                <p class="text-sm text-slate-300">
                    Satu akun untuk mengikuti lelang, menyimpan watchlist, dan melacak
                    seluruh aktivitas bid Anda di Tempus Auctions.
                </p>
                <div class="mt-4 grid grid-cols-2 gap-4 text-xs text-slate-200/90">
                    <div class="flex items-start gap-2">
                        <span class="mt-1 h-5 w-5 rounded-full bg-amber-400/10 text-amber-300
                                     flex items-center justify-center text-[11px]">1</span>
                        <div>
                            <p class="font-medium">Profil tersimpan</p>
                            <p class="text-[11px] text-slate-400">
                                Data Anda aman dan tersusun untuk setiap transaksi.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="mt-1 h-5 w-5 rounded-full bg-amber-400/10 text-amber-300
                                     flex items-center justify-center text-[11px]">2</span>
                        <div>
                            <p class="font-medium">Pengalaman personal</p>
                            <p class="text-[11px] text-slate-400">
                                Simpan lot favorit dan pantau hanya yang penting untuk Anda.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <p class="mt-10 text-[11px] text-slate-500 max-w-sm">
                Dengan mendaftar, Anda setuju untuk menggunakan platform sesuai Syarat &amp; Ketentuan Tempus Auctions.
            </p>
        </div>

        {{-- PANEL FORM REGISTER (KANAN) --}}
        <div class="md:col-span-3 
                    bg-slate-50 text-slate-900 
                    md:bg-white 
                    px-4 py-8 sm:px-10 sm:py-10
                    flex items-center">

            {{-- CARD MOBILE --}}
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
                                Buat akun baru
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ERROR --}}
                <!-- <x-validation-errors class="mb-4" /> -->

                {{-- HEADER --}}
                <div class="mb-6">
                    <h2 class="text-xl sm:text-2xl font-semibold text-slate-900">
                        Buat akun Tempus Auctions
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Lengkapi data di bawah untuk mulai mengikuti lelang.
                    </p>
                </div>

                {{-- FORM REGISTER --}}
                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <x-label for="name" value="Nama" required />
                            <x-input id="name" placeholder="Nama Lengkap"
                                    class="placeholder:text-slate-400 placeholder:text-sm
                                            block mt-1 w-full rounded-xl border-slate-200
                                            focus:border-slate-500 focus:ring-slate-300"
                                    type="text" name="name" :value="old('name')"
                                    required autocomplete="name" />
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <x-label for="username" value="Username" required />
                                <p id="username-status" class="text-[11px] text-slate-400"></p>
                            </div>

                            <x-input id="username" placeholder="Username"
                                    class="placeholder:text-slate-400 placeholder:text-sm
                                            block mt-1 w-full rounded-xl border-slate-200
                                            focus:border-slate-500 focus:ring-slate-300"
                                    type="text" name="username" :value="old('username')"
                                    required autocomplete="username" />

                            {{-- error validasi server khusus username --}}
                            @error('username')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror

                            {{-- error format realtime (regex) --}}
                            <p id="username-format-error"
                            class="mt-1 text-xs text-red-600 hidden"></p>
                        </div>
                    </div>

                    {{-- EMAIL + PHONE DALAM 1 ROW --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- EMAIL --}}
                        <div>
                            <div class="flex items-center justify-between">
                                <x-label for="email" value="Email" required />
                                <p id="email-status" class="text-[11px] text-slate-400"></p>
                            </div>

                            <x-input id="email" placeholder="username@domain.com"
                                    class="placeholder:text-slate-400 placeholder:text-sm block mt-1 w-full rounded-xl border-slate-200
                                            focus:border-slate-500 focus:ring-slate-300"
                                    type="email" name="email" :value="old('email')"
                                    required autocomplete="email" />

                            @error('email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- NOMOR HP --}}
                        <div
                            x-data="{
                                value: '{{ old('phone') ? preg_replace('/^(\+62|0)/','',old('phone')) : '' }}',
                                minLength: 9,
                                invalidStart: false,
                                invalidLength: false,
                                onInput(e) {
                                    // hanya digit
                                    let v = e.target.value.replace(/[^0-9]/g, '');

                                    // max 12 digit
                                    if (v.length > 12) v = v.slice(0, 12);

                                    this.value = v;

                                    // cek aturan
                                    this.invalidStart  = (this.value.length > 0 && this.value[0] !== '8');
                                    this.invalidLength = (!this.invalidStart && this.value.length > 0 && this.value.length < this.minLength);
                                }
                            }"
                        >
                            <x-label for="phone" value="Nomor HP" required />

                            <div
                                class="mt-1 flex rounded-xl border border-slate-200
                                    focus-within:border-slate-500 focus-within:ring-2 focus-within:ring-slate-300 overflow-hidden"
                            >
                                {{-- Prefix +62 --}}
                                <span class="inline-flex items-center px-3 text-sm text-slate-600 bg-slate-50 border-r border-slate-200 select-none">
                                    +62
                                </span>

                                {{-- Input tanpa prefix --}}
                                <input  id="phone"
                                        name="phone"
                                        type="tel"
                                        x-model="value"
                                        x-on:input="onInput($event)"
                                        class="flex-1 border-0 bg-white px-3 py-2 text-sm rounded-r-xl
                                            focus:ring-0 focus:outline-none placeholder:text-slate-400 text-slate-900"
                                        placeholder="81234567890"
                                        required
                                        autocomplete="tel" />
                            </div>

                            {{-- ERROR realtime --}}
                            <p x-show="invalidStart"
                            x-cloak
                            class="mt-1 text-xs text-red-600">
                                Nomor harus dimulai dengan angka 8.
                            </p>

                            <p x-show="!invalidStart && invalidLength"
                            x-cloak
                            class="mt-1 text-xs text-red-600">
                                Nomor HP minimal 9 digit.
                            </p>

                            {{-- ERROR dari backend --}}
                            @error('phone')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="relative">
                            <x-label for="password" value="Kata Sandi" required />

                            {{-- wrapper biar hint bisa absolute relatif ke sini --}}
                            <div class="mt-1">
                                <x-input id="password" placeholder="Kata Sandi"
                                    class="placeholder:text-slate-400 placeholder:text-sm
                                            block w-full rounded-xl border-slate-200
                                            focus:border-slate-500 focus:ring-slate-300"
                                        type="password" name="password"
                                        required autocomplete="new-password" />
                            </div>

                            @error('password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror

                            {{-- error realtime password --}}
                            <p id="password-error" class="mt-1 text-xs"></p>

                            {{-- HINT KRITERIA PASSWORD --}}
                            <div id="password-hint"
                                class="absolute z-30 mt-2 w-full max-w-xs md:max-w-sm
                                        rounded-lg border border-slate-200 bg-white shadow-lg
                                        text-xs text-slate-600 p-3 hidden">

                                <p class="font-semibold text-slate-700 mb-1">
                                    Kriteria kata sandi:
                                </p>
                                <ul class="space-y-1">
                                    <li class="flex items-start gap-2">
                                        <span class="mt-0.5 text-emerald-500">✓</span>
                                        <span>Minimal 8 karakter.</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="mt-0.5 text-emerald-500">✓</span>
                                        <span>Mengandung setidaknya satu huruf (a–z atau A–Z).</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="mt-0.5 text-emerald-500">✓</span>
                                        <span>Mengandung setidaknya satu angka (0–9).</span>
                                    </li>

                                    @if(app()->isProduction())
                                        <li class="flex items-start gap-2">
                                            <span class="mt-0.5 text-emerald-500">✓</span>
                                            <span>Mengandung huruf besar dan huruf kecil.</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="mt-0.5 text-emerald-500">✓</span>
                                            <span>Mengandung setidaknya satu simbol (mis. ! @ # ?).</span>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>

                        <div>
                            <x-label for="password_confirmation" value="Konfirmasi Kata Sandi" required />
                            <x-input id="password_confirmation" placeholder="Konfirmasi Kata Sandi"
                                    class="placeholder:text-slate-400 placeholder:text-sm
                                            block mt-1 w-full rounded-xl border-slate-200
                                            focus:border-slate-500 focus:ring-slate-300"
                                    type="password" name="password_confirmation"
                                    required autocomplete="new-password" />

                            @error('password_confirmation')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror

                            {{-- error realtime konfirmasi --}}
                            <p id="password-confirm-error" class="mt-1 text-xs"></p>
                        </div>
                    </div>

                    {{-- TERMS --}}
                    @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                        <div class="pt-2">
                            <label class="flex items-start gap-2 text-xs text-gray-600 leading-relaxed">
                                <x-checkbox name="terms" id="terms" required />
                                <span>
                                    Saya setuju dengan
                                    <a href="{{ route('terms.show') }}"
                                        target="_blank"
                                        class="underline text-gray-700 hover:text-gray-900">
                                            Syarat Layanan
                                    </a>
                                    <!-- <button type="button" class="underline text-gray-700 hover:text-gray-900"
                                            @click="showTerms = true">
                                        Syarat Layanan
                                    </button> -->
                                    dan
                                    <a href="{{ route('policy.show') }}"
                                        target="_blank"
                                        class="underline text-gray-700 hover:text-gray-900">
                                            Kebijakan Privasi
                                    </a>.
                                    <!-- <button type="button" class="underline text-gray-700 hover:text-gray-900"
                                            @click="showPolicy = true">
                                        Kebijakan Privasi
                                    </button>. -->
                                </span>
                            </label>
                        </div>
                    @endif

                    <div class="pt-2">
                        <x-button class="w-full justify-center rounded-xl bg-slate-900 hover:bg-slate-800">
                            {{ __('Daftar') }}
                        </x-button>
                    </div>
                </form>

                {{-- FOOTER --}}
                <div class="mt-6">
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <span class="flex-1 h-px bg-slate-200"></span>
                        <span>atau</span>
                        <span class="flex-1 h-px bg-slate-200"></span>
                    </div>

                    <div class="mt-3 text-sm text-center text-slate-600">
                        Sudah punya akun?
                        <a href="{{ route('login') }}"
                        class="font-semibold text-slate-900 hover:text-slate-700 underline-offset-2 hover:underline">
                            Masuk
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= MODAL SYARAT LAYANAN ================= --}}
        <!-- <div
            x-show="showTerms"
            x-cloak
            class="fixed inset-0 z-40 flex items-center justify-center px-4"
            aria-modal="true"
            role="dialog"
            >
            {{-- overlay --}}
            <div class="absolute inset-0 bg-slate-900/50" @click="showTerms = false"></div>

            {{-- content --}}
            <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-lg p-6 md:p-8 z-50">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <h2 class="text-lg md:text-xl font-semibold text-slate-900">
                            Ringkasan Syarat Layanan
                        </h2>
                        <p class="text-xs md:text-sm text-slate-500">
                            Berlaku untuk penggunaan platform lelang jam tangan Tempus Auctions.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="text-slate-400 hover:text-slate-600"
                        @click="showTerms = false"
                    >
                        ✕
                    </button>
                </div>

                <div class="space-y-3 text-sm text-slate-700 max-h-80 overflow-y-auto">
                    <p>
                        Ini versi singkat. Versi lengkap bisa dibaca di halaman Syarat &amp; Ketentuan.
                    </p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Anda harus mendaftar dengan data yang benar (nama, username, email, password).</li>
                        <li>Anda bertanggung jawab menjaga kerahasiaan password dan aktivitas pada akun Anda.</li>
                        <li>Hanya pengguna terverifikasi yang dapat mengikuti lelang dan melakukan bid.</li>
                        <li>Setiap bid yang masuk ke sistem bersifat mengikat dan tidak dapat dibatalkan sepihak.</li>
                        <li>Pemenang lelang wajib menyelesaikan pembayaran sebelum batas waktu yang ditentukan.</li>
                        <li>Perilaku manipulatif (multi akun, bid palsu, dll.) dilarang dan dapat menyebabkan pemblokiran akun.</li>
                    </ul>
                </div>

                <div class="mt-6 flex items-center justify-between gap-3">
                    <a
                        href="{{ route('terms.show') }}"
                        target="_blank"
                        class="text-xs md:text-sm underline text-slate-600 hover:text-slate-900"
                    >
                        Baca Syarat &amp; Ketentuan lengkap
                    </a>

                    <x-button type="button" class="px-4 py-2" @click="showTerms = false">
                        Mengerti
                    </x-button>
                </div>
            </div>
        </div> -->

        {{-- ================= MODAL KEBIJAKAN PRIVASI ================= --}}
        <!-- <div
            x-show="showPolicy"
            x-cloak
            class="fixed inset-0 z-40 flex items-center justify-center px-4"
            aria-modal="true"
            role="dialog"
            >
            {{-- overlay --}}
            <div class="absolute inset-0 bg-slate-900/50" @click="showPolicy = false"></div>

            {{-- content --}}
            <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-lg p-6 md:p-8 z-50">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <h2 class="text-lg md:text-xl font-semibold text-slate-900">
                            Ringkasan Kebijakan Privasi
                        </h2>
                        <p class="text-xs md:text-sm text-slate-500">
                            Menjelaskan secara singkat bagaimana data Anda digunakan di Tempus Auctions.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="text-slate-400 hover:text-slate-600"
                        @click="showPolicy = false"
                    >
                        ✕
                    </button>
                </div>

                <div class="space-y-3 text-sm text-slate-700 max-h-80 overflow-y-auto">
                    <p>
                        Ini versi singkat. Versi lengkap bisa dibaca di halaman Kebijakan Privasi.
                    </p>

                    <ul class="list-disc list-inside space-y-1">
                        <li>Kami menyimpan data akun Anda (nama, username, email, password yang di-hash).</li>
                        <li>Kami menyimpan data profil dan riwayat bid/pembayaran untuk keperluan lelang dan audit.</li>
                        <li>Data KYC (misalnya KTP &amp; selfie) digunakan untuk verifikasi identitas dan pencegahan penipuan.</li>
                        <li>Data teknis seperti IP dan user agent digunakan untuk keamanan dan log aktivitas.</li>
                        <li>Kami tidak menjual data pribadi Anda ke pihak ketiga untuk tujuan pemasaran.</li>
                        <li>Anda dapat meminta pembaruan atau penutupan akun sesuai ketentuan yang berlaku.</li>
                    </ul>
                </div>

                <div class="mt-6 flex items-center justify-between gap-3">
                    <a
                        href="{{ route('policy.show') }}"
                        target="_blank"
                        class="text-xs md:text-sm underline text-slate-600 hover:text-slate-900"
                    >
                        Baca Kebijakan Privasi lengkap
                    </a>

                    <x-button type="button" class="px-4 py-2" @click="showPolicy = false">
                        Mengerti
                    </x-button>
                </div>
            </div>
        </div> -->
    </div>

    <script>
        function debounce(fn, delay) {
            let timer = null;
            return function (...args) {
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, args), delay);
            };
        }

        (function () {
            console.log('Script register realtime check LOADED');

            const usernameInput  = document.getElementById('username');
            const emailInput     = document.getElementById('email');
            const passwordInput  = document.getElementById('password');
            const passwordConf   = document.getElementById('password_confirmation');
            const passwordHint   = document.getElementById('password-hint');

            if (!usernameInput || !emailInput) {
                console.warn('Input username/email tidak ditemukan');
                return;
            }

            const usernameStatus      = document.getElementById('username-status');
            const emailStatus         = document.getElementById('email-status');
            const usernameFormatError = document.getElementById('username-format-error');

            const passwordError       = document.getElementById('password-error');
            const passwordConfError   = document.getElementById('password-confirm-error');

            // ========== USERNAME ==========
            const checkUsername = debounce(async () => {
                const value = usernameInput.value.trim();

                // reset tampilan
                usernameStatus.textContent = '';
                usernameStatus.className   = 'text-[11px] text-slate-400';
                usernameFormatError.textContent = '';
                usernameFormatError.classList.add('hidden');

                if (value === '') return;

                // 1) cek format dulu
                const regex = /^[a-z0-9._]+$/;
                if (!regex.test(value)) {
                    usernameFormatError.textContent =
                        'Username hanya boleh berisi huruf kecil, angka, tanpa spasi, garis bawah (_), atau titik (.).';
                    usernameFormatError.classList.remove('hidden');
                    // format salah → jangan cek unik
                    return;
                }

                if (value.length < 3) {
                    // opsional: jangan cek unik kalau terlalu pendek
                    return;
                }

                // 2) cek unik ke server
                try {
                    const res  = await fetch(`{{ route('check.username') }}?username=${encodeURIComponent(value)}`);
                    const data = await res.json();

                    if (data.exists) {
                        usernameStatus.textContent = 'Username sudah dipakai';
                        usernameStatus.className   = 'text-[12px] text-red-600';
                    } else {
                        usernameStatus.textContent = 'Username Tersedia ✓';
                        usernameStatus.className   = 'text-[12px] text-emerald-600';
                    }
                } catch (e) {
                    console.error('Gagal cek username', e);
                }
            }, 400);

            // ========== EMAIL ==========
            const checkEmail = debounce(async () => {
                const value = emailInput.value.trim();

                emailStatus.textContent = '';
                emailStatus.className   = 'text-[11px] text-slate-400';

                if (value === '' || !value.includes('@')) {
                    return;
                }

                try {
                    const res  = await fetch(`{{ route('check.email') }}?email=${encodeURIComponent(value)}`);
                    const data = await res.json();

                    if (data.exists) {
                        emailStatus.textContent = 'Email sudah terdaftar';
                        emailStatus.className   = 'text-[12px] text-red-600';
                    } else {
                        emailStatus.textContent = 'Email Tersedia ✓';
                        emailStatus.className   = 'text-[12px] text-emerald-600';
                    }
                } catch (e) {
                    console.error('Gagal cek email', e);
                }
            }, 400);
            
            // ========== PASSWORD ==========
            function validatePassword() {
                if (!passwordInput || !passwordError) return;

                const value = passwordInput.value || '';
                passwordError.textContent = '';
                passwordError.className   = 'mt-1 text-xs';

                if (value === '') return;

                if (value.length < 8) {
                    passwordError.textContent = 'Kata sandi minimal 8 karakter.';
                    passwordError.classList.add('text-red-600');
                    return;
                }

                if (!/[A-Za-z]/.test(value)) {
                    passwordError.textContent = 'Kata sandi harus mengandung setidaknya satu huruf.';
                    passwordError.classList.add('text-red-600');
                    return;
                }

                if (!/[0-9]/.test(value)) {
                    passwordError.textContent = 'Kata sandi harus mengandung setidaknya satu angka.';
                    passwordError.classList.add('text-red-600');
                    return;
                }

                // kalau mau sesuaikan rule production (mixedCase + simbol), bisa tambahin cek di sini.

                // kalau sudah oke semua
                passwordError.textContent = 'Kata sandi memenuhi kriteria ✓';
                passwordError.classList.add('text-emerald-600');
            }

            function validatePasswordConfirmation() {
                if (!passwordInput || !passwordConf || !passwordConfError) return;

                const pass = passwordInput.value || '';
                const conf = passwordConf.value || '';

                passwordConfError.textContent = '';
                passwordConfError.className   = 'mt-1 text-xs';

                if (conf === '') return;

                if (pass !== conf) {
                    passwordConfError.textContent = 'Konfirmasi password tidak sama.';
                    passwordConfError.classList.add('text-red-600');
                } else {
                    passwordConfError.textContent = 'Konfirmasi cocok ✓';
                    passwordConfError.classList.add('text-emerald-600');
                }
            }

            // ========== HINT PASSWORD (FOCUS / HOVER) ==========
            if (passwordInput && passwordHint) {
                // muncul saat fokus
                passwordInput.addEventListener('focus', () => {
                    passwordHint.classList.remove('hidden');
                });

                // sembunyi saat blur
                passwordInput.addEventListener('blur', () => {
                    passwordHint.classList.add('hidden');
                });

                // opsional: muncul juga saat pointer diarahkan
                passwordInput.addEventListener('mouseenter', () => {
                    passwordHint.classList.remove('hidden');
                });
                passwordInput.addEventListener('mouseleave', () => {
                    // kalau lagi fokus, jangan disembunyikan
                    if (document.activeElement !== passwordInput) {
                        passwordHint.classList.add('hidden');
                    }
                });
            }

            // ========== EVENT LISTENERS ==========
            usernameInput.addEventListener('input', checkUsername);
            emailInput.addEventListener('input', checkEmail);

            if (passwordInput) {
                passwordInput.addEventListener('input', debounce(() => {
                    validatePassword();
                    validatePasswordConfirmation();
                }, 200));
            }

            if (passwordConf) {
                passwordConf.addEventListener('input', debounce(() => {
                    validatePasswordConfirmation();
                }, 200));
            }
        })();
    </script>  
</x-auth-layout>
