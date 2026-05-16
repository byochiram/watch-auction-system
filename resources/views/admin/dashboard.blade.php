{{-- resources/views/admin/dashboard.blade.php --}}

<x-app-layout title="Dashboard Admin">
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Dashboard Admin
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Ringkasan cepat aktivitas Tempus Auctions.
                </p>
            </div>
            <div class="hidden sm:flex items-center gap-3 text-sm text-gray-500">
                <span>
                    {{ now()->translatedFormat('l, d F Y') }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- HERO + PERLU PERHATIAN --}}
            <section class="grid gap-4 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
                {{-- HERO --}}
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white p-6 sm:p-7">
                    {{-- bikin layout full tinggi agar tombol bisa “nempel bawah” --}}
                    <div class="flex flex-col h-full min-h-[220px] sm:min-h-[240px]">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-400 mb-2">
                                    Tempus Auctions · Admin
                                </p>
                                <h1 class="text-2xl sm:text-3xl font-semibold mb-2">
                                    Halo, {{ $me->name ?? 'Administrator' }} 👋
                                </h1>
                                <p class="text-sm text-slate-300 max-w-xl">
                                    Kelola produk, jadwal lelang, pengguna, dan transaksi dari satu tempat.
                                    Gunakan shortcut di bawah untuk berpindah cepat.
                                </p>
                            </div>

                            <div class="hidden sm:flex flex-col items-end gap-2 text-xs text-slate-300">
                                <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10">
                                    Mode Admin
                                </span>
                                <span>
                                    Bidder baru hari ini: <span class="font-semibold text-amber-300">{{ number_format($newBiddersToday) }}</span>
                                </span>
                            </div>
                        </div>

                        <div class="mt-auto pt-6">
                            <p class="text-[11px] uppercase tracking-[0.18em] text-slate-400 mb-3">
                                Aksi cepat
                            </p>

                            <div class="flex flex-wrap gap-3 text-sm">
                                <a href="{{ route('lots.index') }}"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-amber-400 text-slate-900 font-semibold shadow-sm hover:bg-amber-300 transition">
                                    <span>Kelola Lelang</span>
                                </a>

                                <a href="{{ route('products.index') }}"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-white/5 border border-white/10 hover:bg-white/10 transition">
                                    Produk jam tangan
                                </a>

                                <a href="{{ route('users.index') }}"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-white/5 border border-white/10 hover:bg-white/10 transition">
                                    Pengguna & bidder
                                </a>

                                <a href="{{ route('payments.index') }}"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-white/5 border border-white/10 hover:bg-white/10 transition">
                                    Transaksi
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- dekorasi --}}
                    <div class="pointer-events-none absolute -right-10 -bottom-10 opacity-40">
                        <div class="w-40 h-40 rounded-full border border-amber-400/40"></div>
                    </div>
                </div>
                {{-- PERLU PERHATIAN --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 flex flex-col gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Perlu perhatian</h3>
                        <p class="text-xs text-slate-500">Ringkasan hal penting yang perlu dicek admin.</p>
                    </div>

                    <div class="space-y-3 text-sm mt-1">
                        <div class="rounded-xl border border-slate-100 px-3 py-2.5 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-slate-900">Lelang akan dimulai ≤ 24 jam</p>
                                <p class="text-xs text-slate-500">Lot terjadwal yang akan segera dibuka.</p>
                            </div>
                            <span class="text-lg font-semibold text-amber-600">{{ $upcomingLots24h }}</span>
                        </div>

                        <div class="rounded-xl border border-slate-100 px-3 py-2.5 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-slate-900">Lelang berakhir hari ini</p>
                                <p class="text-xs text-slate-500">Pastikan pemenang & proses setelah lelang.</p>
                            </div>
                            <span class="text-lg font-semibold text-emerald-600">{{ $lotsEndingToday }}</span>
                        </div>

                        <div class="rounded-xl border border-slate-100 px-3 py-2.5 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-slate-900">Invoice akan kedaluwarsa ≤ 1 jam</p>
                                <p class="text-xs text-slate-500">Pembayaran pending yang perlu dipantau.</p>
                            </div>
                            <span class="text-lg font-semibold text-rose-600">{{ $invoicesExpiringSoon }}</span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- STAT KECIL: PRODUK / TOTAL LELANG / PENGGUNA --}}
            <section class="grid gap-4 md:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500">Produk terdaftar</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($totalProducts) }}</p>
                        <p class="mt-1 text-xs text-slate-500">jam tangan siap dilelang</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-amber-300 flex items-center justify-center text-lg">⌚</div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500">Total lelang</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($totalLots) }}</p>
                        <p class="mt-1 text-xs text-slate-500">lot yang pernah dibuat di sistem</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg">🧾</div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500">Pengguna terdaftar</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($totalUsers) }}</p>
                        <p class="mt-1 text-xs text-slate-500">akun bidder & admin</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-700 flex items-center justify-center text-lg">👤</div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500">Total transaksi</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($totalPayments) }}</p>
                        <p class="mt-1 text-xs text-slate-500">invoice yang pernah dibuat</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg">💳</div>
                </div>
            </section>

            {{-- STAT TRANSAKSI --}}
            <section class="grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500">Menunggu pembayaran</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($pendingPayments) }}</p>
                        <p class="mt-1 text-xs text-slate-500">
                            expiring ≤ 1 jam: <span class="font-semibold text-rose-600">{{ number_format($invoicesExpiringSoon) }}</span>
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-lg">⏳</div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500">Pembayaran bulan ini</p>
                        <p class="mt-1 text-xl font-semibold text-slate-900">
                            Rp {{ number_format($paidAmountThisMonth, 0, ',', '.') }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">total paid: {{ number_format($paidPayments) }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-lg">✅</div>
                </div>
            </section>

            {{-- DONUT STATUS LELANG + AKTIVITAS TERBARU --}}
            <section class="grid gap-4 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Komposisi status lelang</h3>
                            <p class="text-xs text-slate-500">Distribusi lot berdasarkan status runtime saat ini.</p>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-3 py-1 text-[11px] text-slate-500 border border-slate-100">
                            Total {{ number_format($totalLots) }} lot
                        </span>
                    </div>

                    <div class="grid gap-4 md:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] items-center">
                        <div class="aspect-[4/3]">
                            <canvas id="lotStatusChart"></canvas>
                        </div>

                        <div class="space-y-2 text-xs">
                            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Ringkasan cepat</p>
                            <ul class="space-y-1.5">
                                <li class="flex items-center justify-between">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-400"></span> Aktif
                                    </span>
                                    <span class="font-semibold text-slate-900">{{ number_format($activeLotsCount) }}</span>
                                </li>
                                <li class="flex items-center justify-between">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> Terjadwal / akan dimulai
                                    </span>
                                    <span class="font-semibold text-slate-900">{{ number_format($scheduledLots) }}</span>
                                </li>
                                <li class="flex items-center justify-between">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span> Selesai
                                    </span>
                                    <span class="font-semibold text-slate-900">{{ number_format($endedLots) }}</span>
                                </li>
                                <li class="flex items-center justify-between">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span> Dibatalkan
                                    </span>
                                    <span class="font-semibold text-slate-900">{{ number_format($cancelledLots) }}</span>
                                </li>
                            </ul>
                            <p class="mt-3 text-[11px] text-slate-500 leading-relaxed">
                                Grafik ini membantu melihat seberapa banyak lelang yang sudah selesai,
                                masih aktif, terjadwal, atau dibatalkan.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- AKTIVITAS TERBARU --}}
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
                    <h3 class="text-sm font-semibold text-slate-900 mb-2">Aktivitas terbaru</h3>
                    <p class="text-xs text-slate-500 mb-3">5 aktivitas terakhir (lelang, produk, pengguna, transaksi).</p>

                    <ul class="space-y-2 text-xs">
                        @forelse($activities as $activity)
                            @php
                                $created = $activity['created_at'];
                                $updated = $activity['updated_at'];

                                $action = 'Diperbarui';
                                if ($created && $updated && $created->equalTo($updated)) {
                                    $action = 'Dibuat';
                                }

                                $chipClasses = match($activity['type']) {
                                    'Lelang'    => 'bg-indigo-50 text-indigo-700',
                                    'Produk'    => 'bg-sky-50 text-sky-700',
                                    'Pengguna'  => 'bg-slate-100 text-slate-700',
                                    'Transaksi' => 'bg-emerald-50 text-emerald-700',
                                    default     => 'bg-slate-100 text-slate-700',
                                };
                            @endphp

                            <li class="flex items-start justify-between gap-2">
                                <div class="space-y-0.5">
                                    <p class="font-medium text-slate-900 line-clamp-1">{{ $activity['name'] }}</p>
                                    <div class="flex flex-wrap items-center gap-1.5 text-[11px] text-slate-500">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full {{ $chipClasses }}">
                                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                            <span class="font-medium">{{ $activity['type'] }}</span>
                                        </span>
                                        <span>•</span>
                                        <span>Aksi: {{ $action }}</span>
                                        @if(!empty($activity['status']))
                                            <span>•</span>
                                            <span>Status: {{ $activity['status'] }}</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-[11px] text-slate-400 whitespace-nowrap mt-0.5">
                                    {{ optional($updated)->diffForHumans() }}
                                </span>
                            </li>
                        @empty
                            <li class="text-slate-500">Belum ada aktivitas.</li>
                        @endforelse
                    </ul>
                </div>
            </section>

            {{-- LELANG TERBARU --}}
            <section>
                <div class="rounded-2xl border border-slate-200 bg-white">
                    <div class="px-4 sm:px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Lelang terbaru</h3>
                            <p class="text-xs text-slate-500">5 lot terakhir yang dibuat.</p>
                        </div>
                        <a href="{{ route('lots.index') }}" class="text-xs text-slate-500 hover:text-slate-900">
                            Lihat semua →
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wide text-slate-500 bg-slate-50">
                                    <th class="px-4 sm:px-5 py-2.5">Lot</th>
                                    <th class="px-4 sm:px-5 py-2.5">Status</th>
                                    <th class="px-4 sm:px-5 py-2.5 hidden md:table-cell">Mulai</th>
                                    <th class="px-4 sm:px-5 py-2.5 hidden md:table-cell">Selesai</th>
                                    <th class="px-4 sm:px-5 py-2.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($latestLots as $lot)
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="px-4 sm:px-5 py-3 align-top">
                                            <div class="font-medium text-slate-900 line-clamp-1">
                                                {{ $lot->title ?? ('Lot #' . $lot->id) }}
                                            </div>
                                            <div class="text-xs text-slate-500">ID: {{ $lot->id }}</div>
                                        </td>
                                        <td class="px-4 sm:px-5 py-3 align-top">
                                            @php $st = $lot->runtime_status; @endphp
                                            <span @class([
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold',
                                                'bg-amber-100 text-amber-800'      => $st === 'SCHEDULED',
                                                'bg-indigo-100 text-indigo-800'    => $st === 'ACTIVE',
                                                'bg-emerald-100 text-emerald-800'  => $st === 'ENDED',
                                                'bg-red-100 text-red-700'          => $st === 'CANCELLED',
                                                'bg-slate-100 text-slate-700'      => !in_array($st, ['SCHEDULED','ACTIVE','ENDED','CANCELLED']),
                                            ])>
                                                {{ $st ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="px-4 sm:px-5 py-3 align-top text-xs text-slate-500 hidden md:table-cell">
                                            {{ $lot->start_at?->format('d M Y H:i') ?? '—' }}
                                        </td>
                                        <td class="px-4 sm:px-5 py-3 align-top text-xs text-slate-500 hidden md:table-cell">
                                            {{ $lot->end_at?->format('d M Y H:i') ?? '—' }}
                                        </td>
                                        <td class="px-4 sm:px-5 py-3 align-top text-right">
                                            <a href="{{ route('lots.detail', $lot) }}"
                                               class="inline-flex items-center gap-1 text-xs text-slate-700 hover:text-slate-900">
                                                Detail <span class="text-[10px]">→</span>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 sm:px-5 py-6 text-center text-sm text-slate-500">
                                            Belum ada lot yang dibuat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const ctx = document.getElementById('lotStatusChart');
                if (!ctx) return;

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Aktif', 'Terjadwal', 'Selesai', 'Dibatalkan'],
                        datasets: [{
                            data: [
                                {{ $activeLotsCount }},
                                {{ $scheduledLots }},
                                {{ $endedLots }},
                                {{ $cancelledLots }},
                            ],
                            backgroundColor: [
                                '#4f46e5', // indigo
                                '#f59e0b', // amber
                                '#10b981', // emerald
                                '#f97373', // red-ish
                            ],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        plugins: { legend: { display: false } },
                        cutout: '65%',
                        responsive: true,
                        maintainAspectRatio: false,
                    }
                });
            });
        </script>
    @endpush

</x-app-layout>
