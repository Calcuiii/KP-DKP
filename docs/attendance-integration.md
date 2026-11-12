# Integrasi presensi SI-MELAYUR

**Pembaruan kesiapan lokal:** migrasi, kredensial lokal dan data uji kini sudah disiapkan; lihat [laporan kesiapan lokal](attendance-local-readiness.md). Catatan "belum dijalankan" di bawah merekam checkpoint implementasi awal, sebelum penyiapan lokal tersebut.

Implementasi 8 November 2026, Laravel 12 / PHP 8.3+, di repository KP-DKP pada branch pengembangan `feature/portal-peserta`. Repository `absensi-dkp` tidak diubah. API ini hanya untuk backend Cloud Functions, bukan Flutter langsung.

## Temuan dan sumber data

Inspeksi kode, migration, dan daftar kolom database lokal mengonfirmasi:

| Kontrak | Sumber / aturan |
|---|---|
| `id` | `participant_applications.id`, diubah menjadi string. ID **pengajuan**, bukan ID akun; jangan gunakan untuk identitas Firebase UID. |
| `email` | `participants.email`, melalui FK `participant_applications.participant_id`. |
| `full_name` | `participants.name`. |
| `status` | `participant_applications.decision`: `accepted`, `rejected`; null/kosong menjadi `pending`. Nilai lain ditolak dengan 409. |
| `is_active` | `attendance_active === true` DAN keputusan `accepted` DAN `completed_at === null`. Tidak bergantung tanggal saat ini. |
| `start_date` | `official_started_at`, dikonversi Asia/Jakarta, `YYYY-MM-DD`. |
| `end_date` | `official_ended_at`, dikonversi Asia/Jakarta, `YYYY-MM-DD`. |

`status` internal pengajuan adalah status proses (persiapan, pemeriksaan, selesai, dll.), sehingga tidak dipakai sebagai keputusan penerimaan. Persetujuan surat permohonan bukan keputusan penerimaan. Controller Surat Balasan menulis keputusan resmi `accepted`/`rejected`. Saat menolak, controller tersebut mengosongkan tanggal resmi.

Tidak ada field keaktifan administratif sebelumnya. Sesuai keputusan pemilik proyek, ditambahkan `attendance_active` nullable per pengajuan: null = belum ditetapkan, true = aktif administratif, false = dinonaktifkan. Migration tidak mengaktifkan data lama. Field tidak dimasukkan ke `$fillable`, sehingga tidak dapat ditetapkan melalui pengisian massal form peserta. Pendaftaran, seleksi, dan keputusan dinas tidak diubah.

Autentikasi lama memakai session guard `web`/`peserta`; route chatbot di `web.php` bukan API antarlayanan. Tidak ditemukan Sanctum maupun endpoint verifikasi peserta yang sesuai. Endpoint baru memakai grup API stateless tanpa CSRF/session; tidak mengubah middleware chatbot.

## Kontrak

`POST /api/participants/verify`

```http
Authorization: Bearer <token-integrasi-server>
Content-Type: application/json
Accept: application/json
```

```json
{"email":"peserta@example.com"}
```

Email di-trim dan di-lowercase. Pencarian tidak membedakan huruf besar/kecil untuk mendukung data pendaftaran lama, yang tidak menormalisasi case secara eksplisit. Titik dan `+alias` tidak dihapus. Email bukan parameter URL dan tidak dicatat oleh kode integrasi.

Contoh sukses berikut hanya contoh, bukan data yang dibuat pada database:

```json
{
  "participant": {
    "id": "123",
    "email": "peserta@example.com",
    "full_name": "Contoh Peserta",
    "status": "accepted",
    "is_active": true,
    "start_date": "2026-10-01",
    "end_date": "2026-12-31"
  }
}
```

Hanya tujuh field tersebut dikembalikan. Tidak ada password, telepon, institusi, identitas pemerintah, dokumen, peran admin, atau token. `Cache-Control: no-store` ditambahkan.

