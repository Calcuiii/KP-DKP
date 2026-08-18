<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ConversationLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InfographicController;
use App\Http\Controllers\Admin\KnowledgeBaseController;
use App\Http\Controllers\Admin\UnansweredQuestionController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\ParticipantAuthController;
use App\Http\Controllers\Auth\ParticipantEmailVerificationController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\GuestbookCheckinController;
use App\Http\Controllers\Peserta\ParticipantApplicationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\InternshipLocationController;
use App\Http\Controllers\Admin\DocumentReviewController;

Route::view('/', 'pages.landing')->name('landing');

Route::view('/infografis', 'pages.infographics')->name('infographics');

Route::get('/buku-tamu', [GuestbookCheckinController::class, 'show'])
    ->name('guestbook.checkin');
Route::post('/buku-tamu/selesai', [GuestbookCheckinController::class, 'complete'])
    ->name('guestbook.complete');

Route::get('/chatbot', [ChatbotController::class, 'index'])
    ->middleware('guestbook')
    ->name('chatbot');

Route::prefix('api/chatbot')
    ->name('chatbot.api.')
    ->middleware(['throttle:60,1', 'guestbook'])
    ->group(function (): void {
        Route::get('/history', [ChatbotController::class, 'history'])
            ->name('history');

        Route::get('/conversations/{conversation}', [ChatbotController::class, 'conversation'])
            ->name('conversation');

        Route::post('/messages', [ChatbotController::class, 'send'])
            ->name('messages.send');

        Route::post('/messages/{message}/feedback', [ChatbotController::class, 'feedback'])
            ->name('messages.feedback');

        Route::post('/messages/{message}/escalate', [ChatbotController::class, 'escalate'])
            ->name('messages.escalate');
    });

Route::prefix('akun')->name('peserta.')->middleware('guest:peserta')->group(function (): void {
    Route::get('/masuk', [ParticipantAuthController::class, 'createLogin'])->name('login');
    Route::post('/masuk', [ParticipantAuthController::class, 'storeLogin'])->name('login.store');
    Route::get('/daftar', [ParticipantAuthController::class, 'createRegister'])->name('register');
    Route::post('/daftar', [ParticipantAuthController::class, 'storeRegister'])->name('register.store');
});

