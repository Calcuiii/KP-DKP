<?php

use App\Http\Controllers\Api\VerifyAttendanceParticipantController;
use App\Http\Middleware\AuthenticateAttendanceService;
use Illuminate\Support\Facades\Route;

Route::post('/participants/verify', VerifyAttendanceParticipantController::class)
    ->middleware(['throttle:60,1', AuthenticateAttendanceService::class])
    ->name('api.participants.verify');
