import fs from "node:fs/promises";
import { Presentation, PresentationFile } from "@oai/artifact-tool";

const OUT = "/Users/test/Documents/KP-DKP/SI-MELAYUR-Progress-DKP-26-Agustus-2026.pptx";
const RENDER = "/Users/test/Documents/KP-DKP/.codex-artifacts/simelayur-progress/rendered";
const IMG = "/Users/test/Documents/KP-DKP/public/images/infografis";

const C = {
  navy: "#0B1F3A",
  ocean: "#1768AC",
  blue: "#2E8BC0",
  pale: "#EAF5FB",
  teal: "#118C8B",
  mint: "#DDF4EF",
  amber: "#E7A928",
  cream: "#FFF6DF",
  ink: "#152A46",
  muted: "#64748B",
  line: "#D8E5EF",
  white: "#FFFFFF",
  cloud: "#F7FAFC",
  soft: "#EEF3F7",
  green: "#16856A",
};

const deck = Presentation.create({ slideSize: { width: 1280, height: 720 } });

function shape(slide, geometry, left, top, width, height, fill, radius = undefined, line = "none") {
  return slide.shapes.add({
    geometry,
    position: { left, top, width, height },
    fill,
    line: { style: "solid", fill: line, width: line === "none" ? 0 : 1 },
    ...(radius ? { borderRadius: radius } : {}),
  });
}

function text(slide, value, left, top, width, height, options = {}) {
  const box = slide.shapes.add({
    geometry: "textbox",
    position: { left, top, width, height },
    fill: options.fill ?? "none",
    line: { style: "solid", fill: "none", width: 0 },
  });
  box.text = value;
  box.text.style = {
    fontFamily: "Aptos",
    fontSize: options.size ?? 22,
    bold: options.bold ?? false,
    color: options.color ?? C.ink,
    alignment: options.align ?? "left",
    verticalAlignment: options.valign ?? "top",
    ...(options.italic ? { italic: true } : {}),
  };
  return box;
}

function addFooter(slide, n, dark = false) {
  text(slide, "SI-MELAYUR  •  Progress Pengembangan", 72, 675, 460, 20, {
    size: 11, color: dark ? "#B9D7EA" : C.muted, bold: true,
  });
  text(slide, String(n).padStart(2, "0"), 1160, 672, 48, 22, {
    size: 12, color: dark ? C.white : C.ocean, bold: true, align: "right",
  });
}

function addHeader(slide, eyebrow, title, n, subtitle = "") {
  slide.background.fill = C.cloud;
  shape(slide, "rect", 0, 0, 18, 720, C.ocean);
  text(slide, eyebrow.toUpperCase(), 72, 48, 500, 24, { size: 13, color: C.teal, bold: true });
  text(slide, title, 72, 82, 1110, 70, { size: 36, color: C.navy, bold: true });
  if (subtitle) text(slide, subtitle, 72, 150, 1090, 44, { size: 18, color: C.muted });
  shape(slide, "rect", 72, 204, 1136, 2, C.line);
  addFooter(slide, n);
}

function addBullets(slide, items, left, top, width, options = {}) {
  const gap = options.gap ?? 70;
  items.forEach((item, i) => {
    const y = top + i * gap;
    shape(slide, "ellipse", left, y + 8, 16, 16, options.dot ?? C.teal);
    text(slide, item, left + 34, y, width - 34, gap - 8, {
      size: options.size ?? 20,
      color: options.color ?? C.ink,
      bold: options.bold ?? false,
      valign: "middle",
    });
  });
}

function addStatus(slide, label, left, top, kind = "ready") {
  const map = {
    ready: [C.mint, C.green],
    refine: [C.pale, C.ocean],
    confirm: [C.cream, "#9A6500"],
    next: [C.soft, C.muted],
  };
  const [fill, color] = map[kind];
  shape(slide, "roundRect", left, top, 190, 34, fill, "rounded-full");
  text(slide, label, left + 12, top + 6, 166, 22, { size: 12, color, bold: true, align: "center" });
}

