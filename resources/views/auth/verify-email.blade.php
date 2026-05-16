{{-- resources/views/auth/verify-email.blade.php --}}
<x-auth-layout>
    <div
        x-data="{ showEditEmail: {{ $errors->has('email') ? 'true' : 'false' }} }"
        class="min-h-screen w-full flex items-center justify-center bg-slate-50 px-4"
    >
        <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-6 sm:p-8">
            {{-- Logo + title --}}
            <div class="flex items-center gap-3 mb-5">
                <x-authentication-card-logo class="h-9 w-9" />
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">
                        Tempus Auctions
                    </p>
                    <p class="text-sm text-slate-800">
                        Verifikasi email Anda
                    </p>
                </div>
            </div>

            {{-- Info utama --}}
            <div class="mb-5 text-sm text-slate-600 space-y-2">
                <p>
                    Sebelum melanjutkan, silakan verifikasi alamat email Anda melalui
                    tautan yang telah kami kirimkan.
                </p>
                <p>
                    Jika belum menerima email, Anda dapat mengirim ulang tautan
                    verifikasi atau memperbarui alamat email Anda terlebih dahulu.
                </p>
            </div>

            {{-- Status sukses kirim ulang --}}
            @if (session('status') === 'verification-link-sent')
                <div class="mb-4 font-medium text-sm text-emerald-600 bg-emerald-50 border border-emerald-100 rounded-xl px-4 py-2.5">
                    Link verifikasi baru telah dikirimkan ke email Anda.
                </div>
            @endif

            {{-- Aksi utama --}}
            <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                {{-- Kirim ulang verifikasi --}}
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <x-button type="submit">
                        Kirim Ulang Email Verifikasi
                    </x-button>
                </form>

                <div class="flex items-center gap-3">
                    {{-- Buka modal ubah email --}}
                    <button
                        type="button"
                        class="underline text-sm text-gray-600 hover:text-gray-900"
                        @click="showEditEmail = true"
                    >
                        Ubah Email
                    </button>

                    {{-- Logout --}}
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button
                            type="submit"
                            class="underline text-sm text-gray-600 hover:text-gray-900"
                        >
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ======================== MODAL UBAH EMAIL ========================== --}}
        <div
            x-show="showEditEmail"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-40 flex items-center justify-center px-4"
            aria-modal="true"
            role="dialog"
        >
            {{-- overlay --}}
            <div
                class="absolute inset-0 bg-black/50"
                @click="showEditEmail = false"
            ></div>

            {{-- modal content --}}
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-lg p-6 sm:p-8 z-50">
                <div class="flex justify-between items-start mb-4 gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            Ubah Email
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Masukkan email baru. Sistem akan mengirim ulang link verifikasi ke alamat tersebut.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="text-gray-400 hover:text-gray-600"
                        @click="showEditEmail = false"
                    >
                        ✕
                    </button>
                </div>

                <form method="POST" action="{{ route('email.update') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-label for="new_email" value="Email Baru" required />
                        <x-input
                            id="new_email"
                            class="block mt-1 w-full rounded-xl border-slate-200
                                   focus:border-slate-500 focus:ring-slate-300"
                            type="email"
                            name="email"
                            value="{{ old('email', auth()->user()->email) }}"
                            required
                            autofocus
                        />

                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3 mt-2">
                        <button
                            type="button"
                            class="text-sm text-gray-500 hover:text-gray-700"
                            @click="showEditEmail = false"
                        >
                            Batal
                        </button>

                        <x-button type="submit">
                            Simpan
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-auth-layout>
