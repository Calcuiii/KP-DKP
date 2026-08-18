<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Provides the approved answers for the Magang / PKL and WOPPS quick questions.
 *
 * These answers are deliberately deterministic: the quick-question buttons
 * represent frequently requested official information and should not vary with
 * lexical retrieval ranking or an LLM response.
 */
final class OfficialChatbotFaqResponder
{
    /**
     * @return array{
     *     status: string,
     *     answer: string,
     *     sources: array<int, array{
     *         document_id: string,
     *         document_title: string,
     *         section_title: string
     *     }>
     * }|null
     */
    public function answer(string $question): ?array
    {
        $normalizedQuestion = $this->normalize($question);

        if ($this->isGuestbookAccessQuestion($normalizedQuestion)) {
            return $this->guestbookAccess();
        }

        if ($this->isRequestLetterContentsQuestion($normalizedQuestion)) {
            return $this->requestLetterContents();
        }

        if ($this->isRequestLetterTemplateQuestion($normalizedQuestion)) {
            return $this->requestLetterTemplate();
        }

        if ($this->isInternshipReportFormatQuestion($normalizedQuestion)) {
            return $this->internshipReportFormat();
        }

        return match ($normalizedQuestion) {
            'apa saja persyaratan pengajuan magang?' => $this->submissionRequirements(),
            'bagaimana alur pengajuan magang / pkl?' => $this->magangFlow(),
            'bagaimana ketentuan peserta selama magang?' => $this->participantConditions(),
            'bagaimana penerbitan surat keterangan dan sertifikat?' => $this->certificatePolicy(),
            'apa saja persyaratan pengajuan penelitian atau permintaan data?' => $this->woppsRequirements(),
            'bagaimana cara mengajukan wawancara atau observasi?' => $this->woppsSubmission(),
            'dokumen apa saja yang perlu disiapkan untuk wopps?' => $this->woppsDocuments(),
            'ke mana saya mengirimkan dokumen pengajuan?' => $this->woppsDestination(),
            default => null,
        };
    }

    private function isGuestbookAccessQuestion(string $normalizedQuestion): bool
    {
        if (! str_contains($normalizedQuestion, 'buku tamu')) {
            return false;
        }

        return $this->containsAny($normalizedQuestion, [
            'melalui apa',
            'di mana',
            'dimana',
            'link',
            'tautan',
            'cara isi',
            'cara mengisi',
            'mengisinya',
        ]);
    }

