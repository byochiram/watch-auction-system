{{-- admin/users/index.blade.php --}}
@php
    $resetModalUser = session('reset_user_id') ? \App\Models\User::find(session('reset_user_id')) : null;
@endphp
<x-app-layout title="Pengguna">
    <div class="py-6"
        x-data="userPage(
            '{{ route('users.index') }}',
            '{{ $currentRole ?: 'BIDDER' }}',
            '{{ url('/admin/users') }}',
            {{ session('modal') === 'create-admin' && $errors->any() ? 'true' : 'false' }},
            {{ session('modal') === 'reset-admin' && $errors->any() ? 'true' : 'false' }},
            @js($resetModalUser ? ['id'=>$resetModalUser->id, 'name'=>$resetModalUser->name, 'email'=>$resetModalUser->email] : null),
            @js($resetModalUser ? url('/admin/users/'.$resetModalUser->id.'/reset') : '')
        )"
        x-init="init()">

        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Pengguna
            </h2>
        </x-slot>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg">

                {{-- HEADER --}}
                <div class="px-6 pt-6 pb-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <h1 class="text-lg font-semibold text-slate-900">
                            Daftar Pengguna
                        </h1>
                        <p class="text-sm text-slate-500">
                            Kelola akun Admin &amp; Bidder yang terdaftar di Tempus Auctions.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        {{-- tombol tambah admin, hanya superadmin --}}
                        @if(auth()->check() && auth()->user()->isSuperAdmin())
                            <button type="button"
                                    @click="createOpen = true"
                                    class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-sm text-white hover:bg-blue-500">
                                + Tambah Admin
                            </button>
                        @endif
                    </div>
                </div>

                {{-- STAT CARDS --}}
                <div class="px-6 pt-4 pb-1 grid grid-cols-2 md:grid-cols-6 gap-3 text-sm">
                    <div class="rounded-xl border border-slate-100 bg-slate-50/70 px-3 py-3">
                        <div class="text-xs text-slate-500">Total Admin</div>
                        <div class="mt-1 text-xl font-semibold text-slate-900">
                            {{ number_format($userStats['admin'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 px-3 py-3">
                        <div class="text-xs text-emerald-700">Total Bidder</div>
                        <div class="mt-1 text-xl font-semibold text-emerald-800">
                            {{ number_format($userStats['bidder'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                {{-- TABS ADMIN / BIDDER --}}
                <div class="flex justify-center mt-3 mb-2">
                    <div class="inline-flex gap-2 text-sm">
                        <button type="button"
                                @click="changeTab('ADMIN')"
                                :class="tab === 'ADMIN'
                                    ? 'px-3 py-2 rounded-full bg-slate-900 text-white'
                                    : 'px-3 py-2 rounded-full bg-slate-100 text-slate-700'">
                            Admin
                        </button>
                        <button type="button"
                                @click="changeTab('BIDDER')"
                                :class="tab === 'BIDDER'
                                    ? 'px-3 py-2 rounded-full bg-slate-900 text-white'
                                    : 'px-3 py-2 rounded-full bg-slate-100 text-slate-700'">
                            Bidder
                        </button>
                    </div>
                </div>

                {{-- FILTER BAR (AJAX, tanpa tombol Terapkan) --}}
                <div class="px-6 pt-4 pb-3">
                    <form x-ref="filterForm"
                        class="grid gap-3 md:grid-cols-6 lg:grid-cols-8 text-[15px] items-center"
                        @submit.prevent>
                        {{-- role dikontrol oleh TAB --}}
                        <input type="hidden" name="role" :value="tab">

                        {{-- search --}}
                        <div class="md:col-span-3 lg:col-span-3">
                            <input type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Cari nama, username, atau email…"
                                class="w-full rounded-lg border-slate-300 text-[15px]"
                                @input.debounce.500ms="apply()" />
                        </div>

                        {{-- verifikasi --}}
                        <div>
                            <select name="verification"
                                    class="w-full rounded-lg border-slate-300 text-[15px]"
                                    @change="apply()">
                                <option value="">Verifikasi</option>
                                <option value="verified"   @selected($verification === 'verified')>Sudah Verifikasi</option>
                                <option value="unverified" @selected($verification === 'unverified')>Belum Verifikasi</option>
                            </select>
                        </div>

                        {{-- status aktif/inaktif --}}
                        <div>
                            <select name="status"
                                    class="w-full rounded-lg border-slate-300 text-[15px]"
                                    @change="apply()">
                                <option value="">Status</option>
                                <option value="ACTIVE"   @selected($status === 'ACTIVE')>Aktif</option>
                                <option value="SUSPENDED" @selected($status === 'SUSPENDED')>Ditangguhkan</option>
                            </select>
                        </div>

                        {{-- per page --}}
                        <div class="flex items-center gap-2">
                            <select name="per"
                                    class="rounded-lg border-slate-300 text-[15px]"
                                    @change="apply()">
                                @foreach([10,25,50] as $n)
                                    <option value="{{ $n }}" @selected((int)$perPage === $n)>{{ $n }}/Hal</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>

                {{-- TABEL (dibungkus untuk AJAX swap) --}}
                <div class="px-6 pb-4">
                    <div class="overflow-x-auto" x-ref="tableWrap">
                        @include('admin.users._table', [
                            'users'       => $users,
                            'currentRole' => $currentRole ?? 'BIDDER',
                        ])
                    </div>
                </div>

                {{-- PAGINATION --}}
                <div class="px-6 py-3 border-t border-slate-100" x-ref="pager">
                    {{ $users->onEachSide(1)->links() }}
                </div>
            </div>
        </div>

        {{-- MODAL: TAMBAH ADMIN --}}
        <div x-show="createOpen"
             x-cloak
             class="fixed inset-0 z-[100] flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50" @click="createOpen = false"></div>

            <div class="relative mx-4 w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-xl bg-white shadow-xl p-6"
                x-data="{
                    username: @js(old('username')),
                    email: @js(old('email')),

                    usernameStatus: '',      // teks kecil di kanan (tersedia / sudah dipakai)
                    emailStatus: '',         // teks kecil di kanan email
                    usernameFormatError: '', // error format di bawah input

                    debounceTimerUsername: null,
                    debounceTimerEmail: null,

                    // mirip script register: cek username dengan debounce + regex
                    checkUsername() {
                        clearTimeout(this.debounceTimerUsername);

                        this.debounceTimerUsername = setTimeout(async () => {
                            const value = (this.username || '').trim();

                            // reset tampilan
                            this.usernameStatus      = '';
                            this.usernameFormatError = '';

                            if (value === '') return;

                            // 1) cek format dulu
                            const regex = /^[a-z0-9._]+$/;
                            if (!regex.test(value)) {
                                this.usernameFormatError =
                                    'Username hanya boleh berisi huruf kecil, angka, tanpa spasi, garis bawah (_), atau titik (.).';
                                // format salah → jangan cek unik
                                return;
                            }

                            if (value.length < 3) {
                                // terlalu pendek, belum perlu cek unik
                                return;
                            }

                            // 2) cek unik ke server
                            try {
                                const res  = await fetch(`{{ route('check.username') }}?username=${encodeURIComponent(value)}`);
                                const data = await res.json();

                                if (data.exists) {
                                    this.usernameStatus = 'Username Sudah dipakai';
                                } else {
                                    this.usernameStatus = 'Username Tersedia ✓';
                                }
                            } catch (e) {
                                console.error('Gagal cek username', e);
                            }
                        }, 400);
                    },

                    // mirip script register: cek email hanya kalau sudah ada '@'
                    checkEmail() {
                        clearTimeout(this.debounceTimerEmail);

                        this.debounceTimerEmail = setTimeout(async () => {
                            const value = (this.email || '').trim();

                            this.emailStatus = '';

                            if (value === '' || !value.includes('@')) {
                                return;
                            }

                            try {
                                const res  = await fetch(`{{ route('check.email') }}?email=${encodeURIComponent(value)}`);
                                const data = await res.json();

                                if (data.exists) {
                                    this.emailStatus = 'Email Sudah terdaftar';
                                } else {
                                    this.emailStatus = 'Email Tersedia ✓';
                                }
                            } catch (e) {
                                console.error('Gagal cek email', e);
                            }
                        }, 400);
                    },
                }"
            >

                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Tambah Admin Baru</h3>
                    <button class="text-slate-500 hover:text-slate-800" @click="createOpen = false">✕</button>
                </div>

                <form method="POST" action="{{ route('users.store-admin') }}"
                      @submit.prevent="confirmAndSubmit($el,'Konfirmasi','Buat admin baru?','Simpan')">
                    @csrf
                    <div class="space-y-4 text-sm">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nama <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}"
                                   class="w-full rounded-md border-gray-300"
                                   required>
                            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-sm font-medium text-slate-700">
                                    Username
                                </label>
                                <p class="text-[12px]"
                                    :class="{
                                        'text-slate-400': !usernameStatus,
                                        'text-red-600': usernameStatus === 'Username Sudah dipakai',
                                        'text-emerald-600': usernameStatus === 'Username Tersedia ✓',
                                    }"
                                    x-text="usernameStatus">
                                </p>
                            </div>

                            <input type="text"
                                name="username"
                                x-model="username"
                                @input="checkUsername"
                                class="w-full rounded-md border-gray-300 text-sm"
                                :class="{
                                    'border-red-400': usernameFormatError !== '' || usernameStatus === 'Username Sudah dipakai',
                                    'border-emerald-400': usernameStatus === 'Username Tersedia ✓',
                                }">

                            {{-- error validasi server --}}
                            @error('username')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror

                            {{-- error format realtime (regex) --}}
                            <p x-text="usernameFormatError"
                            x-show="usernameFormatError !== ''"
                            x-cloak
                            class="mt-1 text-xs text-red-600">
                            </p>
                        </div>

                       <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-sm font-medium text-slate-700">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <p class="text-[12px]"
                                    :class="{
                                        'text-slate-400': !emailStatus,
                                        'text-red-600': emailStatus === 'Email Sudah terdaftar',
                                        'text-emerald-600': emailStatus === 'Email Tersedia ✓',
                                    }"
                                    x-text="emailStatus">
                                </p>
                            </div>

                            <input type="email"
                                name="email"
                                x-model="email"
                                @input="checkEmail"
                                class="w-full rounded-md border-gray-300 text-sm"
                                :class="{
                                    'border-red-400': emailStatus === 'Email Sudah terdaftar',
                                    'border-emerald-400': emailStatus === 'Email Tersedia ✓',
                                }"
                                required>

                            @error('email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            {{-- Password baru admin --}}
                            <div class="relative">
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    Password <span class="text-red-500">*</span>
                                </label>

                                <div class="mt-1">
                                    <input
                                        id="admin_new_password"
                                        type="password"
                                        name="password"
                                        class="w-full rounded-md border-gray-300"
                                        required
                                    >
                                </div>

                                @error('password')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror

                                {{-- error realtime --}}
                                <p id="admin_new_password_error" class="mt-1 text-xs"></p>

                                {{-- hint kriteria password (sama seperti register) --}}
                                <div id="admin_new_password_hint"
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
                                    </ul>
                                </div>
                            </div>

                            {{-- Konfirmasi password baru admin --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    Konfirmasi Password <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="admin_new_password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    class="w-full rounded-md border-gray-300"
                                    required
                                >
                                {{-- error realtime konfirmasi --}}
                                <p id="admin_new_password_confirm_error" class="mt-1 text-xs"></p>
                            </div>
                        </div>

                        <div class="pt-2 border-t">
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Password Anda <span class="text-red-500">*</span>
                            </label>
                            <p class="text-[13px] text-slate-500 mb-1">
                                Untuk keamanan, masukkan password akun Anda sendiri sebagai konfirmasi.
                            </p>
                            <input type="password" name="admin_password"
                                   class="w-full rounded-md border-gray-300"
                                   required>
                            @error('admin_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button"
                                class="rounded-md bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200"
                                @click="createOpen = false">
                            Batal
                        </button>
                        <button type="submit"
                                class="rounded-md bg-yellow-500 px-4 py-2 font-semibold text-slate-900 hover:bg-yellow-400">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL: RESET PASSWORD ADMIN (hanya superadmin) --}}
        <div x-show="resetOpen"
            x-cloak
            class="fixed inset-0 z-[110] flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50" @click="resetOpen = false"></div>

            <div class="relative mx-4 w-full max-w-md max-h-[90vh] overflow-y-auto rounded-xl bg-white shadow-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Reset Password Admin</h3>
                    <button class="text-slate-500 hover:text-slate-800" @click="resetOpen = false">✕</button>
                </div>

                <div class="mb-4 text-sm">
                    <p class="text-slate-600">
                        Anda akan mereset password untuk:
                    </p>
                    <p class="mt-1 font-medium text-slate-900" x-text="resetUser.name || '—'"></p>
                    <p class="text-sm text-slate-500" x-text="resetUser.email"></p>
                </div>

                <form method="POST"
                    :action="resetAction"
                    @submit.prevent="confirmAndSubmit($el,'Konfirmasi','Reset password admin ini?','Reset')">
                    @csrf
                    <div class="space-y-4 text-sm">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Password Baru <span class="text-red-500">*</span>
                            </label>
                            <input id="reset_password" type="password" name="password"
                                    class="w-full rounded-md border-gray-300" required>
                            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            <p id="reset_password_error" class="mt-1 text-xs"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Konfirmasi Password Baru <span class="text-red-500">*</span>
                            </label>
                            <input id="reset_password_confirmation" type="password" name="password_confirmation"
                                    class="w-full rounded-md border-gray-300" required>
                            @error('password_confirmation') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            <p id="reset_password_confirm_error" class="mt-1 text-xs"></p>
                        </div>

                        <div class="pt-2 border-t">
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Password Anda (Superadmin) <span class="text-red-500">*</span>
                            </label>
                            <p class="text-[13px] text-slate-500 mb-1">
                                Untuk keamanan, masukkan password akun Anda sebagai konfirmasi.
                            </p>
                            <input type="password" name="admin_password"
                                    class="w-full rounded-md border-gray-300" required>
                            @error('admin_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button"
                                class="rounded-md bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200"
                                @click="resetOpen = false">
                            Batal
                        </button>
                        <button type="submit"
                                class="rounded-md bg-red-500 px-4 py-2 font-semibold text-white hover:bg-red-400">
                            Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL: SUSPEND AKUN --}}
        <div x-show="suspendOpen"
            x-cloak
            class="fixed inset-0 z-[120] flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50" @click="suspendOpen = false"></div>

            <div class="relative mx-4 w-full max-w-md max-h-[90vh] overflow-y-auto rounded-xl bg-white shadow-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Tangguhkan Akun</h3>
                    <button class="text-slate-500 hover:text-slate-800" @click="suspendOpen = false">✕</button>
                </div>

                <p class="text-sm text-slate-600 mb-2">
                    Anda menangguhkan akun:
                </p>
                <p class="font-medium text-slate-900" x-text="suspendUser.name || '—'"></p>
                <p class="text-sm text-slate-500 mb-4" x-text="suspendUser.email"></p>

                <form :action="suspendAction"
                    method="POST"
                    @submit.prevent="confirmAndSubmit($el,'Konfirmasi','Tangguhkan akun ini?','Tangguhkan')">
                    @csrf
                    @method('PATCH')

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Alasan Penangguhan <span class="text-red-500">*</span></label>
                        <textarea name="suspend_reason"
                            class="w-full rounded-md border-gray-300 text-sm"
                            rows="4"
                            required
                            placeholder="Tulis alasan penangguhan…"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 mt-4">
                        <button type="button"
                            class="rounded-md bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200"
                            @click="suspendOpen = false">
                            Batal
                        </button>

                        <button type="submit"
                            class="rounded-md bg-red-600 px-4 py-2 font-semibold text-white hover:bg-red-500">
                            Tangguhkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function userPage(baseUrl, initialRole = 'BIDDER', baseResetUrl, initialCreateOpen = false,
                  initialResetOpen=false, initialResetUser=null, initialResetAction='') 
        {
            return {
                baseUrl,
                baseResetUrl,

                // buka modal create kalau ada error & flag modal = create-admin
                createOpen: !!initialCreateOpen,

                // tab awal
                tab: initialRole || 'BIDDER',

                // state modal reset
                resetOpen: false,
                resetUser: { id: null, name: '', email: '' },
                resetAction: '',

                // state modal suspend
                suspendOpen: false,
                suspendUser: { id: null, name: '', email: '' },
                suspendAction: '',

                init() {
                    this._wirePagination();
                    if (initialResetOpen && initialResetUser) {
                        this.resetUser = initialResetUser;
                        this.resetAction = initialResetAction;
                        this.resetOpen = true;
                    }
                },

                changeTab(newRole) {
                    this.tab = newRole;

                    if (this.$refs.filterForm) {
                        const roleInput = this.$refs.filterForm.querySelector('input[name="role"]');
                        if (roleInput) roleInput.value = newRole;
                    }

                    this.apply();

                    const url = new URL(window.location.href);
                    url.searchParams.set('role', newRole);
                    history.replaceState({}, '', url.toString());
                },

                apply() {
                    const form   = this.$refs.filterForm;
                    const params = new URLSearchParams(new FormData(form));
                    this._swap(`${this.baseUrl}?${params.toString()}`);
                },

                async _swap(url) {
                    const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const html = await res.text();
                    const tmp  = document.createElement('div');
                    tmp.innerHTML = html;

                    const newTable = tmp.querySelector('[x-ref="tableWrap"]');
                    const newPager = tmp.querySelector('[x-ref="pager"]');

                    if (newTable && this.$refs.tableWrap) {
                        this.$refs.tableWrap.innerHTML = newTable.innerHTML;
                    }
                    if (newPager && this.$refs.pager) {
                        this.$refs.pager.innerHTML = newPager.innerHTML;
                    }

                    this._wirePagination();
                    history.replaceState({}, '', url);
                },

                _wirePagination() {
                    if (!this.$refs.pager) return;

                    this.$refs.pager
                        .querySelectorAll('a[href*="page="]')
                        .forEach(a => {
                            a.addEventListener('click', (e) => {
                                e.preventDefault();
                                this._swap(a.href);
                            }, { once: true });
                        });
                },

                async confirmAndSubmit(formEl, title, msg, yesText = 'OK') {
                    // pastikan semua field valid dulu
                    if (!formEl.reportValidity()) return;

                    const ok = await Alpine.store('dialog')?.confirm({
                        title: title,
                        message: msg,
                        confirmText: yesText,
                    }) ?? true;

                    if (ok) formEl.submit();
                },

                // ==== RESET PASSWORD ADMIN MODAL ====
                openResetModal(user) {
                    this.resetUser   = user;
                    this.resetAction = `${this.baseResetUrl}/${user.id}/reset`;
                    this.resetOpen   = true;
                },

                // ==== SUSPEND MODAL ====
                openSuspendModal(user) {
                    this.suspendUser = user;
                    this.suspendAction = `/admin/users/${user.id}/suspend`;
                    this.suspendOpen = true;
                },
            }
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passInput  = document.getElementById('admin_new_password');
            const passConf   = document.getElementById('admin_new_password_confirmation');
            const passError  = document.getElementById('admin_new_password_error');
            const confError  = document.getElementById('admin_new_password_confirm_error');
            const passHint   = document.getElementById('admin_new_password_hint');

            if (!passInput) return;

            function validatePassword() {
                const value = passInput.value || '';

                passError.textContent = '';
                passError.className   = 'mt-1 text-xs';

                if (value === '') return;

                if (value.length < 8) {
                    passError.textContent = 'Kata sandi minimal 8 karakter.';
                    passError.classList.add('text-red-600');
                    return;
                }

                if (!/[A-Za-z]/.test(value)) {
                    passError.textContent = 'Kata sandi harus mengandung setidaknya satu huruf.';
                    passError.classList.add('text-red-600');
                    return;
                }

                if (!/[0-9]/.test(value)) {
                    passError.textContent = 'Kata sandi harus mengandung setidaknya satu angka.';
                    passError.classList.add('text-red-600');
                    return;
                }

                passError.textContent = 'Kata sandi memenuhi kriteria ✓';
                passError.classList.add('text-emerald-600');
            }

            function validatePasswordConfirmation() {
                if (!passConf || !confError) return;

                const pass = passInput.value || '';
                const conf = passConf.value || '';

                confError.textContent = '';
                confError.className   = 'mt-1 text-xs';

                if (conf === '') return;

                if (pass !== conf) {
                    confError.textContent = 'Konfirmasi password tidak sama.';
                    confError.classList.add('text-red-600');
                } else {
                    confError.textContent = 'Konfirmasi cocok ✓';
                    confError.classList.add('text-emerald-600');
                }
            }

            passInput.addEventListener('input', function () {
                validatePassword();
                validatePasswordConfirmation();
            });

            if (passConf) {
                passConf.addEventListener('input', validatePasswordConfirmation);
            }

            // Hint tampil saat fokus / hover
            if (passHint) {
                passInput.addEventListener('focus', () => passHint.classList.remove('hidden'));
                passInput.addEventListener('blur',  () => passHint.classList.add('hidden'));
                passInput.addEventListener('mouseenter', () => passHint.classList.remove('hidden'));
                passInput.addEventListener('mouseleave', () => {
                    if (document.activeElement !== passInput) {
                        passHint.classList.add('hidden');
                    }
                });
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
        const pass = document.getElementById('reset_password');
        const conf = document.getElementById('reset_password_confirmation');
        const passError = document.getElementById('reset_password_error');
        const confError = document.getElementById('reset_password_confirm_error');

        if (!pass || !conf) return;

        function validatePass() {
            const v = pass.value || '';
            passError.textContent = '';
            passError.className = 'mt-1 text-xs';

            if (v === '') return;

            if (v.length < 8) {
            passError.textContent = 'Kata sandi minimal 8 karakter.';
            passError.classList.add('text-red-600');
            return;
            }
            if (!/[A-Za-z]/.test(v)) {
            passError.textContent = 'Kata sandi harus mengandung setidaknya satu huruf.';
            passError.classList.add('text-red-600');
            return;
            }
            if (!/[0-9]/.test(v)) {
            passError.textContent = 'Kata sandi harus mengandung setidaknya satu angka.';
            passError.classList.add('text-red-600');
            return;
            }

            passError.textContent = 'Kata sandi memenuhi kriteria ✓';
            passError.classList.add('text-emerald-600');
        }

        function validateConf() {
            const p = pass.value || '';
            const c = conf.value || '';

            confError.textContent = '';
            confError.className = 'mt-1 text-xs';

            if (c === '') return;

            if (p !== c) {
            confError.textContent = 'Konfirmasi password tidak sama.';
            confError.classList.add('text-red-600');
            } else {
            confError.textContent = 'Konfirmasi cocok ✓';
            confError.classList.add('text-emerald-600');
            }
        }

        pass.addEventListener('input', () => { validatePass(); validateConf(); });
        conf.addEventListener('input', validateConf);
        });
    </script>

</x-app-layout>