HTTP 200 berarti data ditemukan dan lengkap, **bukan izin presensi**. Backend presensi wajib memeriksa:

1. `status === "accepted"`.
2. `is_active === true`.
3. Tanggal hari ini dalam Asia/Jakarta berada pada `start_date <= today <= end_date` (inklusif).

Sebelum mulai/sesudah berakhir tetap HTTP 200 dengan tanggal sebenarnya dan keaktifan administratif yang tidak berubah hanya karena tanggal. Dengan demikian alasan “belum mulai”, “berakhir”, dan “dinonaktifkan” bisa dibedakan. `completed_at` selalu menonaktifkan hasil presensi walaupun flag administratif masih true. `pending`/`rejected` dengan data lengkap mendapat 200, `is_active: false`; jika periodenya kosong mendapat 409. Jangan mengubah tanggal pengajuan ditolak hanya untuk mendapatkan 200.

## Pemilihan pengajuan

- Cari akun berdasarkan email, maksimum dua hasil untuk mendeteksi ambiguitas case.
- Cari hanya `service_type = magang_pkl` (mencakup Magang/KP/PKL), melalui relasi akun tersebut. WOPPS tidak masuk layanan presensi ini.
- Nol akun: 404. Akun ada tetapi tidak memiliki pengajuan magang: 404 dengan kode berbeda.
- Lebih dari satu akun cocok: 409 `AMBIGUOUS_PARTICIPANT`.
- Lebih dari satu pengajuan magang: 409 `AMBIGUOUS_APPLICATION`, walaupun hanya satu yang diterima/berada dalam periode saat ini.
- Jangan memilih latest/first atau menggunakan pengajuan aktif dari session browser. Pemilihan session hanya preferensi portal, bukan aturan resmi presensi.
- Solusi masa depan memerlukan aturan resmi atau perluasan kontrak dengan ID pengajuan yang divalidasi. Tidak diimplementasikan secara spekulatif.

## Kesalahan

```json
{"error":{"code":"PARTICIPANT_DATA_INCOMPLETE","message":"Official identity, activity decision or period is incomplete."}}
```

| HTTP | `error.code` | Tindakan caller |
|---|---|---|
| 401 | UNAUTHENTICATED | Token tidak ada/salah/dicabut. Jangan coba presensi. |
| 403 | FORBIDDEN | Token valid tetapi izin verifikasi dimatikan. |
| 403 | HTTPS_REQUIRED | Production wajib HTTPS. |
| 404 | PARTICIPANT_NOT_FOUND | Email tidak ditemukan. |
| 404 | INTERNSHIP_APPLICATION_NOT_FOUND | Tidak ada pengajuan Magang/KP/PKL. |
| 409 | AMBIGUOUS_PARTICIPANT | Ada lebih dari satu akun cocok. |
| 409 | AMBIGUOUS_APPLICATION | Ada beberapa pengajuan magang. |
| 409 | PARTICIPANT_DATA_INCOMPLETE | Nama, keputusan keaktifan, atau tanggal resmi belum lengkap. |
| 409 | INVALID_PERIOD | Tanggal selesai sebelum tanggal mulai. |
| 409 | UNSUPPORTED_DECISION | Keputusan nonstandar; perlu koreksi data resmi. |
| 422 | INVALID_EMAIL | Email wajib, string, format RFC, panjang <=255. |
| 429 | RATE_LIMITED | Tunggu `Retry-After`, jangan mengizinkan presensi dari hasil kosong. |
| 405 | METHOD_NOT_ALLOWED | Gunakan POST. |
| 500 | SERVICE_UNAVAILABLE | Error internal; tidak ada stack trace/SQL/token, bahkan ketika debug aktif. |

Semua respons non-200 tidak berisi objek `participant`. Field keaktifan belum ditetapkan dan periode hilang tidak diganti nilai default. Error endpoint tidak dilaporkan ke logger exception Laravel agar exception SQL tidak membocorkan email/bindings. Gunakan metrik status HTTP untuk observabilitas; pastikan reverse proxy/APM tidak merekam Authorization/body request. Tidak ada email, pemanggilan Firebase, atau penulisan presensi dari endpoint.