function addDemoSlide(n, demoNumber, titleValue, steps, cue) {
  const slide = deck.slides.add();
  slide.background.fill = C.navy;
  shape(slide, "ellipse", 940, 0, 340, 330, "#123D69");
  shape(slide, "ellipse", 1030, 410, 250, 310, "#0D5264");
  text(slide, `DEMO ${demoNumber} DARI 5`, 74, 70, 260, 28, { size: 14, color: "#7ED7D5", bold: true });
  text(slide, "LIVE DEMO", 72, 128, 780, 80, { size: 58, color: C.white, bold: true });
  text(slide, titleValue, 74, 218, 760, 76, { size: 31, color: "#CFE8F6", bold: true });
  shape(slide, "roundRect", 74, 344, 760, 186, "#102C4D", "rounded-2xl", "#245A83");
  steps.forEach((s, i) => {
    shape(slide, "ellipse", 104, 374 + i * 48, 28, 28, C.teal);
    text(slide, String(i + 1), 104, 378 + i * 48, 28, 18, { size: 12, color: C.white, bold: true, align: "center" });
    text(slide, s, 150, 371 + i * 48, 630, 34, { size: 18, color: C.white, valign: "middle" });
  });
  text(slide, cue, 74, 575, 760, 40, { size: 16, color: "#8FBAD4", italic: true });
  addFooter(slide, n, true);
  slide.speakerNotes.textFrame.setText([
    `Transisi: ${cue}`,
    `Urutan demo: ${steps.join(" → ")}.`,
    "Sebelum presentasi, siapkan tab browser, akun demo, dan data contoh. Jika demo gagal, kembali ke slide dan jelaskan alur menggunakan tiga langkah yang tampil.",
  ]);
  return slide;
}

function addFeatureSlide(n, eyebrow, titleValue, takeaway, points, visualLabel, status = "ready") {
  const slide = deck.slides.add();
  addHeader(slide, eyebrow, titleValue, n, takeaway);
  addBullets(slide, points, 80, 250, 600, { gap: 70, size: 19 });
  shape(slide, "roundRect", 750, 244, 410, 330, C.white, "rounded-2xl", C.line);
  shape(slide, "roundRect", 782, 278, 346, 178, C.pale, "rounded-xl");
  text(slide, visualLabel, 810, 322, 290, 78, { size: 24, color: C.ocean, bold: true, align: "center", valign: "middle" });
  addStatus(slide, status === "ready" ? "SUDAH TERSEDIA" : "SEDANG DISEMPURNAKAN", 860, 482, status);
  text(slide, "Fokus demo", 782, 540, 100, 20, { size: 12, color: C.muted, bold: true });
  text(slide, points[0], 878, 534, 244, 44, { size: 14, color: C.ink });
  slide.speakerNotes.textFrame.setText([
    `Pesan utama: ${takeaway}`,
    `Jelaskan secara singkat: ${points.join("; ")}.`,
    "Setelah penjelasan, arahkan audiens ke slide Live Demo berikutnya.",
  ]);
  return slide;
}

// 1 — Cover
{
  const s = deck.slides.add();
  s.background.fill = C.navy;
  shape(s, "rect", 0, 0, 22, 720, C.teal);
  shape(s, "ellipse", 900, 0, 380, 360, "#123D69");
  shape(s, "ellipse", 1030, 400, 250, 320, "#0D5264");
  text(s, "DINAS KELAUTAN DAN PERIKANAN\nPROVINSI JAWA TIMUR", 76, 58, 520, 54, { size: 14, color: "#8FCBE5", bold: true });
  text(s, "Progress Pengembangan\nSI-MELAYUR", 74, 170, 790, 164, { size: 54, color: C.white, bold: true });
  text(s, "Sistem Informasi Magang, Penelitian, dan Data Kelautan Jawa Timur", 78, 365, 760, 76, { size: 23, color: "#CFE8F6" });
  shape(s, "rect", 78, 476, 116, 4, C.teal);
  text(s, "Presentasi progress & live demo  •  26 Agustus 2026", 78, 506, 700, 30, { size: 16, color: "#9EC7DD", bold: true });
  text(s, "Presenter: [Nama Presenter]", 78, 548, 500, 26, { size: 15, color: "#769CB5" });
  addFooter(s, 1, true);
  s.speakerNotes.textFrame.setText(["Buka dengan tujuan pertemuan: menunjukkan progress nyata dan meminta konfirmasi untuk tahap selanjutnya.", "Sampaikan bahwa demo dilakukan setelah setiap penjelasan fitur agar alurnya mudah diikuti."]);
}

