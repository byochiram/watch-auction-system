{{-- admin/transactions/show.blade.php --}}
<x-app-layout :title="'Detail Transaksi '.$payment->invoice_no">
    @php
        $status = $payment->status;

        [$statusLabel, $badgeClass] = match ($status) {
            'PAID'      => ['Dibayar',    'bg-emerald-100 text-emerald-800'],
            'EXPIRED'   => ['Expired',    'bg-slate-200 text-slate-800'],
            'CANCELLED' => ['Dibatalkan', 'bg-red-100 text-red-800'],
            default     => ['Pending',    'bg-amber-100 text-amber-800'],
        };

        $user     = $payment->user;
        $profile  = $payment->bidderProfile;

        // alamat – utamakan dari Payment, baru fallback profile kalau perlu
        $cityLabel      = $payment->cityName      ?? $profile?->cityName      ?? null;
        $provinceLabel  = $payment->provinceName  ?? $profile?->provinceName  ?? null;
        $districtLabel  = $payment->districtName  ?? $profile?->districtName  ?? null;

        // breakdown biaya
        $auctionAmount = (int) $payment->amount_due;
        $serviceFee    = (int) ($payment->service_fee ?? 500);
        $shippingFee   = (int) ($payment->shipping_fee ?? 0);
        $grandTotal    = (int) $payment->grand_total;

        // label status pengiriman
        if ($payment->shipping_completed_at) {
            $shipLabel = 'Selesai';
            $shipBadge = 'bg-emerald-100 text-emerald-800';
        } elseif ($payment->shipping_shipped_at) {
            $shipLabel = 'Dikirim';
            $shipBadge = 'bg-blue-100 text-blue-800';
        } else {
            $shipLabel = 'Belum dikirim';
            $shipBadge = 'bg-slate-100 text-slate-700';
        }

        $fromParam = request('from');
        $backUrl   = null;
        $backText  = '← Kembali ke daftar transaksi';

        if ($fromParam && str_starts_with($fromParam, 'lot-')) {
            $lotId   = (int) str_replace('lot-', '', $fromParam);
            $backUrl = route('lots.detail', ['lot' => $lotId]);
            $backText = '← Kembali ke detail lelang';
        } elseif ($fromParam && str_starts_with($fromParam, 'user-')) {
            $userId  = (int) str_replace('user-', '', $fromParam);
            $backUrl = route('users.show', $userId);
            $backText = '← Kembali ke detail pengguna';
        } elseif ($fromParam && str_starts_with($fromParam, 'product-')) {
            $productId = (int) str_replace('product-', '', $fromParam);
            $backUrl   = route('products.show', $productId);
            $backText  = '← Kembali ke detail produk';
        } else {
            // fallback: kembali ke daftar pembayaran dengan tab aktif (kalau ada)
            $backUrl = route('payments.index', ['tab' => request('tab', 'pending')]);
        }
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Transaksi #{{ $payment->id }}
            </h2>

            <a href="{{ $backUrl }}"
                class="px-3 py-1.5 rounded-full border border-slate-200 text-slate-700 text-xs hover:bg-slate-50">
                    {{ $backText }}
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
        <div class="bg-white shadow-sm sm:rounded-2xl overflow-hidden">

            {{-- HEADER --}}
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold text-slate-900">
                        Invoice {{ $payment->invoice_no }}
                    </h1>

                    @if($payment->lot)
                        <p class="text-sm text-slate-500">
                            Untuk lelang:
                            <span class="font-medium text-slate-700">
                                {{ $payment->lot->title }}
                            </span>
                            @if($payment->lot->product)
                                <span class="text-slate-400">
                                    • {{ $payment->lot->product->brand }} {{ $payment->lot->product->model }}
                                </span>
                            @endif
                            <span class="text-slate-400">
                                (Lot #{{ $payment->lot->id }})
                            </span>
                        </p>
                    @else
                        <p class="text-sm text-slate-500 italic">
                            Data lot tidak tersedia.
                        </p>
                    @endif
                </div>

                <div class="space-y-1 text-xs text-left md:text-right">
                    <div class="flex flex-wrap items-center gap-2 md:justify-end">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold {{ $badgeClass }}">
                            {{ $statusLabel }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold {{ $shipBadge }}">
                            Pengiriman: {{ $shipLabel }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="px-6 py-6 space-y-8">

                {{-- GRID: RINGKASAN + PEMBELI/PENGIRIMAN --}}
                <div class="grid lg:grid-cols-2 gap-6 items-start">

                    {{-- KIRI: RINGKASAN TAGIHAN + LELANG + TIMELINE --}}
                    <div class="space-y-4">

                        {{-- Ringkasan Tagihan --}}
                        <div class="border border-slate-200 rounded-xl p-4 space-y-4">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">
                                    Ringkasan Tagihan
                                </h3>
                                <span class="text-xs text-slate-500">
                                    ID Payment #{{ $payment->id }}
                                </span>
                            </div>

                            <div class="bg-slate-50 rounded-lg p-3 text-sm">
                                <div class="flex justify-between mb-1">
                                    <span class="text-slate-500">Harga lelang</span>
                                    <span>Rp {{ number_format($auctionAmount, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-slate-500">Biaya layanan</span>
                                    <span>Rp {{ number_format($serviceFee, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-slate-500">Ongkos kirim</span>
                                    <span>Rp {{ number_format($shippingFee, 0, ',', '.') }}</span>
                                </div>
                                <div class="border-t border-slate-200 pt-2 mt-1 flex justify-between items-baseline">
                                    <span class="text-[13px] text-slate-500 font-medium uppercase tracking-wide">
                                        Total Tagihan
                                    </span>
                                    <span class="text-lg font-semibold text-slate-900">
                                        Rp {{ number_format($grandTotal, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            <dl class="text-sm space-y-1.5">
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">Status Pembayaran</dt>
                                    <dd class="font-semibold text-right">{{ $statusLabel }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">Transaction ID Gateway</dt>
                                    <dd class="font-mono text-sm text-right">
                                        {{ $payment->pg_transaction_id ?? '—' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">Berat kiriman</dt>
                                    <dd class="text-right">
                                        {{ $payment->shipping_weight ? number_format($payment->shipping_weight,0,',','.') . ' gram' : '—' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        {{-- Informasi Lelang --}}
                        <div class="border border-slate-200 rounded-xl p-4 space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">
                                    Informasi Lelang
                                </h3>
                            </div>

                            @if($payment->lot)
                                <div class="space-y-1">
                                    <a href="{{ route('lots.detail', ['lot' => $payment->lot, 'from' => 'payment-'.$payment->id]) }}"
                                        class="text-sm font-semibold text-blue-600 hover:underline">
                                            Lot #{{ $payment->lot->id }}
                                            @if($payment->lot->product)
                                                • {{ $payment->lot->product->brand }} {{ $payment->lot->product->model }}
                                            @endif
                                    </a>
                                    <div class="text-xs text-slate-500">
                                        Jadwal:
                                        {{ $payment->lot->start_at?->format('d M Y H:i') ?? '—' }}
                                        –
                                        {{ $payment->lot->end_at?->format('d M Y H:i') ?? '—' }}
                                    </div>
                                </div>
                            @else
                                <p class="text-sm text-slate-500 italic">
                                    Data lot tidak tersedia.
                                </p>
                            @endif
                        </div>

                        {{-- Timeline Transaksi (dipindah ke sini) --}}
                        <div class="border border-slate-200 rounded-xl p-4 space-y-3">
                            <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">
                                Timeline Transaksi
                            </h3>

                            <dl class="mt-1 text-sm space-y-1.5">
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">Invoice diterbitkan</dt>
                                    <dd class="text-right">
                                        {{ $payment->issued_at?->format('d M Y H:i') ?? '—' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">Jatuh tempo pembayaran</dt>
                                    <dd class="text-right">
                                        {{ $payment->expires_at?->format('d M Y H:i') ?? '—' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">Pembayaran diterima</dt>
                                    <dd class="text-right">
                                        {{ $payment->paid_at?->format('d M Y H:i') ?? '—' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">Pesanan dikirim</dt>
                                    <dd class="text-right">
                                        {{ $payment->shipping_shipped_at?->format('d M Y H:i') ?? '—' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">Pesanan selesai</dt>
                                    <dd class="text-right">
                                        {{ $payment->shipping_completed_at?->format('d M Y H:i') ?? '—' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    {{-- KANAN: PEMBELI & PENGIRIMAN --}}
                    <div class="space-y-4">

                        {{-- Pembeli --}}
                        <div class="border border-slate-200 rounded-xl p-4 space-y-3">
                            <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">
                                Informasi Pembeli
                            </h3>

                            @if($user)
                                <dl class="text-sm space-y-1.5">
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-slate-500">Nama</dt>
                                         <a href="{{ route('users.show', ['user' => $user, 'from' => 'payment-'.$payment->id]) }}"
                                            class="font-semibold text-right text-blue-600 hover:underline">
                                                {{ $user->name ?: '—' }}
                                        </a>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-slate-500">Email</dt>
                                        <dd class="text-right break-all">
                                            {{ $user->email }}
                                        </dd>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-slate-500">Username</dt>
                                        <dd class="text-right">
                                            {{ $user->username ?: '—' }}
                                        </dd>
                                    </div>
                                </dl>
                            @else
                                <p class="text-sm text-slate-500 italic">
                                    Data pengguna tidak tersedia.
                                </p>
                            @endif
                        </div>

                        {{-- Alamat & Pengiriman --}}
                        <div class="border border-slate-200 rounded-xl p-4 space-y-3">
                            <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">
                                Alamat & Pengiriman
                            </h3>

                            <dl class="text-sm space-y-2">
                                <div>
                                    <dt class="text-slate-500 text-xs">Nama Penerima</dt>
                                    <dd class="text-slate-800">
                                        {{ $user?->name ?? '—' }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-slate-500 text-xs">Telepon</dt>
                                    <dd class="text-slate-800">
                                        {{ $payment->phone ?: ($profile?->phone ?: '—') }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-slate-500 text-xs">Alamat</dt>
                                    <dd class="text-slate-800">
                                        {{ $payment->address ?: '—' }}
                                    </dd>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <dt class="text-slate-500 text-xs">Kecamatan</dt>
                                        <dd class="text-slate-800">
                                            {{ $districtLabel ?: '—' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-500 text-xs">Kota / Kabupaten</dt>
                                        <dd class="text-slate-800">
                                            {{ $cityLabel ?: '—' }}
                                        </dd>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <dt class="text-slate-500 text-xs">Provinsi</dt>
                                        <dd class="text-slate-800">
                                            {{ $provinceLabel ?: '—' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-500 text-xs">Kode Pos</dt>
                                        <dd class="text-slate-800">
                                            {{ $payment->postal_code ?: '—' }}
                                        </dd>
                                    </div>
                                </div>

                                <div class="border-t border-slate-200 pt-3 mt-2 space-y-1">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-500">Kurir</span>
                                        <span class="font-medium">
                                            {{ $payment->shipping_courier ? strtoupper($payment->shipping_courier) : '—' }}
                                            @if($payment->shipping_service)
                                                <span class="text-xs text-slate-500">
                                                    ({{ $payment->shipping_service }})
                                                </span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-500">No. Resi</span>
                                        <span class="font-mono text-sm">
                                            {{ $payment->shipping_tracking_no ?? '—' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-500">Estimasi tiba</span>
                                        <span>
                                            {{ $payment->shipping_etd ?? '—' }}
                                        </span>
                                    </div>
                                </div>

                                @if($payment->shipping_rajaongkir_district_id)
                                    <div class="text-[11px] text-slate-400">
                                        ID Kecamatan (RajaOngkir):
                                        {{ $payment->shipping_rajaongkir_district_id }}
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>

            </div>

            {{-- FOOTER --}}
            <div class="p-6 border-t border-slate-100">
                <a href="{{ $backUrl }}"
                    class="px-3 py-1.5 rounded-full border border-slate-200 text-slate-700 text-xs hover:bg-slate-50">
                        {{ $backText }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
