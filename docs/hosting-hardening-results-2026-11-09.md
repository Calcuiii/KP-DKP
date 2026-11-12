# Hasil perbaikan kesiapan hosting — 9 November 2026

Dokumen ini memperbarui status temuan pada `hosting-readiness-audit-2026-11-09.md`. Audit awal tetap disimpan sebagai catatan kondisi sebelum perbaikan.

Pembaruan berikutnya untuk kebijakan biaya AI, pembatasan pertanyaan, pemantauan, dan perpindahan ke akun dinas ada di [panduan chatbot Free Plan](chatbot-free-plan-handover.md). Groq saat ini nonaktif sampai akun Free Plan dinas dikonfirmasi; website tetap menggunakan KB tanpa kredensial eksternal.

## Perbaikan prioritas tinggi

| Temuan | Perbaikan |
|---|---|
| Advisori dependensi | Composer dan npm diperbarui dalam rentang versi proyek. Lockfile PHP menggunakan platform 8.3 agar tidak tanpa sengaja mewajibkan PHP 8.4. Audit registry setelah pembaruan: PHP 0 advisori, npm 0 kerentanan. |
| Surat balasan publik | Upload, unduh admin/peserta, dan lampiran email sekarang memakai disk privat. Dokumen KB unggahan juga privat. Penerbit surat menyimpan file baru dan mengunci transaksi pengajuan sebelum mengganti berkas lama. |
| Data file lama | Perintah `documents:privatize` memverifikasi hash salinan dan backup sebelum menghapus sumber publik; konflik isi menghentikan pemindahan. Sudah dijalankan lokal: 3 dokumen dipindahkan. Backup privat: `storage/app/private/migration-backups/20261109185833-8abda8bb`. Tidak mengubah identitas/data peserta. |
| WOPPS terputus | Route keputusan dan penyelesaian tersedia di kelompok superadmin. Alur diterima/ditolak → dihubungi → selesai diuji. Surat balasan peserta mengikuti pengajuan yang dipilih; formulir admin mengirim ID pengajuan yang diperiksa kepemilikannya. |
| Admin bawaan | Seeder tidak lagi membuat akun/password tetap atau memanggil class seeder contoh yang tidak tersedia. `admin:provision` meminta password tersembunyi yang kuat, menolak penggantian akun tanpa opsi eksplisit, serta mempertahankan batas satu superadmin. Login produksi menolak password bawaan yang diketahui. |

## Perbaikan temuan lainnya

- Penghapusan KB mencabut registry dan Markdown olahan. Reindex mencabut versi lama sebelum ekstraksi sehingga kegagalan tidak meninggalkan pengetahuan lama aktif. Perubahan lifecycle dan registry menggunakan lock. Pengujian mencakup penghapusan dan kegagalan reindex.
- Ekspor log percakapan dan analitik memakai formatter bersama yang menetralkan awalan formula dan mengamankan tanda kutip CSV.
- Penerbitan sertifikat memeriksa layanan magang serta keputusan diterima, menyimpan file baru dengan transaksi, dan menghapus file sebelumnya sesudah sukses. Timestamp email baru diisi setelah transport email menerima pengiriman. SMTP gagal menghasilkan pesan bahwa sertifikat tersedia di portal sementara email gagal; file dan timestamp tetap benar. Pengiriman saat ini sinkron; queue worker tidak diperlukan untuk notifikasi ini saja.
- Bukti tahap 6/7 disimpan dengan penguncian pengajuan. Pengajuan selesai atau sertifikat terbit tidak dapat diubah; tahap 6 juga tidak dapat diganti setelah tahap 7 dikirim. File tergantikan dibersihkan setelah sukses. POST tahapan awal menolak pengajuan yang sudah diputuskan/ditutup dan diberi throttle unggahan.
- Metadata surat bersama disimpan dengan hash isi dan normalisasi institusi. Pemakaian ulang pada pengajuan berbeda oleh peserta yang sama ditolak. Admin melihat kelompok dan wajib mencocokkan identitas setiap peserta; keputusan setiap pengajuan tetap terpisah.
- Jalur lama pemeriksaan permintaan sertifikat dihapus dari persetujuan surat dan panel penghambat dashboard, sesuai keputusan sebelumnya bahwa pemeriksaan surat magang tidak memeriksa permohonan sertifikat. Metadata lama tidak mengunci Form Resmi. Template permohonan dan informasi persiapan sertifikat tetap tersedia sesuai kebijakan dinas.
- Notifikasi hasil pemeriksaan mengarahkan peserta ke tahap terkait dan memilih pengajuan miliknya yang benar. Dashboard admin tidak menampilkan tombol pemeriksaan dokumen untuk admin operasional.
- Jawaban overview mengenali kata “tahapan”. Test corpus chatbot tidak lagi bercampur dengan unggahan KB lokal, dan provider eksternal dinonaktifkan secara default dalam test. Test provider tersendiri memakai HTTP fake.
- Test lama diselaraskan dengan keputusan terbaru: pemilihan layanan untuk tamu; akun login dapat langsung memakai chatbot; kalender di halaman kegiatan; pengelolaan sertifikat yang sudah tersedia. Fixture timestamp secara eksplisit dikonversi ke timezone aplikasi sebelum disimpan pada kolom waktu tanpa zona. Tidak mengubah timestamp database peserta.
- Header keamanan nosniff, frame SAMEORIGIN, Referrer-Policy, Permissions-Policy, dan no-store pada respons akun/API diterapkan. Produksi HTTPS menambahkan HSTS. Host produksi dibatasi ke host APP_URL; proxy tetap harus dikonfigurasi sesuai hosting. Cookie Secure menjadi default produksi.
- Kunci riwayat chatbot browser menggunakan UUID kriptografis dan storage terpisah untuk tamu/peserta/admin. Browser mengirim kunci GET lewat header, bukan query URL. Error chatbot hanya mencatat jenis exception, tanpa session_key atau exception mentah yang dapat menyertakan SQL/payload. Riwayat tetap berbasis perangkat dan kemampuan memegang UUID; ini bukan migrasi riwayat ke kepemilikan akun server.
- Template `.env.production.example` dan perintah read-only `deployment:check` tersedia. Pemeriksaan ini sengaja menolak konfigurasi development, password admin default, marker Vite, dan dokumen resmi yang masih publik.