// 2 — Context
{
  const s = deck.slides.add();
  addHeader(s, "Mengapa SI-MELAYUR", "Informasi, pengajuan, dan tindak lanjut perlu berada dalam satu alur", 2);
  const cols = [
    ["01", "Informasi tersebar", "Pemohon membutuhkan jawaban resmi yang konsisten dan mudah ditemukan."],
    ["02", "Proses sulit dipantau", "Status dokumen, revisi, dan tindak lanjut perlu terlihat oleh peserta dan petugas."],
    ["03", "Evaluasi membutuhkan data", "Pertanyaan, aktivitas, dan kebutuhan pengguna perlu direkam sebagai bahan perbaikan."],
  ];
  cols.forEach((c, i) => {
    const x = 72 + i * 378;
    text(s, c[0], x, 252, 68, 44, { size: 28, color: C.teal, bold: true });
    shape(s, "rect", x, 306, 330, 2, C.line);
    text(s, c[1], x, 334, 330, 40, { size: 23, color: C.navy, bold: true });
    text(s, c[2], x, 392, 320, 108, { size: 17, color: C.muted });
  });
  shape(s, "roundRect", 72, 554, 1136, 72, C.pale, "rounded-xl");
  text(s, "SI-MELAYUR menghubungkan layanan informasi, portal peserta, dan pengelolaan petugas dalam satu sistem.", 106, 572, 1070, 36, { size: 20, color: C.ocean, bold: true, align: "center" });
  s.speakerNotes.textFrame.setText(["Tekankan bahwa sistem bukan sekadar chatbot; sistem menghubungkan pencarian informasi, proses peserta, dan pekerjaan admin.", "Hindari menyebut angka efisiensi sebelum ada pengukuran resmi."]);
}

// 3 — Objective
{
  const s = deck.slides.add();
  addHeader(s, "Tujuan sistem", "Satu pintu layanan untuk masyarakat, peserta, dan petugas", 3);
  const rows = [
    ["MASYARAKAT", "Mendapat informasi resmi dengan cepat", C.pale, C.ocean],
    ["PESERTA", "Menyiapkan pengajuan dan memantau status", C.mint, C.green],
    ["PETUGAS", "Memeriksa, menindaklanjuti, dan mengevaluasi layanan", C.cream, "#9A6500"],
  ];
  rows.forEach((r, i) => {
    const y = 250 + i * 112;
    shape(s, "roundRect", 90, y, 250, 78, r[2], "rounded-xl");
    text(s, r[0], 110, y + 25, 210, 28, { size: 16, color: r[3], bold: true, align: "center" });
    shape(s, "rect", 340, y + 38, 110, 3, C.line);
    text(s, r[1], 478, y + 17, 650, 50, { size: 23, color: C.navy, bold: true, valign: "middle" });
  });
  s.speakerNotes.textFrame.setText(["Jelaskan tiga kelompok manfaat tanpa masuk ke detail teknis.", "Transisi: setelah tujuan dipahami, tunjukkan bagaimana setiap pengguna bergerak di dalam sistem."]);
}

// 4 — Overall flow
{
  const s = deck.slides.add();
  addHeader(s, "Gambaran alur", "Setiap pengguna masuk melalui jalur yang sesuai dengan kebutuhannya", 4);
  const nodes = [
    ["Pengunjung", "Buku tamu & chatbot", C.pale, C.ocean],
    ["Peserta", "Akun & pengajuan", C.mint, C.green],
    ["Admin", "Pemeriksaan & tindak lanjut", C.cream, "#9A6500"],
    ["Superadmin", "Konten & kewenangan", C.soft, C.muted],
  ];
  nodes.forEach((n, i) => {
    const x = 62 + i * 300;
    shape(s, "roundRect", x, 282, 244, 150, n[2], "rounded-2xl");
    text(s, n[0], x + 22, 310, 200, 34, { size: 23, color: n[3], bold: true, align: "center" });
    text(s, n[1], x + 24, 362, 196, 46, { size: 16, color: C.ink, align: "center" });
    if (i < 3) text(s, "→", x + 248, 329, 48, 48, { size: 34, color: C.blue, bold: true, align: "center" });
  });
  shape(s, "roundRect", 190, 500, 900, 72, C.navy, "rounded-xl");
  text(s, "Informasi → Persiapan → Pemeriksaan → Evaluasi layanan", 220, 520, 840, 32, { size: 22, color: C.white, bold: true, align: "center" });
  s.speakerNotes.textFrame.setText(["Jelaskan bahwa empat peran memiliki akses dan tanggung jawab berbeda.", "Tidak perlu mendemokan semua peran sekaligus; demo dibagi menjadi lima sesi singkat."]);
}

