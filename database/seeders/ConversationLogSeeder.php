<?php

namespace Database\Seeders;

use App\Models\ConversationLog;
use Illuminate\Database\Seeder;

class ConversationLogSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code' => 'C-001', 'question' => 'Apa saja persyaratan untuk mengajukan magang di DKP?', 'category' => 'Persyaratan', 'status' => 'Dijawab', 'sources' => 3, 'score' => 0.92, 'response_time' => 1.2],
            ['code' => 'C-002', 'question' => 'Bagaimana alur pengajuan Kerja Praktik?', 'category' => 'Alur', 'status' => 'Dijawab', 'sources' => 2, 'score' => 0.88, 'response_time' => 0.9],
            ['code' => 'C-003', 'question' => 'Apakah siswa SMK bisa magang di DKP Jatim?', 'category' => 'Umum', 'status' => 'Dijawab', 'sources' => 1, 'score' => 0.79, 'response_time' => 1.5],
            ['code' => 'C-004', 'question' => 'Berapa lama proses verifikasi dokumen pengajuan?', 'category' => 'Alur', 'status' => 'Tidak Ditemukan', 'sources' => 0, 'score' => 0.34, 'response_time' => 2.1],
            ['code' => 'C-005', 'question' => 'Dokumen apa saja yang harus disiapkan untuk KP?', 'category' => 'Dokumen', 'status' => 'Dijawab', 'sources' => 4, 'score' => 0.95, 'response_time' => 1.1],
        ];

        foreach ($rows as $row) {
            ConversationLog::create($row);
        }
    }
}