## Kredensial dan rate limit

Konfigurasi Laravel (`.env` server atau secret manager):

```dotenv
SI_MELAYUR_ATTENDANCE_TOKEN_HASH=<sha256-token-baru>
SI_MELAYUR_ATTENDANCE_PREVIOUS_TOKEN_HASH=
SI_MELAYUR_ATTENDANCE_CAN_VERIFY=true
```

Default izin false, kedua hash kosong: akses ditolak. Token khusus integrasi ini hanya berlaku di endpoint verifikasi, tidak memberi akses admin atau endpoint lain. Dua slot token memakai izin yang sama untuk transisi rotasi, bukan dua role.

1. Buat token acak kriptografis minimal 32 byte (contoh operator lokal: `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`). Jangan paste output ke chat, Git, log CI, Flutter, atau command history berisi token literal.
2. Simpan token mentah hanya di secret manager backend presensi sebagai `SI_MELAYUR_API_KEY`. Hitung SHA-256 melalui utilitas lokal tepercaya, simpan digest hex lowercase 64 karakter pada konfigurasi Laravel. Token contoh di test bukan kredensial nyata.
3. Set `SI_MELAYUR_ATTENDANCE_CAN_VERIFY=true`, lalu `php artisan config:cache` pada server. Untuk pengembangan `php artisan config:clear`.
4. Rotasi: pindahkan hash aktif ke `PREVIOUS_TOKEN_HASH`, pasang hash baru, refresh config; ganti secret backend; setelah semua instance beralih, kosongkan hash lama dan refresh config lagi.
5. Cabut satu token: hapus hash slotnya. Cabut semua: kosongkan kedua hash. Matikan kemampuan baca tanpa menghapus token: `CAN_VERIFY=false` (403).

Rate limit: 60 permintaan/menit per IP pemanggil melalui middleware throttle Laravel, termasuk percobaan autentikasi yang gagal. Backend yang memakai satu IP egress berbagi kuota. Gunakan cache bersama seperti Redis pada deployment multi-instance; store lokal tidak memberi batas global. Konfigurasikan trusted proxies hanya untuk proxy resmi. Tidak ada CORS publik yang dibutuhkan karena Flutter tidak memanggil endpoint ini langsung. Production menolak HTTP; TLS termination harus meneruskan scheme dengan trusted proxy yang benar.

## Penetapan keaktifan oleh pengelola

Migration baru harus dijalankan sebelum fitur dipakai. Tidak ada mass update, dan tidak ada perubahan akun lokal/produksi selama pengujian implementasi.

```sh
php artisan migrate --path=database/migrations/2026_11_08_000000_add_attendance_active_to_participant_applications.php
php artisan attendance:set-active <id-pengajuan> active --operator=<id-superadmin>
php artisan attendance:set-active <id-pengajuan> inactive --operator=<id-superadmin>
php artisan attendance:set-active <id-pengajuan> unset --operator=<id-superadmin>
```

Perintah memerlukan superadmin berstatus Aktif untuk atribusi audit dan konfirmasi interaktif; `--force` hanya untuk operator server yang telah memastikan targetnya. Akses shell server adalah otorisasi perintah ini—ID operator bukan pengganti autentikasi. Aktivasi ditolak jika belum accepted, tanggal belum valid, atau sudah selesai. Perintah mengubah field keaktifan dan menulis activity log atomik, bukan mengubah keputusan penerimaan. Endpoint verifikasi sendiri tetap baca saja (selain counter rate limit pada cache).

Belum ada UI admin untuk flag baru. Pengelola menggunakan perintah server; jangan membuka perintah ini sebagai API publik. Perubahan tanggal/keputusan oleh dinas tidak otomatis mengisi flag null menjadi true.

## Lokal dan cloud

Lokal Laravel:

```sh
php artisan serve --host=127.0.0.1 --port=8000
```

