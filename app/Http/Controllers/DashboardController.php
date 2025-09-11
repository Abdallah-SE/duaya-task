<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $userSettings = $user->getIdleSettings();
        
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
        
        return Inertia::render('Dashboard', [
            'user' => $user,
            'userSettings' => $userSettings,
            'initialSettings' => $userSettings, // Add this for IdleMonitor component
            'canControlIdleMonitoring' => $user->canControlIdleMonitoring(),
            'isIdleMonitoringEnabled' => $user->isIdleMonitoringEnabled(),
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'userPenalties' => $userPenalties,
        ]);
    }
    
    private function getIdleSessionsCount()
    {
        // This would typically check for sessions that haven't been active
        // For now, we'll return a placeholder
        return 0;
    }
}
