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
        
        // Get comprehensive task-specific statistics for admin dashboard
        $stats = [
            'totalActivities' => ActivityLog::count(),
            'idleSessions' => IdleSession::count(),
            'penalties' => Penalty::count(),
            'totalUsers' => User::count(),
            'totalEmployees' => Employee::count(),
            'todayActivities' => ActivityLog::whereDate('created_at', today())->count(),
            'thisWeekActivities' => ActivityLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'thisMonthActivities' => ActivityLog::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'totalIdleTime' => IdleSession::sum('duration_seconds'),
            'averageIdleTime' => IdleSession::avg('duration_seconds') ?? 0,
            'activeIdleSessions' => IdleSession::whereNull('idle_ended_at')->count(),
            'completedIdleSessions' => IdleSession::whereNotNull('idle_ended_at')->count(),
        ];
        
        // Get recent activities with more details for admin
        $recentActivities = ActivityLog::with(['user.employee'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'action' => $activity->action,
                    'subject_type' => $activity->subject_type,
                    'subject_id' => $activity->subject_id,
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

        // Get CRUD operations breakdown
        $crudBreakdown = ActivityLog::selectRaw('
            CASE 
                WHEN action LIKE "%create%" OR action = "create" THEN "Create"
                WHEN action LIKE "%read%" OR action = "read" OR action LIKE "%view%" THEN "Read"
                WHEN action LIKE "%update%" OR action = "update" OR action LIKE "%edit%" THEN "Update"
                WHEN action LIKE "%delete%" OR action = "delete" OR action LIKE "%remove%" THEN "Delete"
                ELSE "Other"
            END as operation_type,
            COUNT(*) as count
        ')
        ->groupBy('operation_type')
        ->orderBy('count', 'desc')
        ->get();

        // Get employee activity statistics
        $employeeActivityStats = ActivityLog::with('user.employee')
            ->selectRaw('
                user_id,
                COUNT(*) as activity_count,
                COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_activities,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as week_activities
            ')
            ->whereHas('user.employee')
            ->groupBy('user_id')
            ->orderBy('activity_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($stat) {
                return [
                    'user' => $stat->user ? [
                        'name' => $stat->user->name,
                        'email' => $stat->user->email,
                        'employee' => $stat->user->employee,
                    ] : null,
                    'activity_count' => $stat->activity_count,
                    'today_activities' => $stat->today_activities,
                    'week_activities' => $stat->week_activities,
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
            'crudBreakdown' => $crudBreakdown,
            'employeeActivityStats' => $employeeActivityStats,
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
