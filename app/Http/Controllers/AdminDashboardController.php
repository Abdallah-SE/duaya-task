<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Employee;
use App\Models\Penalty;
use App\Models\ActivityLog;
use App\Models\IdleSetting;
use App\Models\IdleSession;
use App\Models\RoleSetting;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        // Middleware is handled at route level
    }

    public function index(Request $request)
    { 
        $user = $request->user();
        
        // Check if user is authenticated
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access the admin dashboard.');
        }
        
        // Verify user has admin role (additional safety check)
        if (!$user->hasRole('admin')) {
            return redirect()->route('login')->with('error', 'Access denied. Administrator privileges required.');
        }
        
        $userSettings = $user->getIdleSettings();
        
        // Get tasddk-specific statistics for User Activity Logs & Inactivity Monitoring
        $stats = $this->getTaskSpecificStats();
        
        // Get recent activities with more details for admin - filter out redundant activities
        $recentActivities = $this->getFilteredRecentActivities();

        // Get CRUD operations breakdown for the task
        $crudBreakdown = $this->getCrudOperationsBreakdown();
        
        // Get employee activity statistics
        $employeeActivityStats = $this->getEmployeeActivityStats();
        
        // Get all penalties for admin view
        $allPenalties = $this->getPenaltyStats();
        
        // Get idle session statistics by user
        $idleSessionStats = $this->getIdleSessionStats();
        
        // Get activity statistics by action type
        $activityStats = $this->getActivityStats();
        
        // Get inactivity monitoring settings
        $inactivitySettings = $this->getInactivitySettings();
        
        return Inertia::render('Admin/Dashboard', [
            'user' => $user,
            'userSettings' => $userSettings,
            'isIdleMonitoringEnabled' => $user->isIdleMonitoringEnabled(),
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'crudBreakdown' => $crudBreakdown,
            'employeeActivityStats' => $employeeActivityStats,
            'allPenalties' => $allPenalties,
            'idleSessionStats' => $idleSessionStats,
            'activityStats' => $activityStats,
            'inactivitySettings' => $inactivitySettings,
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
        // Use select to only fetch needed columns and limit before processing
        $activities = ActivityLog::with(['user.employee', 'user.roles'])
            ->select(['id', 'action', 'subject_type', 'subject_id', 'user_id', 'ip_address', 'device', 'browser', 'created_at'])
            ->where('created_at', '>=', now()->subDays(7))
            ->latest()
            ->limit(100) // Limit before filtering to reduce memory usage
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
            case 'login_admin_user':
                $details['description'] = 'Admin user logged in';
                break;
            case 'login_employee_user':
                $details['description'] = 'Employee user logged in';
                break;
            case 'logout_admin_user':
                $details['description'] = 'Admin user logged out';
                break;
            case 'logout_employee_user':
                $details['description'] = 'Employee user logged out';
                break;
            case 'auto_logout_employee_user':
                $details['description'] = 'Employee auto-logged out due to inactivity';
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
            'login_admin_user', 'login_employee_user', 'logout_admin_user', 'logout_employee_user', 'auto_logout_employee_user', 'update_idle_timeout'
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

    /**
     * Get task-specific statistics for User Activity Logs & Inactivity Monitoring
     */
    private function getTaskSpecificStats()
    {
        // Use raw queries for better performance on large datasets
        $today = today();
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $sevenDaysAgo = now()->subDays(7);
        
        // Single query to get all activity statistics
        $activityStats = DB::table('activity_logs')
            ->selectRaw('
                COUNT(*) as total_activities,
                COUNT(CASE WHEN DATE(created_at) = ? THEN 1 END) as today_activities,
                COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as week_activities,
                COUNT(CASE WHEN action IN ("create", "read", "update", "delete") THEN 1 END) as crud_operations,
                COUNT(CASE WHEN action IN ("login_admin_user", "login_employee_user", "logout_admin_user", "logout_employee_user", "auto_logout_employee_user") THEN 1 END) as login_logout_events
            ', [$today, $weekStart, $weekEnd])
            ->first();
        
        // Single query to get all idle session statistics
        $idleStats = DB::table('idle_sessions')
            ->selectRaw('
                COUNT(*) as total_sessions,
                COUNT(CASE WHEN idle_ended_at IS NULL THEN 1 END) as active_sessions,
                COUNT(CASE WHEN idle_ended_at IS NOT NULL THEN 1 END) as completed_sessions,
                COALESCE(SUM(duration_seconds), 0) as total_idle_time,
                COALESCE(AVG(duration_seconds), 0) as average_idle_time
            ')
            ->first();
        
        // Single query to get all penalty statistics
        $penaltyStats = DB::table('penalties')
            ->selectRaw('
                COUNT(*) as total_penalties,
                COUNT(CASE WHEN DATE(date) = ? THEN 1 END) as today_penalties,
                COUNT(CASE WHEN date BETWEEN ? AND ? THEN 1 END) as week_penalties,
                COUNT(CASE WHEN reason LIKE "%auto logout%" THEN 1 END) as auto_logout_penalties
            ', [$today, $weekStart, $weekEnd])
            ->first();
        
        // Single query to get user statistics
        $userStats = DB::table('users')
            ->selectRaw('COUNT(*) as total_users')
            ->first();
        
        $employeeStats = DB::table('employees')
            ->selectRaw('COUNT(*) as total_employees')
            ->first();
        
        // Count active users with a more efficient query
        $activeUsers = DB::table('users')
            ->whereExists(function ($query) use ($sevenDaysAgo) {
                $query->select(DB::raw(1))
                    ->from('activity_logs')
                    ->whereColumn('activity_logs.user_id', 'users.id')
                    ->where('activity_logs.created_at', '>=', $sevenDaysAgo);
            })
            ->count();
        
        return [
            // Activity Logs Statistics
            'totalActivities' => $activityStats->total_activities,
            'todayActivities' => $activityStats->today_activities,
            'thisWeekActivities' => $activityStats->week_activities,
            'crudOperations' => $activityStats->crud_operations,
            'loginLogoutEvents' => $activityStats->login_logout_events,
            
            // Inactivity Tracking Statistics
            'totalIdleSessions' => $idleStats->total_sessions,
            'activeIdleSessions' => $idleStats->active_sessions,
            'completedIdleSessions' => $idleStats->completed_sessions,
            'totalIdleTime' => $idleStats->total_idle_time,
            'averageIdleTime' => $idleStats->average_idle_time,
            
            // Penalty System Statistics
            'totalPenalties' => $penaltyStats->total_penalties,
            'todayPenalties' => $penaltyStats->today_penalties,
            'thisWeekPenalties' => $penaltyStats->week_penalties,
            'autoLogoutPenalties' => $penaltyStats->auto_logout_penalties,
            
            // User Statistics
            'totalUsers' => $userStats->total_users,
            'totalEmployees' => $employeeStats->total_employees,
            'activeUsers' => $activeUsers,
        ];
    }

    /**
     * Get CRUD operations breakdown
     */
    private function getCrudOperationsBreakdown()
    {
        return ActivityLog::selectRaw('
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
    }

    /**
     * Get employee activity statistics
     */
    private function getEmployeeActivityStats()
    {
        // Use raw query for better performance
        $stats = DB::table('activity_logs')
            ->join('users', 'activity_logs.user_id', '=', 'users.id')
            ->join('employees', 'users.id', '=', 'employees.user_id')
            ->selectRaw('
                activity_logs.user_id,
                users.name as user_name,
                users.email as user_email,
                employees.department,
                employees.job_title,
                COUNT(activity_logs.id) as activity_count,
                COUNT(CASE WHEN DATE(activity_logs.created_at) = CURDATE() THEN 1 END) as today_activities,
                COUNT(CASE WHEN activity_logs.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as week_activities
            ')
            ->groupBy('activity_logs.user_id', 'users.name', 'users.email', 'employees.department', 'employees.job_title')
            ->orderBy('activity_count', 'desc')
            ->limit(10)
            ->get();
        
        return $stats->map(function ($stat) {
            return [
                'user' => [
                    'name' => $stat->user_name,
                    'email' => $stat->user_email,
                    'employee' => [
                        'department' => $stat->department,
                        'job_title' => $stat->job_title,
                    ],
                ],
                'activity_count' => $stat->activity_count,
                'today_activities' => $stat->today_activities,
                'week_activities' => $stat->week_activities,
            ];
        });
    }

    /**
     * Get penalty statistics
     */
    private function getPenaltyStats()
    {
        // Use raw query for better performance
        $penalties = DB::table('penalties')
            ->join('users', 'penalties.user_id', '=', 'users.id')
            ->leftJoin('employees', 'users.id', '=', 'employees.user_id')
            ->select([
                'penalties.id',
                'penalties.reason',
                'penalties.count',
                'penalties.date',
                'users.name as user_name',
                'users.email as user_email',
                'employees.department',
                'employees.job_title'
            ])
            ->orderBy('penalties.date', 'desc')
            ->limit(20)
            ->get();
        
        return $penalties->map(function ($penalty) {
            return [
                'id' => $penalty->id,
                'user' => [
                    'name' => $penalty->user_name,
                    'email' => $penalty->user_email,
                    'employee' => $penalty->department ? [
                        'department' => $penalty->department,
                        'job_title' => $penalty->job_title,
                    ] : null,
                ],
                'reason' => $penalty->reason,
                'count' => $penalty->count,
                'date' => $penalty->date,
            ];
        });
    }

    /**
     * Get idle session statistics
     */
    private function getIdleSessionStats()
    {
        // Use raw query for better performance
        $stats = DB::table('idle_sessions')
            ->join('users', 'idle_sessions.user_id', '=', 'users.id')
            ->selectRaw('
                idle_sessions.user_id,
                users.name as user_name,
                users.email as user_email,
                COUNT(idle_sessions.id) as session_count,
                COALESCE(SUM(idle_sessions.duration_seconds), 0) as total_duration,
                COALESCE(AVG(idle_sessions.duration_seconds), 0) as avg_duration,
                COUNT(CASE WHEN idle_sessions.idle_ended_at IS NULL THEN 1 END) as active_sessions
            ')
            ->groupBy('idle_sessions.user_id', 'users.name', 'users.email')
            ->orderBy('session_count', 'desc')
            ->limit(10)
            ->get();
        
        return $stats->map(function ($stat) {
            return [
                'user' => [
                    'name' => $stat->user_name,
                    'email' => $stat->user_email,
                ],
                'session_count' => $stat->session_count,
                'total_duration' => $stat->total_duration,
                'avg_duration' => $stat->avg_duration,
                'active_sessions' => $stat->active_sessions,
            ];
        });
    }

    /**
     * Get activity statistics by action type
     */
    private function getActivityStats()
    {
        return ActivityLog::selectRaw('
            action,
            COUNT(*) as count
        ')
        ->groupBy('action')
        ->orderBy('count', 'desc')
        ->limit(15)
        ->get();
    }

    /**
     * Get inactivity monitoring settings
     */
    private function getInactivitySettings()
    {
        $settings = IdleSetting::first();
        
        if (!$settings) {
            return [
                'idle_timeout' => 5,
                'warning_threshold' => 2,
                'auto_logout_enabled' => true,
                'monitoring_enabled' => $this->isMonitoringEnabledForAnyRole(),
            ];
        }

        return [
            'idle_timeout' => $settings->idle_timeout,
            'warning_threshold' => $settings->max_idle_warnings,
            'auto_logout_enabled' => true, // Always enabled as per task requirements
            'monitoring_enabled' => $this->isMonitoringEnabledForAnyRole(),
        ];
    }

    /**
     * Check if monitoring is enabled for any role
     */
    private function isMonitoringEnabledForAnyRole()
    {
        // Check if any role has monitoring enabled
        $enabledRoles = RoleSetting::where('idle_monitoring_enabled', true)->count();
        return $enabledRoles > 0;
    }
}
