# Audit kesiapan hosting SI-MELAYUR — 9 November 2026

**Keputusan: belum layak dibuka ke publik.** Build berhasil, tetapi masih ada masalah keamanan dependensi, privasi dokumen, dan alur WOPPS yang terputus. Ini audit terhadap working tree saat ini, termasuk perubahan yang belum di-commit; bukan hanya isi GitHub main. Integrasi presensi tidak dikerjakan pada audit ini.

## Cara pemeriksaan dan batas bukti

- Membaca rute, middleware, autentikasi dua guard, controller, validasi unggahan, penyimpanan, notifikasi, chatbot/retrieval, konfigurasi, seeder, dan test.
- Menjalankan seluruh PHPUnit: **262 test, 1.558 assertion; 233 lulus, 27 failure, 2 error**. Pengujian database memakai SQLite memory sesuai phpunit.xml, bukan reset database pengembangan.
- `npm run build`: berhasil (Vite 8.1.3). `php artisan view:cache`: berhasil. Ini tidak membuktikan semua halaman dengan data dinamis bebas error.
- HTTP GET lokal: `/`, `/infografis`, `/chatbot`, `/akun/masuk`, `/akun/daftar`, `/akun/lupa-kata-sandi`, `/admin-login` = 200; `/peserta/dashboard`, `/admin/dashboard` = 302 untuk pengunjung; `/.env` = 404.
- `composer audit --locked --no-dev --format=json`: **29 advisori pada 8 paket**. `npm audit --json`: **7 paket terdampak, 2 critical dan 5 high** (tingkat paket, termasuk alat development/build). Jumlah advisori tidak sama dengan jumlah celah yang sudah terbukti dapat dieksploitasi di aplikasi ini.
- Migrasi database lokal: tidak ada yang pending. Tidak menjalankan migrasi atau perubahan data peserta dalam audit ini.
- Tidak melakukan uji visual browser/mobile seluruh peran, pengiriman email nyata sampai inbox, verifikasi Google Sheets nyata, jawaban Groq end-to-end baru, pentest eksploitasi, load test, ataupun pengujian server hosting. Hasil lokal tidak menjamin konfigurasi produksi.
- Artefak rinci ada di folder ignored `.codex-artifacts`: `hosting-audit-tests.txt`, `hosting-audit-tests.xml`, `hosting-composer-audit.json`, `hosting-npm-audit.json`, `hosting-routes.json`. Tidak memuat nilai kredensial dalam laporan ini.

## Temuan prioritas

### P1 — Paket terpasang memiliki advisori keamanan

Audit registry langsung melaporkan Laravel v12.64.0 (3 advisori), Guzzle 7.14.2 (5), CommonMark 2.8.3 (12), Flysystem 3.35.2 (1), Symfony HTTP Foundation v7.4.14 (5), HTTP Kernel v7.4.14 (1), Mime v7.4.13 (1), Routing v7.4.13 (1).

Audit npm melaporkan DOMPurify (critical), concurrently (high), nanoid (high), postcss (high), shell-quote (critical), source-map-js (high), Vite (high). DOMPurify dipakai di `resources/js/chatbot.js:372` untuk sanitasi Markdown ke innerHTML, sehingga perlu prioritas pembaruan dan pengujian XSS. Sebagian advisori DOMPurify mensyaratkan mode seperti IN_PLACE atau SAFE_FOR_TEMPLATES yang tidak terlihat digunakan pada pemanggilan ini; audit paket saja bukan bukti XSS berhasil pada chatbot. Demikian pula advisori Laravel TokenGuard tidak berarti guard session situs ini sudah terbukti dapat dilewati.

Tindakan: perbarui dependensi dan lockfile secara terkontrol ke versi yang tidak terdampak, audit ulang, build ulang, dan jalankan regresi. Jangan memublikasikan Vite dev server.