addFeatureSlide(5, "Fitur 1", "Chatbot menjawab berdasarkan sumber resmi", "Pengunjung memperoleh informasi dan dapat menelusuri sumber jawaban.", ["Isi buku tamu sebelum mengakses chatbot", "Ajukan pertanyaan dan lihat sumber jawaban", "Beri feedback atau teruskan pertanyaan kepada petugas"], "Buku Tamu  →  Chatbot", "ready");
addDemoSlide(6, 1, "Akses Informasi dan Chatbot", ["Buka landing page dan isi buku tamu", "Tanyakan persyaratan Magang/KP/PKL", "Lihat sumber, feedback, dan eskalasi"], "Mari kita lihat pengalaman pengguna dari awal.");

addFeatureSlide(7, "Fitur 2", "Akun peserta menjaga proses tetap personal dan terlacak", "Registrasi, verifikasi email, dan pemulihan akun membentuk akses yang aman.", ["Daftar menggunakan identitas dan email peserta", "Verifikasi email sebelum membuka dashboard", "Login, logout, dan lupa kata sandi tersedia"], "Daftar  →  Verifikasi  →  Masuk", "ready");
addDemoSlide(8, 2, "Registrasi dan Login Peserta", ["Tunjukkan halaman pendaftaran", "Jelaskan verifikasi dan pemulihan akun", "Masuk ke dashboard peserta"], "Berikutnya, kita masuk sebagai peserta layanan.");

// 9 participant portal (custom to fit document list)
{
  const s = deck.slides.add();
  addHeader(s, "Fitur 3", "Portal peserta memandu dokumen dan status pengajuan", 9, "Tahapan dibuat terlihat agar peserta mengetahui apa yang harus dilakukan berikutnya.");
  const docs = ["Pas foto peserta", "KTM — mahasiswa", "Kartu Pelajar — siswa", "Ethical Clearance", "Surat Permohonan", "Surat Kesehatan resmi"];
  docs.forEach((d, i) => {
    const col = i % 2, row = Math.floor(i / 2);
    const x = 80 + col * 330, y = 250 + row * 86;
    shape(s, "roundRect", x, y, 290, 62, C.white, "rounded-xl", C.line);
    shape(s, "ellipse", x + 18, y + 18, 26, 26, C.teal);
    text(s, "✓", x + 18, y + 20, 26, 20, { size: 13, color: C.white, bold: true, align: "center" });
    text(s, d, x + 58, y + 15, 210, 34, { size: 16, color: C.ink, bold: true, valign: "middle" });
  });
  shape(s, "roundRect", 788, 248, 360, 286, C.navy, "rounded-2xl");
  text(s, "Alur peserta", 820, 278, 290, 34, { size: 20, color: "#86D7D2", bold: true });
  addBullets(s, ["Pilih jenis layanan", "Unggah dokumen", "Pantau pemeriksaan", "Terima revisi atau persetujuan", "Unduh surat balasan"], 820, 330, 290, { gap: 42, size: 15, color: C.white, dot: C.teal });
  addStatus(s, "SEDANG DISEMPURNAKAN", 873, 563, "refine");
  s.speakerNotes.textFrame.setText(["Tegaskan enam dokumen yang saat ini dipersiapkan untuk pengajuan Magang/KP/PKL.", "Beberapa detail alur masih perlu dikonfirmasi bersama DKP agar sesuai SOP operasional."]);
}
addDemoSlide(10, 3, "Portal dan Pengajuan Peserta", ["Pilih jenis layanan", "Unggah dokumen dan lihat status", "Tampilkan notifikasi serta surat balasan"], "Sekarang kita ikuti perjalanan peserta di portal.");

