<?php

use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.landing')->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/admin-login', [AdminLoginController::class, 'create'])->name('admin.login');
    Route::post('/admin-login', [AdminLoginController::class, 'store'])->name('admin-login.store');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('admin.logout');
});