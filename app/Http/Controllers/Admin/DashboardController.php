<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $metrics = [
            ['icon' => 'message-square', 'label' => 'Total Percakapan', 'value' => '1,284', 'sub' => '+12% bulan ini', 'color' => 'ocean'],
            ['icon' => 'hash', 'label' => 'Total Pertanyaan', 'value' => '3,571', 'sub' => '↑ 8% dari bulan lalu', 'color' => 'teal'],
            ['icon' => 'activity', 'label' => 'Pertanyaan Hari Ini', 'value' => '47', 'sub' => 'Diperbarui real-time', 'color' => 'indigo'],
            ['icon' => 'database', 'label' => 'Knowledge Base Aktif', 'value' => '6', 'sub' => '2 dalam proses', 'color' => 'amber'],
            ['icon' => 'inbox', 'label' => 'Pertanyaan Tidak Terjawab', 'value' => '42', 'sub' => '4 baru hari ini', 'color' => 'red'],
            ['icon' => 'thumbs-up', 'label' => 'Feedback Positif', 'value' => '86%', 'sub' => 'Dari 512 feedback', 'color' => 'teal'],
            ['icon' => 'clock', 'label' => 'Rata-rata Response Time', 'value' => '1.3s', 'sub' => 'Stabil minggu ini', 'color' => 'ocean'],
            ['icon' => 'star', 'label' => 'Satisfaction Rate', 'value' => '4.2/5', 'sub' => 'Berdasarkan rating', 'color' => 'amber'],
        ];

        $trend = collect(range(1, 30))->map(fn ($day) => [
            'day' => (string) $day,
            'pertanyaan' => rand(20, 80),
            'dijawab' => rand(15, 65),
        ]);

        $statusData = [
            ['name' => 'Berhasil', 'value' => 72, 'color' => '#0D9E8A'],
            ['name' => 'Tidak Ditemukan', 'value' => 18, 'color' => '#F59E0B'],
            ['name' => 'Error', 'value' => 10, 'color' => '#EF4444'],
        ];

        $categoryData = [
            ['name' => 'Persyaratan KP', 'value' => 38],
            ['name' => 'Alur Pengajuan', 'value' => 27],
            ['name' => 'Dokumen', 'value' => 19],
            ['name' => 'Pelaksanaan', 'value' => 11],
            ['name' => 'Sertifikat', 'value' => 5],
        ];

        $unanswered = [
            ['question' => 'Apakah ada kuota magang per semester?', 'freq' => 12, 'status' => 'Baru'],
            ['question' => 'Bagaimana prosedur jika pembimbing lapangan berhalangan?', 'freq' => 8, 'status' => 'Ditinjau'],
            ['question' => 'Apakah ada tunjangan untuk peserta magang?', 'freq' => 6, 'status' => 'Baru'],
        ];

        $recentQuestions = [
            ['question' => 'Apa saja persyaratan untuk mengajukan magang di DKP?', 'category' => 'Persyaratan', 'status' => 'Dijawab', 'time' => '2024-04-10 14:32', 'feedback' => 'Positif'],
            ['question' => 'Bagaimana alur pengajuan Kerja Praktik?', 'category' => 'Alur', 'status' => 'Dijawab', 'time' => '2024-04-10 13:15', 'feedback' => 'Positif'],
            ['question' => 'Apakah siswa SMK bisa magang di DKP Jatim?', 'category' => 'Umum', 'status' => 'Dijawab', 'time' => '2024-04-10 11:44', 'feedback' => 'Negatif'],
            ['question' => 'Berapa lama proses verifikasi dokumen pengajuan?', 'category' => 'Alur', 'status' => 'Tidak Ditemukan', 'time' => '2024-04-10 10:02', 'feedback' => '-'],
            ['question' => 'Dokumen apa saja yang harus disiapkan untuk KP?', 'category' => 'Dokumen', 'status' => 'Dijawab', 'time' => '2024-04-09 16:30', 'feedback' => 'Positif'],
        ];

        return view('pages.admin.dashboard', compact(
            'metrics', 'trend', 'statusData', 'categoryData', 'unanswered', 'recentQuestions'
        ));
    }
}