addFeatureSlide(11, "Fitur 4", "Keputusan pemeriksaan tetap pada petugas", "Sistem memberi hasil awal; admin menyetujui atau meminta revisi dengan catatan.", ["Buka antrean dan detail dokumen peserta", "Tinjau hasil pemeriksaan otomatis", "Setujui atau minta revisi secara manual"], "Otomatis  +  Verifikasi Admin", "refine");
addDemoSlide(12, 4, "Pemeriksaan Dokumen", ["Buka antrean pemeriksaan", "Tinjau hasil otomatis dan dokumen", "Berikan persetujuan atau catatan revisi"], "Mari berpindah ke sudut pandang petugas.");

// 13 admin suite with actual infographic image
{
  const s = deck.slides.add();
  addHeader(s, "Fitur 5", "Admin memantau kualitas layanan dalam satu dashboard", 13, "Dashboard menyatukan konten, percakapan, tindak lanjut, dan evaluasi.");
  const items = ["Knowledge base", "Infografis", "Log percakapan", "Pertanyaan tidak terjawab", "Analitik", "Log aktivitas", "Lokasi & kuota", "Manajemen admin"];
  items.forEach((d, i) => {
    const col = i % 2, row = Math.floor(i / 2);
    const x = 78 + col * 274, y = 244 + row * 72;
    shape(s, "roundRect", x, y, 242, 52, i < 6 ? C.white : C.cream, "rounded-xl", C.line);
    text(s, d, x + 18, y + 14, 206, 28, { size: 15, color: C.ink, bold: true, align: "center" });
  });
  const bytes = await fs.readFile(`${IMG}/infografis-01.jpg`);
  s.images.add({ blob: bytes, contentType: "image/jpeg", alt: "Infografis resmi layanan DKP", fit: "cover", position: { left: 700, top: 242, width: 432, height: 310 }, geometry: "roundRect", borderRadius: "rounded-2xl" });
  shape(s, "roundRect", 754, 524, 324, 48, C.navy, "rounded-xl");
  text(s, "Konten resmi menjadi sumber chatbot", 770, 537, 292, 22, { size: 14, color: C.white, bold: true, align: "center" });
  s.speakerNotes.textFrame.setText(["Jelaskan bahwa superadmin mengelola knowledge base, infografis, dan akun admin.", "Admin operasional memeriksa dokumen, menindaklanjuti pertanyaan, serta melihat analitik sesuai kewenangan."]);
}
addDemoSlide(14, 5, "Dashboard dan Pengelolaan Admin", ["Buka dashboard dan knowledge base", "Tinjau percakapan serta pertanyaan masuk", "Lihat analitik, aktivitas, dan pengaturan layanan"], "Terakhir, kita lihat bagaimana petugas mengelola layanan.");

// 15 progress
{
  const s = deck.slides.add();
  addHeader(s, "Progress saat ini", "Fondasi layanan end-to-end sudah terbentuk", 15, "Progress dinilai berdasarkan fungsi yang tersedia—bukan persentase yang belum divalidasi.");
  const rows = [
    ["Informasi publik", "Landing page, infografis, buku tamu, chatbot", "ready"],
    ["Portal peserta", "Akun, pilihan layanan, unggah, status, notifikasi", "refine"],
    ["Operasional admin", "Pemeriksaan, knowledge base, tindak lanjut", "refine"],
    ["Monitoring", "Percakapan, analitik, dan log aktivitas", "ready"],
  ];
  rows.forEach((r, i) => {
    const y = 246 + i * 88;
    text(s, r[0], 82, y + 10, 220, 30, { size: 18, color: C.navy, bold: true });
    text(s, r[1], 320, y + 10, 520, 34, { size: 16, color: C.muted });
    addStatus(s, r[2] === "ready" ? "SUDAH TERSEDIA" : "DISEMPURNAKAN", 930, y + 4, r[2]);
    shape(s, "rect", 82, y + 60, 1050, 1, C.line);
  });
  s.speakerNotes.textFrame.setText(["Hindari menyebut progress dalam persen karena belum ada definisi pengukuran yang disepakati.", "Tekankan bahwa fondasi sudah ada, namun kesiapan operasional membutuhkan validasi DKP."]);
}

