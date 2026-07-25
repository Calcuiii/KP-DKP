# Generic Retrieval Response Skill

## Tujuan

Gunakan dokumen ini saat mengubah retrieval atau penyusunan jawaban chatbot KP-DKP. Pertahankan perilaku berbasis Knowledge Base: jawaban harus berasal dari section Markdown yang berhasil diretrieve, bukan dari pengetahuan generatif.

## Masalah yang Ditangani

Versi lokal sebelumnya memiliki jalur khusus di `GroundedChatbotResponder` yang mendeteksi pola pertanyaan alur Magang/PKL, memuat dokumen `KB-007` secara langsung, lalu memaksa seluruh Langkah 1–11 ke jawaban. Pendekatan itu mengunci logika bisnis ke ID dokumen dan kata kunci tertentu.

Retriever juga memberi poin untuk setiap token yang cocok di judul dokumen. Karena itu, section yang tidak mempunyai kecocokan pada judul section ataupun konten dapat memperoleh skor gabungan dari judul dokumen saja. Nilai skor total tidak boleh menjadi satu-satunya dasar eligibility section.

## Desain Target

### Eligibility retrieval

Pertahankan skor lexical untuk ranking, tetapi hanya masukkan result bila memiliki bukti kecocokan langsung:

```text
score > 0
AND hasDirectMatch = true
```

`hasDirectMatch` bernilai `true` bila frasa atau minimal satu token query informatif cocok pada `section_title` atau `content`. Kecocokan pada `document_title` hanya menambah ranking dan tidak pernah dapat menjadi satu-satunya alasan section lolos.

Hitung sinyal tersebut di `LexicalKnowledgeBaseRetriever`, tempat komponen skor masih tersedia. Jangan menduplikasi normalisasi atau tokenisasi di responder.

### Penyusunan jawaban

Untuk jawaban grounded:

- minta maksimal `topK=20` candidate dari pipeline;
- gunakan hanya candidate yang sudah lolos eligibility retriever;
- pertahankan urutan ranking retrieval kecuali test membuktikan presentasi section berurutan memerlukan pengurutan generik berdasarkan metadata section;
- tambahkan section secara utuh hingga budget body `5.500` karakter;
- jangan memotong section di tengah;
- jangan mengambil candidate baru untuk sekadar mengisi sisa budget;
- jangan mendeteksi document ID, kata kunci bisnis, atau jumlah langkah tertentu di responder.

`topK=20` dipilih karena corpus saat ini memiliki KB-007 dengan 14 chunk setelah granularitas `##`/`###`, sehingga ada ruang untuk seluruh candidate dokumen tersebut dan margin kandidat lain. Nilai ini tetap harus diuji terhadap corpus aktual.

## Alur

### Sebelum

```text
Query
  -> retrieve(topK=5)
  -> maksimal 3 section unik, tiap section dapat dipotong
  -> jika pola alur Magang/PKL cocok:
       muat KB-007 langsung dan paksa Langkah 1-11
  -> jawaban
```

### Sesudah (target)

```text
Query
  -> retrieval lexical
       -> ranking oleh skor section + content + document title
       -> eligibility: wajib ada direct match section/content
  -> retrieve(topK=20)
  -> pilih section relevan secara berurutan sampai 5.500 karakter
  -> jawaban grounded tanpa aturan per-dokumen
```

## Perlindungan Metadata Internal

`chatbot_notes` KB-007 harus berada di frontmatter dan tidak boleh ada sebagai heading/body yang dapat di-chunk. Loader menghapus frontmatter sebelum chunking. Tambahkan regression test yang memastikan pertanyaan tentang alur/langkah berurutan tidak pernah menampilkan instruksi internal tersebut.

## File yang Direncanakan

| File | Perubahan target |
| --- | --- |
| `app/KnowledgeBase/LexicalKnowledgeBaseRetriever.php` | Hitung `hasDirectMatch` dari section title/content dan tolak result document-title-only. |
| `tests/Unit/LexicalKnowledgeBaseRetrieverTest.php` | Tambahkan regresi dua token yang hanya cocok pada document title; pertahankan direct section/content match. |
| `app/Services/GroundedChatbotResponder.php` | Hapus jalur KB-007 dan helper khusus; gunakan `topK=20` serta budget 5.500 karakter dengan section utuh. |
| `tests/Unit/GroundedChatbotResponderTest.php` | Uji alur lengkap melalui retrieval generik, no truncation, dan tidak bocor `chatbot_notes`. |
| `tests/Unit/KnowledgeBaseRetrievalPipelineTest.php` | Uji ranking/regresi D1-D3 dan candidate alur Magang/PKL. |

## Checklist Eksekusi

1. Implementasikan `hasDirectMatch` di `LexicalKnowledgeBaseRetriever`; pertahankan bobot scoring dan tie-breaking yang sudah ada.
2. Tambahkan test unit retriever, termasuk section yang hanya mendapatkan dua poin dari dua token document title dan harus ditolak.
3. Implementasikan `topK=20` serta budget body 5.500 karakter di responder; hapus hard-code KB-007 dan semua helper khusus terkait.
4. Tambahkan/ubah test unit responder untuk section utuh, jawaban non-prosedur ringkas, dan anti-kebocoran `chatbot_notes`.
5. Jalankan regression D1-D3 dan query: alur utama pengajuan Magang, alur dari awal sampai akhir, tahapan PKL, Langkah 1, Langkah 5, penerbitan sertifikat, serta query alur/berurutan anti-kebocoran.
6. Jalankan seluruh test suite existing setelah targeted test lulus; jangan commit sebelum seluruh hasil ditinjau.

## Larangan

- Jangan menambahkan kembali ID `KB-007` di responder.
- Jangan memakai regex pertanyaan khusus Magang/PKL sebagai jalur retrieval alternatif.
- Jangan menjadikan document title sebagai bukti eligibility section.
- Jangan menampilkan frontmatter, `chatbot_notes`, skor internal, checksum, atau path sumber kepada pengguna.
