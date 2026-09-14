<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\InternshipLocation;
use App\Models\KnowledgeBaseDocument;
use App\Models\ParticipantApplication;
use App\Models\ParticipantApplicationDocument;
use App\Models\ReplyLetter;
use App\Support\KnowledgeBaseCategoryResolver;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(KnowledgeBaseCategoryResolver $resolver): View
    {
        $totalConversations = ChatConversation::count();
        $today = ChatMessage::where('role', 'user')->whereDate('created_at', today())->count();

        $unanswered = ChatMessage::where('role', 'assistant')->where('status', 'insufficient_information')->count();

        $avgResponseMs = ChatMessage::where('role', 'assistant')->whereNotNull('response_time_ms')->avg('response_time_ms');
        $avgResponseSeconds = $avgResponseMs ? round($avgResponseMs / 1000, 1) : 0;

        $metrics = [
            ['icon' => 'message-square', 'label' => 'Total Percakapan', 'value' => number_format($totalConversations), 'sub' => null, 'color' => 'ocean'],
            ['icon' => 'activity', 'label' => 'Pertanyaan Hari Ini', 'value' => (string) $today, 'sub' => 'Diperbarui real-time', 'color' => 'sky'],
            ['icon' => 'inbox', 'label' => 'Pertanyaan Tidak Terjawab', 'value' => (string) $unanswered, 'sub' => null, 'color' => 'amber'],
            ['icon' => 'clock', 'label' => 'Rata-rata Response Time', 'value' => $avgResponseSeconds.'s', 'sub' => null, 'color' => 'purple'],
        ];

        $trend = collect(range(29, 0))->map(function ($daysAgo) {
            $date = Carbon::now()->subDays($daysAgo);

            return [
                'day' => $date->format('d'),
                'pertanyaan' => ChatMessage::where('role', 'user')->whereDate('created_at', $date)->count(),
                'dijawab' => ChatMessage::where('role', 'assistant')->where('status', 'success')->whereDate('created_at', $date)->count(),
            ];
        });

        $successCount = ChatMessage::where('role', 'assistant')->where('status', 'success')->count();
        $insufficientCount = ChatMessage::where('role', 'assistant')->where('status', 'insufficient_information')->count();
        $totalAnswered = $successCount + $insufficientCount;

        $statusData = [
            ['name' => 'Berhasil', 'value' => $totalAnswered > 0 ? round(($successCount / $totalAnswered) * 100) : 0, 'color' => '#0D9E8A'],
            ['name' => 'Tidak Ditemukan', 'value' => $totalAnswered > 0 ? round(($insufficientCount / $totalAnswered) * 100) : 0, 'color' => '#F59E0B'],
        ];

        $unansweredList = ChatMessage::where('role', 'assistant')
            ->where('status', 'insufficient_information')
            ->with('conversation')
            ->latest()
            ->take(3)
            ->get()
            ->map(fn ($m) => [
                'question' => optional($m->conversation->messages->where('role', 'user')->last())->content ?? '-',
                'time' => $m->created_at->diffForHumans(),
            ]);

        $recentQuestions = ChatMessage::where('role', 'user')
            ->with(['conversation.messages' => fn ($q) => $q->where('role', 'assistant')])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($userMsg) use ($resolver) {
                $answer = $userMsg->conversation->messages->where('role', 'assistant')->first();

                return [
                    'question' => $userMsg->content,
                    'category' => $answer && $answer->sources->first()
                        ? $resolver->categoryFor($answer->sources->first()->document_id)
                        : 'Umum',
                    'status' => $answer?->status === 'success' ? 'Dijawab' : 'Tidak Ditemukan',
                    'time' => $userMsg->created_at->format('Y-m-d H:i'),
                ];
            });

        // ── Portal Peserta ─────────────────────────────────────────────

        /*
        |--------------------------------------------------------------------------
        | "Dokumen Menunggu Review" HANYA menghitung dokumen yang:
        | 1. Jenisnya memang direview admin (surat permohonan / ethics approval),
        |    BUKAN bukti buku tamu / bukti Google Form / bukti form WOPPS
        |    (jenis-jenis itu tidak pernah melalui alur approve/revisi admin).
        | 2. Sudah LOLOS pengecekan otomatis ('passed') — kalau belum lolos,
        |    itu masih tugas peserta untuk memperbaiki, bukan tugas admin.
        | 3. Belum ada keputusan final admin (belum approved, belum revision_required).
        |--------------------------------------------------------------------------
        */
        $reviewableTypes = [
            ParticipantApplicationDocument::TYPE_REQUEST_LETTER,
            ParticipantApplicationDocument::TYPE_ETHICS_APPROVAL,
        ];

        $pendingDocumentsQuery = ParticipantApplicationDocument::query()
            ->whereIn('type', $reviewableTypes)
            ->where('automated_check_status', 'passed')
            ->whereNotIn('review_status', [
                ParticipantApplicationDocument::REVIEW_APPROVED,
                ParticipantApplicationDocument::REVIEW_REVISION,
            ]);

        $pendingDocuments = (clone $pendingDocumentsQuery)->count();

        $repliesSent = ReplyLetter::count();

        // Surat balasan HANYA berlaku untuk layanan Magang/PKL — WOPPS tidak
        // pernah mendapat surat balasan (tindak lanjutnya lewat WhatsApp PIC).
        $awaitingReplyLetterQuery = ParticipantApplication::query()
            ->where('service_type', ParticipantApplication::SERVICE_MAGANG_PKL)
            ->whereNotNull('google_form_confirmed_at')
            ->whereHas('participant', fn ($q) => $q->whereDoesntHave('replyLetter'));

        $awaitingReplyLetter = (clone $awaitingReplyLetterQuery)->count();

        $totalApplications = ParticipantApplication::count();

        $applicationsByService = ParticipantApplication::selectRaw('service_type, count(*) as total')
            ->groupBy('service_type')
            ->pluck('total', 'service_type');

        $locationsNeedingAttention = InternshipLocation::whereIn('quota_status', [
            InternshipLocation::QUOTA_FULL,
            InternshipLocation::QUOTA_UNAVAILABLE,
        ])->count();

        $totalQuotaRemaining = InternshipLocation::sum('quota_available');

        $portalMetrics = [
            ['icon' => 'file-check', 'label' => 'Dokumen Menunggu Review', 'value' => (string) $pendingDocuments, 'sub' => null, 'color' => 'rose'],
            ['icon' => 'mail', 'label' => 'Surat Balasan Belum Dikirim', 'value' => (string) $awaitingReplyLetter, 'sub' => null, 'color' => 'red'],
            ['icon' => 'users-round', 'label' => 'Total Pengajuan', 'value' => number_format($totalApplications), 'sub' => 'Magang/PKL: '.($applicationsByService[ParticipantApplication::SERVICE_MAGANG_PKL] ?? 0).' · WOPPS: '.($applicationsByService[ParticipantApplication::SERVICE_WOPPS] ?? 0), 'color' => 'emerald'],
            ['icon' => 'map-pin', 'label' => 'Lokasi Penuh / Tidak Menerima', 'value' => (string) $locationsNeedingAttention, 'sub' => 'Sisa kuota total: '.$totalQuotaRemaining, 'color' => 'orange'],
        ];

        $recentApplications = ParticipantApplication::with([
                'participant',
                'documents' => fn ($q) => $q->whereIn('type', $reviewableTypes)->latest(),
            ])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($application) {
                /*
                |--------------------------------------------------------------------------
                | Label "Tahap" mengikuti logika Status Proses di menu Pemeriksaan Dokumen,
                | berdasarkan dokumen reviewable (surat permohonan/ethics approval) terbaru
                | milik pengajuan ini. Kalau pengajuan sudah lebih maju dari tahap dokumen
                | (misal sudah isi Google Form), tampilkan progres yang lebih relevan.
                |--------------------------------------------------------------------------
                */
                $latestDocument = $application->documents->first();

                $stage = match (true) {
                    $application->google_form_confirmed_at !== null => 'Menunggu keputusan',

                    $latestDocument && $latestDocument->automated_check_status !== 'passed' => 'Menunggu perbaikan peserta',

                    $latestDocument && $latestDocument->review_status === ParticipantApplicationDocument::REVIEW_REVISION => 'Menunggu perbaikan peserta',

                    $latestDocument && $latestDocument->review_status === ParticipantApplicationDocument::REVIEW_APPROVED => 'Disetujui admin',

                    $latestDocument !== null => 'Siap diperiksa admin',

                    $application->guestbook_confirmed_at !== null => 'Persiapan dokumen',

                    default => 'Baru mendaftar',
                };

                return [
                    'name' => $application->participant?->name ?? '-',
                    'service' => $application->serviceLabel(),
                    'stage' => $stage,
                    'time' => $application->created_at->diffForHumans(),
                ];
            });

        $awaitingReplyLetterList = (clone $awaitingReplyLetterQuery)
            ->with('participant')
            ->oldest('google_form_confirmed_at')
            ->take(3)
            ->get()
            ->map(fn ($application) => [
                'name' => $application->participant?->name ?? '-',
                'time' => $application->google_form_confirmed_at?->diffForHumans() ?? '-',
            ]);

        return view('pages.admin.dashboard', compact(
            'metrics', 'trend', 'statusData', 'unansweredList', 'recentQuestions',
            'portalMetrics', 'recentApplications', 'awaitingReplyLetterList'
        ));
    }
}