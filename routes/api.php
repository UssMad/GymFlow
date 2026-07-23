<?php

use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\AdminMemberController;
use App\Http\Controllers\Api\AdminMemberSubscriptionController;
use App\Http\Controllers\Api\CoachAiGenerationController;
use App\Http\Controllers\Api\CoachAuthController;
use App\Http\Controllers\Api\CoachMemberProgressController;
use App\Http\Controllers\Api\CoachProgrammeController;
use App\Http\Controllers\Api\CoachSportProfileController;
use App\Http\Controllers\Api\MemberAuthController;
use App\Http\Controllers\Api\MemberProgrammeController;
use App\Http\Controllers\Api\MemberWorkoutSessionController;
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
        Route::get('members/{member}/progress', [CoachMemberProgressController::class, 'show']);
        Route::post('members/{member}/ai-generations', [CoachAiGenerationController::class, 'store']);
        Route::get('ai-generations/{generation}', [CoachAiGenerationController::class, 'show']);
        Route::post('members/{member}/programmes', [CoachProgrammeController::class, 'store']);
        Route::get('programmes/{programme}', [CoachProgrammeController::class, 'show']);
        Route::put('programmes/{programme}', [CoachProgrammeController::class, 'update']);
        Route::post('programmes/{programme}/validate', [CoachProgrammeController::class, 'validateProgramme']);
        Route::post('programmes/{programme}/publish', [CoachProgrammeController::class, 'publish']);
    });
});

Route::prefix('member')->group(function (): void {
    Route::post('login', [MemberAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'role:member'])->group(function (): void {
        Route::get('me', [MemberAuthController::class, 'me']);
        Route::post('logout', [MemberAuthController::class, 'logout']);
        Route::get('programmes/current', [MemberProgrammeController::class, 'current']);
        Route::get('programmes/history', [MemberProgrammeController::class, 'history']);
        Route::get('programmes/{programme}', [MemberProgrammeController::class, 'show']);
        Route::put('workout-sessions/{workoutSession}/completion', [MemberWorkoutSessionController::class, 'complete']);
    });
});
