{{-- resources/views/bidder/transactions/index.blade.php --}}
<x-guest-layout>
    @php
        /** @var \Illuminate\Pagination\LengthAwarePaginator|\App\Models\Payment[] $payments */
        $payments = $payments ?? collect();
        $user     = $user ?? auth()->user();
    @endphp

    <div class="max-w-screen-xl mx-auto px-4 space-y-8">
        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mt-2">
            <div class="space-y-1">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                    Dashboard Bidder
                </p>
                <h1 class="text-2xl md:text-3xl font-semibold text-slate-900">
                    Transaksi Saya
                </h1>
                <p class="text-sm text-slate-500 max-w-xl">
                    Lihat tagihan, status pembayaran, dan riwayat lelang yang Anda menangkan.
                    Setiap invoice harus dibayar maksimal dalam waktu <span class="font-semibold">1×24 jam</span>.
                </p>
            </div>

            {{-- Ringkasan Kecil --}}
            <div class="grid grid-cols-3 gap-3 text-sm w-full md:w-auto">
                <div class="rounded-xl bg-slate-900 text-white px-4 py-3 shadow-sm">
                    <div class="text-[11px] uppercase tracking-[0.16em] text-slate-300">Total Transaksi</div>
                    <div class="mt-1 text-xl font-semibold">
                        {{ $totalTrans }}
                    </div>
                </div>
                <div class="rounded-xl bg-emerald-50 px-4 py-3 border border-emerald-200">
                    <div class="text-[11px] uppercase tracking-[0.16em] text-emerald-700">Lunas</div>
                    <div class="mt-1 text-xl font-semibold text-emerald-800">
                        {{ $paidCount }}
                    </div>
                </div>
                <div class="rounded-xl bg-amber-50 px-4 py-3 border border-amber-200">
                    <div class="text-[11px] uppercase tracking-[0.16em] text-amber-800">Menunggu Bayar</div>
                    <div class="mt-1 text-xl font-semibold text-amber-900">
                        {{ $pendingCount }}
                    </div>
                </div>
            </div>
        </div>

        {{-- LIST TRANSAKSI --}}
        <section class="mt-4 space-y-3">

            {{-- Keterangan status transaksi --}}
            @if($payments->isNotEmpty())
                <div class="px-4 sm:px-6 pb-3 pt-3 text-[11px] sm:text-xs text-slate-600 bg-slate-50 border border-slate-100 rounded-2xl">
                    <p class="font-semibold text-slate-700 mb-1.5">
                        Keterangan status transaksi:
                    </p>
                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1.5">
                        <li><span class="font-semibold text-amber-800">PENDING</span> — invoice sudah dibuat dan menunggu pembayaran maksimal 1×24 jam.</li>
                        <li><span class="font-semibold text-emerald-700">PAID</span> — pembayaran sudah diterima, transaksi selesai dan barang siap diproses.</li>
                        <li><span class="font-semibold text-red-700">EXPIRED</span> — batas waktu 1×24 jam terlewati tanpa pembayaran. Barang dinyatakan <span class="font-semibold">unsold</span> dan akun ditangguhkan selama 7 hari.</li>
                        <li><span class="font-semibold text-slate-700">CANCELLED</span> — transaksi dibatalkan oleh sistem / admin.</li>
                    </ul>
                    <p class="font-semibold text-slate-700 mt-3.5 mb-1.5">
                        Keterangan status pengiriman:
                    </p>
                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1.5">
                        <li>
                            <span class="font-semibold text-amber-700">SEDANG DIKEMAS</span>
                            — pembayaran sudah diterima dan pesanan sedang disiapkan admin.
                        </li>
                        <li>
                            <span class="font-semibold text-blue-700">SEDANG DIKIRIM</span>
                            — admin sudah menginput nomor resi dan pesanan dalam proses pengiriman.
                        </li>
                        <li>
                            <span class="font-semibold text-emerald-700">SELESAI</span>
                            — pesanan sudah diterima dan dikonfirmasi melalui tombol <span class="font-semibold">“Pesanan Selesai”</span>.
                        </li>
                    </ul>
                </div>
            @endif

            <div class="flex items-center justify-between gap-3 pt-2">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">
                        Daftar Tagihan & Transaksi
                    </h2>
                    <p class="text-xs text-slate-500">
                        Termasuk invoice yang masih menunggu pembayaran maupun yang sudah lunas.
                    </p>
                </div>
            </div>

            <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                {{-- Header desktop --}}
                <div class="hidden md:grid grid-cols-12 gap-3 px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500 bg-slate-50 border-b border-slate-100">
                    <div class="col-span-4">Invoice / Lot</div>
                    <div class="col-span-2 text-center">Total</div>
                    <div class="col-span-2 text-center">Status</div>
                    <div class="col-span-2 text-center">Jatuh Tempo / Bayar</div>
                    <div class="col-span-2 text-center">Aksi</div>
                </div>

                @forelse($payments as $payment)
                    @php
                        $lot     = $payment->lot;
                        $product = $lot?->product;
                        $img     = optional($product?->images->first())->public_url ?? asset('tempus/placeholder.jpg');

                        $status  = $payment->status ?? 'PENDING';

                        $shipCode = $payment->shipping_status;

                        // fallback: kalau transaksi PAID tapi shipping_status kosong/PENDING → anggap PACKING
                            if (($shipCode === null || $shipCode === 'PENDING') && $status === 'PAID') {
                            $shipCode = 'PACKING';
                        }

                        // optional fallback: kalau ada resi & belum COMPLETED → anggap SHIPPED
                            if (!$shipCode && $payment->shipping_tracking_no) {
                            $shipCode = 'SHIPPED';
                        }

                        $shipLabel = match ($shipCode) {
                            'PACKING'   => 'DIKEMAS',
                            'SHIPPED'   => 'DIKIRIM',
                            'COMPLETED' => 'SELESAI',
                            default     => '-',
                        };

                        $shipClasses = match ($shipCode) {
                            'PACKING'   => 'bg-amber-50 text-amber-700 ring-amber-100',
                            'SHIPPED'   => 'bg-blue-50 text-blue-700 ring-blue-100',
                            'COMPLETED' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                            default     => 'bg-slate-50 text-slate-600 ring-slate-100',
                        };

                        $statusClasses = match ($status) {
                            'PAID'      => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                            'PENDING'   => 'bg-amber-50 text-amber-800 ring-amber-100',
                            'EXPIRED'   => 'bg-red-50 text-red-700 ring-red-100',
                            'CANCELLED' => 'bg-slate-200 text-slate-700 ring-slate-300',
                            default     => 'bg-slate-100 text-slate-700 ring-slate-200',
                        };

                        $totalFormatted = 'Rp '.number_format($payment->amount_due ?? 0, 0, ',', '.');

                        $code = $payment->invoice_no ?? ('INV-'.$payment->id);

                        $isExpired = $payment->status === 'EXPIRED' || ($payment->expires_at && now()->gt($payment->expires_at) && $payment->status !== 'PAID');
                    @endphp

                    <div class="border-b last:border-b-0 border-slate-100 hover:bg-slate-50/60 transition-colors">

                        {{-- ===== MOBILE CARD ===== --}}
                        <div class="md:hidden px-4 py-3 space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="w-14 h-14 rounded-lg overflow-hidden bg-slate-100 flex-shrink-0 ring-1 ring-slate-200/70">
                                    <img src="{{ $img }}" alt="{{ $lot->title ?? 'Lot' }}" class="w-full h-full object-cover">
                                </div>
                                <div class="space-y-0.5 min-w-0">
                                    <p class="text-xs text-slate-500">
                                        {{ $code }}
                                    </p>
                                    <p class="text-sm font-semibold text-slate-900 truncate">
                                        {{ $lot->title ?? (($product->brand ?? '-') . ' ' . ($product->model ?? '')) }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        Lot #{{ $lot->id ?? '—' }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 text-sm">
                                {{-- Total --}}
                                <div>
                                    <div class="text-[11px] uppercase tracking-[0.14em] text-slate-400 mb-0.5">
                                        Total
                                    </div>
                                    <span class="inline-flex items-center rounded-full bg-slate-900/5 px-3 py-1 text-xs font-semibold text-slate-900">
                                        {{ $totalFormatted }}
                                    </span>
                                </div>

                                {{-- Status --}}
                                <div>
                                    <div class="text-[11px] uppercase tracking-[0.14em] text-slate-400 mb-0.5">
                                        Status
                                    </div>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 {{ $statusClasses }}">
                                        {{ strtoupper($status) }}
                                    </span>
                                </div>

                                {{-- Pengiriman --}}
                                <div>
                                    <div class="text-[11px] uppercase tracking-[0.14em] text-slate-400 mb-0.5">
                                        Pengiriman
                                    </div>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 {{ $shipClasses }}">
                                        {{ $shipLabel }}
                                    </span>
                                </div>

                                {{-- Tanggal --}}
                                <div>
                                    <div class="text-[11px] uppercase tracking-[0.14em] text-slate-400 mb-0.5">
                                        Jatuh tempo / bayar
                                    </div>
                                    <span class="text-slate-700">
                                        @if($payment->paid_at)
                                            Dibayar: {{ $payment->paid_at->format('d M Y, H:i') }}
                                        @elseif($payment->expires_at)
                                            {{ $payment->expires_at->format('d M Y, H:i') }}
                                        @else
                                            -
                                        @endif
                                    </span>
                                </div>

                                {{-- Aksi --}}
                                <div class="col-span-2 pt-1">
                                    @if($status === 'PENDING' && ! $isExpired)
                                        <a href="{{ route('transactions.show', $payment) }}"
                                        class="inline-flex w-full items-center justify-center rounded-full px-3 py-2 text-xs font-semibold bg-slate-900 text-white hover:bg-slate-800">
                                            Bayar sekarang
                                        </a>
                                    @else
                                        <a href="{{ route('transactions.show', $payment) }}"
                                        class="inline-flex w-full items-center justify-center rounded-full px-3 py-2 text-xs font-semibold bg-slate-200 text-slate-800 hover:bg-slate-300">
                                            Detail
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- ===== DESKTOP ROW ===== --}}
                        <div class="hidden md:grid md:grid-cols-12 gap-3 items-center px-5 py-3">
                            {{-- Invoice / Lot --}}
                            <div class="col-span-4 flex items-start gap-3">
                                <div class="w-14 h-14 rounded-lg overflow-hidden bg-slate-100 flex-shrink-0 ring-1 ring-slate-200/70">
                                    <img src="{{ $img }}" alt="{{ $lot->title ?? 'Lot' }}" class="w-full h-full object-cover">
                                </div>
                                <div class="space-y-0.5 min-w-0">
                                    <p class="text-xs text-slate-500">
                                        {{ $code }}
                                    </p>
                                    <p class="text-sm font-semibold text-slate-900 truncate">
                                        {{ $lot->title ?? (($product->brand ?? '-') . ' ' . ($product->model ?? '')) }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        Lot #{{ $lot->id ?? '—' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Total --}}
                            <div class="col-span-2 text-sm text-center">
                                <span class="inline-flex items-center rounded-full bg-slate-900/5 px-3 py-1 text-xs font-semibold text-slate-900">
                                    {{ $totalFormatted }}
                                </span>
                            </div>

                            {{-- Status (Transaksi + Pengiriman) --}}
                            <div class="col-span-2 text-sm text-center">
                                <div class="inline-flex flex-col items-center gap-1">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 {{ $statusClasses }}">
                                        {{ strtoupper($status) }}
                                    </span>

                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 {{ $shipClasses }}">
                                        {{ $shipLabel }}
                                    </span>
                                </div>
                            </div>

                            {{-- Jatuh tempo / Bayar --}}
                            <div class="col-span-2 text-sm text-center">
                                <span class="text-slate-700">
                                    @if($payment->paid_at)
                                        Dibayar: {{ $payment->paid_at->format('d M Y, H:i') }}
                                    @elseif($payment->expires_at)
                                        {{ $payment->expires_at->format('d M Y, H:i') }}
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>

                            {{-- Aksi --}}
                            <div class="col-span-2 text-sm text-center">
                                @if($status === 'PENDING' && ! $isExpired)
                                    <a href="{{ route('transactions.show', $payment) }}"
                                       class="inline-flex items-center justify-center rounded-full px-4 py-1.5 text-xs font-semibold bg-slate-900 text-white hover:bg-slate-800">
                                        Bayar sekarang
                                    </a>
                                @else
                                    <a href="{{ route('transactions.show', $payment) }}"
                                       class="inline-flex items-center justify-center rounded-full px-4 py-1.5 text-xs font-semibold bg-slate-200 text-slate-800 hover:bg-slate-300">
                                        Lihat Detail
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 sm:px-6 py-10 text-center text-sm text-slate-500">
                        Belum ada transaksi yang tercatat.
                        <br>
                        <span class="text-xs text-slate-400">
                            Transaksi akan muncul di sini setelah Anda memenangkan lelang dan sistem membuat invoice pembayaran.
                        </span>
                    </div>
                @endforelse
                @if($payments instanceof \Illuminate\Pagination\LengthAwarePaginator && $payments->hasPages())
                    <div class="px-2 sm:px-0 mt-4 mb-4 ml-4 mr-4">
                        {{ $payments->onEachSide(1)->links() }}
                    </div>
                @endif
            </div>
            
        </section>
    </div>
</x-guest-layout>
