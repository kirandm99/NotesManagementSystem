<?php

use App\Http\Controllers\NoteController;
use App\Http\Middleware\SimpleRateLimit;
use Illuminate\Support\Facades\Route;

Route::middleware(SimpleRateLimit::class.':120,1')->group(function (): void {
    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'service' => 'laravel-notes-api',
    ]));

    Route::get('/notes/search', [NoteController::class, 'search']);
    Route::post('/notes/{note}/summary', [NoteController::class, 'summary']);
    Route::apiResource('notes', NoteController::class);
});
