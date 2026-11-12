# Kesiapan integrasi lokal — 8 November 2026

Status ini memperbarui bagian kesiapan lokal pada `attendance-integration.md`. Tidak ada deployment, tunnel, push, atau perubahan repository absensi-dkp.

## Lingkungan

- Root: `C:\Users\ThinkPad T14s G3\Documents\KP-DKP`.
- Branch: `feature/portal-peserta`, perubahan kerja lain dipertahankan.
- Laravel environment: `local`; MySQL `chatbot_DKP` di `127.0.0.1`.
- Migrasi `2026_11_08_000000_add_attendance_active_to_participant_applications` berhasil diterapkan. Tidak menjalankan migrate:fresh, rollback/reset, maupun penghapusan tabel.
- Menggunakan server yang sudah berjalan dengan `php artisan serve --no-reload`, hanya bind loopback `127.0.0.1:8000`.
- Endpoint HTTP nyata: `POST http://127.0.0.1:8000/api/participants/verify`.

## Kredensial lokal

File rahasia: `C:\Users\ThinkPad T14s G3\Documents\KP-DKP\.codex-artifacts\attendance-local\integration.env`.

Variabel: `SI_MELAYUR_URL` dan `SI_MELAYUR_API_KEY`. Token acak khusus lokal 32 byte (64 karakter hex); nilainya tidak dicetak ke chat/log. Laravel `.env` hanya menerima digest SHA-256 dan izin `SI_MELAYUR_ATTENDANCE_CAN_VERIFY=true`. Slot token sebelumnya kosong. Config cache sudah dibersihkan dan HTTP nyata membuktikan token diterima.

File/direktori tersebut dan `.env` sudah diverifikasi diabaikan Git serta tidak berada di tracked files/staging. Cadangan `.env` lama ada di folder rahasia yang sama sebagai `laravel-env.before`; perlakukan sebagai rahasia juga.

Pengguna dapat membuka file kredensial dengan editor lokal; jangan paste isi token ke chat, Git, Flutter, atau screenshot. Chat proyek presensi dapat membaca file tersebut di lingkungan lokal dan menaruh nilainya pada konfigurasi rahasia Functions emulator. File ini tidak disalin ke absensi-dkp dalam pekerjaan ini.

Untuk rotasi/pencabutan, ikuti `attendance-integration.md`; jika `.env` berubah, restart `artisan serve --no-reload` agar tidak memakai environment lama.

## Data uji

Semua periode uji: **1–30 November 2026**, mencakup tanggal pengujian 8 November (Asia/Jakarta). Tanggal mulai dan akhir inklusif.

| Kasus | ID akun | ID pengajuan | Hasil |
|---|---:|---|---|
| Sintetis aktif | 6 | 7 | accepted, is_active true |
| Sintetis nonaktif | 7 | 8 | accepted, is_active false |
| Sintetis ambigu | 8 | 9, 10 | 409 AMBIGUOUS_APPLICATION |
| Sintetis belum ditetapkan | 9 | 11 | 409 PARTICIPANT_DATA_INCOMPLETE |
| Akun Google uji dikonfirmasi pengguna | 1 | 4 | accepted, is_active true |

Akun sintetis memakai nama `SIMULASI Presensi Lokal ...`, institusi `SIMULASI - Bukan peserta resmi`, serta domain `example.test`. Dibuat dengan Participant factory, password acak tidak dibagikan, tidak ditandai verifikasi email dan tidak mengirim email. Akun ini untuk API langsung, bukan login Google nyata.

Email Google yang dikonfirmasi pengguna disamarkan di laporan: `s***@gmail.com`. Sebelumnya ditemukan pengajuan #4 periode 21–24 September 2026. Pengguna mengonfirmasi bahwa akun tersebut khusus uji dan boleh disesuaikan. Hanya periode, attendance_active, dan completed_at akun ini yang disiapkan; keputusan accepted dipertahankan. Data awal dicadangkan di folder rahasia `google-account-before-*.json`. Identitas akun tidak diganti dan peserta lain tidak diubah.

Manifest identitas lengkap lokal: `C:\Users\ThinkPad T14s G3\Documents\KP-DKP\.codex-artifacts\attendance-local\fixtures.json`. Jangan commit manifest/backup; berisi data koordinasi lokal.

## Hasil HTTP nyata

Client pengujian benar-benar melakukan request HTTP ke server Laravel yang berjalan, membaca token dari file lokal tanpa mencetaknya. Ini belum merupakan request dari Firebase emulator.

| Kasus | HTTP | Hasil verifikasi |
|---|---:|---|
| Tanpa token | 401 | UNAUTHENTICATED |
| Token salah | 401 | UNAUTHENTICATED |
| Email tidak valid | 422 | INVALID_EMAIL |
| Email tidak ditemukan | 404 | PARTICIPANT_NOT_FOUND |
| Sintetis aktif | 200 | Kontrak tujuh field; id string; is_active boolean true |
| Sintetis nonaktif | 200 | is_active boolean false |
| Pengajuan ganda | 409 | AMBIGUOUS_APPLICATION |
| Aktivitas belum ditetapkan | 409 | PARTICIPANT_DATA_INCOMPLETE |
| Akun Google uji | 200 | Pengajuan #4, accepted, true, periode November |