## Validasi

- Full suite: **273 test, 1.794 assertion, seluruhnya lulus** setelah perbaikan fitur dan penambahan regresi keamanan.
- Build produksi berhasil dengan dependensi baru.
- Composer audit non-dev: `advisories: []`; npm audit penuh: 0 kerentanan.
- `composer check-platform-reqs --no-dev`: berhasil pada PHP lokal 8.4.25; resolusi lockfile dibatasi untuk PHP 8.3.
- Kompilasi template, PHP syntax, Pint, dan diff-check dijalankan pada perubahan.
- Artefak verifikasi tersimpan ignored di `.codex-artifacts/hardening-*`.

## Langkah di server hosting/staging

Perbaikan kode ini belum berarti deployment produksi sudah divalidasi. Server/domain hosting belum diberikan.

1. Buat database dan kredensial khusus staging/produksi; jangan menyalin database development beserta akun uji. Salin nilai konfigurasi dari `.env.production.example`, isi domain HTTPS, SMTP dan kredensial Sheets/provider secara privat. Gunakan APP_KEY baru hanya pada instalasi baru; pertahankan key pada instalasi yang sudah menyimpan data terenkripsi.
2. Arahkan document root ke `public/`. Root proyek, `.env`, `.git`, vendor, file privat, work, dan `.codex-artifacts` tidak boleh menjadi area unduhan. Terapkan deny-execution pada area unggahan dan matikan directory listing. Vite development dan `artisan serve` bukan server publik produksi.
3. Instal dependensi dengan `composer install --no-dev --optimize-autoloader`. Bangun aset menggunakan `npm ci --ignore-scripts` lalu `npm run build` pada mesin build yang kompatibel. Hapus `public/hot` pada **rilis hosting**, bukan sekadar mengandalkan keberadaan `public/build`. File hot lokal masih dipertahankan untuk sesi development yang sedang berjalan.
4. Backup DB, private storage, unggahan infografis, registry dan hasil olahan KB. Jalankan migrasi yang sudah ditinjau dengan `php artisan migrate --force`; jangan migrate:fresh. Pada instalasi lama jalankan `php artisan documents:privatize --force` sebelum membuka trafik. Pastikan direktori backup tetap privat. Pada instalasi baru yang tidak memiliki berkas lama, perintah tersebut tidak memindahkan apa pun.
5. Buat superadmin dengan `php artisan admin:provision EMAIL --name="NAMA"`. Password diisi pada prompt tersembunyi. Jika mengganti password superadmin lama, gunakan `--reset-existing` secara eksplisit. Akun admin lokal yang sudah ada tidak diubah diam-diam; password default yang terdeteksi harus diganti sebelum go-live. Reset password admin operasional dari manajemen admin. Akun berpassword default ditolak saat login produksi dan akan menggagalkan deployment:check.
6. Atur permission tulis hanya pada direktori yang diperlukan (storage, bootstrap/cache, dan unggahan infografis). Permission public storage hanya untuk berkas yang memang ditujukan bagi umum. Sesuaikan upload_max_filesize/post_max_size, batas request web server dan timeout untuk batas aplikasi 5/10/50 MB.
7. Jalankan `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`, lalu `php artisan deployment:check`. Semua pemeriksaan harus OK. Jalankan queue worker jika terdapat job/notification queued lain yang diaktifkan, serta monitoring dan log rotation.
8. Uji melalui HTTPS di staging: tamu WOPPS dan magang/Buku Tamu; daftar/verifikasi/reset email; upload/revisi/keputusan; kalender; bukti tahap 6/7; sertifikat; tombol selesai; akun admin/superadmin; unduhan lintas akun yang ditolak; logout; pemulihan email/jaringan gagal. Uji email sampai inbox, akses Google Sheets, dan provider chatbot nyata dengan data uji yang diizinkan.
9. Uji restore backup dan rollback rilis. Tetapkan kebijakan retensi/penghapusan dokumen/chat serta pemberitahuan privasi yang disetujui dinas; perbaikan ini tidak mengarang masa retensi atau menghapus arsip resmi.

`deployment:check` pada mesin lokal masih gagal untuk environment/debug/domain HTTPS/cookie produksi, penanda development, dan password admin bawaan. Itu penanda bahwa konfigurasi lokal tidak boleh langsung dibuka sebagai produksi, bukan kegagalan build atau test aplikasi. Tidak ada deployment, push, atau perubahan proyek absensi pada pekerjaan ini.
