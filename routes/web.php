<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IdleMonitoringController;

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth', 'log.activity'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    
    // Activities
    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
    Route::get('/activities/user/{user}', [ActivityController::class, 'getUserActivities'])->name('activities.user');
    Route::post('/activities/penalty', [ActivityController::class, 'applyPenalty'])->name('activities.penalty');
    
    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/global', [SettingsController::class, 'updateGlobalSettings'])->name('settings.global');
});

// API routes for idle monitoring
Route::prefix('api/idle-monitoring')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/start-session', [IdleMonitoringController::class, 'startIdleSession']);
    Route::post('/end-session', [IdleMonitoringController::class, 'endIdleSession']);
    Route::post('/handle-warning', [IdleMonitoringController::class, 'handleIdleWarning']);
    Route::get('/settings', [IdleMonitoringController::class, 'getSettings']);
    Route::post('/update-settings', [IdleMonitoringController::class, 'updateSettings']);
});
Route::get('/test-tailwind', function () { return Inertia::render('TestTailwind'); });
