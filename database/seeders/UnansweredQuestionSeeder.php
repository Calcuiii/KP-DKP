<?php

namespace Database\Seeders;

use App\Models\UnansweredQuestion;
use Illuminate\Database\Seeder;

class UnansweredQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['question' => 'Apakah ada kuota magang per semester?', 'category' => 'Kuota', 'frequency' => 12, 'score' => 0.42, 'first_asked' => '2024-03-20', 'last_asked' => '2024-04-10', 'status' => 'Baru'],
            ['question' => 'Bagaimana prosedur jika pembimbing lapangan berhalangan?', 'category' => 'Pelaksanaan', 'frequency' => 8, 'score' => 0.38, 'first_asked' => '2024-03-25', 'last_asked' => '2024-04-09', 'status' => 'Ditinjau'],
            ['question' => 'Apakah ada tunjangan untuk peserta magang?', 'category' => 'Tunjangan', 'frequency' => 6, 'score' => 0.29, 'first_asked' => '2024-04-01', 'last_asked' => '2024-04-08', 'status' => 'Baru'],
            ['question' => 'Kapan jadwal penerimaan magang periode berikutnya?', 'category' => 'Jadwal', 'frequency' => 15, 'score' => 0.45, 'first_asked' => '2024-03-15', 'last_asked' => '2024-04-10', 'status' => 'Perlu Update KB'],
        ];

        foreach ($rows as $row) {
            UnansweredQuestion::create($row + [
                'fallback_response' => 'Maaf, saya belum menemukan informasi yang cukup untuk menjawab pertanyaan tersebut berdasarkan dokumen yang tersedia.',
            ]);
        }
    }
}