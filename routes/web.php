<?php

use App\Http\Controllers\ChatbotController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.landing')->name('landing');

Route::get('/chatbot', [ChatbotController::class, 'index'])
    ->name('chatbot');

Route::prefix('api/chatbot')
    ->name('chatbot.api.')
    ->middleware('throttle:60,1')
    ->group(function (): void {
        Route::get('/history', [ChatbotController::class, 'history'])
            ->name('history');
        Route::get('/conversations/{conversation}', [ChatbotController::class, 'conversation'])
            ->name('conversation');
        Route::post('/messages', [ChatbotController::class, 'send'])
            ->name('messages.send');
        Route::post('/messages/{message}/feedback', [ChatbotController::class, 'feedback'])
            ->name('messages.feedback');
    });
