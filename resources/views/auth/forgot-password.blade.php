{{-- resources/views/auth/forgot-password.blade.php --}}
<x-auth-layout>
    <div class="h-screen w-full flex items-center justify-center bg-slate-50">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-6 sm:p-8">

            {{-- Logo + title --}}
            <div class="flex items-center gap-3 mb-6">
                <x-authentication-card-logo class="h-9 w-9" />
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">
                        Tempus Auctions
                    </p>
                    <p class="text-sm text-slate-800">
                        Reset kata sandi
                    </p>
                </div>
            </div>

            <div class="mb-4 text-sm text-slate-600">
                {{ __('Lupa kata sandi Anda? Tidak masalah. Masukkan alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.') }}
            </div>

            @session('status')
                <div class="mb-4 font-medium text-sm text-emerald-600 bg-emerald-50 border border-emerald-100 rounded-xl px-4 py-2.5">
                    {{ $value }}
                </div>
            @endsession

            <x-validation-errors class="mb-4" />

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf

                <div>
                    <x-label for="email" value="{{ __('Email') }}" required/>
                    <x-input id="email"
                             class="block mt-1 w-full rounded-xl border-slate-200
                                    focus:border-slate-500 focus:ring-slate-300"
                             type="email"
                             name="email"
                             :value="old('email')"
                             required autofocus autocomplete="username" />
                </div>

                <div class="pt-2">
                    <x-button class="w-full justify-center rounded-xl bg-slate-900 hover:bg-slate-800">
                        {{ __('Kirim Tautan Reset Kata Sandi') }}
                    </x-button>
                </div>
            </form>

            <div class="mt-6 text-center text-sm text-slate-600">
                <a href="{{ route('login') }}"
                   class="font-medium text-slate-900 hover:text-slate-700 underline-offset-2 hover:underline">
                    Kembali ke halaman masuk
                </a>
            </div>
        </div>
    </div>
</x-auth-layout>
