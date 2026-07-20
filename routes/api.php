<?php

use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\CoachAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function (): void {
    Route::post('login', [AdminAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function (): void {
        Route::get('me', [AdminAuthController::class, 'me']);
        Route::post('logout', [AdminAuthController::class, 'logout']);
    });
});

Route::prefix('coach')->group(function (): void {
    Route::post('login', [CoachAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'role:coach'])->group(function (): void {
        Route::get('me', [CoachAuthController::class, 'me']);
        Route::post('logout', [CoachAuthController::class, 'logout']);
    });
});
