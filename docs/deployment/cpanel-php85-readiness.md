# Persiapan cPanel SI-MELAYUR — 12 November 2026

## Informasi dari pengelola DKP

- PHP server: **8.5** (informasi terbaru, menggantikan konfirmasi 8.3 sebelumnya).
- Database: MySQL **5.7.44**, belum diverifikasi ulang di hosting.
- SSH tidak tersedia. Pemasangan dilakukan melalui cPanel.
- Folder publik aplikasi: `public_html/si-melayur`.
- URL aplikasi: `https://ppid.dkp.jatimprov.go.id/si-melayur`.
- Pengelola mengizinkan pembuatan database dan akun database khusus aplikasi.
- Terdapat aplikasi lain pada hosting ini; pemasangan harus dibatasi ke direktori dan database SI-MELAYUR.

## Hasil pemeriksaan lokal

PHP Windows **8.5.12 NTS x64** disiapkan terpisah di direktori lokal yang diabaikan Git. Instalasi Herd/PHP 8.4 yang sudah digunakan tidak diganti.

Sumber resmi: <https://www.php.net/downloads.php?os=windows>.
Arsip `php-8.5.12-1-nts-Win32-vs17-x64.zip` diverifikasi dengan SHA-256:

```text
2a36a2edcb7a5b9252f638fd5e4cf84d7f6e066092654220dc0b63e5cf4e9723
```

Pemeriksaan yang sudah dijalankan:

| Pemeriksaan | Hasil |
| --- | --- |
| Seluruh tes lama pada PHP 8.5 | 304 lulus, 2.019 assertion |
| Tes tambahan subfolder `/si-melayur` | 3 lulus, 15 assertion |
| Pengujian akhir seluruh fitur, termasuk subfolder dan footer terbaru | 307 lulus, 2.035 assertion |
| Composer `check-platform-reqs --no-dev` pada PHP 8.5 | Lulus |
| Composer `validate --no-check-publish` | Lulus |
| Build Vite dengan `ASSET_URL=/si-melayur` | Berhasil, hasil terpisah dari build development |
| Autentikasi Google dan pembacaan header spreadsheet pada PHP 8.5 | Berhasil, tanpa perubahan data spreadsheet |

`SubdirectoryHostingTest` mensimulasikan front controller pada `/si-melayur/index.php`, memeriksa URL gambar dan API chatbot, action formulir pop-up Buku Tamu, redirect login peserta, dan formulir pilihan layanan. Tes menggunakan database SQLite memory serta HTTP palsu.

Persyaratan `php: ^8.3` di Composer tetap valid untuk PHP 8.5. Platform resolusi 8.3 dipertahankan agar dependensi tetap kompatibel dengan versi minimum yang didukung; bukan pembatas runtime ke PHP 8.3. Tidak ada perubahan framework atau pembaruan paket yang diperlukan berdasarkan pemeriksaan ini.

Hasil di atas belum membuktikan kecocokan seluruh ekstensi, filesystem, jaringan, dan MySQL pada hosting. Belum ada file atau database dinas yang diubah maupun aplikasi yang dipublikasikan.

## Konfigurasi rilis subfolder

Gunakan `.env.production.example` sebagai dasar, lalu isi konfigurasi privat di server. Nilai khusus untuk pemasangan ini:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ppid.dkp.jatimprov.go.id/si-melayur
ASSET_URL=https://ppid.dkp.jatimprov.go.id/si-melayur
SESSION_COOKIE=simelayur_session
SESSION_PATH=/si-melayur
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
```

Untuk build di komputer, set `ASSET_URL=/si-melayur` pada proses build lalu jalankan `npm run build`. Sertakan `public/build` hasil build ke rilis; jangan sertakan `public/hot` atau penanda development. Pertahankan `public/hot` development lokal.

File JSON Google dan `.env` dipasang secara privat di server, tidak ditambahkan ke GitHub. Kunci untuk server dinas nantinya harus dikelola pihak dinas. AI eksternal tetap nonaktif sampai akun dinas Free Plan disiapkan dan dikonfirmasi.

## Pemeriksaan berikutnya melalui cPanel

1. Pengguna masuk sendiri melalui halaman cPanel. Jangan menyimpan password cPanel di repository, laporan, atau chat.
2. Periksa bahwa domain tujuan benar-benar menjalankan PHP 8.5 beserta ekstensi yang dibutuhkan. Pastikan aplikasi lain tetap memakai pengaturan yang sesuai.
3. Konfirmasikan lokasi direktori privat yang bisa digunakan di luar `public_html`. Rencana: sumber Laravel privat terpisah dari `public_html/si-melayur`, yang hanya memuat front controller dan aset publik.
4. Bila memakai susunan terpisah tersebut, entry point rilis harus mengarah ke lokasi sumber privat yang benar dan menetapkan `usePublicPath()` ke direktori publik rilis. Jangan mengubah entry point development atau menerka nama home directory hosting sebelum diperiksa.
5. Periksa Cron Jobs, lokasi PHP CLI 8.5, dan phpMyAdmin. Tanpa SSH, perlu cara yang tersedia untuk menjalankan migrasi/optimisasi dan menyiapkan superadmin secara aman. Jangan membuat endpoint Artisan terbuka di website.
6. Siapkan database dan user khusus dengan hak hanya pada database SI-MELAYUR. Uji migrasi pada database kosong di hosting. Jangan mengimpor akun, dokumen peserta, dan data uji development.
7. Periksa SMTP/email resmi, HTTPS, batas unggah, izin tulis, akses HTTPS keluar ke Google, dan penyimpanan dokumen privat.
8. Setelah hasil pemeriksaan tersedia, susun paket rilis dan langkah pemasangan yang sesuai kondisi aktual. Tinjau sebelum mengubah hosting.
9. Setelah pemasangan, jalankan pemeriksaan kesiapan produksi dan uji seluruh alur melalui URL subfolder yang sebenarnya. Pemeriksaan lokal bukan pengganti pengujian hosting.

Panduan hardening sebelumnya: `docs/hosting-hardening-results-2026-11-09.md`.
Panduan akun AI dinas: `docs/chatbot-free-plan-handover.md`.
