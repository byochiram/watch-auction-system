{{-- resources/views/admin/transactions/index.blade.php --}}
@php
  $shipPaymentId = session('ship_payment_id');
@endphp
<x-app-layout title="Transaksi">
    <div class="py-6"
         x-data="txPage(
            '{{ route('payments.index') }}',
            '{{ $tab }}',
            {{ session('modal') === 'ship' && $errors->any() ? 'true' : 'false' }},
            @js($shipPaymentId),
            @js(old('shipping_courier')),
            @js(old('shipping_tracking_no'))
        )"
         x-init="init()">

        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Transaksi
            </h2>
        </x-slot>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg">

                {{-- HEADER --}}
                <div class="px-6 pt-6 pb-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <h1 class="text-lg font-semibold text-slate-900">
                            Daftar Transaksi
                        </h1>
                        <p class="text-sm text-slate-500">
                            Pantau status pembayaran pemenang lelang di Tempus Auctions.
                        </p>
                    </div>
                </div>

                {{-- STAT CARDS --}}
                <div class="px-6 pt-4 pb-1 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                    <div class="rounded-xl border border-slate-100 bg-slate-50/70 px-3 py-3">
                        <div class="text-xs text-slate-500">Total Invoice</div>
                        <div class="mt-1 text-xl font-semibold text-slate-900">
                            {{ number_format($stats['total'] ?? 0,0,',','.') }}
                        </div>
                    </div>
                    <div class="rounded-xl border border-amber-100 bg-amber-50/60 px-3 py-3">
                        <div class="text-xs text-amber-700">Menunggu Pembayaran</div>
                        <div class="mt-1 text-xl font-semibold text-amber-800">
                            {{ number_format($stats['pending'] ?? 0,0,',','.') }}
                        </div>
                    </div>
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 px-3 py-3">
                        <div class="text-xs text-emerald-700">Sudah Dibayar</div>
                        <div class="mt-1 text-xl font-semibold text-emerald-800">
                            {{ number_format($stats['paid'] ?? 0,0,',','.') }}
                        </div>
                    </div>
                    <div class="rounded-xl border border-red-100 bg-red-50/60 px-3 py-3">
                        <div class="text-xs text-red-500">Expired</div>
                        <div class="mt-1 text-xl font-semibold text-red-900">
                            {{ number_format($stats['expired'] ?? 0,0,',','.') }}
                        </div>
                    </div>
                </div>

                {{-- TABS STATUS --}}
                <div class="flex justify-center mt-3 mb-2 px-6">
                    <div class="inline-flex gap-2 overflow-x-auto max-w-full whitespace-nowrap text-xs sm:text-sm">
                        <button type="button"
                                @click="changeTab('pending')"
                                class="px-3 py-2 rounded-full"
                                :class="tab === 'pending'
                                    ? 'bg-slate-900 text-white'
                                    : 'bg-slate-100 text-slate-700'">
                            Pending
                        </button>
                        <button type="button"
                                @click="changeTab('expired')"
                                class="px-3 py-2 rounded-full"
                                :class="tab === 'expired'
                                    ? 'bg-slate-900 text-white'
                                    : 'bg-slate-100 text-slate-700'">
                            Expired
                        </button>
                        <button type="button"
                                @click="changeTab('paid')"
                                class="px-3 py-2 rounded-full"
                                :class="tab === 'paid'
                                    ? 'bg-slate-900 text-white'
                                    : 'bg-slate-100 text-slate-700'">
                            Paid
                        </button>
                    </div>
                </div>

                {{-- FILTER BAR --}}
                <div class="px-6 pt-4 pb-3">
                    <form x-ref="filterForm"
                          class="grid gap-3 md:grid-cols-5 lg:grid-cols-7 text-[15px] items-center"
                          @submit.prevent>
                        {{-- tab dikendalikan tombol di atas --}}
                        <input type="hidden" name="tab" :value="tab">

                        {{-- search --}}
                        <div class="md:col-span-3 lg:col-span-3">
                            <input type="text"
                                   name="search"
                                   value="{{ $search ?? '' }}"
                                   placeholder="Cari invoice, nama, atau username…"
                                   class="w-full rounded-lg border-slate-300 text-[15px]"
                                   @input.debounce.500ms="apply()" />
                        </div>

                        {{-- URUTKAN --}}
                        <div>
                            <select name="sort"
                                    class="w-full rounded-lg border-slate-300 text-[15px]"
                                    @change="apply()">
                                <option value="">Urut terbaru</option>
                                <option value="issued_asc"  @selected(($sort ?? '') === 'issued_asc')>Invoice tertua dulu</option>
                                <option value="amount_desc" @selected(($sort ?? '') === 'amount_desc')>Total terbesar</option>
                                <option value="amount_asc"  @selected(($sort ?? '') === 'amount_asc')>Total terkecil</option>
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

                {{-- TABEL --}}
                <div class="px-6 pb-4">
                    <div class="overflow-x-auto" x-ref="tableWrap">
                        @include('admin.transactions._table', [
                            'payments' => $payments,
                            'tab'      => $tab,
                        ])
                    </div>
                </div>

                {{-- PAGINATION --}}
                <div class="px-6 py-3 border-t border-slate-100" x-ref="pager">
                    {{ $payments->onEachSide(1)->links() }}
                </div>
            </div>
        </div>

        {{-- MODAL INPUT / EDIT NO RESI (punyamu yang lama, copy apa adanya) --}}
        <div x-show="shipOpen" x-cloak
             class="fixed inset-0 z-[120] flex items-start justify-center overflow-y-auto">
            <div class="absolute inset-0 bg-black/50" @click="closeShip()"></div>

            <div class="relative w-full max-w-md mx-4 sm:mx-0 mt-10 mb-6 rounded-xl bg-white shadow-xl p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Pengiriman / No. Resi</h3>
                    <button @click="closeShip()">✕</button>
                </div>

                <form method="POST"
                      :action="shipAction"
                      @submit.prevent="submitShip($el)">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-4 text-sm">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Kurir <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="shipping_courier"
                                   x-model="shipForm.courier"
                                   class="w-full rounded-md border-gray-300"
                                   maxlength="20"
                                   required>
                            @error('shipping_courier')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Nomor Resi <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="shipping_tracking_no"
                                   x-model="shipForm.tracking"
                                   class="w-full rounded-md border-gray-300"
                                   maxlength="50"
                                   required>
                            @error('shipping_tracking_no')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button"
                                class="rounded-md bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200"
                                @click="closeShip()">
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

    </div>

    <script>
        function txPage(baseUrl, initialTab='pending', initialShipOpen=false, shipPaymentId=null, oldCourier='', oldTracking='') {
            return {
                baseUrl,
                tab: initialTab || 'pending',

                // modal resi
                shipOpen: false,
                shipAction: '#',
                shipForm: { id: null, courier: '', tracking: '' },

                init() {
                    this._wirePagination();

                    if (initialShipOpen && shipPaymentId) {
                        this.shipForm.id = shipPaymentId;
                        this.shipForm.courier = oldCourier || '';
                        this.shipForm.tracking = oldTracking || '';
                        this.shipAction = `/admin/transactions/${shipPaymentId}/shipping`;
                        this.shipOpen = true;
                    }
                },

                changeTab(newTab) {
                    this.tab = newTab;

                    // sync ke hidden input di filterForm
                    if (this.$refs.filterForm) {
                        const tabInput = this.$refs.filterForm.querySelector('input[name="tab"]');
                        if (tabInput) tabInput.value = newTab;
                    }

                    this.apply();

                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', newTab);
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

                // ==== MODAL RESI (boleh tetap yang lama) ====
                openShip(data) {
                    this.shipForm.id       = data.id;
                    this.shipForm.courier  = data.courier || '';
                    this.shipForm.tracking = data.tracking || '';
                    this.shipAction        = `/admin/transactions/${data.id}/shipping`;
                    this.shipOpen          = true;
                },
                closeShip() {
                    this.shipOpen = false;
                },
                async submitShip(formEl) {
                    if (!formEl.reportValidity()) return;

                    const ok = await Alpine.store('dialog')?.confirm({
                        title: 'Konfirmasi',
                        message: 'Simpan informasi pengiriman?',
                        confirmText: 'Simpan',
                    }) ?? true;

                    if (ok) formEl.submit();
                },
            }
        }
    </script>

</x-app-layout>