Backend Cloud Functions emulator yang berjalan di laptop yang sama memakai:

```dotenv
SI_MELAYUR_URL=http://127.0.0.1:8000/api/participants/verify
SI_MELAYUR_API_KEY=<token-integrasi-server>
```

Contoh cURL (PowerShell Windows gunakan `curl.exe`):

```sh
curl -X POST 'http://127.0.0.1:8000/api/participants/verify' \
  -H 'Authorization: Bearer <token-integrasi-server>' \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  --data '{"email":"peserta@example.com"}'
```

Placeholder harus diganti secara aman saat operator menjalankan; tidak ada token nyata dibuat dalam pekerjaan ini. Test otomatis menggunakan fixture SQLite, tidak memakai akun Tazkiyah/Budi atau database MySQL lokal.

Lokasi runtime `absensi-dkp` belum diperiksa. Jika Functions emulator berada dalam container Docker Desktop, loopback container bukan laptop; konfigurasi host yang sesuai (umumnya `host.docker.internal`) perlu diverifikasi. Android emulator/perangkat Flutter tetap memanggil Functions emulator, **bukan Laravel**; alamat perangkat untuk Functions diatur di proyek mobile. Jangan menaruh API key SI-MELAYUR di aplikasi mobile.

Cloud Functions yang dideploy memerlukan alamat Laravel HTTPS publik/privat yang dapat dijangkau infrastrukturnya, misalnya `https://<domain-si-melayur>/api/participants/verify`. Domain deployment belum diketahui/ditetapkan. `localhost` cloud bukan laptop. Tidak dibuat tunnel, perubahan firewall, hosting, atau deployment. Sebelum deploy: review migrasi, tentukan domain/TLS, trusted proxies, cache bersama, provisioning flag resmi, secret manager, timeout/backoff dan pemantauan status HTTP.

## Pengujian dan batasan

File khusus checkpoint ini:

- `routes/api.php`: endpoint stateless.
- `bootstrap/app.php`: registrasi API dan sanitasi exception khusus endpoint.
- `app/Http/Controllers/Api/VerifyAttendanceParticipantController.php`: validasi, pemilihan pengajuan, pemetaan respons.
- `app/Http/Middleware/AuthenticateAttendanceService.php`: autentikasi, izin, HTTPS production.
- `config/attendance.php` dan `.env.example`: konfigurasi digest/rotasi/izin.
- `database/migrations/2026_11_08_000000_add_attendance_active_to_participant_applications.php`: field nullable baru.
- `app/Models/ParticipantApplication.php`: cast boolean field baru (perubahan lama lain di file tetap dipertahankan).
- `app/Console/Commands/SetAttendanceActivity.php`: pengelolaan flag dan audit administratif.
- `tests/Feature/AttendanceVerificationApiTest.php`: pengujian integrasi API.
- `docs/attendance-integration.md`: kontrak dan panduan ini.

Test: `php artisan test --filter=AttendanceVerificationApiTest` atau `php vendor/bin/phpunit tests/Feature/AttendanceVerificationApiTest.php`.

Mencakup kredensial hilang/salah/dicabut/rotasi/izin, email, 404, kontrak minimal, normalisasi, status pending/rejected/accepted, nonaktif/selesai, batas tanggal inklusif (tanggal diserahkan ke caller), ambiguitas akun/pengajuan, data hilang/tidak valid, baca saja, 429, error sanitization, HTTPS production, dan perintah keaktifan dengan audit.

Hasil aktual 8 November 2026:

- `AttendanceVerificationApiTest`: **12 test lulus, 144 assertion**.
- Regresi `ParticipantAuthenticationTest`, `GuestbookCheckinTest`, `ReplyLetterDeliveryTest`: **32/33 test lulus**, total 185 assertion. Satu gagal pada `ReplyLetterDeliveryTest:112`: mengharapkan teks “Kalender kegiatan magang” pada dashboard, sedangkan kalender sudah dipisah ke halaman kegiatan pada revisi UI sebelumnya. Tidak diubah dalam checkpoint API ini.
- Pint pada file API/command/config/test/bootstrap/migration berhasil.
- `git diff --check` berhasil; route list menemukan tepat satu endpoint POST `api/participants/verify`.
- Tidak menjalankan seluruh suite, tidak menguji koneksi Firebase/deployment, tidak mengirim email atau mengubah database MySQL peserta.

