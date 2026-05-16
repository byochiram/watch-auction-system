{{-- resources/public/partials/bid_pagination.blade.php --}}
@php
    $perPage     = $perPage ?? 12;
    $totalBids   = $totalBids ?? 0;
    $pageCount   = (int) ceil($totalBids / $perPage);
    $currentPage = $currentPage ?? 1;

    if ($pageCount < 1) {
        $pageCount = 1;
    }

    // clamp currentPage biar nggak keluar range
    $currentPage = max(1, min($currentPage, $pageCount));

    $pages = [];

    if ($pageCount <= 7) {
        // kalau halamannya sedikit, tampilkan semua
        $pages = range(1, $pageCount);
    } else {
        // selalu tampilkan halaman pertama
        $pages[] = 1;

        // bagian kiri ellipsis (…)
        if ($currentPage > 3) {
            $pages[] = '...';
        }

        // halaman sekitar current (current-1, current, current+1)
        $start = max(2, $currentPage - 1);
        $end   = min($pageCount - 1, $currentPage + 1);

        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }

        // bagian kanan ellipsis (…)
        if ($currentPage < $pageCount - 2) {
            $pages[] = '...';
        }

        // selalu tampilkan halaman terakhir
        $pages[] = $pageCount;
    }
@endphp

@if($pageCount > 1)
    <nav class="inline-flex items-center gap-1 text-xs md:text-sm">

        {{-- Tombol Prev --}}
        @if($currentPage > 1)
            <a href="{{ route('lots.show', $lot) }}?page={{ $currentPage - 1 }}#bid-history-header"
               class="px-2 py-1 rounded border bg-white text-slate-700 border-slate-200 hover:bg-slate-50">
                ‹
            </a>
        @endif

        {{-- Nomor halaman (+ ellipsis) --}}
        @foreach($pages as $page)
            @if($page === '...')
                <span class="px-2 py-1 text-slate-400 select-none">…</span>
            @else
                <a href="{{ route('lots.show', $lot) }}?page={{ $page }}#bid-history-header"
                   class="px-2 py-1 rounded border
                        {{ $page == $currentPage
                            ? 'bg-slate-900 text-white border-slate-900'
                            : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
                    {{ $page }}
                </a>
            @endif
        @endforeach

        {{-- Tombol Next --}}
        @if($currentPage < $pageCount)
            <a href="{{ route('lots.show', $lot) }}?page={{ $currentPage + 1 }}#bid-history-header"
               class="px-2 py-1 rounded border bg-white text-slate-700 border-slate-200 hover:bg-slate-50">
                ›
            </a>
        @endif
    </nav>
@endif
