<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IdleMonitoringController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmployeeController;

// Authentication routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    // Separate login routes
    Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'adminLogin']);
    Route::get('/employee/login', [AuthController::class, 'showEmployeeLogin'])->name('employee.login');
    Route::post('/employee/login', [AuthController::class, 'employeeLogin']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth', 'log.activity'])->group(function () {
    // General Dashboard (fallback)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    
    // Admin Dashboard
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        
        // User Management Routes
        Route::resource('users', UserController::class)->names('admin.users');
        
        // Employee Management Routes
        Route::resource('employees', EmployeeController::class)->names('admin.employees');
    });
    
    // Employee Dashboard
    Route::prefix('employee')->middleware('role:employee')->group(function () {
        Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
        
        // Employee User Management Routes (employees can manage other employees)
        Route::resource('users', UserController::class)->names('employee.users');
        
        // Employee Management Routes
        Route::resource('employees', EmployeeController::class)->names('employee.employees');
    });
    
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
Route::prefix('api/idle-monitoring')->middleware(['auth', 'log.activity', 'web'])->group(function () {
    Route::post('/start-session', [IdleMonitoringController::class, 'startIdleSession']);
    Route::post('/end-session', [IdleMonitoringController::class, 'endIdleSession']);
    Route::post('/handle-warning', [IdleMonitoringController::class, 'handleIdleWarning']);
    Route::get('/settings', [IdleMonitoringController::class, 'getSettings']);
    Route::post('/update-settings', [IdleMonitoringController::class, 'updateSettings']);
    Route::get('/test-db', [IdleMonitoringController::class, 'testDatabase']);
    Route::get('/stats', [IdleMonitoringController::class, 'getIdleStats']);
    
    // Role-based idle monitoring management (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/role-settings', [IdleMonitoringController::class, 'getRoleSettings']);
        Route::post('/role-settings', [IdleMonitoringController::class, 'updateRoleSettings']);
    });
});

Route::get('/debug-idle', function () {
    $user = Auth::user();
    if (!$user) {
        return response()->json(['error' => 'Not authenticated']);
    }
    
    $userSettings = $user->getIdleSettings();
    $isIdleMonitoringEnabled = $user->isIdleMonitoringEnabled();
    
    // Check IdleSession table
    $idleSessions = \App\Models\IdleSession::where('user_id', $user->id)->get();
    $totalIdleSessions = \App\Models\IdleSession::count();
    
    return response()->json([
        'user_id' => $user->id,
        'user_roles' => $user->getRoleNames()->toArray(),
        'isIdleMonitoringEnabled' => $isIdleMonitoringEnabled,
        'role_setting_check' => \App\Models\RoleSetting::isIdleMonitoringEnabledForRole('employee'),
        'userSettings' => $userSettings->toArray(),
        'idle_sessions_for_user' => $idleSessions->toArray(),
        'total_idle_sessions' => $totalIdleSessions,
        'timestamp' => now()->toDateTimeString(),
    ]);
});

// Web route for idle monitoring (better CSRF handling)
Route::post('/idle-monitoring/handle-warning', [IdleMonitoringController::class, 'handleIdleWarning'])->middleware(['auth', 'log.activity']);
