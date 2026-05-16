{{-- resources/views/bidder/transactions/show.blade.php --}}
<x-guest-layout :title="'Detail Transaksi '.$payment->invoice_no">
    @php
        $lot     = $payment->lot;
        $product = $lot?->product;
        $img     = optional($product?->images->first())->public_url ?? asset('tempus/placeholder.jpg');

        $status  = $payment->status ?? 'PENDING';

        $statusLabel = strtoupper($status);
        $statusClasses = match ($status) {
            'PAID'      => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
            'PENDING'   => 'bg-amber-50 text-amber-800 ring-amber-100',
            'EXPIRED'   => 'bg-red-50 text-red-700 ring-red-100',
            'CANCELLED' => 'bg-slate-200 text-slate-700 ring-slate-300',
            default     => 'bg-slate-100 text-slate-700 ring-slate-200',
        };

        $baseAmount  = (int) ($payment->amount_due ?? 0);                  // harga lelang
        $shippingFee = (int) ($payment->shipping_fee ?? 0);                // dari RajaOngkir
        $serviceFee  = 500;                                                // biaya layanan flat

        $grandTotal  = $baseAmount + $shippingFee + $serviceFee;

        $subtotalFormatted = 'Rp '.number_format($baseAmount, 0, ',', '.');
        $shippingFormatted = 'Rp '.number_format($shippingFee, 0, ',', '.');
        $serviceFormatted  = 'Rp '.number_format($serviceFee, 0, ',', '.');
        $totalFormatted    = 'Rp '.number_format($grandTotal, 0, ',', '.');

        $code           = $payment->invoice_no ?? ('INV-'.$payment->id);

        $isExpired = $payment->status === 'EXPIRED'
            || ($payment->expires_at && now()->gt($payment->expires_at) && $payment->status !== 'PAID');

        $user = isset($user) ? $user : auth()->user();
        $suspendedUntilText = ($user && $user->suspended_until)
            ? $user->suspended_until->format('d M Y, H:i')
            : null;

        $canEditShipping = config('rajaongkir.ui_dev_mode')
            || ($status === 'PENDING' && ! $isExpired);

        // Normalisasi nomor HP dari payment (untuk diisi ke field +62 ...)
        $phoneLocal = $payment->phone
            ? preg_replace('/^(\+62|0)/', '', preg_replace('/\D/', '', $payment->phone))
            : '';

        // ===== ETA: ubah "3 day" / "1-3 day" → rentang tanggal, dihitung dari paid_at / issued_at / created_at =====
        $shippingEtaText = null;
        if ($payment->shipping_etd) {
            $etdRaw = trim($payment->shipping_etd);
            $minDay = $maxDay = null;

            if (preg_match('/(\d+)\s*-\s*(\d+)/', $etdRaw, $m)) {
                $minDay = (int) $m[1];
                $maxDay = (int) $m[2];
            } elseif (preg_match('/(\d+)/', $etdRaw, $m)) {
                $minDay = (int) $m[1];
                $maxDay = (int) $m[1];
            }

            $baseDate = $payment->paid_at ?? $payment->issued_at ?? $payment->created_at;

            if ($baseDate && $minDay) {
                $start = $baseDate->copy()->addDays($minDay);
                $end   = $baseDate->copy()->addDays($maxDay);

                if ($maxDay && $maxDay !== $minDay) {
                    // kalau masih di bulan & tahun yang sama, cukup tulis dd–dd M Y
                    if ($start->format('mY') === $end->format('mY')) {
                        $shippingEtaText = $start->format('d').'–'.$end->format('d M Y');
                    } else {
                        $shippingEtaText = $start->format('d M Y').' – '.$end->format('d M Y');
                    }
                } else {
                    $shippingEtaText = $start->format('d M Y');
                }
            }
        }
        $hasAddress = !empty($payment->address)
            && !empty($payment->postal_code)
            && !empty($payment->phone)
            && !empty($payment->shipping_rajaongkir_district_id);

        $hasShipping = !empty($payment->shipping_courier) && (int)$payment->shipping_fee > 0;

        $readyToPay = $hasAddress && $hasShipping;
    @endphp

    <div class="max-w-screen-xl mx-auto px-4 space-y-6">
        {{-- Back link --}}
        <div class="text-sm">
            <a href="{{ route('transactions.index') }}"
            class="inline-flex items-center gap-2
                    text-slate-700 hover:text-slate-900
                    bg-slate-50 border border-slate-200
                    px-3 py-1.5 rounded-full
                    hover:bg-slate-100 hover:border-slate-300
                    transition-all duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                        d="M15 19l-7-7 7-7" />
                </svg>
                <span class="text-[12px] font-semibold tracking-tight">
                    Kembali ke daftar transaksi
                </span>
            </a>
        </div>

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div class="md:max-w-xl">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                    Detail Transaksi
                </p>
                <h1 class="mt-1 text-2xl md:text-3xl font-semibold text-slate-900 break-words">
                    {{ $code }}
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Invoice untuk lot lelang yang Anda menangkan.
                </p>
            </div>

            <div class="flex flex-col items-start md:items-end gap-2 md:flex-shrink-0 md:text-right">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $statusClasses }}">
                    Status: {{ $statusLabel }}
                </span>
            </div>
        </div>

        {{-- MAIN CONTENT --}}
        <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">

            {{-- KOLOM KIRI: lot + rincian + ongkir --}}
            <div class="space-y-4">
                {{-- Info lot / produk --}}
                <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-4 sm:p-5 flex gap-4">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden bg-slate-100 flex-shrink-0 ring-1 ring-slate-200/70">
                        <img src="{{ $img }}" alt="{{ $lot->title ?? 'Lot' }}" class="w-full h-full object-cover">
                    </div>
                    <div class="space-y-1 min-w-0">
                        <p class="text-xs text-slate-500">
                            Lot #{{ $lot->id ?? '—' }}
                        </p>
                        <h2 class="text-base sm:text-lg font-semibold text-slate-900 break-words">
                            {{ $lot->title ?? (($product->brand ?? '-') . ' ' . ($product->model ?? '')) }}
                        </h2>
                        @if($lot)
                            <a href="{{ route('lots.show', ['lot' => $lot, 'from' => 'transactions']) }}"
                            class="inline-flex items-center text-[13px] text-blue-600 hover:underline">
                                Lihat halaman lot
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Rincian Pemesanan --}}
                <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-4 sm:p-5 space-y-3 text-sm">
                    <h3 class="font-semibold text-slate-900 mb-1">Rincian Pemesanan</h3>

                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 text-xs sm:text-sm space-y-1">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-2 mt-2 ml-2 mb-2 text-sm">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-700">Invoice diterbitkan</dt>
                                <dd class="text-slate-800">
                                    {{ $payment->issued_at?->format('d M Y, H:i') ?? '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-700">Jatuh tempo</dt>
                                <dd class="text-slate-800">
                                    {{ $payment->expires_at?->format('d M Y, H:i') ?? '-' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-700">Pembayaran diterima</dt>
                                <dd class="text-slate-800">
                                    {{ $payment->paid_at?->format('d M Y, H:i') ?? 'Belum dibayar' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-700">Waktu pengiriman</dt>
                                <dd class="text-slate-800">
                                    {{ $payment->shipping_shipped_at?->format('d M Y, H:i') ?? '-' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-700">Pesanan selesai</dt>
                                <dd class="text-slate-800">
                                    {{ $payment->shipping_completed_at?->format('d M Y, H:i') ?? 'Belum diselesaikan' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-700">Metode pembayaran</dt>
                                <dd class="text-slate-800">
                                    Duitku (virtual account / e-wallet)
                                </dd>
                            </div>
                        </dl>
                    </div>

                    @if($status === 'EXPIRED')
                        <p class="mt-2 text-xs text-red-700 bg-red-50 border border-red-100 rounded-lg px-3 py-2 leading-relaxed">
                            Batas pembayaran 1×24 jam terlewati tanpa pembayaran. Barang dinyatakan
                            <span class="font-semibold">unsold</span>
                            dan akun Anda ditangguhkan sementara waktu
                            @if($suspendedUntilText)
                                hingga <span class="font-semibold">{{ $suspendedUntilText }}</span>
                            @else
                                hingga masa penangguhan berakhir
                            @endif
                            sesuai ketentuan. Jika menurut Anda ini keliru, silakan hubungi admin.
                        </p>
                    @elseif($status === 'PAID')
                        <p class="mt-2 text-xs text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg px-3 py-2">
                            Terima kasih, pembayaran Anda sudah kami terima. Barang akan diproses sesuai ketentuan.
                        </p>
                    @endif
                </div>

                {{-- Alamat & Ongkos Kirim --}}
                <section
                        x-data="shippingForm(@js($canEditShipping), @js($errors->shippingAddress->any()))"
                        x-init="init()"
                        class="bg-white border border-slate-100 rounded-2xl shadow-sm p-4 sm:p-5 space-y-4"
                    >
                    <div class="flex items-start justify-between gap-3">
                    
                            <h3 class="font-semibold text-sm text-slate-900">
                                Alamat & Ongkos Kirim
                            </h3>
                        
                    </div>
                    {{-- Alamat ringkas --}}
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 text-xs sm:text-sm space-y-1">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs mb-2 mt-2 ml-2 font-semibold text-slate-700 uppercase tracking-[0.14em]">
                                Alamat pengiriman
                            </span>

                            @if($canEditShipping)
                                <button type="button"
                                        class="text-[13px] text-blue-600 mr-2 hover:underline"
                                        @click="toggleEditAddress()">
                                    <span x-show="!editAddress">Ubah alamat</span>
                                    <span x-show="editAddress">Tutup</span>
                                </button>
                            @endif
                        </div>

                        <p class="text-slate-700 ml-2">
                            {{ $payment->address ?? '-' }}
                        </p>

                        <p class="text-slate-700 ml-2">
                            KEC. {{ strtoupper($payment->district_name) }},
                            {{ strtoupper($payment->city_name ?? '-') }},
                            {{ strtoupper($payment->province_name ?? '-') }}
                            {{ $payment->postal_code }}
                        </p>
                        @if($payment->phone)
                            <p class="text-slate-500 ml-2 mb-2">Telp: {{ $payment->phone }}</p>
                        @endif
                    </div>

                    {{-- Form ubah alamat (lazy load RajaOngkir) --}}
                    @if($canEditShipping)
                        <form method="POST"
                            action="{{ route('shipping.address.update', $payment) }}"
                            x-show="editAddress"
                            x-cloak
                            class="border border-slate-100 rounded-xl p-3 sm:p-4 text-xs sm:text-sm space-y-4 bg-white/60">
                            @csrf

                            {{-- Detail alamat --}}
                            <div>
                                <label class="block text-xs font-medium text-slate-600">
                                    Detail alamat <span class="text-red-500">*</span>
                                </label>
                                <textarea name="address"
                                        x-model="addressLine"
                                        rows="2"
                                        class="placeholder:text-slate-400 mt-1 w-full rounded-md border-slate-300 text-sm"
                                        placeholder="Nama jalan, nomor rumah, RT/RW, dsb"></textarea>
                                @error('address', 'shippingAddress')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Baris 1 & 2: Provinsi/Kecamatan di kiri, Kota + (Kode pos & No HP) di kanan --}}
                            <div class="grid gap-3 md:grid-cols-2">
                                {{-- Kolom kiri: Provinsi + Kecamatan --}}
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-xs font-medium text-slate-600">Provinsi <span class="text-red-500">*</span></label>
                                        <select x-model="provinceId"
                                                @change="onProvinceChange"
                                                class="mt-1 w-full rounded-md border-slate-300 text-sm">
                                            <option value="">Pilih provinsi</option>
                                            <template x-for="p in provinces" :key="p.id">
                                                <option :value="p.id" x-text="p.name"></option>
                                            </template>
                                        </select>
                                        @error('province_id', 'shippingAddress')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="text-xs font-medium text-slate-600">Kecamatan <span class="text-red-500">*</span></label>
                                        <select x-model="districtId"
                                                @change="onDistrictChange"
                                                class="mt-1 w-full rounded-md border-slate-300 text-sm">
                                            <option value="">Pilih kecamatan</option>
                                            <template x-for="d in districts" :key="d.id">
                                                <option :value="d.id" x-text="d.name"></option>
                                            </template>
                                        </select>
                                        @error('district_id', 'shippingAddress')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Kolom kanan: Kota + (Kode pos + No HP) --}}
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-xs font-medium text-slate-600">Kota / Kabupaten <span class="text-red-500">*</span></label>
                                        <select x-model="cityId"
                                                @change="onCityChange"
                                                class="mt-1 w-full rounded-md border-slate-300 text-sm">
                                            <option value="">Pilih kota/kabupaten</option>
                                            <template x-for="c in cities" :key="c.id">
                                                <option :value="c.id" x-text="c.name"></option>
                                            </template>
                                        </select>
                                        @error('city_id', 'shippingAddress')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Kode pos kecil + No HP (lebih lebar) --}}
                                    <div class="grid gap-3 md:grid-cols-3">
                                        <div>
                                            <label class="block mt-1 text-xs font-medium text-slate-600">
                                                Kode pos <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text"
                                                name="postal_code"
                                                x-model="postalCode"
                                                class="mt-1 w-full rounded-md border-slate-300 text-sm" />
                                            @error('postal_code', 'shippingAddress')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        {{-- No HP: 2 kolom (lebih lebar) --}}
                                        <div
                                            class="md:col-span-2"
                                            x-data="{
                                                value: '{{ $phoneLocal }}',
                                                minLength: 9,
                                                invalidStart: false,
                                                invalidLength: false,
                                                onInput(e) {
                                                    let v = e.target.value.replace(/[^0-9]/g, '');
                                                    if (v.length > 12) v = v.slice(0, 12);
                                                    this.value = v;
                                                    this.invalidStart  = (this.value.length > 0 && this.value[0] !== '8');
                                                    this.invalidLength = (!this.invalidStart && this.value.length > 0 && this.value.length < this.minLength);
                                                }
                                            }"
                                        >
                                            <label class="block mt-1 text-xs font-medium text-slate-600">
                                                No. HP <span class="text-red-500">*</span>
                                            </label>

                                            <div
                                                class="mt-1 flex rounded-md border border-slate-300
                                                    focus-within:border-slate-500 focus-within:ring-2 focus-within:ring-slate-300 overflow-hidden"
                                            >
                                                <span class="inline-flex items-center px-3 text-sm text-slate-600 bg-slate-50 border-r border-slate-200 select-none">
                                                    +62
                                                </span>
                                                <input  type="tel"
                                                        name="phone"
                                                        x-model="value"
                                                        x-on:input="onInput($event)"
                                                        class="flex-1 border-0 bg-white px-3 py-2 text-sm rounded-r-md
                                                            focus:ring-0 focus:outline-none placeholder:text-slate-400 text-slate-900"
                                                        placeholder="81234567890"
                                                        autocomplete="tel" />
                                            </div>

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
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- hidden id + nama dari dropdown RajaOngkir --}}
                            <input type="hidden" name="province_id"   :value="provinceId">
                            <input type="hidden" name="city_id"       :value="cityId">
                            <input type="hidden" name="district_id"   :value="districtId">
                            <input type="hidden" name="province_name" :value="selectedProvinceName">
                            <input type="hidden" name="city_name"     :value="selectedCityName">
                            <input type="hidden" name="district_name" :value="selectedDistrictName">

                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pt-1">
                                <p class="text-[11px] text-slate-500 sm:max-w-sm">
                                    Mengubah alamat akan menghapus pilihan ongkos kirim sebelumnya. <br> Setelah menyimpan, silakan hitung ulang ongkos kirim.
                                </p>

                                <div class="flex items-center justify-end gap-2">
                                    <button type="button"
                                            @click="editAddress = false"
                                            class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                        Batal
                                    </button>
                                    <button type="submit"
                                            class="inline-flex items-center px-3 py-1.5 rounded-md bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800">
                                        Simpan Alamat
                                    </button>
                                </div>
                            </div>
                        </form>
                        @if($errors->shippingAddress->any())
                            <script>
                                document.addEventListener('DOMContentLoaded', () => {
                                window.$toast?.error(@json($errors->shippingAddress->first()));
                                });
                            </script>
                        @endif
                    @endif

                    {{-- Ongkos kirim --}}
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 text-xs sm:text-sm space-y-1">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h4 class="text-xs mb-2 mt-2 ml-2 font-semibold text-slate-700 uppercase tracking-[0.14em]">
                                    Ongkos kirim
                                </h4>
                                <p class="text-[12px] ml-2 text-slate-500">
                                    Perhitungan berdasarkan berat paket dan alamat pengiriman.
                                </p>
                                <p class="mt-1 text-[12px] ml-2 text-slate-500">
                                    Berat paket untuk perhitungan:
                                    <span class="font-semibold">
                                        {{ (int) ($payment->shipping_weight ?: config('rajaongkir.default_weight')) }} gram
                                    </span>
                                </p>
                            </div>

                            @if(! $payment->shipping_rajaongkir_district_id)
                                <span class="text-[12px] ml-2 text-amber-700 bg-amber-50 border border-amber-100 px-2 py-1 rounded-full">
                                    Lengkapi alamat terlebih dulu
                                </span>
                            @endif
                        </div>

                        {{-- Baris: kurir dikonfirmasi + tombol hitung --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div class="text-[13px] text-slate-600">
                                @if($payment->shipping_courier)
                                    <p class="font-semibold ml-2 text-slate-700">
                                        Kurir dikonfirmasi:
                                        <span class="font-medium">
                                            {{ strtoupper($payment->shipping_courier) }} – {{ $payment->shipping_service }}
                                        </span>
                                    </p>
                                    <p class="mt-0.5 ml-2">
                                        {{ $shippingFee > 0 ? $shippingFormatted : 'Rp 0' }}
                                        @if($payment->shipping_etd)
                                            • Estimasi tiba
                                            {{ $shippingEtaText ?? $payment->shipping_etd }}
                                        @endif
                                    </p>
                                @else
                                    <p class="ml-2">Belum ada kurir dikonfirmasi.</p>
                                @endif
                            </div>

                            <div class="flex flex-col items-end">
                                <button type="button"
                                    @click="(canEdit && hasAddressDistrict) ? fetchOptions('{{ route('shipping.options', $payment) }}') : null"
                                    :disabled="!canEdit || !hasAddressDistrict || loading || (hasCalculated && options.length)"
                                    :class="[
                                        'inline-flex items-center mr-2 px-3 py-1.5 rounded-md text-xs font-semibold',
                                        (!canEdit || !hasAddressDistrict || loading || (hasCalculated && options.length))
                                            ? 'bg-slate-300 text-slate-600 cursor-not-allowed'
                                            : 'bg-slate-900 text-white hover:bg-slate-800'
                                    ]">
                                    <span x-show="!loading">Hitung Ongkos Kirim</span>
                                    <span x-show="loading">Menghitung...</span>
                                </button>

                                <div class="text-right text-[11px] mr-2 mt-1 text-slate-500">
                                    <template x-if="!loading && !options.length">
                                        <p x-show="canEdit">
                                            Klik tombol untuk melihat pilihan kurir.
                                        </p>
                                    </template>
                                    <template x-if="hasCalculated && options.length">
                                        <p>Pilihan kurir di bawah berasal dari perhitungan terakhir.</p>
                                    </template>
                                    <template x-if="!canEdit">
                                        <p>Status transaksi sudah tidak dapat mengubah ongkos kirim.</p>
                                    </template>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    {{-- List opsi kurir + radio + tombol Konfirmasi --}}
                    <template x-if="options.length">
                        <div class="mt-2 space-y-2">
                            <div class="rounded-xl border border-slate-100 bg-slate-50/50 overflow-y-auto divide-y">
                                <template x-for="opt in options" :key="makeKey(opt)">
                                    <label
                                        class="flex items-center justify-between gap-3 px-3 py-2 cursor-pointer hover:bg-white/70"
                                        :class="{ 'bg-white shadow-sm': selectedKey === makeKey(opt) }"
                                    >
                                        <div class="flex-1 ml-1 mt-1 mb-1">
                                            <div class="text-sm font-semibold"
                                                x-text="opt.name + ' - ' + opt.service"></div>
                                            <div class="text-xs text-slate-500" x-text="opt.description"></div>
                                            <div class="text-xs text-slate-500" x-text="'ETD: ' + (opt.etd || '-')"></div>
                                        </div>

                                        <div class="flex items-center gap-3">
                                            <div class="text-right">
                                                <div class="text-sm font-semibold" x-text="formatRupiah(opt.cost)"></div>
                                                <div class="text-[11px] text-emerald-600"
                                                    x-show="confirmedKey === makeKey(opt)">
                                                    Sudah dikonfirmasi
                                                </div>
                                            </div>
                                            <input type="radio"
                                                name="shipping_option"
                                                class="h-4 w-4 mr-1 border-slate-400 text-slate-900 focus:ring-slate-500"
                                                :value="makeKey(opt)"
                                                x-model="selectedKey">
                                        </div>
                                    </label>
                                </template>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="button"
                                    @click="
                                        if(!canEdit) return;

                                        if(!selectedKey) {
                                            showPopup('error', 'Kurir belum dipilih', 'Silakan pilih salah satu jasa kirim dulu.');
                                            return;
                                        }

                                        confirmSelection('{{ route('shipping.select', $payment) }}')
                                        "
                                    :disabled="!canEdit"
                                    :class="[
                                        'inline-flex items-center px-4 py-1.5 rounded-md text-xs font-semibold',
                                        canEdit
                                            ? 'bg-slate-900 text-white hover:bg-slate-800'
                                            : 'bg-slate-300 text-slate-600 cursor-not-allowed'
                                    ]">
                                    Konfirmasi Pilihan Kurir
                                </button>
                            </div>
                        </div>

                        @if($payment->shipping_courier)
                            <div class="sm:hidden border-t border-slate-100 pt-3 text-xs text-slate-600">
                                Kurir dikonfirmasi:
                                <span class="font-medium">
                                    {{ strtoupper($payment->shipping_courier) }} – {{ $payment->shipping_service }}
                                </span>
                                ({{ $shippingFormatted }}).
                            </div>
                        @endif
                    </template>

                    <div x-show="popup.open" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center px-4">
                        <div class="absolute inset-0 bg-black/40" @click="closePopup()"></div>

                        <div class="relative w-full max-w-sm rounded-xl bg-white shadow-lg p-4">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5">
                                    <span x-show="popup.type==='success'" class="text-emerald-600 font-bold">✓</span>
                                    <span x-show="popup.type==='error'" class="text-red-600 font-bold">!</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-slate-900" x-text="popup.title"></p>
                                    <p class="text-sm text-slate-600 mt-1" x-text="popup.message"></p>
                                </div>
                            </div>

                            <div class="mt-4 flex justify-end">
                                <button type="button"
                                    class="px-4 py-2 rounded-md bg-slate-900 text-white text-sm font-semibold"
                                    @click="closePopup()">
                                    OK
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            {{-- KOLOM KANAN: ringkasan & tombol aksi + status pengiriman --}}
            <div class="space-y-4">

                {{-- Ringkasan Tagihan --}}
                <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-4 sm:p-5 text-sm">
                    <h3 class="font-semibold text-slate-900 mb-3">Ringkasan Tagihan</h3>

                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 text-xs sm:text-sm space-y-1">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-slate-500">Harga lelang</span>
                            <span class="font-medium text-slate-900">{{ $subtotalFormatted }}</span>
                        </div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-slate-500">Ongkos kirim</span>
                            <span class="font-medium text-slate-900">
                                {{ $shippingFee > 0 ? $shippingFormatted : 'Belum dipilih' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-slate-500">Biaya layanan</span>
                            <span class="font-medium text-slate-900">{{ $serviceFormatted }}</span>
                        </div>
                        <!-- <div class="flex items-center justify-between mb-2">
                            <span class="text-slate-500">Biaya admin</span>
                            <span class="font-medium text-slate-900">Rp 0</span>
                        </div> -->

                        <div class="border-t border-slate-300 mt-3 pt-3 flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Total harus dibayar</span>
                            <span class="text-lg font-semibold text-slate-900">{{ $totalFormatted }}</span>
                        </div>
                    </div>

                    {{-- Tombol aksi --}}
                    <div class="mt-4 space-y-2">
                        @if($status === 'PENDING' && ! $isExpired)
                            @if($readyToPay)
                                <a href="{{ route('checkout.show', $payment) }}"
                                class="inline-flex w-full items-center justify-center rounded-full px-4 py-2 text-sm font-semibold bg-slate-900 text-white hover:bg-slate-800">
                                    Bayar via Duitku
                                </a>
                                <p class="text-[11px] text-slate-500">
                                    Anda akan diarahkan ke halaman pembayaran Duitku di tab baru.
                                </p>
                            @else
                                <button type="button" disabled
                                    class="inline-flex w-full items-center justify-center rounded-full px-4 py-2 text-sm font-semibold bg-slate-300 text-slate-600 cursor-not-allowed">
                                    Bayar via Duitku
                                </button>

                                <div class="mt-2 text-[11px] text-slate-600 space-y-1">
                                    <p class="font-semibold text-slate-700">Sebelum bayar, lengkapi dulu:</p>

                                    @if(! $hasAddress)
                                        <p>• Alamat pengiriman (detail, provinsi/kota/kecamatan, kode pos, no HP)</p>
                                    @endif

                                    @if(! $hasShipping)
                                        <p>• Hitung ongkos kirim & konfirmasi pilihan kurir</p>
                                    @endif
                                </div>
                            @endif
                        @elseif($status === 'PAID')
                            <p class="text-xs text-slate-500">
                                Simpan invoice ini sebagai bukti pembayaran.
                            </p>
                        @else
                            <p class="text-xs text-slate-500">
                                Tidak ada tindakan pembayaran yang tersedia untuk status ini.
                            </p>
                        @endif
                    </div>
                </div>
                
                {{-- Status Pengiriman --}}
                <section
                        x-data="shippingTracking(
                            @js($payment->shipping_status),
                            @js((bool) $payment->shipping_tracking_no),
                            '{{ route('shipping.complete', $payment) }}'
                        )"
                        class="bg-white border border-slate-100 rounded-2xl shadow-sm p-4 sm:p-5 space-y-3 text-sm"
                    >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-slate-900">Status Pengiriman</h3>
                        </div>

                        @php
                            $shipCode = $payment->shipping_status;

                            $shipLabel = match ($shipCode) {
                                'PACKING'   => 'Sedang Dikemas',
                                'SHIPPED'   => 'Sedang Dikirim',
                                'COMPLETED' => 'Selesai',
                                default     => $status === 'PAID'
                                    ? 'Sedang Dikemas'
                                    : 'Menunggu Pembayaran',
                            };

                            $shipClasses = match ($shipCode) {
                                'PACKING'   => 'bg-amber-50 text-amber-700 ring-amber-100',
                                'SHIPPED'   => 'bg-blue-50 text-blue-700 ring-blue-100',
                                'COMPLETED' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                                default     => 'bg-slate-50 text-slate-700 ring-slate-100',
                            };
                        @endphp

                        <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-semibold ring-1 {{ $shipClasses }}">
                            {{ $shipLabel }}
                        </span>
                    </div>

                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 text-xs sm:text-sm space-y-1">
                        <dl class="space-y-1 text-xs sm:text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-slate-500">Kurir</dt>
                                <dd class="font-medium text-slate-800">
                                    @if($payment->shipping_courier)
                                        {{ strtoupper($payment->shipping_courier) }}
                                        @if($payment->shipping_service)
                                            – {{ $payment->shipping_service }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </dd>
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-slate-500">No. Resi</dt>
                                <dd class="font-mono text-slate-900">
                                    {{ $payment->shipping_tracking_no ?? 'Belum diinput admin' }}
                                </dd>
                            </div>

                            @if($payment->shipping_etd || $shippingEtaText)
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-slate-500">Estimasi tiba</dt>
                                    <dd class="text-slate-800">
                                        {{ $shippingEtaText ?? $payment->shipping_etd }}
                                    </dd>
                                </div>
                            @endif
                        </dl>

                        <div class="border-t border-slate-300 pt-3 text-xs text-slate-600 space-y-2">
                            <div class="flex justify-end">
                                <!-- <button type="button"
                                        @click="track()"
                                        :disabled="!canTrack"
                                        :class="[
                                            'inline-flex items-center px-3 py-1.5 rounded-full border text-[12px] font-semibold',
                                            canTrack
                                                ? 'border-slate-300 text-slate-700 hover:bg-slate-50'
                                                : 'border-slate-200 text-slate-400 cursor-not-allowed bg-slate-50'
                                        ]">
                                    Lacak Paket
                                </button> -->

                                <button type="button"
                                        @click="openConfirmComplete()"
                                        :disabled="!canComplete"
                                        :class="[
                                            'inline-flex items-center px-3 py-1.5 rounded-full border text-[12px] font-semibold',
                                            canComplete
                                                ? 'border-emerald-500 text-emerald-700 hover:bg-emerald-50'
                                                : 'border-slate-200 text-slate-400 cursor-not-allowed bg-slate-50'
                                        ]">
                                    Pesanan Selesai
                                </button>
                            </div>
                        </div>
                    </div>

                    <div x-show="confirmOpen" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center px-4">
                        <div class="absolute inset-0 bg-black/40" @click="closeConfirm()"></div>

                        <div class="relative w-full max-w-sm rounded-xl bg-white shadow-lg p-4">
                            <p class="text-sm font-semibold text-slate-900">Konfirmasi Pesanan Selesai</p>
                            <p class="text-sm text-slate-600 mt-1">
                            Apakah Anda yakin barang sudah diterima? Tindakan ini tidak dapat dibatalkan.
                            </p>

                            <div class="mt-4 flex justify-end gap-2">
                            <button type="button"
                                    class="px-4 py-2 rounded-md border border-slate-300 text-slate-700 text-sm font-semibold"
                                    @click="closeConfirm()"
                                    :disabled="completing">
                                Batal
                            </button>

                            <button type="button"
                                    class="px-4 py-2 rounded-md bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700"
                                    @click="markCompleted()"
                                    :disabled="completing">
                                <span x-show="!completing">Ya, Sudah Diterima</span>
                                <span x-show="completing">Memproses...</span>
                            </button>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
        function shippingForm(canEdit = false, hasAddressErrors = false) {
            return {
                // flag dari backend: boleh edit (PENDING & belum expired)
                canEdit: !!canEdit,
                hasAddressErrors: !!hasAddressErrors,
                editAddress: false,

                // URL AJAX
                provincesUrl: "{{ route('rajaongkir.provinces') }}",
                citiesUrl: "{{ route('rajaongkir.cities') }}",
                districtsUrl: "{{ route('rajaongkir.districts') }}",

                // data dropdown RajaOngkir
                provinces: [],
                cities: [],
                districts: [],
                provinceId: '',
                cityId: '',
                districtId: '',

                // opsi ongkir & state
                options: @json($payment->shipping_raw_response ?? []),
                loading: false,
                hasCalculated: {{ $payment->shipping_raw_response ? 'true' : 'false' }},

                // pemilihan kurir
                selectedKey: @json(
                    $payment->shipping_courier
                        ? ($payment->shipping_courier.'::'.$payment->shipping_service)
                        : null
                ),
                confirmedKey: @json(
                    $payment->shipping_courier
                        ? ($payment->shipping_courier.'::'.$payment->shipping_service)
                        : null
                ),

                // alamat pengiriman
                editAddress: false,
                addressLine: @json($payment->address ?? ''),
                postalCode: @json($payment->postal_code ?? ''),
                selectedProvinceName: @json($payment->province_name ?? ''),
                selectedCityName: @json($payment->city_name ?? ''),
                selectedDistrictName: @json($payment->district_name ?? ''),
                hasAddressDistrict: {{ $payment->shipping_rajaongkir_district_id ? 'true' : 'false' }},

                init() {
                if (this.canEdit && this.hasAddressErrors) {
                    this.editAddress = true;
                    if (this.provinces.length === 0) this.loadProvinces();
                }
                },

                toggleEditAddress() {
                    this.editAddress = !this.editAddress;
                    if (this.editAddress && this.provinces.length === 0) {
                        this.loadProvinces();
                    }
                },

                loadProvinces() {
                    fetch(this.provincesUrl)
                        .then(r => r.json())
                        .then(d => { this.provinces = d; });
                },

                onProvinceChange() {
                    this.cities = [];
                    this.districts = [];
                    this.cityId = '';
                    this.districtId = '';

                    const found = this.provinces.find(p => String(p.id) === String(this.provinceId));
                    this.selectedProvinceName = found ? found.name : '';

                    if (this.provinceId) this.loadCities();
                },

                onCityChange() {
                    this.districts = [];
                    this.districtId = '';

                    const found = this.cities.find(c => String(c.id) === String(this.cityId));
                    this.selectedCityName = found ? found.name : '';

                    if (this.cityId) this.loadDistricts();
                },

                onDistrictChange() {
                    const found = this.districts.find(d => String(d.id) === String(this.districtId));
                    this.selectedDistrictName = found ? found.name : '';
                },

                loadCities() {
                    if (!this.provinceId) return;

                    fetch(this.citiesUrl + '?province_id=' + this.provinceId)
                        .then(r => r.json())
                        .then(d => { this.cities = d; });
                },

                loadDistricts() {
                    if (!this.cityId) return;

                    fetch(this.districtsUrl + '?city_id=' + this.cityId)
                        .then(r => r.json())
                        .then(d => { this.districts = d; });
                },

                fetchOptions(url) {
                    if (!this.hasAddressDistrict) {
                        window.$toast?.error('Lengkapi alamat & kecamatan terlebih dahulu.');
                        return;
                    }

                    if (this.loading || (this.hasCalculated && this.options.length)) return;

                    this.loading = true;

                    fetch(url)
                        .then(async (r) => {
                            const text = await r.text();

                            if (!r.ok) {
                                console.error('Shipping options error', r.status, text);
                                window.$toast?.error('Gagal menghitung ongkos kirim (' + r.status + ').');
                                return null;
                            }

                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.error('Invalid JSON from server', text);
                                window.$toast?.error('Respon server tidak valid.');
                                return null;
                            }
                        })
                        .then((d) => {
                            if (d) {
                                this.options = d.data || [];
                                this.hasCalculated = true;
                            }
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                },

                makeKey(opt) {
                    return (opt.code || '') + '::' + (opt.service || '');
                },

                notify(type, message) {
                    // type: 'success' | 'error' | 'info'
                    if (window.$toast && typeof window.$toast[type] === 'function') {
                        window.$toast[type](message);
                    } else {
                        alert(message); // fallback popup
                    }
                },

                confirmSelection(url) {
                    if (!this.selectedKey) {
                        this.showPopup('error', 'Kurir belum dipilih', 'Silakan pilih salah satu jasa kirim dulu.');
                        return;
                    }

                    const opt = this.options.find(o => this.makeKey(o) === this.selectedKey);
                    if (!opt) {
                        this.showPopup('error', 'Pilihan tidak valid', 'Pilihan kurir tidak dikenal. Silakan hitung ulang ongkos kirim.');
                        return;
                    }

                    const fd = new FormData();
                    fd.append('courier_code', opt.code);
                    fd.append('service', opt.service);
                    fd.append('cost', opt.cost);
                    fd.append('etd', opt.etd || '');

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: fd
                    })
                    .then(async (r) => {
                        if (!r.ok) {
                            const t = await r.text();
                            console.error('Shipping select error', r.status, t);
                            this.showPopup('error', 'Gagal', 'Gagal menyimpan pilihan kurir. Coba lagi ya.');
                            return;
                        }

                        // SUCCESS → pakai popup + reload setelah klik OK
                        this.confirmedKey = this.selectedKey;

                        this.showPopup(
                        'success',
                        'Berhasil',
                        `Berhasil memilih kurir: ${opt.name} - ${opt.service}.`,
                        'reload'
                        );
                    })
                    .catch((e) => {
                        console.error(e);
                        this.showPopup('error', 'Error', 'Terjadi kesalahan jaringan. Coba lagi ya.');
                    });
                },

                popup: { open:false, type:'info', title:'', message:'', action:null },

                showPopup(type, title, message, action=null) {
                    this.popup = { open:true, type, title, message, action };
                },
                closePopup() {
                    const action = this.popup.action;
                    this.popup.open = false;
                    this.popup.action = null;
                    if (action === 'reload') window.location.reload();
                },

                formatRupiah(v) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(v);
                }
            }
        }

        function shippingTracking(initialStatus = null, hasTracking = false, completeUrl = null) {
            return {
                status: initialStatus || 'PENDING',
                hasTracking: hasTracking,
                completeUrl: completeUrl,
                loadingTrack: false,
                completing: false,
                confirmOpen: false,

                get canTrack() {
                    return this.status === 'SHIPPED' && this.hasTracking;
                },
                get canComplete() {
                    return this.status === 'SHIPPED';
                },

                track() {
                    if (!this.canTrack || this.loadingTrack) return;
                    this.loadingTrack = true;

                    // TODO: nanti integrasi RajaOngkir Tracking AWB
                    window.$toast?.info('Fitur lacak paket akan diintegrasikan dengan RajaOngkir.');
                    this.loadingTrack = false;
                },

                openConfirmComplete() {
                    if (!this.canComplete || this.completing) return;
                    this.confirmOpen = true;
                },

                closeConfirm() {
                    this.confirmOpen = false;
                },

                markCompleted() {
                    if (!this.canComplete || this.completing) return;
                    this.completing = true;

                    fetch(this.completeUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(async (r) => {
                        if (!r.ok) {
                            const t = await r.text();
                            console.error('Complete order error', r.status, t);
                            window.$toast?.error('Gagal menandai pesanan selesai.');
                            return;
                        }
                        window.location.reload();
                    })
                    .finally(() => {
                        this.completing = false;
                        this.confirmOpen = false;
                    });
                }
            }
        }
    </script>
</x-guest-layout>
