<?php

use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KnowledgeBaseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ConversationLogController;
use App\Http\Controllers\Admin\UnansweredQuestionController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AnalyticsController;

Route::view('/', 'pages.landing')->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/admin-login', [AdminLoginController::class, 'create'])->name('admin.login');
    Route::post('/admin-login', [AdminLoginController::class, 'store'])->name('admin-login.store');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/knowledge-base', [KnowledgeBaseController::class, 'index'])->name('admin.knowledge-base');
    Route::post('/knowledge-base', [KnowledgeBaseController::class, 'store'])->name('admin.knowledge-base.store');
    Route::delete('/knowledge-base/{document}', [KnowledgeBaseController::class, 'destroy'])->name('admin.knowledge-base.destroy');
    Route::post('/knowledge-base/{document}/reindex', [KnowledgeBaseController::class, 'reindex'])->name('admin.knowledge-base.reindex');

    Route::get('/conversation-logs', [ConversationLogController::class, 'index'])->name('admin.conversation-logs');
    Route::get('/conversation-logs/export', [ConversationLogController::class, 'export'])->name('admin.conversation-logs.export');

    Route::get('/unanswered-questions', [UnansweredQuestionController::class, 'index'])->name('admin.unanswered-questions');
    Route::post('/unanswered-questions/{question}/resolve', [UnansweredQuestionController::class, 'markResolved'])->name('admin.unanswered-questions.resolve');

    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('admin.analytics');
    Route::get('/analytics/export', [AnalyticsController::class, 'export'])->name('admin.analytics.export');
    
    Route::get('/manajemen-admin', [AdminUserController::class, 'index'])->name('admin.manajemen-admin');
    Route::post('/manajemen-admin', [AdminUserController::class, 'store'])->name('admin.manajemen-admin.store');
    Route::get('/manajemen-admin/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.manajemen-admin.edit');
    Route::put('/manajemen-admin/{user}', [AdminUserController::class, 'update'])->name('admin.manajemen-admin.update');
    Route::post('/manajemen-admin/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.manajemen-admin.toggle-status');
    Route::delete('/manajemen-admin/{user}', [AdminUserController::class, 'destroy'])->name('admin.manajemen-admin.destroy');

    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('admin.activity-log');

    Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('admin.logout');
});