// 16 roadmap needs
{
  const s = deck.slides.add();
  addHeader(s, "Kebutuhan berikutnya", "Kesiapan operasional bergantung pada keputusan proses, bukan hanya penambahan fitur", 16);
  const cols = [
    ["KONFIRMASI PROSES", ["Finalisasi SOP layanan", "Validasi dokumen wajib", "Kewenangan petugas", "Alur surat balasan"], C.cream, "#9A6500"],
    ["PENYEMPURNAAN SISTEM", ["Pemeriksaan otomatis", "Notifikasi email/WhatsApp", "Kuota lokasi & periode", "Knowledge base chatbot"], C.pale, C.ocean],
    ["KESIAPAN OPERASIONAL", ["Hosting & domain", "Keamanan & backup", "Akun petugas", "Uji coba bersama"], C.mint, C.green],
  ];
  cols.forEach((c, i) => {
    const x = 72 + i * 378;
    shape(s, "roundRect", x, 246, 342, 332, c[2], "rounded-2xl");
    text(s, c[0], x + 24, 276, 294, 30, { size: 15, color: c[3], bold: true, align: "center" });
    shape(s, "rect", x + 38, 324, 266, 2, "#C8DCE8");
    addBullets(s, c[1], x + 34, 348, 282, { gap: 50, size: 16, color: C.ink, dot: c[3] });
  });
  s.speakerNotes.textFrame.setText(["Gunakan slide ini untuk membedakan kebutuhan kebijakan, pengembangan teknis, dan persiapan operasional.", "Minta DKP menentukan prioritas, bukan langsung menyepakati seluruh item sekaligus."]);
}

// 17 decisions / close
{
  const s = deck.slides.add();
  s.background.fill = C.navy;
  shape(s, "rect", 0, 0, 20, 720, C.teal);
  text(s, "DISKUSI & KEPUTUSAN", 74, 54, 400, 24, { size: 13, color: "#7ED7D5", bold: true });
  text(s, "Apa yang perlu kita sepakati agar SI-MELAYUR siap digunakan?", 74, 100, 1040, 112, { size: 40, color: C.white, bold: true });
  const qs = ["Alur layanan dan SOP", "Dokumen wajib per layanan", "Peran dan kewenangan petugas", "Prioritas pengembangan berikutnya"];
  qs.forEach((q, i) => {
    const x = 74 + (i % 2) * 550, y = 276 + Math.floor(i / 2) * 114;
    text(s, `0${i + 1}`, x, y, 54, 38, { size: 22, color: C.teal, bold: true });
    text(s, q, x + 70, y, 430, 52, { size: 21, color: C.white, bold: true });
  });
  shape(s, "roundRect", 74, 540, 1070, 72, "#102C4D", "rounded-xl", "#245A83");
  text(s, "Fondasi sudah tersedia. Langkah berikutnya adalah menyelaraskan sistem dengan proses resmi DKP.", 108, 559, 1000, 36, { size: 20, color: "#CFE8F6", bold: true, align: "center" });
  addFooter(s, 17, true);
  s.speakerNotes.textFrame.setText(["Tutup dengan meminta empat keputusan: SOP, dokumen wajib, kewenangan, dan prioritas.", "Catat masukan DKP langsung dan konfirmasikan tindak lanjut setelah presentasi."]);
}

await fs.mkdir(RENDER, { recursive: true });
for (const [i, slide] of deck.slides.items.entries()) {
  const png = await deck.export({ slide, format: "png", scale: 1 });
  await fs.writeFile(`${RENDER}/slide-${String(i + 1).padStart(2, "0")}.png`, new Uint8Array(await png.arrayBuffer()));
  const layout = await slide.export({ format: "layout" });
  await fs.writeFile(`${RENDER}/slide-${String(i + 1).padStart(2, "0")}.layout.json`, await layout.text());
}

const montage = await deck.export({ format: "webp", montage: true, scale: 1 });
await fs.writeFile(`${RENDER}/montage.webp`, new Uint8Array(await montage.arrayBuffer()));
const pptx = await PresentationFile.exportPptx(deck);
await pptx.save(OUT);

console.log(JSON.stringify({ output: OUT, slides: deck.slides.items.length, montage: `${RENDER}/montage.webp` }));