**9/9 skenario berhasil.** Snapshot tabel participants dan participant_applications sebelum/sesudah HTTP identik: endpoint tidak mengubah data. Tidak ada field sensitif tambahan. HTTP 200 masih memerlukan pemeriksaan accepted + is_active + rentang tanggal oleh Cloud Functions.

Contoh respons valid dari peserta sintetis:

```json
{
  "participant": {
    "id": "7",
    "email": "attendance.local.active@example.test",
    "full_name": "SIMULASI Presensi Lokal Active",
    "status": "accepted",
    "is_active": true,
    "start_date": "2026-11-01",
    "end_date": "2026-11-30"
  }
}
```

Laporan lokal tersamarkan: `.codex-artifacts/attendance-local/http-results.json`. Ulangi dengan `php scripts/attendance-local-smoke.php`. Script tidak menyiapkan/mengubah data dan akan melaporkan kegagalan jika server/fixture tidak sesuai.

## Pengujian otomatis dan regresi

Perintah yang dijalankan:

```sh
php vendor/bin/phpunit tests/Feature/AttendanceVerificationApiTest.php tests/Feature/ParticipantAuthenticationTest.php tests/Feature/GuestbookCheckinTest.php tests/Feature/ReplyLetterDeliveryTest.php
```

Hasil: **45 test, 329 assertion; 44 lulus, 1 gagal**. Semua 12 test API lolos. Pengujian memakai SQLite memory, bukan database lokal MySQL.

Satu kegagalan tetap pada `ReplyLetterDeliveryTest:112`, yang menuntut teks “Kalender kegiatan magang” di respons dashboard. Inspeksi view membuktikan kalender berada pada `components/peserta/activity-page.blade.php`, yang hanya di-include saat route `peserta.activities`; dashboard memiliki tautan ke halaman itu. Kegagalan ini adalah ketidaksesuaian ekspektasi UI setelah pemisahan kalender sebelumnya, bukan autentikasi/kontrak API. Test dan UI tidak diubah sekadar untuk meluluskan suite.

Laporan test lengkap tersimpan lokal di `.codex-artifacts/attendance-local/automated-tests.txt`. Seluruh suite proyek belum dijalankan.

## Perubahan pekerjaan ini

- Database lokal: migrasi satu kolom, empat akun sintetis/lima pengajuan baru, penyesuaian pengajuan #4 yang disetujui pengguna sebagai akun uji.
- `.env` lokal: digest token dan izin endpoint; tidak di-commit.
- `.codex-artifacts/attendance-local/`: file rahasia, manifest, backup, laporan (semua ignored).
- `scripts/attendance-local-smoke.php`: client HTTP ulang tanpa token tertanam.
- Dokumentasi readiness ini dan tautan status pada dokumentasi integrasi.
- Skrip penyiapan sementara telah dihapus agar tidak dijalankan ulang tanpa sengaja.

## Ringkasan untuk Chat absensi-dkp

Root Laravel: `C:\Users\ThinkPad T14s G3\Documents\KP-DKP`.
Endpoint: `POST http://127.0.0.1:8000/api/participants/verify`.
Header: `Authorization: Bearer <token>`, `Content-Type: application/json`, `Accept: application/json`.
Body: `{"email":"<email Google terverifikasi>"}`.

Sumber konfigurasi rahasia lokal: `C:\Users\ThinkPad T14s G3\Documents\KP-DKP\.codex-artifacts\attendance-local\integration.env`. Variabel `SI_MELAYUR_URL` dan `SI_MELAYUR_API_KEY`; baca lokal, jangan tampilkan nilai token atau taruh di Flutter.

Akun Google uji sudah dikonfirmasi pengguna, email `s***@gmail.com`, ID akun 1, ID pengajuan **"4"**, accepted, aktif, periode **2026-11-01–2026-11-30**. Email lengkap ada pada `fixtures.json` di direktori rahasia tersebut, entri `google_test`. Peserta sintetis aktif memakai `attendance.local.active@example.test`, pengajuan **"7"**.

Respons contoh: `{"participant":{"id":"7","email":"attendance.local.active@example.test","full_name":"SIMULASI Presensi Lokal Active","status":"accepted","is_active":true,"start_date":"2026-11-01","end_date":"2026-11-30"}}`.

Migrasi attendance_active sudah dijalankan. Server loopback berjalan menggunakan artisan serve --no-reload; 9/9 request HTTP nyata berhasil. Test otomatis: API 12/12 lulus, total regresi+API 44/45 lulus; satu kegagalan UI kalender lama tidak diubah. Tidak ada deployment/tunnel/perubahan repository absensi-dkp.

Langkah selanjutnya di proyek presensi: masukkan konfigurasi tersebut ke secret/env lokal Functions emulator sesuai implementasinya; jalankan emulator pada laptop yang sama; login dengan akun Google uji; verifikasi ID token/email; panggil Laravel dari Functions dan pastikan id string serta is_active boolean diterima adapter. Periksa status accepted, aktif, dan periode inklusif Asia/Jakarta. Tangani 401/403/404/409/422/429/5xx secara fail-closed. Uji Firebase/Flutter end-to-end belum dilakukan dari chat SI-MELAYUR ini. Periode fixture berakhir 30 November 2026; sesudahnya hasil tidak layak presensi sampai pengelola menyiapkan periode uji baru.
