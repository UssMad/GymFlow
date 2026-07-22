<?php

use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\AdminMemberController;
use App\Http\Controllers\Api\AdminMemberSubscriptionController;
use App\Http\Controllers\Api\CoachAiGenerationController;
use App\Http\Controllers\Api\CoachAuthController;
use App\Http\Controllers\Api\CoachSportProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function (): void {
    Route::post('login', [AdminAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function (): void {
        Route::get('me', [AdminAuthController::class, 'me']);
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::apiResource('members', AdminMemberController::class)->only(['index', 'store', 'show', 'update']);
        Route::get('members/{member}/subscriptions', [AdminMemberSubscriptionController::class, 'index']);
        Route::post('members/{member}/subscriptions', [AdminMemberSubscriptionController::class, 'store']);
    });
});

Route::prefix('coach')->group(function (): void {
    Route::post('login', [CoachAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'role:coach'])->group(function (): void {
        Route::get('me', [CoachAuthController::class, 'me']);
        Route::post('logout', [CoachAuthController::class, 'logout']);
        Route::get('members/{member}/sport-profile', [CoachSportProfileController::class, 'show']);
        Route::put('members/{member}/sport-profile', [CoachSportProfileController::class, 'update']);
        Route::post('members/{member}/ai-generations', [CoachAiGenerationController::class, 'store']);
    });
});
