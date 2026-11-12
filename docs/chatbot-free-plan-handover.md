# Chatbot gratis dan serah-terima akun dinas

Implementasi 9 November 2026. Website menggunakan jawaban dari KB sebagai layanan dasar tanpa API AI. Groq hanya tambahan untuk menyusun jawaban, dan tidak menggunakan login, langganan, atau kuota ChatGPT Plus.

## Keadaan konfigurasi saat ini

- Akses Groq lokal dinonaktifkan dan API key pribadi dikosongkan dari `.env` aktif. Pengaturan Groq sebelumnya dicadangkan hanya pada `.codex-artifacts/groq-personal-before-handover.env` yang ignored; ini rahasia lokal, jangan dimasukkan ke rilis atau disalin ke hosting.
- Template environment berisi `GROQ_ENABLED=false`, key kosong, pemilik `unset`, konfirmasi Free Plan `false`, dan fingerprint kosong.
- Model calon yang dikonfigurasi adalah `openai/gpt-oss-20b` pada endpoint Groq. Awalan `openai/` adalah ID model di Groq, bukan pemakaian akun ChatGPT/OpenAI API. Tidak tersedia pergantian otomatis ke model, penyedia, atau paket berbayar.
- Model tersebut tercantum dalam tabel [Free Plan Groq](https://console.groq.com/docs/rate-limits). Model yang sama dapat tersedia pada paket berbayar: **nama model dan API key saja tidak membuktikan paket akun gratis**. Status akun dinas harus diperiksa di Groq Console oleh pemilik akun sebelum konfirmasi. Aplikasi tidak mempunyai akses billing untuk membuktikannya, dan deklarasi konfigurasi tidak dapat mendeteksi perubahan paket di dashboard setelahnya.
- Pengujian implementasi memakai respons HTTP palsu; tidak menggunakan key pribadi atau mengirim query/KB ke Groq dalam pekerjaan ini. Kualitas model pada akun dinas baru harus diuji setelah akun tersedia.

## Batas yang diterapkan

| Jenis | Batas awal |
|---|---|
| Pertanyaan tamu | 5 per menit dan 30 per 24 jam sejak jendela limiter dimulai |
| Pertanyaan akun login | 10 per menit dan 100 per 24 jam |
| Pengaman seluruh akun/sesi pada satu IP | 60 pertanyaan per menit, 500 per 24 jam |
| Riwayat/buka percakapan | 120 permintaan per menit, terpisah dari pertanyaan |
| Feedback dan eskalasi | 20 tindakan per menit, terpisah dari pertanyaan |
| Panjang pertanyaan | Maksimal 500 karakter |
| Panggilan Groq seluruh website | Maksimal 24 per menit, 800 per hari WIB |
| Anggaran token Groq seluruh website | Maksimal 6.400 per menit, 160.000 per hari WIB |
| Output AI termasuk reasoning | Maksimal 1.800 token, reasoning effort low |
| Panjang prompt AI | Maksimal 4.000 byte; konteks besar dijawab dari KB secara utuh |
| Cache jawaban AI | 15 menit, terenkripsi, fingerprint pertanyaan/prompt/konteks/model/akun |
| Timeout Groq | Default 15 detik; konfigurasi aplikasi dibatasi 5–20 detik |

Angka harian Groq adalah anggaran lokal website, bukan pembacaan langsung saldo atau jendela organisasi. Batas resmi organisasi dapat berbeda dan pemakaian aplikasi lain ikut memengaruhi kuota. Groq tetap dapat mengembalikan 429; penanganannya kembali ke KB. Anggaran awal memakai ruang cadangan sekitar 20% dari tabel Free Plan yang dirujuk saat implementasi, dan header batas penyedia yang lebih rendah dapat memperketat anggaran. Konfigurasi dapat menurunkan batas; kenaikan di atas batas pengaman dalam kode memerlukan review kembali, bukan upgrade otomatis.

Tamu diidentifikasi dari sesi server, akun login dari guard dan ID akun. Mengganti UUID percakapan tidak mengganti jatah. Pembatasan IP tetap berlaku jika cookie/sesi tamu diganti; kantor yang berbagi satu IP dapat menyesuaikan pengaman IP setelah mengamati pemakaian. IP dan identitas limiter disimpan sebagai HMAC. Request pesan yang invalid tetap menghitung percobaan, tetapi hanya request yang mencapai generator dan lolos anggaran yang memanggil Groq.

## Perilaku saat kuota atau penyedia gagal

1. Pertanyaan FAQ yang telah disetujui tetap dijawab langsung tanpa panggilan AI.
2. Cache AI hanya digunakan untuk konteks yang sama dan akun dinas yang masih terkonfirmasi. Cache terenkripsi; key hanya HMAC, tanpa pertanyaan atau API key plaintext. Perubahan isi/seleksi sumber KB, prompt, model, atau akun menghasilkan fingerprint baru; jawaban lama tidak digunakan. Entri lama kedaluwarsa dalam 15 menit.
3. Sebelum request AI, anggaran request serta estimasi token input + overhead + batas output/reasoning dipesan dalam transaksi DB dengan row lock. Jadi semua pengguna/proses pada database yang sama berbagi anggaran. Ini estimasi konservatif berbasis byte, bukan tokenizer model yang persis.
4. Setelah respons, usage yang benar-benar dilaporkan menggantikan estimasi. Saat timeout/usage tidak diketahui, estimasi tetap dihitung untuk mencegah pemakaian tak terukur. Panel admin memisahkan token dicadangkan dan token yang dilaporkan penyedia.
5. Anggaran habis, prompt terlalu besar, storage/cache bermasalah, respons terpotong/kosong, model tidak tersedia, atau Groq gagal: responder memakai kembali bagian KB yang sudah dipilih, tanpa memotong prosedur demi memaksakan request AI.
6. Respons 429 mengaktifkan cooldown sesuai Retry-After (default 60 detik); tidak ada retry beruntun. Kredensial/model bermasalah memakai cooldown lebih lama. Tidak berpindah ke provider berbayar. Jika KB tidak cukup, jawaban menyatakan keterbatasan informasi dan jalur eskalasi ke petugas tetap ada.
7. Jika batas **pertanyaan pengguna** sendiri habis, pesan baru ditolak 429 dengan waktu tunggu. Riwayat, feedback sesuai batasnya, dan halaman panduan tetap dapat dibuka. Ini berbeda dari kuota Groq habis yang masih dapat dijawab dari KB.

## Pemantauan admin

Dashboard admin memuat panel ringkas yang dapat dibuka: mode KB/AI, jumlah panggilan, anggaran token, token dilaporkan, kegagalan, penggunaan cache, fallback, dan peringatan saat anggaran harian mencapai 80%. Snapshot header Groq menampilkan sisa request harian dan token per menit dengan timestamp, bukan saldo uang atau jaminan sisa kuota saat ini.

Penghitung pada tabel `chatbot_ai_counters` tidak menyimpan email, nama, pertanyaan, jawaban, ataupun API key. Scope anggaran memakai HMAC identitas instalasi dengan APP_KEY, sehingga pergantian key tidak mereset anggaran harian website. Cache jawaban mempunyai namespace terpisah per key agar akun baru tidak menggunakan cache akun sebelumnya. Data diringkas per menit/hari dan penghitung lama dibersihkan scheduler setelah 30 hari. `chatbot:prune-usage --days=30` tidak menghapus percakapan, dokumen, atau data peserta. APP_KEY harus tetap sama antar proses/rilis; semua node harus memakai DB dan cache database/Redis yang sama agar pembatasan konsisten.

## Langkah serah-terima kepada tim dinas

1. Buat akun/organisasi Groq yang dikelola dinas, pilih **Free Plan**, dan pastikan tidak mengaktifkan paket billing berbayar/upgrade otomatis. Jangan memakai akun pribadi pengembang. [Spend Limits](https://console.groq.com/docs/spend-limits) adalah fitur paket berbayar dan bukan cara membuat akun berbayar menjadi gratis.
2. Buat key khusus website pada akun dinas dan simpan hanya di environment rahasia hosting sebagai `GROQ_API_KEY`. Jangan memasukkan key ke Git, JavaScript/VITE, screenshot, atau chat. Pastikan model `openai/gpt-oss-20b` masih tersedia dan batas akun sesuai dashboard. Jika tidak tersedia, pertahankan mode KB; perubahan model memerlukan verifikasi Free Plan dan review allowlist.
3. Jalankan migrasi yang disertakan, termasuk `2026_11_09_200000_create_chatbot_ai_counters_table`. Gunakan CACHE_STORE=database atau redis. Jalankan `php artisan config:clear` setelah mengisi key/model.
4. Jalankan `php artisan chatbot:confirm-free-plan`. Operator harus menjawab tiga konfirmasi: kepemilikan dinas, paket gratis tanpa billing berbayar, dan pengaktifan. Command tidak menghubungi Groq, tidak menampilkan key, dan mengikat konfirmasi ke hash SHA-256 key yang aktif.
5. Jalankan `php artisan config:cache` dan restart proses PHP/worker/Octane yang memegang konfigurasi lama. Jalankan `php artisan deployment:check`; jika AI diaktifkan tetapi key/konfirmasi/pemilik/model tidak sesuai, pemeriksaan gagal dan generator menolak panggilan eksternal.
6. Uji end-to-end menggunakan akun dinas dengan beberapa pertanyaan sintetis: persyaratan, alur magang lengkap, tata tertib, WOPPS, pertanyaan di luar KB, dan simulasi kuota/error. Pastikan jawaban lengkap, sumber benar, usage tercatat, serta tidak ada layanan berbayar. Bandingkan snapshot header dengan [halaman Limits Groq](https://console.groq.com/settings/limits).
7. Jika key diganti, konfirmasi lama tidak berlaku pada key baru. Periksa kepemilikan dan paket lagi, lalu ulangi command. Jika paket akun diubah, cabut dahulu dengan `php artisan chatbot:confirm-free-plan --revoke`, bangun config cache dan restart proses. Untuk batasan tanpa risiko biaya AI eksternal, biarkan GROQ_ENABLED=false; website tetap menjawab dari KB.
8. Aktifkan cron Laravel scheduler pada hosting (`schedule:run` setiap menit) untuk pembersihan penghitung. Pengaturan batas ada di config/chatbot.php dan template environment. Perubahan batas memerlukan config cache/restart yang sama.

Belum ada aktivasi atau panggilan langsung menggunakan akun dinas karena kredensialnya belum tersedia. Tidak ada deployment atau peningkatan paket berbayar pada pekerjaan ini.

## Bukti validasi implementasi

Full suite: **293 test, 1.894 assertion, seluruhnya lulus**. Mencakup batas menit/24 jam, pemisahan riwayat dan pesan, pengaman IP, penolakan key pribadi/tidak terverifikasi, anggaran persisten lintas rotasi key, cache terenkripsi dan perubahan konteks KB, cooldown 429, kegagalan storage, fallback responder, panel admin, serta alur konfirmasi/pencabutan akun.

Semua request provider pada pengujian memakai HTTP fake; test harness menolak request HTTP yang tidak difake. Build produksi dan Pint berhasil. Migrasi penghitung penggunaan sudah dijalankan pada database pengembangan lokal. Uji model dan kuota nyata dengan akun dinas tetap merupakan langkah serah-terima, bukan hasil pengujian yang diklaim telah dilakukan.
