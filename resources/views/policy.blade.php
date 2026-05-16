{{-- resources/views/policy.blade.php --}}
<x-auth-layout>
    <div class="w-full max-w-3xl mx-auto bg-white border border-slate-200 rounded-2xl shadow-sm p-8 md:p-10">
        {{-- HEADER --}}
        <div class="flex items-center gap-3 mb-6">
            <x-authentication-card-logo />
            <div>
                <h1 class="text-xl md:text-2xl font-semibold text-slate-900">
                    Kebijakan Privasi Tempus Auctions
                </h1>
                <p class="text-sm text-slate-500">
                    Menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi data Anda.
                </p>
            </div>
        </div>

        {{-- CONTENT --}}
        <div class="prose prose-sm md:prose-base max-w-none text-slate-800">
            <h2>1. Ringkasan</h2>
            <p>
                Kebijakan Privasi ini berlaku saat Anda mengakses, mendaftar, atau menggunakan Tempus Auctions.
                Kami memproses data seperlunya untuk menjalankan layanan lelang, transaksi, pengiriman, keamanan sistem,
                dan komunikasi layanan.
            </p>

            <h2>2. Data yang Kami Kumpulkan</h2>

            <h3>2.1 Data akun</h3>
            <ul>
                <li>Nama dan username</li>
                <li>Alamat email</li>
                <li>Password (disimpan dalam bentuk hash dan tidak ditampilkan dalam bentuk asli)</li>
            </ul>

            <h3>2.2 Data profil & layanan</h3>
            <ul>
                <li>Nomor HP</li>
                <li>Alamat pengiriman (saat diperlukan untuk pengiriman barang)</li>
            </ul>

            <h3>2.3 Data transaksi & aktivitas lelang</h3>
            <ul>
                <li>Riwayat bid dan aktivitas lelang</li>
                <li>Informasi transaksi/pembayaran (misalnya status tagihan dan waktu transaksi)</li>
            </ul>

            <h3>2.4 Data teknis</h3>
            <ul>
                <li>Alamat IP dan informasi perangkat/browser (user agent)</li>
                <li>Cookie/sesi login untuk menjaga Anda tetap masuk</li>
                <li>Log aktivitas dasar untuk keamanan, audit, dan perbaikan layanan</li>
            </ul>

            <h2>3. Tujuan Penggunaan Data</h2>
            <ul>
                <li>Membuat dan mengelola akun Anda.</li>
                <li>Menyediakan fitur lelang: menampilkan lot, mencatat bid, dan menampilkan riwayat bid.</li>
                <li>Memproses transaksi pembayaran dan menampilkan status tagihan.</li>
                <li>Mendukung pengiriman barang jika Anda memenangkan lelang.</li>
                <li>Menjaga keamanan platform: pencegahan penyalahgunaan, fraud, akses tidak sah, serta troubleshooting.</li>
                <li>Mengirim notifikasi layanan yang diperlukan untuk pengelolaan akun dan proses lelang, termasuk status lelang, bid, dan transaksi.</li>
            </ul>

            <h2>4. Berbagi Data dengan Pihak Ketiga</h2>
            <p>
                Kami dapat membagikan data seperlunya hanya kepada pihak berikut sesuai kebutuhan layanan:
            </p>
            <ul>
                <li><strong>Mitra pembayaran (payment gateway)</strong> untuk memproses pembayaran.</li>
                <li><strong>Penyedia jasa pengiriman/kurir</strong> untuk pengiriman barang ke alamat Anda.</li>
                <li><strong>Penyedia infrastruktur/hosting</strong> untuk menjalankan layanan secara teknis.</li>
                <li><strong>Otoritas berwenang</strong> jika diminta secara sah berdasarkan hukum.</li>
            </ul>
            <p>
                Kami tidak menjual data pribadi Anda kepada pihak ketiga untuk tujuan pemasaran.
            </p>

            <h2>5. Penyimpanan & Retensi</h2>
            <ul>
                <li>Data disimpan selama diperlukan untuk menyediakan layanan dan menjaga keamanan/audit.</li>
                <li>Data terkait bid/transaksi dapat disimpan untuk kebutuhan pencatatan dan keamanan sistem.</li>
            </ul>

            <h2>6. Hak Anda</h2>
            <ul>
                <li>Anda dapat mengakses dan memperbarui data profil melalui fitur yang tersedia di platform.</li>
                <li>Anda dapat meminta koreksi data jika terdapat ketidaksesuaian.</li>
            </ul>

            <h2>7. Keamanan Data</h2>
            <ul>
                <li>Kami menerapkan langkah keamanan yang wajar untuk melindungi data dari akses tidak sah.</li>
                <li>Anda bertanggung jawab menjaga kerahasiaan password dan akses akun Anda.</li>
            </ul>

            <h2>8. Cookie</h2>
            <ul>
                <li>Kami menggunakan cookie/sesi untuk menjaga Anda tetap login dan meningkatkan pengalaman penggunaan.</li>
                <li>Anda dapat mengatur browser untuk menolak cookie, namun beberapa fitur mungkin tidak berjalan optimal.</li>
            </ul>

            <h2>9. Perubahan Kebijakan</h2>
            <p>
                Kebijakan Privasi ini dapat diperbarui sewaktu-waktu. Versi terbaru akan ditampilkan di halaman ini.
                Dengan tetap menggunakan Tempus Auctions setelah perubahan, Anda dianggap menyetujui kebijakan yang diperbarui.
            </p>

            <h2>10. Kontak</h2>
            <p>
                Jika Anda membutuhkan bantuan, silakan hubungi admin melalui kanal kontak yang tersedia di platform.
            </p>
        </div>
    </div>
</x-auth-layout>