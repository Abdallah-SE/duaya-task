<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Employee;
use App\Models\Penalty;
use App\Models\ActivityLog;
use App\Models\IdleSetting;
use App\Models\IdleSession;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        // Middleware is handled at route level
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $userSettings = $user->getIdleSettings();
        
        // Get comprehensive statistics for admin
        $stats = [
            'totalActivities' => ActivityLog::count(),
            'activeUsers' => User::where('updated_at', '>=', now()->subHours(24))->count(),
            'totalEmployees' => Employee::count(),
            'idleSessions' => IdleSession::count(),
            'activeIdleSessions' => IdleSession::active()->count(),
            'totalIdleTime' => IdleSession::sum('duration_seconds'),
            'penalties' => Penalty::count(),
            'penaltiesToday' => Penalty::whereDate('date', today())->count(),
            'adminUsers' => User::role('admin')->count(),
            'employeeUsers' => User::role('employee')->count(),
        ];
        
        // Get recent activities with more details for admin
        $recentActivities = ActivityLog::with(['user.employee'])
            ->latest()
            ->limit(15)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'action' => $activity->action,
                    'user' => $activity->user ? [
                        'name' => $activity->user->name,
                        'email' => $activity->user->email,
                        'employee' => $activity->user->employee,
                    ] : null,
                    'ip_address' => $activity->ip_address,
                    'device' => $activity->device,
                    'browser' => $activity->browser,
                    'created_at' => $activity->created_at,
                ];
            });
        
        // Get all penalties for admin view
        $allPenalties = Penalty::with('user.employee')
            ->latest('date')
            ->limit(10)
            ->get();
        
        // Get employee statistics
        $employeeStats = Employee::selectRaw('
            department,
            COUNT(*) as count
        ')
        ->groupBy('department')
        ->get();

        // Get idle session statistics by user
        $idleSessionStats = IdleSession::with('user')
            ->selectRaw('
                user_id,
                COUNT(*) as session_count,
                SUM(duration_seconds) as total_duration,
                AVG(duration_seconds) as avg_duration
            ')
            ->groupBy('user_id')
            ->orderBy('session_count', 'desc')
            ->limit(10)
            ->get();

        // Get activity statistics by action type
        $activityStats = ActivityLog::selectRaw('
            action,
            COUNT(*) as count
        ')
        ->groupBy('action')
        ->orderBy('count', 'desc')
        ->limit(10)
        ->get();
        
        return Inertia::render('Admin/Dashboard', [
            'user' => $user,
            'userSettings' => $userSettings,
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'allPenalties' => $allPenalties,
            'employeeStats' => $employeeStats,
            'idleSessionStats' => $idleSessionStats,
            'activityStats' => $activityStats,
            'greeting' => $this->getGreeting($user),
        ]);
    }
    
    private function getGreeting($user)
    {
        $hour = now()->hour;
        $name = $user->name;
        
        if ($hour < 12) {
            return "Good morning, Admin {$name}!";
        } elseif ($hour < 17) {
            return "Good afternoon, Admin {$name}!";
        } else {
            return "Good evening, Admin {$name}!";
        }
    }
}
