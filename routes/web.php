<?php

use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\CoachDashboardController;
use App\Http\Controllers\Web\MemberDashboardController;
use App\Http\Controllers\Web\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebAuthController::class, 'home'])->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [WebAuthController::class, 'create'])->name('login');
    Route::post('/login', [WebAuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [WebAuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [WebAuthController::class, 'destroy'])->name('logout');

    Route::prefix('admin')->middleware('role:admin')->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/members/create', [AdminDashboardController::class, 'createMember'])->name('admin.members.create');
        Route::post('/members', [AdminDashboardController::class, 'storeMember'])->name('admin.members.store');
        Route::get('/members/{member}/edit', [AdminDashboardController::class, 'editMember'])->name('admin.members.edit');
        Route::put('/members/{member}', [AdminDashboardController::class, 'updateMember'])->name('admin.members.update');
        Route::post('/members/{member}/subscriptions', [AdminDashboardController::class, 'storeSubscription'])->name('admin.members.subscriptions.store');
        Route::post('/subscription-plans', [AdminDashboardController::class, 'storeSubscriptionPlan'])->name('admin.subscription-plans.store');
        Route::post('/members/{member}/attendance', [AdminDashboardController::class, 'storeAttendance'])->name('admin.attendance.store');
    });

    Route::prefix('coach')->middleware('role:coach')->group(function (): void {
        Route::get('/dashboard', [CoachDashboardController::class, 'index'])->name('coach.dashboard');
        Route::get('/members/{member}', [CoachDashboardController::class, 'showMember'])->name('coach.members.show');
        Route::put('/members/{member}/sport-profile', [CoachDashboardController::class, 'updateSportProfile'])->name('coach.members.sport-profile.update');
        Route::post('/members/{member}/ai-generations', [CoachDashboardController::class, 'generateProgramme'])->name('coach.members.ai-generations.store');
        Route::put('/programmes/{programme}', [CoachDashboardController::class, 'updateProgramme'])->name('coach.programmes.update');
        Route::post('/programmes/{programme}/validate', [CoachDashboardController::class, 'validateProgramme'])->name('coach.programmes.validate');
        Route::post('/programmes/{programme}/publish', [CoachDashboardController::class, 'publishProgramme'])->name('coach.programmes.publish');
    });

    Route::prefix('member')->middleware('role:member')->group(function (): void {
        Route::get('/dashboard', [MemberDashboardController::class, 'index'])->name('member.dashboard');
        Route::put('/workout-sessions/{workoutSession}/completion', [MemberDashboardController::class, 'completeWorkout'])
            ->name('member.workouts.complete');
        Route::put('/workout-sessions/{workoutSession}/missed', [MemberDashboardController::class, 'missWorkout'])
            ->name('member.workouts.missed');
    });
});