    private function guestbookAccess(): array
    {
        return $this->success(
            <<<'MARKDOWN'
Buku Tamu Magang / PKL diisi melalui Google Form resmi berikut:

[Isi Buku Tamu Magang / PKL](https://bit.ly/DaftarMagangPKL_DKP_JATIM)

Setelah selesai, simpan screenshot atau PDF bukti pengisian untuk diunggah pada Portal Peserta SI-MELAYUR.
MARKDOWN,
            [
                $this->source('KB-006', 'Layanan Pendaftaran Magang / PKL', 'Buku Tamu Magang / PKL'),
                $this->source('KB-007', 'Alur Utama Magang / Praktik Kerja Lapang (PKL)', 'Langkah 1: Isi Buku Tamu'),
            ],
        );
    }

    private function isRequestLetterContentsQuestion(string $normalizedQuestion): bool
    {
        if (! str_contains($normalizedQuestion, 'surat permohonan')) {
            return false;
        }

        return $this->containsAny($normalizedQuestion, [
            'apa saja informasi',
            'informasi yang perlu',
            'apa yang perlu',
            'harus ada',
            'harus dicantumkan',
            'wajib dicantumkan',
            'isi surat',
            'memuat apa',
        ]);
    }

    private function requestLetterContents(): array
    {
        return $this->success(
            <<<'MARKDOWN'
Surat Permohonan Magang / PKL perlu memuat informasi berikut:

1. Nama lengkap setiap peserta.
2. NIS atau NIM setiap peserta.
3. Judul kegiatan.
4. Tema kegiatan.
5. Kompetensi keahlian atau program studi.
6. Periode kegiatan, termasuk tanggal mulai dan selesai yang diajukan.
7. Sekolah atau fakultas asal.
8. Lokasi kegiatan yang dituju.
9. Universitas atau institusi pendidikan asal.
10. Jumlah peserta dan nomor WhatsApp perwakilan untuk koordinasi.

Surat resmi dibuat oleh institusi pendidikan dan ditujukan kepada Kepala Dinas Kelautan dan Perikanan Provinsi Jawa Timur. Jika peserta membutuhkan sertifikat, kebutuhan tersebut juga harus dinyatakan secara jelas dalam surat permohonan.

[Buka Template Surat Permohonan](https://bit.ly/Surat_Permohonan_DKP)
MARKDOWN,
            [
                $this->source('KB-004', 'Informasi Wajib dalam Surat Permohonan Magang / PKL', 'Informasi Wajib'),
                $this->source('KB-010', 'Contoh Surat Permohonan Magang dan PKL', 'Struktur Surat'),
            ],
        );
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{status: string, answer: string, sources: array<int, array{document_id: string, document_title: string, section_title: string}>}
     */
    private function requestLetterTemplate(): array
    {
        return $this->success(
            <<<'MARKDOWN'
Ya, template Surat Permohonan Magang / PKL tersedia melalui tautan resmi berikut:

[Buka Template Surat Permohonan](https://bit.ly/Surat_Permohonan_DKP)

Template tersebut dapat digunakan sebagai referensi struktur surat. Surat resmi tetap dibuat dan diterbitkan oleh sekolah, perguruan tinggi, atau institusi pendidikan asal peserta.

Pastikan surat mencantumkan identitas peserta, judul atau tema kegiatan, kompetensi keahlian, periode kegiatan, lokasi tujuan, institusi asal, jumlah peserta, serta nomor WhatsApp perwakilan untuk koordinasi.
MARKDOWN,
            [
                $this->source('KB-004', 'Informasi Wajib dalam Surat Permohonan Magang / PKL', 'Persyaratan Pengajuan — Template Surat Permohonan'),
                $this->source('KB-010', 'Contoh Surat Permohonan Magang dan PKL', 'Ruang Lingkup'),
            ],
        );
    }

    private function isRequestLetterTemplateQuestion(string $normalizedQuestion): bool
    {
        $mentionsTemplate = str_contains($normalizedQuestion, 'template')
            || str_contains($normalizedQuestion, 'contoh surat')
            || str_contains($normalizedQuestion, 'format surat');

        return $mentionsTemplate
            && str_contains($normalizedQuestion, 'surat')
            && str_contains($normalizedQuestion, 'permohonan');
    }

    private function isInternshipReportFormatQuestion(string $normalizedQuestion): bool
    {
        if (! str_contains($normalizedQuestion, 'laporan')) {
            return false;
        }

        return $this->containsAny($normalizedQuestion, [
            'format',
            'template',
            'ketentuan dinas',
            'ketentuan kampus',
            'ketentuan sekolah',
            'institusi asal',
        ]);
    }

    /**
     * @return array{status: string, answer: string, sources: array<int, array{document_id: string, document_title: string, section_title: string}>}
     */
    private function internshipReportFormat(): array
    {
        return $this->success(
            <<<'MARKDOWN'
Laporan kegiatan Magang / PKL dibuat mengikuti **format atau template dari institusi pendidikan asal**, yaitu kampus atau sekolah peserta. Jadi, laporan tidak menggunakan format pribadi dan dokumen resmi yang tersedia tidak menetapkan format khusus dari Dinas.

Setelah selesai, laporan dikirimkan dalam bentuk **PDF**.
MARKDOWN,
            [
                $this->source('KB-001', 'Ketentuan Umum Peserta Magang / PKL', 'Laporan'),
                $this->source('KB-002', 'Penerbitan Surat Keterangan dan Sertifikat', 'Laporan Hasil'),
            ],
        );
    }

    /**
     * @return array{status: string, answer: string, sources: array<int, array{document_id: string, document_title: string, section_title: string}>}
     */
    private function submissionRequirements(): array
    {
        return $this->success(
            <<<'MARKDOWN'
Untuk mengajukan Magang atau PKL, calon peserta perlu:

- Mengisi Buku Tamu Magang / PKL melalui `bit.ly/DaftarMagangPKL_DKP_JATIM`.
- Berkoordinasi mengenai ketersediaan kuota dan kesesuaian program studi atau jurusan.
- Menyiapkan Surat Permohonan resmi dari institusi pendidikan.
- Memastikan Surat Permohonan memuat:
  - nama lengkap;
  - NIS atau NIM tiap peserta;
  - judul dan tema kegiatan;
  - kompetensi keahlian;
  - periode kegiatan;
  - sekolah atau fakultas;
  - lokasi kegiatan;
  - universitas asal;
  - jumlah peserta; dan
  - nomor WhatsApp perwakilan untuk koordinasi.
- Bila membutuhkan sertifikat, kebutuhan tersebut harus dicantumkan dengan jelas dalam Surat Permohonan.
- Mengisi Form Pelaksanaan Magang / PKL sesuai jenjang pendidikan.

Template Surat Permohonan tersedia di https://bit.ly/Surat_Permohonan_DKP.
MARKDOWN,
            [
                $this->source('KB-004', 'Informasi Wajib dalam Surat Permohonan Magang / PKL', 'Persyaratan Pengajuan — Ruang Lingkup'),
                $this->source('KB-007', 'Alur Utama Magang / Praktik Kerja Lapang (PKL)', 'Tahap 1: Pengajuan'),
            ],
        );
    }

    /**
     * @return array{status: string, answer: string, sources: array<int, array{document_id: string, document_title: string, section_title: string}>}
     */
    private function magangFlow(): array
    {
        return $this->success(
            <<<'MARKDOWN'
Alur Magang / PKL terdiri dari tahap pengajuan, konfirmasi, pelaksanaan, hingga penyelesaian:

1. Isi Buku Tamu Magang / PKL.
2. Koordinasi ketersediaan kuota serta kesesuaian jurusan.
3. Ajukan Surat Permohonan dari institusi pendidikan.
4. Isi Form Pelaksanaan Magang / PKL sesuai jenjang pendidikan.
5. Dinas memproses permohonan dan menerbitkan Surat Balasan.
6. Peserta melaksanakan Magang / PKL sesuai ketentuan.
7. Peserta menyusun laporan kegiatan.
8. Peserta menyiapkan bahan presentasi.
9. Peserta melakukan presentasi hasil kegiatan.
10. Peserta mengisi Form Selesai Magang / PKL.
11. Proses dokumen akhir dilakukan sesuai ketentuan yang berlaku.

Pengisian Form Pelaksanaan belum berarti permohonan diterima. Keputusan Dinas disampaikan melalui Surat Balasan.
MARKDOWN,
            [
                $this->source('KB-007', 'Alur Utama Magang / Praktik Kerja Lapang (PKL)', 'Ringkasan Alur'),
            ],
        );
    }

    /**
     * @return array{status: string, answer: string, sources: array<int, array{document_id: string, document_title: string, section_title: string}>}
     */
    private function participantConditions(): array
    {
        return $this->success(
            <<<'MARKDOWN'
Selama Magang / PKL, peserta wajib:

- Mengikuti jam kerja: Senin–Kamis pukul 07.30–16.00 WIB, dan Jumat pukul 07.00–16.30 WIB.
- Mengikuti kegiatan yang berlaku, termasuk upacara atau kegiatan insidental.
- Mematuhi aturan kantor serta menjaga etika dan tata krama terhadap pegawai maupun rekan kerja.
- Menanggung biaya pelaksanaan secara mandiri.
- Bersedia ditempatkan pada unit kerja yang membutuhkan.
- Menjaga keamanan dan kerahasiaan dokumen negara yang bersifat rahasia.
- Membuat laporan sesuai format institusi asal dan mengirimkannya dalam bentuk PDF.
- Melakukan presentasi hasil pada minggu terakhir; pelaksanaannya bersifat tentatif.

Pelanggaran dapat dikenai pembinaan, penundaan sertifikat, hingga penghentian kegiatan sesuai tingkat pelanggaran.
MARKDOWN,
            [
                $this->source('KB-001', 'Ketentuan Umum Peserta Magang dan PKL', 'Ruang Lingkup'),
            ],
        );
    }

    /**
     * @return array{status: string, answer: string, sources: array<int, array{document_id: string, document_title: string, section_title: string}>}
     */
    private function certificatePolicy(): array
    {
        return $this->success(
            <<<'MARKDOWN'
Berdasarkan kebijakan terbaru, DKP Jawa Timur tidak lagi menerbitkan Surat Keterangan Magang, PKL, atau Penelitian.

Namun, sertifikat tetap dapat diterbitkan. Untuk mengajukannya:

1. Kebutuhan sertifikat harus dicantumkan secara jelas dalam Surat Permohonan awal dari sekolah atau perguruan tinggi.
2. Peserta menyelesaikan kegiatan, laporan, dan presentasi sesuai ketentuan.
3. Peserta mengumpulkan hasil presentasi serta laporan melalui Form Selesai Magang / PKL sesuai jenjang pendidikan.

Template Surat Permohonan dapat diakses di https://bit.ly/Surat_Permohonan_DKP.
MARKDOWN,
            [
                $this->source('KB-008', 'Kebijakan Surat Keterangan Magang, PKL, dan Penelitian', 'Surat Keterangan Magang, PKL, atau Penelitian'),
                $this->source('KB-002', 'Penerbitan Surat Keterangan dan Sertifikat', 'Tahap 3: Konfirmasi'),
            ],
        );
    }

    /**
     * @return array{status: string, answer: string, sources: array<int, array{document_id: string, document_title: string, section_title: string}>}
     */
    private function woppsRequirements(): array
    {
        return $this->success(
            <<<'MARKDOWN'
Untuk pengajuan penelitian, permintaan data atau informasi, wawancara, maupun observasi, siapkan dokumen berikut:

1. Identitas diri yang masih berlaku, berupa KTM, KTP, atau SIM.
2. Surat Permohonan Resmi dari institusi pendidikan asal yang memuat:
   - nama mahasiswa;
   - NIM;
   - semester;
   - program studi atau departemen;
   - fakultas;
   - universitas;
   - nama dosen pembimbing atau dosen lapangan beserta nomor WhatsApp aktif;
   - batas waktu kebutuhan data; dan
   - tujuan penggunaan data atau informasi.
3. Form Ethical Clearance atau Persetujuan Etik dengan mengisi melalui tautan berikut: [https://bit.ly/EASL-DKP](https://bit.ly/EASL-DKP)
4. Proposal kegiatan sesuai format institusi asal.

Pastikan seluruh dokumen lengkap dan sesuai ketentuan sebelum dikirimkan.
MARKDOWN,
            [
                $this->source('KB-009', 'Persyaratan Dokumen Pengajuan Penelitian, Permintaan Data, Informasi, Wawancara, dan Observasi', 'Ruang Lingkup'),
            ],
        );
    }

    /**
     * @return array{status: string, answer: string, sources: array<int, array{document_id: string, document_title: string, section_title: string}>}
     */
    private function woppsSubmission(): array
    {
        return $this->success(
            <<<'MARKDOWN'
Untuk mengajukan layanan wawancara atau observasi:

1. Siapkan identitas diri, surat permohonan resmi dari institusi, serta proposal kegiatan.
2. Lengkapi informasi dalam surat permohonan, termasuk tujuan kegiatan dan batas waktu kebutuhan data.
3. Unggah dokumen pengajuan melalui formulir layanan WOPPS di [https://bit.ly/WOPPS](https://bit.ly/WOPPS).
4. Pastikan dokumen telah lengkap agar proses pengajuan dapat berjalan dengan lancar.
MARKDOWN,
            [
                $this->source('KB-009', 'Persyaratan Dokumen Pengajuan Penelitian, Permintaan Data, Informasi, Wawancara, dan Observasi', 'Link Pengajuan'),
            ],
        );
    }

    /**
     * @return array{status: string, answer: string, sources: array<int, array{document_id: string, document_title: string, section_title: string}>}
     */
    private function woppsDocuments(): array
    {
        return $this->success(
            <<<'MARKDOWN'
Dokumen yang perlu disiapkan meliputi:

- Identitas diri yang masih berlaku: KTM, KTP, atau SIM.
- Surat Permohonan Resmi dari institusi pendidikan asal.
- Informasi mahasiswa, NIM, semester, program studi, fakultas, dan universitas.
- Nama dosen pembimbing atau dosen lapangan beserta nomor WhatsApp aktif.
- Tujuan penggunaan data atau informasi.
- Batas waktu kebutuhan data.
- Form Ethical Clearance atau Persetujuan Etik.
- Proposal kegiatan sesuai format institusi asal.
MARKDOWN,
            [
                $this->source('KB-009', 'Persyaratan Dokumen Pengajuan Penelitian, Permintaan Data, Informasi, Wawancara, dan Observasi', 'Ruang Lingkup'),
            ],
        );
    }

    /**
     * @return array{status: string, answer: string, sources: array<int, array{document_id: string, document_title: string, section_title: string}>}
     */
    private function woppsDestination(): array
    {
        return $this->success(
            <<<'MARKDOWN'
Dokumen pengajuan Wawancara, Observasi, Penelitian, Permintaan Data, atau layanan terkait dikirim melalui formulir WOPPS:

[https://bit.ly/WOPPS](https://bit.ly/WOPPS)

Jika memerlukan koordinasi, hubungi Bagian Umum dan Kepegawaian melalui WhatsApp:

**0852-53000-485**

Jam layanan yang tercantum adalah pukul **08.00–16.00 WIB**.
MARKDOWN,
            [
                $this->source('KB-009', 'Persyaratan Dokumen Pengajuan Penelitian, Permintaan Data, Informasi, Wawancara, dan Observasi', 'Link Pengajuan'),
                $this->source('KB-006', 'Layanan Pendaftaran Magang Kerja / Praktik Kerja Lapang', 'Kontak Layanan'),
            ],
        );
    }

    /**
     * @param  array<int, array{document_id: string, document_title: string, section_title: string}>  $sources
     * @return array{status: string, answer: string, sources: array<int, array{document_id: string, document_title: string, section_title: string}>}
     */
    private function success(string $answer, array $sources): array
    {
        return [
            'status' => GroundedChatbotResponder::STATUS_SUCCESS,
            'answer' => $answer,
            'sources' => $sources,
        ];
    }

    /**
     * @return array{document_id: string, document_title: string, section_title: string}
     */
    private function source(string $documentId, string $documentTitle, string $sectionTitle): array
    {
        return [
            'document_id' => $documentId,
            'document_title' => $documentTitle,
            'section_title' => $sectionTitle,
        ];
    }

    private function normalize(string $question): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $question) ?? $question), 'UTF-8');
    }
}
