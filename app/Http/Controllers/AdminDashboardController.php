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
        
        // Get recent activities with more details for admin - filter out redundant activities
        $recentActivities = $this->getFilteredRecentActivities();

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

    /**
     * Get filtered recent activities with enhanced details for admin
     */
    private function getFilteredRecentActivities()
    {
        // Get activities from the last 7 days to avoid too much data
        $activities = ActivityLog::with(['user.employee', 'user.roles'])
            ->where('created_at', '>=', now()->subDays(7))
            ->latest()
            ->get();

        // Filter out redundant activities
        $filteredActivities = $this->filterRedundantActivities($activities);

        return $filteredActivities->take(25)->map(function ($activity) {
            return [
                'id' => $activity->id,
                'action' => $activity->action,
                'subject_type' => $activity->subject_type,
                'subject_id' => $activity->subject_id,
                'user' => $activity->user ? [
                    'name' => $activity->user->name,
                    'email' => $activity->user->email,
                    'employee' => $activity->user->employee,
                    'roles' => $activity->user->roles->pluck('name')->toArray(),
                    'department' => $activity->user->employee?->department,
                    'job_title' => $activity->user->employee?->job_title,
                ] : null,
                'ip_address' => $activity->ip_address,
                'device' => $activity->device,
                'browser' => $activity->browser,
                'created_at' => $activity->created_at,
                'details' => $this->getActivityDetails($activity),
                'importance' => $this->getActivityImportance($activity),
            ];
        });
    }

    /**
     * Filter out redundant activities (consecutive similar actions by same user)
     */
    private function filterRedundantActivities($activities)
    {
        $filtered = collect();
        $lastActivity = null;

        foreach ($activities as $activity) {
            // Skip if it's a redundant view action
            if ($this->isRedundantActivity($activity, $lastActivity)) {
                continue;
            }

            $filtered->push($activity);
            $lastActivity = $activity;
        }

        return $filtered;
    }

    /**
     * Check if an activity is redundant
     */
    private function isRedundantActivity($current, $last)
    {
        if (!$last) {
            return false;
        }

        // Skip consecutive view actions by the same user within 5 minutes
        if ($current->user_id === $last->user_id && 
            $this->isViewAction($current->action) && 
            $this->isViewAction($last->action) &&
            $current->created_at->diffInMinutes($last->created_at) < 5) {
            return true;
        }

        // Skip repeated admin settings views within 2 minutes
        if ($current->user_id === $last->user_id && 
            $current->action === 'view_admin_settings' && 
            $last->action === 'view_admin_settings' &&
            $current->created_at->diffInMinutes($last->created_at) < 2) {
            return true;
        }

        return false;
    }

    /**
     * Check if action is a view action
     */
    private function isViewAction($action)
    {
        return str_contains($action, 'view') || 
               str_contains($action, 'read') || 
               str_contains($action, 'index') ||
               str_contains($action, 'show');
    }

    /**
     * Get detailed information about the activity
     */
    private function getActivityDetails($activity)
    {
        $details = [];

        // Add specific details based on action type
        switch ($activity->action) {
            case 'create_user':
                $details['description'] = 'Created a new user account';
                break;
            case 'update_user':
                $details['description'] = 'Updated user information';
                break;
            case 'delete_user':
                $details['description'] = 'Deleted user account';
                break;
            case 'create_employee':
                $details['description'] = 'Created a new employee record';
                break;
            case 'update_employee':
                $details['description'] = 'Updated employee information';
                break;
            case 'delete_employee':
                $details['description'] = 'Deleted employee record';
                break;
            case 'update_idle_timeout':
                $details['description'] = 'Modified idle timeout settings';
                break;
            case 'admin_login':
                $details['description'] = 'Admin user logged in';
                break;
            case 'employee_login':
                $details['description'] = 'Employee logged in';
                break;
            case 'logged_out':
                $details['description'] = 'User logged out';
                break;
            default:
                $details['description'] = ucfirst(str_replace('_', ' ', $activity->action));
        }

        // Add subject information if available
        if ($activity->subject_type && $activity->subject_id) {
            $modelName = class_basename($activity->subject_type);
            $details['target'] = "{$modelName} #{$activity->subject_id}";
        }

        return $details;
    }

    /**
     * Determine activity importance level
     */
    private function getActivityImportance($activity)
    {
        $highImportance = [
            'delete_user', 'delete_employee', 'create_user', 'create_employee',
            'admin_login', 'employee_login', 'logged_out', 'update_idle_timeout'
        ];

        $mediumImportance = [
            'update_user', 'update_employee', 'view_admin_settings'
        ];

        if (in_array($activity->action, $highImportance)) {
            return 'high';
        } elseif (in_array($activity->action, $mediumImportance)) {
            return 'medium';
        } else {
            return 'low';
        }
    }
}
