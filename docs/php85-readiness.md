# Pengujian PHP 8.5 SI-MELAYUR

Tanggal: 12 November 2026.

## Status

Runtime PHP **8.5.12 NTS x64** dari distribusi resmi PHP telah disiapkan terpisah untuk proyek ini di Windows. SHA-256 arsip cocok dengan publikasi resmi. Direktori runtime diabaikan Git dan tidak dimasukkan ke paket aplikasi.

- Pengujian akhir seluruh 307 tes lulus pada PHP 8.5, dengan 2.035 assertion, termasuk tes subfolder dan footer terbaru; menggunakan SQLite memory dan HTTP palsu.
- `composer check-platform-reqs --no-dev` dan `composer validate --no-check-publish` lulus pada PHP 8.5.
- Autentikasi Service Account dan pembacaan header Google Sheets juga diuji langsung pada PHP 8.5: berhasil, tanpa perubahan spreadsheet.
- Tidak ada pembaruan paket atau perubahan kode aplikasi yang diperlukan untuk meluluskan pemeriksaan tersebut.
- Batas Composer `^8.3` tetap mengizinkan PHP 8.5. Platform Composer 8.3 tetap dipertahankan untuk kompatibilitas dependensi dengan versi minimum; ini bukan pengaturan versi PHP server.

PHP bawaan Herd dan perintah `php` global tidak diubah. Gunakan pembungkus proyek di bawah untuk memastikan proses benar-benar memakai PHP 8.5.

## Menjalankan di PowerShell

Dari direktori proyek:

```powershell
.\scripts\php85.ps1 -v
.\scripts\php85.ps1 artisan --version
.\scripts\php85.ps1 artisan test --compact
```

Untuk menjalankan server pengembangan:

```powershell
.\scripts\php85.ps1 artisan serve --host=127.0.0.1 --port=8000 --no-reload
```

Hentikan server lama pada port yang sama sebelum memakai perintah tersebut. Untuk menjalankan dua server lokal sementara, pilih port berbeda, misalnya `--port=8015`.

Jika memakai instalasi PHP 8.5 di tempat lain, atur lokasi executable untuk sesi PowerShell tersebut:

```powershell
$env:SI_MELAYUR_PHP85_BINARY = 'C:\lokasi\php85\php.exe'
.\scripts\php85.ps1 -v
```

Laptop lain tetap harus menyediakan runtime PHP 8.5 dan ekstensi yang diperlukan; menyalin repository tidak ikut memasang PHP. Unduh hanya dari <https://www.php.net/downloads.php?os=windows>. Script memeriksa versi 8.5.x sebelum meneruskan argumen ke PHP.

## Batas validasi

Ini adalah pemeriksaan runtime lokal Windows. Ekstensi, konfigurasi, database, email, dan filesystem server hosting belum diuji. Pengujian hosting tetap diperlukan terpisah. Halaman cPanel sempat dibuka sampai login lalu ditutup sesuai arahan pengguna; tidak melakukan login atau mengubah server.

Untuk persiapan tahap hosting di kemudian hari, lihat `docs/deployment/cpanel-php85-readiness.md`.
