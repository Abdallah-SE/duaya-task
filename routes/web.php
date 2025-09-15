<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\EmployeeAuthController;
use App\Http\Controllers\IdleMonitoringController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\EmployeeSettingsController;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

// Authentication routes (guest only)
Route::middleware('guest')->group(function () {
    // General login route
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    // Admin authentication routes
    Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'login']);
    
    // Employee authentication routes
    Route::get('/employee/login', [EmployeeAuthController::class, 'showLogin'])->name('employee.login');
    Route::post('/employee/login', [EmployeeAuthController::class, 'login']);
});

// Logout routes
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
Route::post('/employee/logout', [EmployeeAuthController::class, 'logout'])->name('employee.logout');

// Admin Dashboard (web guard required)
Route::prefix('admin')->middleware(['auth', 'role:admin', 'log.activity'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    // User Management Routes
    Route::resource('users', UserController::class)->names('admin.users');
    
    // Employee Management Routes
    Route::resource('employees', EmployeeController::class)->names('admin.employees');
    
    // Admin Settings Routes
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('admin.settings');
    Route::put('/settings/global', [AdminSettingsController::class, 'updateGlobalSettings'])->name('admin.settings.global');
    Route::patch('/settings/global/timeout', [AdminSettingsController::class, 'updateTimeout'])->name('admin.settings.global.timeout');
    Route::put('/settings/roles', [AdminSettingsController::class, 'updateRoleSettings'])->name('admin.settings.roles');
    Route::patch('/settings/roles/toggle', [AdminSettingsController::class, 'toggleRoleMonitoring'])->name('admin.settings.roles.toggle');
    Route::post('/settings/reset', [AdminSettingsController::class, 'resetToDefaults'])->name('admin.settings.reset');
    Route::get('/settings/api', [AdminSettingsController::class, 'getSettings'])->name('admin.settings.api');
});

// Employee Dashboard (web guard required)
Route::prefix('employee')->middleware(['auth', 'role:employee', 'log.activity'])->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
    
    // Employee User Management Routes (employees can manage other employees)
    Route::resource('users', UserController::class)->names('employee.users');
    
    // Employee Management Routes
    Route::resource('employees', EmployeeController::class)->names('employee.employees');
    
    // Employee Settings Routes (read-only for employees)
    Route::get('/settings', [EmployeeSettingsController::class, 'index'])->name('employee.settings');
    Route::put('/settings', [EmployeeSettingsController::class, 'update'])->name('employee.settings.update');
    Route::get('/settings/api', [EmployeeSettingsController::class, 'getSettings'])->name('employee.settings.api');
    Route::get('/monitoring-status', [EmployeeSettingsController::class, 'getUserMonitoringStatus'])->name('employee.monitoring.status');
});

// General routes (both admin and web guards)
Route::middleware(['auth', 'log.activity'])->group(function () {
    // Home route - redirect to appropriate dashboard based on user role
    Route::get('/', function () {
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('employee')) {
            return redirect()->route('employee.dashboard');
        }
        // Fallback to admin dashboard if no specific role
        return redirect()->route('admin.dashboard');
    })->name('home');
    
    // Activities
    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
    Route::get('/activities/user/{user}', [ActivityController::class, 'getUserActivities'])->name('activities.user');
    Route::post('/activities/penalty', [ActivityController::class, 'applyPenalty'])->name('activities.penalty');
    
});

// All routes below have CSRF protection by default (Laravel built-in)

// CSRF token refresh endpoint
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->name('csrf.token');


// Web route for idle monitoring (CSRF protected by default)
Route::post('/idle-monitoring/handle-warning', [IdleMonitoringController::class, 'handleIdleWarning'])->middleware(['auth', 'log.activity']);