Batasan: kebijakan beberapa pengajuan belum ditetapkan (409); pengajuan belum diterima/ditolak umumnya tidak punya tanggal sehingga 409; Firebase token validation dan pemeriksaan tanggal inklusif di Cloud Functions belum diuji karena repository terpisah tidak disentuh; tidak ada smoke test produksi; flag administratif lokal belum diisi dan migration belum dijalankan pada database lokal. Pengujian sementara menjalankan migration pada SQLite memory.

## Ringkasan untuk Chat Proyek Presensi

- SI-MELAYUR menyediakan `POST http://127.0.0.1:8000/api/participants/verify` untuk backend lokal pada laptop yang sama. URL deployment HTTPS belum tersedia; placeholder `https://<domain-si-melayur>/api/participants/verify`.
- Header: `Authorization: Bearer <token-integrasi-server>`, `Content-Type: application/json`, `Accept: application/json`. Gunakan `SI_MELAYUR_URL` dan `SI_MELAYUR_API_KEY` di backend/secret manager saja.
- Request: `{"email":"peserta@example.com"}`. Ambil email dari Firebase ID token yang telah diverifikasi, jangan dari klaim body mobile; pastikan akun Google/email terverifikasi.
- Respons 200: `{"participant":{"id":"123","email":"peserta@example.com","full_name":"Contoh Peserta","status":"accepted","is_active":true,"start_date":"2026-10-01","end_date":"2026-12-31"}}`.
- `id` adalah ID pengajuan stabil. `accepted` hanya keputusan resmi diterima; `rejected` ditolak; `pending` belum diputuskan. `is_active` memerlukan keputusan administratif eksplisit, diterima, dan belum selesai. Presensi hanya jika accepted + active + tanggal hari ini Asia/Jakarta berada dalam periode inklusif. Di luar periode masih 200; caller menentukan alasan penolakan.
- Error: `{"error":{"code":"...","message":"..."}}`. 401 UNAUTHENTICATED, 403 FORBIDDEN/HTTPS_REQUIRED, 404 PARTICIPANT_NOT_FOUND/INTERNSHIP_APPLICATION_NOT_FOUND, 422 INVALID_EMAIL, 409 AMBIGUOUS_PARTICIPANT/AMBIGUOUS_APPLICATION/PARTICIPANT_DATA_INCOMPLETE/INVALID_PERIOD/UNSUPPORTED_DECISION, 429 RATE_LIMITED, 500 SERVICE_UNAVAILABLE. Semua harus gagal tertutup, bukan otomatis mengizinkan presensi.
- Hanya pengajuan magang_pkl dipakai. Lebih dari satu pengajuan magang selalu 409, bukan latest atau session portal; WOPPS diabaikan. Jangan pilih record sendiri dari hasil kesalahan.
- Adapter: bentuk objek participant dipertahankan; simpan id sebagai string pengajuan; tangani error non-200 sebelum membaca participant; terapkan tanggal inklusif Asia/Jakarta, timeout dan retry terbatas untuk 429/5xx; jangan gunakan respons lama sebagai izin ketika verifikasi gagal. Tidak ada penetapan role admin presensi dari email request.
- Laravel perlu migration kolom attendance_active, penetapan resmi flag oleh operator, dan konfigurasi digest token/izin. Tidak ada token nyata atau URL cloud yang dibagikan. Test API 12/12 lulus (144 assertion); regresi 32/33 lulus dengan satu ekspektasi UI kalender lama gagal. Integrasi langsung dengan Firebase/Flutter dan deployment masih tertunda.
