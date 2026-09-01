# Verifikasi Buku Tamu Google Sheets

Integrasi read-only untuk membuka chatbot informasi umum tanpa login. Bukan verifikasi kepemilikan nomor dan tidak memberi akses data peserta.

## Konfigurasi

- `GOOGLE_SERVICE_ACCOUNT_PATH`: file JSON privat yang tidak dilacak Git.
- `GOOGLE_SHEETS_SPREADSHEET_ID`: ID spreadsheet respons yang telah dibagikan Viewer ke service account.
- `GOOGLE_SHEETS_RESPONSE_RANGE`: nama tab dan rentang kolom mulai A, contoh `"'Form Responses 1'!A:Z"`.
- `GOOGLE_SHEETS_PHONE_COLUMN`: judul kolom unik, contoh `Nomor Telepon`.
- `GOOGLE_SHEETS_TIMESTAMP_COLUMN`: judul kolom waktu, default `Timestamp`.
- `GOOGLE_SHEETS_TIMEZONE`: zona waktu spreadsheet, default `Asia/Jakarta`.
- `GOOGLE_SHEETS_TIMESTAMP_FORMAT`: format jika timestamp disimpan sebagai teks, default `d/m/Y H:i:s`. Tanggal asli Sheets dibaca sebagai serial numerik agar tidak ambigu karena locale.
- `GOOGLE_GUESTBOOK_FORM_URL`: URL formulir yang terhubung ke spreadsheet tersebut. Tidak menggunakan fallback Form DKP agar data testing tidak tercampur.

Jalankan `php artisan config:clear` setelah mengubah konfigurasi. Composer memasang library resmi `google/auth`.

## Alur

1. Pengunjung memasukkan nomor, lalu menekan Mulai Verifikasi.
2. Server menyimpan HMAC nomor, empat digit terakhir, waktu mulai dan kedaluwarsa (30 menit) dalam sesi. Nomor lengkap tidak disimpan dalam sesi verifikasi.
3. Pengunjung membuka Form dan mengirim respons baru dengan nomor sama.
4. Tombol Periksa Pengisian membaca header serta hanya dua kolom: nomor dan timestamp. Tidak ada penulisan Sheet.
5. Nomor dinormalisasi (08 / 628 / +628) dan timestamp harus berada antara waktu mulai dan sekarang.
6. Setelah cocok, sesi mendapat akses chatbot maksimal 24 jam, atau sampai sesi berakhir. Cookie pengakuan lama tidak diterima.

Google gagal, header hilang/duplikat, data tidak cocok, atau sesi kedaluwarsa tidak membuka chatbot. Endpoint pemeriksaan dibatasi 5 permintaan per menit per IP; pembacaan Sheets juga dibatasi secara global. Token Google di-cache pada penyimpanan cache server yang harus privat. Log hanya mencatat tipe kesalahan, tidak respons Google atau nomor.

## Pengujian

`php artisan test tests/Feature/GuestbookCheckinTest.php tests/Feature/GoogleGuestbookReaderTest.php tests/Feature/ChatbotPageTest.php`

Tes otomatis menggunakan HTTP palsu dan nomor dummy. Untuk tes manual, gunakan Google Form testing sendiri; jangan mengirim respons palsu ke formulir resmi DKP. Isi nomor di portal terlebih dahulu, baru kirim Form. Data lama memang tidak lolos. Uji juga nomor lain, sesi kedaluwarsa, akses API tanpa sesi terverifikasi, dan cookie lama.

Nomor yang sama pada dua browser dapat cocok dengan respons yang sama jika jendela waktunya beririsan. Ini batasan pencocokan tanpa OTP atau kode kunjungan unik; bukan bukti identitas. Integrasi ini tidak boleh dipakai untuk otorisasi data pribadi.