Route::middleware('auth:peserta')->group(function (): void {
    Route::prefix('akun')->group(function (): void {
        Route::get('/verifikasi-email', [ParticipantEmailVerificationController::class, 'notice'])
            ->name('verification.notice');
        Route::get('/verifikasi-email/{id}/{hash}', [ParticipantEmailVerificationController::class, 'verify'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');
        Route::post('/email/verification-notification', [ParticipantEmailVerificationController::class, 'send'])
            ->middleware('throttle:3,1')
            ->name('verification.send');
    });

    Route::prefix('peserta')->name('peserta.')->group(function (): void {
        Route::get('/dashboard', [ParticipantAuthController::class, 'dashboard'])
            ->middleware('verified')
            ->name('dashboard');
        Route::post('/persiapan-pengajuan', [ParticipantApplicationController::class, 'store'])
            ->middleware('verified')
            ->name('application.store');
        Route::post('/bukti-buku-tamu', [ParticipantApplicationController::class, 'storeGuestbookProof'])
            ->middleware('verified')->name('guestbook-proof.store');
        Route::post('/surat-permohonan', [ParticipantApplicationController::class, 'storeRequestLetter'])
            ->middleware('verified')->name('request-letter.store');
        Route::post('/konfirmasi-google-form', [ParticipantApplicationController::class, 'confirmGoogleForm'])
            ->middleware('verified')->name('google-form.confirm');
        Route::get('/dokumen/{document}/unduh', [ParticipantApplicationController::class, 'downloadDocument'])
            ->middleware('verified')->name('document.download');
        Route::post('/keluar', [ParticipantAuthController::class, 'destroy'])->name('logout');

        // WOPPS
        Route::post('/wopps/dokumen', [ParticipantApplicationController::class, 'storeWoppsDocument'])
            ->middleware('verified')
            ->name('wopps.document.store');

        Route::post('/wopps/cek-kelengkapan', [ParticipantApplicationController::class, 'checkWoppsCompleteness'])
            ->middleware('verified')
            ->name('wopps.completeness.check');

        Route::post('/wopps/google-form', [ParticipantApplicationController::class, 'confirmWoppsGoogleForm'])
            ->middleware('verified')
            ->name('wopps.google-form');
    });
});

Route::middleware('guest')->group(function (): void {
    Route::get('/admin-login', [AdminLoginController::class, 'create'])
        ->name('admin.login');

    Route::post('/admin-login', [AdminLoginController::class, 'store'])
        ->name('admin-login.store');
});

Route::middleware(['auth:web', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/conversation-logs', [ConversationLogController::class, 'index'])->name('admin.conversation-logs');
    Route::get('/conversation-logs/export', [ConversationLogController::class, 'export'])->name('admin.conversation-logs.export');

    Route::get('/unanswered-questions', [UnansweredQuestionController::class, 'index'])->name('admin.unanswered-questions');
    Route::get('/unanswered-questions/{escalation}', [UnansweredQuestionController::class, 'show'])->name('admin.unanswered-questions.show');
    Route::post('/unanswered-questions/{escalation}/respond', [UnansweredQuestionController::class, 'respond'])->name('admin.unanswered-questions.respond');
    Route::post('/unanswered-questions/{escalation}/resolve', [UnansweredQuestionController::class, 'markResolved'])->name('admin.unanswered-questions.resolve');

    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('admin.analytics');
    Route::get('/analytics/export', [AnalyticsController::class, 'export'])->name('admin.analytics.export');

    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('admin.activity-log');

    Route::get('/lokasi-kp', [InternshipLocationController::class, 'index'])->name('admin.internship-locations');
    Route::patch('/lokasi-kp/{location}', [InternshipLocationController::class, 'update'])->name('admin.internship-locations.update');

        Route::get('/pemeriksaan-dokumen', [DocumentReviewController::class, 'index'])
        ->name('admin.pemeriksaan-dokumen');

    Route::get('/pemeriksaan-dokumen/{document}', [DocumentReviewController::class, 'show'])
        ->name('admin.pemeriksaan-dokumen.show');

    Route::patch('/pemeriksaan-dokumen/{document}/approve', [DocumentReviewController::class, 'approve'])
        ->name('admin.pemeriksaan-dokumen.approve');

    Route::patch('/pemeriksaan-dokumen/{document}/revision', [DocumentReviewController::class, 'revision'])
        ->name('admin.pemeriksaan-dokumen.revision');

    Route::get('/pemeriksaan-dokumen/{document}/unduh', [DocumentReviewController::class, 'download'])
        ->name('admin.pemeriksaan-dokumen.download');

    Route::middleware('superadmin')->group(function (): void {
        Route::get('/knowledge-base', [KnowledgeBaseController::class, 'index'])->name('admin.knowledge-base');
        Route::post('/knowledge-base', [KnowledgeBaseController::class, 'store'])->name('admin.knowledge-base.store');
        Route::get('/knowledge-base/{document}', [KnowledgeBaseController::class, 'show'])->name('admin.knowledge-base.show');
        Route::get('/knowledge-base/{document}/download', [KnowledgeBaseController::class, 'download'])->name('admin.knowledge-base.download');
        Route::delete('/knowledge-base/{document}', [KnowledgeBaseController::class, 'destroy'])->name('admin.knowledge-base.destroy');
        Route::post('/knowledge-base/{document}/reindex', [KnowledgeBaseController::class, 'reindex'])->name('admin.knowledge-base.reindex');

        Route::get('/infografis', [InfographicController::class, 'index'])->name('admin.infographics');
        Route::get('/infografis/{infographic}/edit', [InfographicController::class, 'edit'])->name('admin.infographics.edit');
        Route::put('/infografis/{infographic}', [InfographicController::class, 'update'])->name('admin.infographics.update');

        Route::get('/manajemen-admin', [AdminUserController::class, 'index'])->name('admin.manajemen-admin');
        Route::post('/manajemen-admin', [AdminUserController::class, 'store'])->name('admin.manajemen-admin.store');
        Route::get('/manajemen-admin/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.manajemen-admin.edit');
        Route::put('/manajemen-admin/{user}', [AdminUserController::class, 'update'])->name('admin.manajemen-admin.update');
        Route::post('/manajemen-admin/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.manajemen-admin.toggle-status');
        Route::delete('/manajemen-admin/{user}', [AdminUserController::class, 'destroy'])->name('admin.manajemen-admin.destroy');
    });

    Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('admin.logout');
});
