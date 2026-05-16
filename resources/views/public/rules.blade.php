<x-guest-layout>
    <div class="max-w-screen-xl mx-auto px-4 space-y-10">

        {{-- BREADCRUMB / HEADER --}}
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.18em] text-slate-400 mb-1">
                    Panduan Pengguna
                </p>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900">
                    Panduan & Aturan Lelang
                </h1>
                <p class="mt-2 text-sm md:text-base text-slate-600 max-w-2xl">
                    Halaman ini membantu Anda memahami cara mengikuti lelang di platform kami,
                    mulai dari pendaftaran hingga proses pembayaran setelah menang.
                </p>
            </div>

            <div class="flex items-center gap-2 text-sm text-slate-500">
                <a href="{{ route('home') }}" class="hover:text-slate-900">Beranda</a>
                <span>/</span>
                <span class="font-medium text-slate-900">Panduan & Aturan</span>
            </div>
        </div>

        {{-- QUICK NAV --}}
        <div class="grid gap-2 sm:flex sm:flex-wrap">
            <a href="#cara-ikut"
               class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium 
                      bg-slate-900 text-white hover:bg-slate-800 transition">
                <span>⚙️ Cara Mengikuti Lelang</span>
            </a>
            <a href="#aturan-utama"
               class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium 
                      bg-slate-50 text-slate-700 border border-slate-200 hover:bg-slate-100 transition">
                📜 Aturan Utama
            </a>
            <a href="#status-lelang"
               class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium 
                      bg-slate-50 text-slate-700 border border-slate-200 hover:bg-slate-100 transition">
                ⏱️ Status & Waktu Lelang
            </a>
            <a href="#pembayaran"
               class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium 
                      bg-slate-50 text-slate-700 border border-slate-200 hover:bg-slate-100 transition">
                💳 Pembayaran & Penyelesaian
            </a>
            <a href="#komplain"
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium 
                    bg-slate-50 text-slate-700 border border-slate-200 hover:bg-slate-100 transition">
                🔁 Komplain & Pengembalian Dana
            </a>
            <a href="#faq"
               class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium 
                      bg-slate-50 text-slate-700 border border-slate-200 hover:bg-slate-100 transition">
                ❓ FAQ
            </a>
        </div>

        {{-- RINGKASAN SINGKAT --}}
        <section class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Langkah 1</p>
                <h2 class="text-sm font-semibold text-slate-900 mb-1">Daftar & verifikasi akun</h2>
                <p class="text-xs text-slate-600">
                    Buat akun, lengkapi data dasar, dan verifikasi email Anda sebelum mulai melakukan bid.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Langkah 2</p>
                <h2 class="text-sm font-semibold text-slate-900 mb-1">Pilih lot & ajukan bid</h2>
                <p class="text-xs text-slate-600">
                    Baca detail produk dengan saksama, perhatikan kelipatan bid, dan ajukan tawaran sesuai aturan.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Langkah 3</p>
                <h2 class="text-sm font-semibold text-slate-900 mb-1">Menang & selesaikan pembayaran</h2>
                <p class="text-xs text-slate-600">
                    Jika Anda pemenang, lakukan pembayaran sesuai instruksi pada invoice dalam batas waktu yang ditentukan.
                </p>
            </div>
        </section>

        {{-- CARA MENGIKUTI LELANG --}}
        <section id="cara-ikut" class="space-y-4 scroll-mt-24">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg md:text-xl font-semibold text-slate-900">
                    Cara Mengikuti Lelang
                </h2>
                <span class="text-[11px] font-medium text-slate-500">
                    Ikuti langkah-langkah di bawah ini sebelum melakukan bid pertama Anda.
                </span>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                {{-- LEFT: STEPS --}}
                <ol class="space-y-3">
                    <li class="flex gap-3">
                        <div class="mt-1 flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 text-white text-xs font-semibold">
                            1
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Buat & verifikasi akun</h3>
                            <p class="text-xs text-slate-600 mt-1">
                                Daftar menggunakan email yang aktif. Setelah itu, cek inbox untuk melakukan verifikasi akun.
                                Hanya akun terverifikasi yang bisa mengikuti lelang.
                            </p>
                        </div>
                    </li>

                    <li class="flex gap-3">
                        <div class="mt-1 flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 text-white text-xs font-semibold">
                            2
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Pastikan data akun sudah benar</h3>
                            <p class="text-xs text-slate-600 mt-1">
                                Data diri dan kontak diisi saat pendaftaran dan digunakan untuk konfirmasi jika Anda memenangkan lelang. Pastikan email dan nomor HP aktif.
                            </p>
                        </div>
                    </li>

                    <li class="flex gap-3">
                        <div class="mt-1 flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 text-white text-xs font-semibold">
                            3
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Pilih lot yang Anda minati</h3>
                            <p class="text-xs text-slate-600 mt-1">
                                Baca deskripsi produk, kondisi, tahun, dan foto dengan teliti.
                                Pastikan Anda memahami spesifikasi sebelum melakukan tawaran.
                            </p>
                        </div>
                    </li>

                    <li class="flex gap-3">
                        <div class="mt-1 flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 text-white text-xs font-semibold">
                            4
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Perhatikan status & waktu lelang</h3>
                            <p class="text-xs text-slate-600 mt-1">
                                Lelang hanya bisa di-bid saat status <span class="font-semibold text-emerald-700">Live</span>.
                                Saat status <span class="font-semibold text-amber-700">Segera Dimulai</span> atau 
                                <span class="font-semibold text-red-700">Selesai</span>, tombol bid akan nonaktif.
                            </p>
                        </div>
                    </li>

                    <li class="flex gap-3">
                        <div class="mt-1 flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 text-white text-xs font-semibold">
                            5
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Ajukan bid sesuai kelipatan</h3>
                            <p class="text-xs text-slate-600 mt-1">
                                Masukkan nominal minimal sebesar <strong>“Minimal Bid Berikutnya”</strong> dan
                                selalu ikuti kelipatan bid yang tertera. Sistem akan menolak bid di bawah ketentuan.
                            </p>
                        </div>
                    </li>
                </ol>

                {{-- RIGHT: INFO CARD --}}
                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 space-y-3">
                    <h3 class="text-sm font-semibold text-slate-900">
                        Tips sebelum melakukan bid
                    </h3>
                    <ul class="text-xs text-slate-600 space-y-2 list-disc list-inside">
                        <li>Tentukan batas harga maksimum yang nyaman untuk Anda sebelum lelang dimulai.</li>
                        <li>Periksa kembali jadwal mulai dan berakhirnya lelang agar tidak terlewat.</li>
                        <li>Pastikan koneksi internet stabil saat melakukan bid mendekati akhir waktu.</li>
                        <li>Simak deskripsi kondisi jam secara detail, termasuk catatan pemakaian dan kelengkapan.</li>
                    </ul>
                </div>
            </div>
        </section>

        {{-- ATURAN UTAMA --}}
        <section id="aturan-utama" class="space-y-4 scroll-mt-24">
            <h2 class="text-lg md:text-xl font-semibold text-slate-900">
                Aturan Utama Lelang
            </h2>

            <div class="grid gap-4 md:grid-cols-2">
                {{-- DO --}}
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-white text-xs">
                            ✔
                        </span>
                        <h3 class="text-sm font-semibold text-emerald-900">Hal yang Diwajibkan</h3>
                    </div>
                    <ul class="text-xs text-emerald-900/90 space-y-2 list-disc list-inside">
                        <li>Mengisi data akun dan profil dengan informasi yang benar dan dapat dipertanggungjawabkan.</li>
                        <li>Membaca deskripsi produk dan syarat lelang masing-masing lot sebelum melakukan bid.</li>
                        <li>Memastikan Anda sanggup menyelesaikan pembayaran jika ditetapkan sebagai pemenang.</li>
                        <li>Merespons konfirmasi dari tim kami dalam jangka waktu yang sudah ditentukan.</li>
                    </ul>
                </div>

                {{-- DON'T --}}
                <div class="rounded-2xl border border-red-200 bg-red-50/70 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-white text-xs">
                            ✖
                        </span>
                        <h3 class="text-sm font-semibold text-red-900">Hal yang Dilarang</h3>
                    </div>
                    <ul class="text-xs text-red-900/90 space-y-2 list-disc list-inside">
                        <li>Melakukan bid hanya untuk merusak harga atau tanpa niat membeli.</li>
                        <li>Menggunakan lebih dari satu akun untuk memanipulasi hasil lelang.</li>
                        <li>Mengabaikan kewajiban pembayaran setelah ditetapkan sebagai pemenang.</li>
                        <li>Menyebarkan informasi yang menyesatkan terkait produk atau proses lelang.</li>
                    </ul>
                </div>
            </div>

            <p class="text-[11px] text-slate-500">
                Pelanggaran terhadap aturan di atas dapat berakibat pada pembatalan hasil lelang, pembatasan akun,
                hingga pemblokiran permanen dari platform.
            </p>
        </section>

        {{-- STATUS & WAKTU LELANG --}}
        <section id="status-lelang" class="space-y-4 scroll-mt-24">
            <h2 class="text-lg md:text-xl font-semibold text-slate-900">
                Status Lelang & Countdown Waktu
            </h2>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 p-4 space-y-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-600/95 text-white">
                        Live
                    </span>
                    <p class="text-xs text-slate-600">
                        Lelang sedang berlangsung. Anda dapat memasukkan tawaran sesuai ketentuan minimal dan kelipatan bid.
                        Countdown menunjukkan sisa waktu hingga lelang berakhir.
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-4 space-y-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-500/95 text-white">
                        Segera Dimulai
                    </span>
                    <p class="text-xs text-slate-600">
                        Lelang belum dimulai. Anda sudah bisa melihat detail produk dan jadwal,
                        namun tombol bid belum dapat digunakan.
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-4 space-y-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-700/95 text-white">
                        Selesai
                    </span>
                    <p class="text-xs text-slate-600">
                        Lelang telah berakhir. Bid tidak dapat lagi diajukan dan sistem akan menentukan pemenang
                        berdasarkan bid tertinggi yang sah.
                    </p>
                </div>
            </div>
        </section>

        {{-- PEMBAYARAN & PENYELESAIAN --}}
        <section id="pembayaran" class="space-y-4 scroll-mt-24">
            <h2 class="text-lg md:text-xl font-semibold text-slate-900">
                Pembayaran & Penyelesaian Lelang
            </h2>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-2">
                    <h3 class="text-sm font-semibold text-slate-900">Jika Anda Menang Lelang</h3>
                    <ul class="text-xs text-slate-600 space-y-2 list-disc list-inside">
                        <li>
                            Anda akan menerima notifikasi melalui email jika ditetapkan sebagai pemenang.
                        </li>
                        <li>
                            Pembayaran wajib diselesaikan dalam jangka waktu yang ditentukan di invoice atau notifikasi.
                        </li>
                        <li>
                            Detail nomor rekening dan metode pembayaran resmi hanya tercantum pada halaman resmi kami.
                        </li>
                        <li>
                            Setelah pembayaran terkonfirmasi, tim kami akan memproses pengiriman dan mengirimkan nomor resi.
                        </li>
                    </ul>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 space-y-2">
                    <h3 class="text-sm font-semibold text-slate-900">Keterlambatan & Pembatalan</h3>
                    <ul class="text-xs text-slate-600 space-y-2 list-disc list-inside">
                        <li>
                            Jika pembayaran tidak dilakukan dalam batas waktu, kami akan menangguhkan sementara akun Anda.
                        </li>
                        <li>
                            Akun yang berulang kali menang namun tidak menyelesaikan pembayaran dapat diblokir permanen.
                        </li>
                        <li>
                            Untuk kondisi khusus, silakan hubungi tim kami melalui kontak resmi yang tercantum di halaman
                            <a href="{{ route('about') }}" class="underline text-slate-900 font-medium">Tentang Kami</a>.
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        {{-- KOMPLAIN & PENGEMBALIAN DANA --}}
        <section id="komplain" class="space-y-4 scroll-mt-24">
            <h2 class="text-lg md:text-xl font-semibold text-slate-900">
                Komplain & Pengembalian Dana
            </h2>

            <div class="grid gap-4 lg:grid-cols-2">
                {{-- KEBIJAKAN --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-2">
                    <h3 class="text-sm font-semibold text-slate-900">Kebijakan</h3>
                    <ul class="text-xs text-slate-600 space-y-2 list-disc list-inside">
                        <li>Pada prinsipnya, transaksi lelang bersifat final dan tidak menerima retur atau tukar.</li>
                        <li>Komplain dapat diajukan jika ada ketidaksesuaian signifikan pada deskripsi lot atau terjadi kesalahan pengiriman.</li>
                        <li>Komplain wajib disertai bukti (foto/video unboxing) dan diajukan segera setelah barang diterima.</li>
                        <li>Pengembalian dana atau solusi lain dipertimbangkan setelah verifikasi tim kami melalui kanal resmi.</li>
                    </ul>
                </div>

                {{-- PROSEDUR KOMPLAIN --}}
                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 space-y-2">
                    <h3 class="text-sm font-semibold text-slate-900">Prosedur Komplain</h3>
                    <ul class="text-xs text-slate-600 space-y-2 list-disc list-inside">
                        <li>Hubungi tim kami melalui kanal resmi yang tercantum di halaman <a href="{{ route('about') }}" class="underline text-slate-900 font-medium">Tentang Kami</a>.</li>
                        <li>Sertakan nomor invoice, nama lot, dan bukti pendukung (foto/video pembukaan paket).</li>
                        <li>Mohon tidak melakukan perbaikan atau modifikasi pada jam sebelum ada arahan dari tim kami.</li>
                        <li>Tim kami akan melakukan verifikasi dan menginformasikan tindak lanjutnya.</li>
                    </ul>
                </div>
            </div>

            <p class="text-[11px] text-slate-500">
                Catatan: Untuk keterlambatan atau kerusakan akibat pengiriman oleh pihak kurir berada di luar tanggung jawab kami dan penanganan akan mengikuti kebijakan jasa pengiriman terkait.
            </p>
        </section>

        {{-- FAQ --}}
        <section id="faq" class="space-y-4 scroll-mt-24" x-data="{ open: 'q1' }">
            <h2 class="text-lg md:text-xl font-semibold text-slate-900">
                Pertanyaan yang Sering Diajukan (FAQ)
            </h2>
            <div class="space-y-2">
                {{-- Q1 --}}
                <div class="border border-slate-200 rounded-xl bg-white">
                    <button type="button"
                            class="w-full flex items-center justify-between px-4 py-3 text-left text-sm font-medium text-slate-900"
                            @click="open === 'q1' ? open = null : open = 'q1'">
                        <span>Apa yang terjadi jika dua orang memasukkan nominal bid yang sama?</span>
                        <span class="ml-3 text-slate-400" x-text="open === 'q1' ? '−' : '+'"></span>
                    </button>
                    <div x-show="open === 'q1'" x-collapse class="px-4 pb-4 text-xs text-slate-600">
                        Jika ada nominal bid yang sama, sistem akan mengutamakan bid yang masuk terlebih dahulu (waktu tercepat).
                    </div>
                </div>

                {{-- Q2 --}}
                <div class="border border-slate-200 rounded-xl bg-white">
                    <button type="button"
                            class="w-full flex items-center justify-between px-4 py-3 text-left text-sm font-medium text-slate-900"
                            @click="open === 'q2' ? open = null : open = 'q2'">
                        <span>Bisakah saya membatalkan bid yang sudah diajukan?</span>
                        <span class="ml-3 text-slate-400" x-text="open === 'q2' ? '−' : '+'"></span>
                    </button>
                    <div x-show="open === 'q2'" x-collapse class="px-4 pb-4 text-xs text-slate-600">
                        Pada prinsipnya bid yang sudah diajukan bersifat mengikat dan tidak dapat dibatalkan.
                        Hubungi tim kami hanya jika terjadi kesalahan teknis yang jelas.
                    </div>
                </div>

                {{-- Q3 --}}
                <div class="border border-slate-200 rounded-xl bg-white">
                    <button type="button"
                            class="w-full flex items-center justify-between px-4 py-3 text-left text-sm font-medium text-slate-900"
                            @click="open === 'q3' ? open = null : open = 'q3'">
                        <span>Apakah semua jam sudah diperiksa keasliannya?</span>
                        <span class="ml-3 text-slate-400" x-text="open === 'q3' ? '−' : '+'"></span>
                    </button>
                    <div x-show="open === 'q3'" x-collapse class="px-4 pb-4 text-xs text-slate-600">
                        Setiap lot melewati proses kurasi internal. Namun, pastikan Anda membaca kembali deskripsi dan
                        catatan kondisi yang kami tuliskan pada halaman detail masing-masing lot.
                    </div>
                </div>

                {{-- Q4 --}}
                <div class="border border-slate-200 rounded-xl bg-white">
                    <button type="button"
                            class="w-full flex items-center justify-between px-4 py-3 text-left text-sm font-medium text-slate-900"
                            @click="open === 'q4' ? open = null : open = 'q4'">
                        <span>Ke mana saya menghubungi jika ada kendala?</span>
                        <span class="ml-3 text-slate-400" x-text="open === 'q4' ? '−' : '+'"></span>
                    </button>
                    <div x-show="open === 'q4'" x-collapse class="px-4 pb-4 text-xs text-slate-600">
                        Anda dapat menghubungi kami melalui kanal resmi yang tercantum di halaman 
                        <a href="{{ route('about') }}" class="underline text-slate-900 font-medium">Tentang Kami</a>.
                        Mohon hindari komunikasi di luar channel resmi untuk mengurangi risiko penipuan.
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-guest-layout>
