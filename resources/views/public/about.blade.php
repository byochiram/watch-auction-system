<x-guest-layout>
    <div class="max-w-screen-xl mx-auto px-4 space-y-10">

        {{-- HEADER --}}
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.18em] text-slate-400 mb-1">
                    Tentang Kami
                </p>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900">
                    Tempus Auctions
                </h1>
                <p class="mt-2 text-base text-slate-600 max-w-2xl">
                    Platform lelang jam tangan yang mengutamakan kurasi, transparansi, dan kenyamanan.
                </p>
            </div>

            <div class="flex items-center gap-2 text-sm text-slate-500">
                <a href="{{ route('home') }}" class="hover:text-slate-900">Beranda</a>
                <span>/</span>
                <span class="font-medium text-slate-900">Tentang Kami</span>
            </div>
        </div>

        {{-- CONTENT (SIMPLE, REFINED) --}}
        <section class="w-full">
            <div class="mx-auto w-full max-w-4xl">
                <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    {{-- accent line --}}
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-slate-900 via-amber-500 to-slate-900 opacity-70"></div>

                    <div class="p-6 md:p-8">
                        {{-- mini highlights (tetap ringkas) --}}
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                                <p class="text-xs font-semibold text-slate-900">Kurasi</p>
                                <p class="mt-1 text-xs text-slate-600">Lot dipilih terarah, bukan sekadar banyak.</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                                <p class="text-xs font-semibold text-slate-900">Transparansi</p>
                                <p class="mt-1 text-xs text-slate-600">Info, kondisi, dan jadwal dibuat jelas.</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                                <p class="text-xs font-semibold text-slate-900">Tertib</p>
                                <p class="mt-1 text-xs text-slate-600">Aturan bid konsisten untuk semua.</p>
                            </div>
                        </div>

                        {{-- copy (boleh tetap punyamu, ini versi sedikit beda dari web lama) --}}
                        <div class="mt-6 space-y-4 text-sm md:text-base text-slate-700 leading-relaxed">
                            <p>
                                Tempus Auctions hadir untuk kolektor yang ingin lelang jam tangan dengan pengalaman yang rapi, tenang, dan mudah diikuti.
                            </p>

                            <p>
                                Setiap lot kami sajikan dengan foto yang jelas, ringkasan kondisi yang jujur, dan jadwal lelang yang tegas agar Anda nyaman mengambil keputusan.
                            </p>

                            <p>
                                Untuk keamanan, seluruh informasi penting terkait lelang dan pembayaran hanya kami sampaikan melalui
                                kanal resmi di platform ini.
                            </p>

                            <p>
                                Jika Anda membutuhkan bantuan, silakan hubungi kami melalui
                                <a href="mailto:halo@tempuscollective.com" class="font-semibold text-slate-900 underline underline-offset-4">
                                    halo@tempuscollective.com
                                </a>.
                            </p>
                        </div>

                        {{-- CTA (biar tidak “teks doang”) --}}
                        <div class="mt-6 flex flex-wrap gap-2">
                            <a href="{{ route('home') }}"
                            class="inline-flex items-center justify-center rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition">
                                Lihat Lelang
                            </a>
                            <a href="{{ route('rules') }}"
                            class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-100 transition">
                                Panduan & Aturan
                            </a>
                        </div>

                        {{-- tagline: jangan sama persis dengan web lama --}}
                        <div class="mt-6 border-t border-slate-200 pt-4 text-sm text-slate-600 italic">
                            Timeless pieces, exclusive bids.
                            <span class="block not-italic text-xs text-slate-500 mt-1">— Tempus Auctions</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</x-guest-layout>