Sumber yang dikembalikan registry: [Laravel TokenGuard](https://github.com/advisories/GHSA-f9x3-7vq9-cph6), [DOMPurify advisory](https://github.com/advisories/GHSA-cg2g-xh64-v4q7), [Symfony Mime](https://symfony.com/cve-2026-45070). Detail semua advisory/affected range disimpan pada JSON audit.

### P1 — Surat balasan peserta disimpan pada disk publik

Bukti: `Admin/ReplyLetterController.php:105–117`, `Admin/WoppsFollowUpController.php:77–81`, `Peserta/ParticipantApplicationController.php::downloadResponseLetter`, serta `config/filesystems.php`.

Unduhan melalui controller dilindungi login, tetapi file sumber berada di `storage/app/public/reply-letters`. Jika deployment mengaktifkan mapping `/storage` seperti konfigurasi Laravel, seseorang yang memperoleh URL file dapat mengunduhnya tanpa pemeriksaan kepemilikan controller. Nama file acak bukan kontrol akses. Pemeriksaan lokal mendeteksi `public/storage` bukan symbolic link; akses langsung di hosting belum diuji dan bergantung mapping server.

Tindakan: pindahkan surat balasan ke disk privat, sesuaikan unduhan/lampiran email, migrasikan file lama dengan backup, pastikan URL publik lama tidak lagi tersedia. Infografis boleh tetap publik. Dokumen KB unggahan juga berada di disk publik: tetapkan dokumen mana yang memang boleh dibaca umum.

### P1 — Alur WOPPS keputusan dan penyelesaian terputus

View `resources/views/pages/admin/wopps-follow-up/index.blade.php` memanggil `admin.wopps-follow-up.decision` dan `admin.wopps-follow-up.complete`. Keduanya tidak terdaftar di `routes/web.php`; controller `sendDecision()` dan `markCompleted()` sudah ada. Dua test WoppsDecisionFlow error karena rute keputusan tidak ditemukan. Halaman admin dengan pengajuan yang menampilkan form itu berisiko 500; penerimaan/penolakan dan penutupan layanan tidak dapat diselesaikan lewat jalur tersebut.

Tindakan: daftarkan kedua rute POST pada kelompok superadmin yang tepat; verifikasi diterima, ditolak, dihubungi, selesai, dan pengajuan berikutnya melalui portal.

Pemindaian semua rujukan route literal pada view/notifikasi juga menemukan `peserta.request-letter.certificate-follow-up` yang dipanggil `components/peserta/internship-workflow.blade.php` tetapi belum terdaftar. Panel ini bersyarat; peserta yang masuk ke kondisi tindak lanjut surat tanpa sertifikat berisiko mengalami error saat dashboard dirender. Karena kebijakan sertifikat sudah beberapa kali berubah, selaraskan panel/model/controller dengan keputusan terbaru, bukan sekadar menambahkan endpoint lama. Rujukan `login` dan `register` juga ada di template bawaan `welcome.blade.php`; template itu bukan landing route saat ini, jadi tidak dihitung sebagai blocker aktif tanpa bukti dipakai.

### P1 — Seeder produksi berpotensi menciptakan superadmin dengan password tetap

`database/seeders/DatabaseSeeder.php:18–27` membuat superadmin berpassword literal yang sama untuk setiap instalasi, lalu memanggil seeder data contoh. Dua class seeder yang dipanggil tidak ditemukan pada inventaris `database/seeders` saat audit. Tidak dibuktikan bahwa akun produksi sudah memakai password tersebut; risiko timbul jika menggunakan seeder ini saat deployment.

Tindakan: sediakan provisioning admin produksi dengan password unik yang dimasukkan operator, pisahkan data contoh, dan larang seed development di produksi. Hindari menjalankan `db:seed` saat hosting sebelum ini dibenahi.

### P2 — Penghapusan KB belum mencabut pengetahuan yang terindeks

`Admin/KnowledgeBaseController.php:82–88` hanya menghapus file upload dan record database. Indexer menulis Markdown olahan serta entri `uploaded-documents.json`; `KnowledgeBaseRegistry::all()` membaca entri itu tanpa mengecek record DB/status. Destroy tidak menghapus keduanya. Dokumen yang dihapus admin dapat tetap menjadi sumber jawaban chatbot. Reindex yang gagal juga perlu mencabut entri lama, bukan sekadar menghapus hasil Markdown sehingga registry menunjuk file hilang.

Tindakan: penghapusan dan kegagalan reindex harus menjaga konsistensi record, registry, dan hasil olahan, menggunakan mekanisme penguncian yang konsisten. Tambahkan pengujian jawaban responder setelah dokumen dicabut.

### P2 — Ekspor CSV belum menetralkan formula dari input pengunjung

`Admin/ConversationLogController.php::export` dan `Admin/AnalyticsController.php::export` hanya menggandakan tanda kutip pada isi pertanyaan, lalu menulis CSV. Isi yang diawali `=`, `+`, `-`, `@`, atau karakter kontrol perlu dinetralkan agar tidak diperlakukan sebagai formula saat dibuka di spreadsheet. Menaruh nilai di dalam tanda kutip CSV tidak otomatis menetralkan formula.

Tindakan: gunakan formatter CSV bersama yang melindungi formula dan karakter kontrol, lalu uji dengan input berbahaya yang tidak menjalankan perintah eksternal. Belum melakukan eksploitasi spreadsheet dalam audit ini.

### P2 — Penerbitan sertifikat dapat mencatat email sukses sebelum berhasil

`Admin/CertificatePublishController.php:75–96`: file lama dihapus sebelum file baru dan DB selesai; `certificate_emailed_at` diisi sebelum `notify()`. Kegagalan penyimpanan/DB/SMTP dapat meninggalkan file hilang atau status email yang keliru. Controller publish hanya memeriksa bukti tahap 6/7, belum menegaskan service magang dan keputusan diterima. Notification memakai Queueable tetapi tidak mengimplementasikan ShouldQueue, jadi pengiriman berjalan dalam request ini.

Tindakan: simpan file baru dahulu, tegakkan state layanan, jaga konsistensi DB/file, hapus file lama sesudah sukses, dan catat status pengiriman sebagai pending/sent/failed yang benar. SMTP accepted tidak membuktikan email masuk inbox; lakukan uji pengiriman pada lingkungan staging.

### P2 — Penguncian tahapan belum seragam pada server

`ParticipantApplicationController::storeGuestbookProof` dapat menerima unggahan berulang dan mengubah status menjadi guestbook_submitted tanpa menolak pengajuan yang sudah diterima/selesai. `PresentationController::store` dan `CompletionFormProofController::store` juga masih memungkinkan penggantian bukti sesudah selesai; berkas lama tidak dibersihkan setelah penggantian berhasil. UI yang tampak terkunci tidak cukup melindungi POST langsung. Beberapa endpoint unggahan belum diberi throttle khusus, sementara parser PDF berjalan sinkron.

Tindakan: definisikan state yang menerima tiap tindakan, tolak pengubahan pengajuan tertutup, pisahkan aturan revisi, beri pembatasan frekuensi yang sesuai, dan bersihkan berkas tergantikan setelah transaksi berhasil. Uji request langsung yang melompati tahap dan pengajuan milik orang lain.

### P2 — Metadata surat bersama hilang pada penyimpanan upload

`UploadRequestLetterRequest` menerima shared_letter dan letter_institution, tetapi `ParticipantApplicationController::storeRequestLetter` tidak menyimpan metadata/grup surat tersebut. Tiga test SharedRequestLetter gagal: grup tidak terbentuk, panel kelompok tidak tampil, serta aturan reuse/metadata tidak ditegakkan seperti ekspektasi. Bukan semata perubahan teks test.

Tindakan: putuskan apakah surat bersama tetap didukung, lalu selaraskan validasi, pengelompokan berdasarkan isi file, pemeriksaan identitas setiap peserta, dan UI admin.

### P2 — Konfigurasi saat ini masih untuk pengembangan

Pemeriksaan runtime hanya menampilkan flag, bukan rahasia: environment local, debug true, APP_URL belum HTTPS, session secure belum diset eksplisit, HttpOnly true, SameSite lax, mail smtp, queue database, Buku Tamu dikonfigurasi, Groq aktif. Ini wajar untuk pengembangan, tetapi jangan disalin langsung ke produksi.

`public/hot` masih ada. Walaupun build berhasil, Laravel dapat tetap menunjuk development server selama file ini ada. Jangan sertakan file itu pada deployment. Semua migrasi lokal selesai; status hosting belum diperiksa.

Tindakan produksi: APP_ENV=production, APP_DEBUG=false, APP_URL domain HTTPS, SESSION_SECURE_COOKIE=true, host/proxy terpercaya spesifik, kredensial khusus produksi, permission minimum, web root hanya `public/`. Audit header server seperti nosniff, frame policy, Referrer-Policy dan HSTS setelah HTTPS stabil. CSP perlu dirancang sesuai script inline yang ada.

### P2/P3 — Regresi chatbot dan test perlu ditriase

Delapan test GroundedChatbotResponder gagal mengenai pemilihan sumber, kelengkapan bagian, dan jawaban kuota. Perlu membandingkan perilaku yang diinginkan dengan jawaban aktual sebelum mengubah ekspektasi. Enam ChatbotPage test mendapat 403 karena fixture akses lama tidak memenuhi pemilihan layanan baru; ini belum membuktikan pengunjung yang mengikuti alur UI baru gagal. Dua ParticipantChatbotAccess test berbeda ekspektasi dengan middleware saat ini yang mengizinkan semua peserta/admin login, termasuk peserta belum verifikasi email. Konfirmasikan aturan login ini sebagai keputusan produk sebelum memperketatnya.

Failure RequestLetterAutomatedChecker terjadi pada label checklist persiapan 'Kebutuhan sertifikat' di model; pemeriksaan teks sertifikat sendiri pada checker telah dilewati sesuai test. Jangan salah memperbaiki parser yang sudah benar.

Failure lain meliputi tampilan waktu/log, tautan notifikasi, menu role admin, ekspektasi fitur sertifikat admin sudah dihapus padahal rutenya ada, serta kalender yang sekarang pindah halaman. Sebagian kemungkinan test lama; tiap kasus tetap perlu keputusan dan bukti. Jangan menghapus test massal atau mengklaim 29 test bermasalah semuanya bug aplikasi.

## Cakupan alur dari awal sampai selesai

| Alur | Bukti saat audit | Status |
|---|---|---|
| Landing dan infografis | GET lokal 200, build/view compile berhasil; test InfographicsPage | Dasar berjalan; unduh lintas browser belum dicoba manual |
| Pilihan layanan chatbot tamu | UI serta gate session dibaca, GuestbookCheckin/PortalCompletion tests | Alur baru tersedia; fixture test chatbot lama perlu diselaraskan |
| Buku Tamu magang | Akses fail-closed saat Sheets gagal, throttle start/complete | Koneksi Google/izin service account produksi belum dibuktikan |
| Registrasi/login/verifikasi/reset | Rate limit, password hash, regenerasi session, signed verification, dua guard | Pengujian otomatis tersedia; email nyata staging belum diuji |
| Dokumen peserta | MIME/ukuran diperiksa, ownership unduhan diperiksa | Perbaiki surat bersama, state unggahan, rate limit dan konsistensi file |
| Pemeriksaan admin | Middleware admin aktif/superadmin tersedia | Alur sertifikat lama dan ekspektasi menu perlu diselaraskan |
| Surat balasan | Portal/email tersedia di kode | Pindah dokumen ke privat; uji retry email dan pemilihan pengajuan |
| WOPPS sampai selesai | Controller ada, rute decision/complete tidak ada | Terblokir |
| Magang tahap 6/7/8 dan tombol selesai | Presentation, CompletionFormProof, InternshipCompletion tests termasuk full suite | Dasar diuji otomatis; perlu penguncian pengubahan setelah selesai dan sertifikat robust |
| Chatbot jawaban/sumber/escalation | Sanitizer dan batas request tersedia; test retrieval/responder dijalankan | Advisory sanitizer, sumber jawaban dan lifecycle KB perlu diperbaiki |
| Notifikasi, log, analitik, ekspor | Dibaca dan test otomatis dijalankan | CSV protection serta regresi tanggal/link |
| Pengelolaan KB/infografis/admin | Rute superadmin tersedia | Lifecycle KB dan provisioning admin produksi perlu diperbaiki |

## Syarat deployment yang perlu dipenuhi

1. Selesaikan temuan P1, lalu P2 yang menyangkut kerahasiaan, integritas data, dan alur layanan.
2. Selaraskan seluruh failure/error dengan keputusan fitur; jalankan full suite dan pastikan hasilnya dapat dijelaskan/lulus. Tambahkan regresi untuk celah yang diperbaiki.
3. Gunakan PHP sesuai composer (>=8.3) beserta ekstensi yang dibutuhkan; versi Node kompatibel Vite. Build saat deployment/CI, gunakan `composer install --no-dev --optimize-autoloader` dari lockfile yang sudah dibenahi. Jangan memakai `composer setup` untuk produksi karena membuat key dan menjalankan setup pengembangan.
4. Siapkan staging HTTPS dengan database dan storage terpisah, akun sintetis, SMTP nyata khusus staging, izin Sheets yang sesuai, dan pengaturan provider chatbot. Uji semua peran melalui browser desktop/mobile, termasuk kegagalan jaringan, revisi, ditolak, file invalid, submit ganda, unduh lintas akun, dan logout.
5. Web root `public/`; blok akses .env/.git/vendor/storage private, matikan directory listing dan eksekusi script di area upload. Jangan unggah arsip lokal/work/.codex-artifacts atau database development berisi peserta uji ke area publik.
6. Siapkan worker bila notifikasi/job dijadikan queue, cron scheduler bila dipakai, batas upload PHP/web server yang konsisten dengan 5/10/50 MB di aplikasi, timeout parser/provider, log rotation dan pemantauan error.
7. Backup dan uji restore untuk DB, file privat surat/bukti/sertifikat, infografis upload, dan registry/Markdown KB; mempertahankan APP_KEY secara aman. Jangan jalankan migrate:fresh di produksi. Tinjau migration data sebelum `migrate --force` dan sediakan rollback deployment.
8. Tetapkan pemberitahuan privasi, retensi chat/dokumen, akses operator, dan aliran data provider eksternal. Chat menggunakan UUID browser di localStorage sebagai kunci riwayat; jangan memperlakukannya sebagai arsip pribadi yang hanya bisa dibuka oleh akun tertentu. `session_key` juga dikirim di query URL dan dicatat saat error; hindari pencatatan kunci akses itu.

## Hasil pekerjaan audit

Tidak mengimplementasikan perbaikan bisnis, tidak mengubah data peserta, tidak deploy/push. Dokumen ini merupakan daftar kerja untuk hardening berikutnya. Build menghasilkan aset lokal ignored, view:cache mengompilasi template lokal, dan artefak audit disimpan ignored. Perubahan kerja yang sudah ada dipertahankan.
