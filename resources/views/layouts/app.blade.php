{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- <title>{{ config('app.name', 'Laravel') }}</title> -->
        <title>Tempus Auctions</title>

        {{-- Favicon --}}
        <link rel="icon" type="image/png" href="{{ asset('tempus/simbol3.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <x-banner />

        @livewire('navigation-menu')

        {{-- TIDAK PERLU pt-16 md:pt-20 LAGI --}}
        <div class="min-h-screen bg-gray-100">

            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-screen-xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main>
                {{ $slot }}
            </main>
        </div>

        @stack('modals')

        @livewireScripts
        
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" defer></script>

        <!-- ===== Dialog Component ===== -->
        <div x-data x-show="$store.dialog.open" x-cloak class="fixed inset-0 z-[120]">
        <div class="absolute inset-0 bg-black/50" @click="$store.dialog._cancel()"></div>
        <div class="absolute inset-0 grid place-items-center p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-5 shadow-2xl">
            <h3 class="text-lg font-semibold mb-2" x-text="$store.dialog.title"></h3>
            <div class="text-slate-700 whitespace-pre-line mb-4" x-text="$store.dialog.message"></div>
            <div class="flex justify-end gap-2">
                <template x-if="$store.dialog.showCancel">
                <button class="px-3 py-2 rounded border" @click="$store.dialog._cancel()">Batal</button>
                </template>
                <button class="px-3 py-2 rounded bg-yellow-500 text-slate-900 font-semibold"
                        @click="$store.dialog._ok()"
                        x-text="$store.dialog.confirmText"></button>
            </div>
            </div>
        </div>
        </div>

        <!-- ===== Toast Stack ===== -->
        <div x-data class="fixed z-[110] right-4 top-4 space-y-2">
        <template x-for="t in $store.toast.items" :key="t.id">
            <div class="rounded-lg shadow px-4 py-2 text-sm text-white"
                :class="{
                'bg-emerald-600': t.type==='success',
                'bg-red-600': t.type==='error',
                'bg-slate-800': t.type==='info',
                'bg-amber-600': t.type==='warn',
                }">
            <div class="flex items-start gap-3">
                <span x-text="t.text"></span>
                <button class="ml-2 opacity-80 hover:opacity-100" @click="$store.toast.remove(t.id)">✕</button>
            </div>
            </div>
        </template>
        </div>

        @php
            $hasErrors = $errors->any();
            $flash = [
                'success' => session('success'),
                'error'   => session('error'),
                'status'  => session('status'),
            ];
        @endphp
        @if(!$hasErrors && ($flash['success'] || $flash['error'] || $flash['status']))
            <script>
                window._flash = {
                success: @json($flash['success']),
                error:   @json($flash['error']),
                status:  @json($flash['status']),
                };
            </script>
        @endif

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // SELECT required
                document.querySelectorAll('select[required]').forEach(el => {
                    el.addEventListener('invalid', function () {
                        // hanya kalau benar-benar kosong
                        if (this.validity.valueMissing && !this.validity.customError) {
                            this.setCustomValidity('Silakan pilih salah satu item.');
                        }
                    });
                    el.addEventListener('input', function () {
                        this.setCustomValidity('');
                    });
                });

                // INPUT + TEXTAREA required
                document.querySelectorAll('input[required], textarea[required]').forEach(el => {
                    el.addEventListener('invalid', function () {
                        // kalau sudah ada customError (dari script lain), jangan di-overwrite
                        if (this.validity.customError) return;

                        if (this.validity.valueMissing) {
                            this.setCustomValidity('Field ini wajib diisi.');
                        } else if (this.validity.rangeUnderflow) {
                            this.setCustomValidity('Nilai terlalu kecil.');
                        } else if (this.validity.rangeOverflow) {
                            this.setCustomValidity('Nilai terlalu besar.');
                        } else if (this.validity.badInput || this.validity.stepMismatch) {
                            this.setCustomValidity('Masukkan nilai yang valid.');
                        }
                    });

                    el.addEventListener('input', function () {
                        // reset custom error saat user mengubah nilai
                        this.setCustomValidity('');
                    });
                });
            });
        </script>
        
        @stack('scripts')
    </body>
</html>
