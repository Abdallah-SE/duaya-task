<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Employee;
use App\Models\Penalty;
use App\Models\ActivityLog;
use App\Models\IdleSetting;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $userSettings = \App\Models\IdleSetting::getDefault();
        
        // Load employee relationship if user is an employee
        $user->load('employee');
        
        // Get statistics
        $stats = [
            'totalActivities' => ActivityLog::count(),
            'activeUsers' => User::where('updated_at', '>=', now()->subHours(24))->count(),
            'totalEmployees' => Employee::count(),
            'idleSessions' => $this->getIdleSessionsCount(),
            'penalties' => Penalty::count(),
        ];
        
        // Get recent activities
        $recentActivities = ActivityLog::with(['user.employee'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'action' => $activity->action,
                    'user' => $activity->user ? [
                        'name' => $activity->user->name,
                        'employee' => $activity->user->employee,
                    ] : null,
                    'created_at' => $activity->created_at,
                ];
            });
        
        // Get user penalties
        $userPenalties = $user->penalties()
            ->latest('date')
            ->limit(5)
            ->get();
        
        // Debug: Log what we're sending to frontend
        Log::info('🔍 DashboardController called for user: ' . $user->id);
        $isIdleMonitoringEnabled = $user->isIdleMonitoringEnabled();
        Log::info('🔍 BACKEND DEBUG - DashboardController', [
            'user_id' => $user->id,
            'user_roles' => $user->getRoleNames()->toArray(),
            'isIdleMonitoringEnabled' => $isIdleMonitoringEnabled,
            'userSettings_idle_timeout' => $userSettings->idle_timeout,
            'userSettings_max_idle_warnings' => $userSettings->max_idle_warnings,
            'timestamp' => now()->toDateTimeString(),
        ]);

        return Inertia::render('Dashboard', [
            'user' => $user,
            'userSettings' => $userSettings,
            'initialSettings' => $userSettings, // Add this for IdleMonitor component
            'canControlIdleMonitoring' => $user->canControlIdleMonitoring(),
            'isIdleMonitoringEnabled' => $isIdleMonitoringEnabled,
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'userPenalties' => $userPenalties,
            'debug_timestamp' => now()->timestamp, // Force refresh
            'cache_buster' => uniqid(), // Additional cache busting
            'force_refresh' => microtime(true), // Microsecond precision
            'refresh_token' => md5(uniqid() . time()), // Additional cache busting
        ]);
    }
    
    private function getIdleSessionsCount()
    {
        // Count completed idle sessions (those that have been ended)
        return \App\Models\IdleSession::whereNotNull('idle_ended_at')->count();
    }
}
