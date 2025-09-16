<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\EmployeeDashboardController;
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
    
    // Admin authentication routes
    Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'login']);
    
    // Employee authentication routes
    Route::get('/employee/login', [EmployeeAuthController::class, 'showLogin'])->name('employee.login');                                                                                      
    Route::post('/employee/login', [EmployeeAuthController::class, 'login']);
});

// Logout routes
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
Route::post('/employee/logout', [EmployeeAuthController::class, 'logout'])->name('employee.logout');                                                                                          

// Admin Dashboard (web guard with admin role required)
Route::prefix('admin')->middleware(['auth:web', 'role:admin', 'log.activity'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');                                                                                            
    
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
Route::prefix('employee')->middleware(['auth:web', 'role:employee', 'log.activity'])->group(function () {                                                                                         
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
Route::middleware(['auth:web', 'log.activity'])->group(function () {
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
    
    
});

// CSRF token refresh endpoint (bypass Inertia)
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->name('csrf.token')->withoutMiddleware([\App\Http\Middleware\HandleInertiaRequests::class]);

// Idle monitoring route (CSRF protected by default)
Route::post('/idle-monitoring/handle-warning', [IdleMonitoringController::class, 'handleIdleWarning'])->middleware(['auth:web'])->name('idle-monitoring.handle-warning');


