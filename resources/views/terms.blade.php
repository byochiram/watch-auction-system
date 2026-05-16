{{-- resources/views/terms.blade.php --}}
<x-auth-layout>
    <div class="w-full max-w-3xl mx-auto bg-white border border-slate-200 rounded-2xl shadow-sm p-8 md:p-10">
        {{-- HEADER --}}
        <div class="flex items-center gap-3 mb-6">
            <x-authentication-card-logo />
            <div>
                <h1 class="text-xl md:text-2xl font-semibold text-slate-900">
                    Syarat &amp; Ketentuan Tempus Auctions
                </h1>
                <p class="text-sm text-slate-500">
                    Dengan menggunakan Tempus Auctions, Anda menyetujui ketentuan di bawah ini.
                </p>
            </div>
        </div>

        {{-- CONTENT --}}
        <div class="prose prose-sm md:prose-base max-w-none text-slate-800">
            <h2>1. Tentang Layanan</h2>
            <p>
                Tempus Auctions adalah platform lelang jam tangan. Anda dapat melihat lot lelang, melakukan penawaran,
                dan menyelesaikan pembelian sesuai aturan lelang yang berlaku di platform.
            </p>

            <h2>2. Definisi Singkat</h2>
            <ul>
                <li><strong>Platform</strong>: Situs Tempus Auctions.</li>
                <li><strong>Pengguna</strong>: Setiap orang yang mengakses platform.</li>
                <li><strong>Bidder</strong>: Pengguna terdaftar yang dapat melakukan penawaran.</li>
                <li><strong>Lot</strong>: Unit jam tangan yang dilelang dalam periode tertentu.</li>
                <li><strong>Bid</strong>: Penawaran harga yang diajukan bidder.</li>
            </ul>

            <h2>3. Akun Pengguna</h2>
            <ul>
                <li>Untuk mengikuti lelang, Anda perlu membuat akun dengan data yang benar dan dapat dipertanggungjawabkan.</li>
                <li>Anda bertanggung jawab menjaga kerahasiaan akun (termasuk password) serta seluruh aktivitas di akun Anda.</li>
                <li>Kami dapat menonaktifkan akun jika terindikasi melanggar ketentuan, penyalahgunaan, atau aktivitas mencurigakan.</li>
            </ul>

            <h2>4. Verifikasi &amp; Keamanan</h2>
            <ul>
                <li>Kami dapat meminta verifikasi email untuk mengaktifkan fitur tertentu.</li>
                <li>Untuk keamanan dan pencegahan penyalahgunaan, kami dapat mencatat informasi teknis yang wajar (misalnya perangkat/sesi) sebagai bagian dari audit keamanan.</li>
            </ul>

            <h2>5. Informasi Produk &amp; Lelang</h2>
            <ul>
                <li>Detail lot (foto, spesifikasi, kondisi, harga awal, waktu lelang) ditampilkan di halaman lot.</li>
                <li>Kami berusaha menampilkan informasi seakurat mungkin. Perbedaan tampilan warna dapat terjadi karena layar/perangkat.</li>
                <li>Anda disarankan membaca detail lot dengan teliti sebelum melakukan bid.</li>
            </ul>

            <h2>6. Aturan Penawaran (Bid)</h2>
            <ol>
                <li>Bid hanya dapat dilakukan oleh bidder yang memenuhi syarat sesuai aturan platform.</li>
                <li>Bid yang berhasil masuk tercatat di sistem dan <strong>mengikat</strong>.</li>
                <li>Nominal bid harus mengikuti aturan minimal/kelipatan yang ditentukan pada lot.</li>
                <li>Upaya manipulasi (misalnya akun ganda, bid palsu, atau rekayasa harga) dilarang dan dapat menyebabkan pembatasan fitur atau penangguhan akun.</li>
            </ol>

            <h2>7. Pemenang &amp; Pembayaran</h2>
            <ol>
                <li>Setelah lelang berakhir, pemenang ditentukan berdasarkan bid tertinggi yang sah pada saat penutupan.</li>
                <li>Pemenang wajib menyelesaikan pembayaran sesuai instruksi dan batas waktu yang ditampilkan di platform.</li>
                <li>Jika pembayaran tidak dilakukan tepat waktu, kami dapat membatalkan kemenangan dan mengambil tindakan lanjutan sesuai kebijakan platform.</li>
                <li>Biaya tambahan seperti ongkos kirim/biaya layanan dapat berlaku dan akan ditampilkan saat proses pembayaran.</li>
            </ol>

            <h2>8. Pengiriman</h2>
            <ul>
                <li>Barang dikirim ke alamat yang Anda pilih/konfirmasi saat checkout.</li>
                <li>Nomor resi/pelacakan akan diberikan apabila tersedia.</li>
                <li>Ketentuan pengiriman mengikuti kebijakan layanan ekspedisi yang digunakan.</li>
            </ul>

            <h2>9. Pembatalan &amp; Kondisi Khusus</h2>
            <ul>
                <li>Dalam kondisi tertentu (misalnya kesalahan teknis, indikasi kecurangan, atau alasan operasional), kami dapat membatalkan lot/lelang yang terdampak dan akan menginformasikan pengguna.</li>
            </ul>

            <h2>10. Larangan</h2>
            <p>Anda dilarang:</p>
            <ul>
                <li>Menggunakan data palsu atau identitas orang lain.</li>
                <li>Membuat banyak akun untuk tujuan manipulasi.</li>
                <li>Mengganggu/merusak sistem atau mencoba akses tanpa izin.</li>
            </ul>

            <h2>11. Batasan Tanggung Jawab</h2>
            <ul>
                <li>Layanan disediakan apa adanya sesuai kemampuan platform.</li>
                <li>Kami tidak bertanggung jawab atas kerugian akibat kelalaian pengguna dalam menjaga akun.</li>
                <li>Jika terjadi gangguan teknis, kami dapat melakukan penyesuaian yang wajar untuk menjaga keadilan dan keamanan proses lelang.</li>
            </ul>

            <h2>12. Perubahan Ketentuan</h2>
            <p>
                Syarat &amp; Ketentuan dapat diperbarui sewaktu-waktu. Versi terbaru akan ditampilkan di halaman ini.
                Dengan terus menggunakan platform, Anda dianggap menyetujui perubahan tersebut.
            </p>

            <h2>13. Kontak</h2>
            <p>
                Jika Anda membutuhkan bantuan, silakan hubungi admin melalui kanal kontak yang tersedia di platform.
            </p>
        </div>
    </div>
</x-auth-layout>