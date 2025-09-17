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
        
        // Get task-specific statistics for User Activity Logs & Inactivity Monitoring
        $stats = $this->getTaskSpecificStats();
        
        // Get recent activities with pagination
        $recentActivities = $this->getPaginatedActivities($request);

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
     * Get paginated activities with enhanced details for admin
     * Optimized for better performance with pagination support
     */
    private function getPaginatedActivities(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        $search = $request->get('search', '');
        
        // Use a single optimized query with proper joins and filtering
        $query = DB::table('activity_logs')
            ->join('users', 'activity_logs.user_id', '=', 'users.id')
            ->leftJoin('employees', 'users.id', '=', 'employees.user_id')
            ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select([
                'activity_logs.id',
                'activity_logs.action',
                'activity_logs.subject_type',
                'activity_logs.subject_id',
                'activity_logs.user_id',
                'activity_logs.ip_address',
                'activity_logs.device',
                'activity_logs.browser',
                'activity_logs.created_at',
                'users.name as user_name',
                'users.email as user_email',
                'employees.department',
                'employees.job_title',
                DB::raw('GROUP_CONCAT(DISTINCT roles.name) as role_names')
            ])
            ->where('activity_logs.created_at', '>=', now()->subDays(7))
            ->whereNotIn('activity_logs.action', $this->getRedundantActions());
        
        // Apply search filter
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('users.name', 'LIKE', "%{$search}%")
                  ->orWhere('users.email', 'LIKE', "%{$search}%")
                  ->orWhere('activity_logs.action', 'LIKE', "%{$search}%")
                  ->orWhere('activity_logs.device', 'LIKE', "%{$search}%")
                  ->orWhere('activity_logs.ip_address', 'LIKE', "%{$search}%")
                  ->orWhere('employees.department', 'LIKE', "%{$search}%")
                  ->orWhere('employees.job_title', 'LIKE', "%{$search}%");
            });
        }
        
        $query->groupBy(
            'activity_logs.id',
            'activity_logs.action',
            'activity_logs.subject_type',
            'activity_logs.subject_id',
            'activity_logs.user_id',
            'activity_logs.ip_address',
            'activity_logs.device',
            'activity_logs.browser',
            'activity_logs.created_at',
            'users.name',
            'users.email',
            'employees.department',
            'employees.job_title'
        )
        ->orderBy('activity_logs.created_at', 'desc');

        // Get total count for pagination with same filters
        $countQuery = DB::table('activity_logs')
            ->join('users', 'activity_logs.user_id', '=', 'users.id')
            ->leftJoin('employees', 'users.id', '=', 'employees.user_id')
            ->where('activity_logs.created_at', '>=', now()->subDays(7))
            ->whereNotIn('activity_logs.action', $this->getRedundantActions());
        
        if (!empty($search)) {
            $countQuery->where(function($q) use ($search) {
                $q->where('users.name', 'LIKE', "%{$search}%")
                  ->orWhere('users.email', 'LIKE', "%{$search}%")
                  ->orWhere('activity_logs.action', 'LIKE', "%{$search}%")
                  ->orWhere('activity_logs.device', 'LIKE', "%{$search}%")
                  ->orWhere('activity_logs.ip_address', 'LIKE', "%{$search}%")
                  ->orWhere('employees.department', 'LIKE', "%{$search}%")
                  ->orWhere('employees.job_title', 'LIKE', "%{$search}%");
            });
        }
        
        $totalCount = $countQuery->count();

        // Get paginated results
        $activities = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        $formattedActivities = $activities->map(function ($activity) {
            return [
                'id' => $activity->id,
                'action' => $activity->action,
                'subject_type' => $activity->subject_type,
                'subject_id' => $activity->subject_id,
                'user' => [
                    'name' => $activity->user_name,
                    'email' => $activity->user_email,
                    'employee' => $activity->department ? [
                        'department' => $activity->department,
                        'job_title' => $activity->job_title,
                    ] : null,
                    'roles' => $activity->role_names ? explode(',', $activity->role_names) : [],
                ],
                'ip_address' => $activity->ip_address,
                'device' => $activity->device,
                'browser' => $activity->browser,
                'created_at' => $activity->created_at,
                'details' => $this->getActivityDetailsFromAction($activity->action, $activity->subject_type, $activity->subject_id),
                'importance' => $this->getActivityImportanceFromAction($activity->action),
            ];
        });

        return [
            'data' => $formattedActivities,
            'pagination' => [
                'current_page' => (int) $page,
                'per_page' => (int) $perPage,
                'total' => (int) $totalCount,
                'last_page' => (int) ceil($totalCount / $perPage),
                'from' => (int) (($page - 1) * $perPage + 1),
                'to' => (int) min($page * $perPage, $totalCount),
            ]
        ];
    }

    /**
     * Get filtered recent activities with enhanced details for admin
     * Optimized for better performance with reduced database calls
     * @deprecated - Use getPaginatedActivities instead
     */
    private function getFilteredRecentActivities()
    {
        // Use a single optimized query with proper joins and filtering
        $activities = DB::table('activity_logs')
            ->join('users', 'activity_logs.user_id', '=', 'users.id')
            ->leftJoin('employees', 'users.id', '=', 'employees.user_id')
            ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select([
                'activity_logs.id',
                'activity_logs.action',
                'activity_logs.subject_type',
                'activity_logs.subject_id',
                'activity_logs.user_id',
                'activity_logs.ip_address',
                'activity_logs.device',
                'activity_logs.browser',
                'activity_logs.created_at',
                'users.name as user_name',
                'users.email as user_email',
                'employees.department',
                'employees.job_title',
                DB::raw('GROUP_CONCAT(DISTINCT roles.name) as role_names')
            ])
            ->where('activity_logs.created_at', '>=', now()->subDays(7))
            ->whereNotIn('activity_logs.action', $this->getRedundantActions())
            ->groupBy(
                'activity_logs.id',
                'activity_logs.action',
                'activity_logs.subject_type',
                'activity_logs.subject_id',
                'activity_logs.user_id',
                'activity_logs.ip_address',
                'activity_logs.device',
                'activity_logs.browser',
                'activity_logs.created_at',
                'users.name',
                'users.email',
                'employees.department',
                'employees.job_title'
            )
            ->orderBy('activity_logs.created_at', 'desc')
            ->limit(25) // Reduced limit since we're filtering at database level
            ->get();

        return $activities->map(function ($activity) {
            return [
                'id' => $activity->id,
                'action' => $activity->action,
                'subject_type' => $activity->subject_type,
                'subject_id' => $activity->subject_id,
                'user' => [
                    'name' => $activity->user_name,
                    'email' => $activity->user_email,
                    'employee' => $activity->department ? [
                        'department' => $activity->department,
                        'job_title' => $activity->job_title,
                    ] : null,
                    'roles' => $activity->role_names ? explode(',', $activity->role_names) : [],
                ],
                'ip_address' => $activity->ip_address,
                'device' => $activity->device,
                'browser' => $activity->browser,
                'created_at' => $activity->created_at,
                'details' => $this->getActivityDetailsFromAction($activity->action, $activity->subject_type, $activity->subject_id),
                'importance' => $this->getActivityImportanceFromAction($activity->action),
            ];
        });
    }

    /**
     * Get list of redundant actions to filter out at database level
     */
    private function getRedundantActions()
    {
        return [
            'view_admin_settings',
            'view_dashboard',
            'view_employee_list',
            'view_user_list',
            'view_penalty_list',
            'view_activity_logs',
            'view_idle_sessions',
            'view_settings',
            'read_settings',
            'index_dashboard',
            'show_dashboard'
        ];
    }

    /**
     * Get activity details from action without model instance
     */
    private function getActivityDetailsFromAction($action, $subjectType = null, $subjectId = null)
    {
        $details = [];

        // Add specific details based on action type
        switch ($action) {
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
                $details['description'] = ucfirst(str_replace('_', ' ', $action));
        }

        // Add subject information if available
        if ($subjectType && $subjectId) {
            $modelName = class_basename($subjectType);
            $details['target'] = "{$modelName} #{$subjectId}";
        }

        return $details;
    }

    /**
     * Get activity importance from action without model instance
     */
    private function getActivityImportanceFromAction($action)
    {
        $highImportance = [
            'delete_user', 'delete_employee', 'create_user', 'create_employee',
            'login_admin_user', 'login_employee_user', 'logout_admin_user', 'logout_employee_user', 'auto_logout_employee_user', 'update_idle_timeout'
        ];

        $mediumImportance = [
            'update_user', 'update_employee', 'view_admin_settings'
        ];

        if (in_array($action, $highImportance)) {
            return 'high';
        } elseif (in_array($action, $mediumImportance)) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Filter out redundant activities (consecutive similar actions by same user)
     * @deprecated - Now handled at database level for better performance
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
     * Optimized to use fewer database queries with better performance
     */
    private function getTaskSpecificStats()
    {
        $today = today();
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $sevenDaysAgo = now()->subDays(7);
        
        // Single comprehensive query to get all statistics at once
        $stats = DB::select("
            SELECT 
                -- Activity Logs Statistics
                (SELECT COUNT(*) FROM activity_logs) as total_activities,
                (SELECT COUNT(*) FROM activity_logs WHERE DATE(created_at) = ?) as today_activities,
                (SELECT COUNT(*) FROM activity_logs WHERE created_at BETWEEN ? AND ?) as week_activities,
                (SELECT COUNT(*) FROM activity_logs WHERE action IN ('create', 'read', 'update', 'delete')) as crud_operations,
                (SELECT COUNT(*) FROM activity_logs WHERE action IN ('login_admin_user', 'login_employee_user', 'logout_admin_user', 'logout_employee_user', 'auto_logout_employee_user')) as login_logout_events,
                
                -- Idle Session Statistics
                (SELECT COUNT(*) FROM idle_sessions) as total_sessions,
                (SELECT COUNT(*) FROM idle_sessions WHERE idle_ended_at IS NULL) as active_sessions,
                (SELECT COUNT(*) FROM idle_sessions WHERE idle_ended_at IS NOT NULL) as completed_sessions,
                (SELECT COALESCE(SUM(duration_seconds), 0) FROM idle_sessions) as total_idle_time,
                (SELECT COALESCE(AVG(duration_seconds), 0) FROM idle_sessions) as average_idle_time,
                
                -- Penalty Statistics
                (SELECT COUNT(*) FROM penalties) as total_penalties,
                (SELECT COUNT(*) FROM penalties WHERE DATE(date) = ?) as today_penalties,
                (SELECT COUNT(*) FROM penalties WHERE date BETWEEN ? AND ?) as week_penalties,
                (SELECT COUNT(*) FROM penalties WHERE reason LIKE '%auto logout%') as auto_logout_penalties,
                
                -- User Statistics
                (SELECT COUNT(*) FROM users) as total_users,
                (SELECT COUNT(*) FROM employees) as total_employees,
                (SELECT COUNT(DISTINCT u.id) FROM users u 
                 INNER JOIN activity_logs al ON u.id = al.user_id 
                 WHERE al.created_at >= ?) as active_users
        ", [$today, $weekStart, $weekEnd, $today, $weekStart, $weekEnd, $sevenDaysAgo]);
        
        $result = $stats[0];
        
        return [
            // Activity Logs Statistics
            'totalActivities' => (int) $result->total_activities,
            'todayActivities' => (int) $result->today_activities,
            'thisWeekActivities' => (int) $result->week_activities,
            'crudOperations' => (int) $result->crud_operations,
            'loginLogoutEvents' => (int) $result->login_logout_events,
            
            // Inactivity Tracking Statistics
            'totalIdleSessions' => (int) $result->total_sessions,
            'activeIdleSessions' => (int) $result->active_sessions,
            'completedIdleSessions' => (int) $result->completed_sessions,
            'totalIdleTime' => (int) $result->total_idle_time,
            'averageIdleTime' => (float) $result->average_idle_time,
            
            // Penalty System Statistics
            'totalPenalties' => (int) $result->total_penalties,
            'todayPenalties' => (int) $result->today_penalties,
            'thisWeekPenalties' => (int) $result->week_penalties,
            'autoLogoutPenalties' => (int) $result->auto_logout_penalties,
            
            // User Statistics
            'totalUsers' => (int) $result->total_users,
            'totalEmployees' => (int) $result->total_employees,
            'activeUsers' => (int) $result->active_users,
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
     * Get employee activity statistics with pagination support
     */
    private function getEmployeeActivityStats()
    {
        // Optimized query with better performance and pagination
        $stats = DB::table('activity_logs')
            ->join('users', 'activity_logs.user_id', '=', 'users.id')
            ->leftJoin('employees', 'users.id', '=', 'employees.user_id')
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
            ->where('activity_logs.created_at', '>=', now()->subDays(30)) // Only last 30 days for better performance
            ->groupBy('activity_logs.user_id', 'users.name', 'users.email', 'employees.department', 'employees.job_title')
            ->having('activity_count', '>', 0) // Only users with activities
            ->orderBy('activity_count', 'desc')
            ->limit(10)
            ->get();
        
        return $stats->map(function ($stat) {
            return [
                'user' => [
                    'name' => $stat->user_name,
                    'email' => $stat->user_email,
                    'employee' => $stat->department ? [
                        'department' => $stat->department,
                        'job_title' => $stat->job_title,
                    ] : null,
                ],
                'activity_count' => (int) $stat->activity_count,
                'today_activities' => (int) $stat->today_activities,
                'week_activities' => (int) $stat->week_activities,
            ];
        });
    }

    /**
     * Get penalty statistics with optimized query
     */
    private function getPenaltyStats()
    {
        // Optimized query with better performance and pagination
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
            ->where('penalties.date', '>=', now()->subDays(30)) // Only last 30 days for better performance
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
                'count' => (int) $penalty->count,
                'date' => $penalty->date,
            ];
        });
    }

    /**
     * Get idle session statistics with optimized query
     */
    private function getIdleSessionStats()
    {
        // Optimized query with better performance and date filtering
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
            ->where('idle_sessions.created_at', '>=', now()->subDays(30)) // Only last 30 days for better performance
            ->groupBy('idle_sessions.user_id', 'users.name', 'users.email')
            ->having('session_count', '>', 0) // Only users with sessions
            ->orderBy('session_count', 'desc')
            ->limit(10)
            ->get();
        
        return $stats->map(function ($stat) {
            return [
                'user' => [
                    'name' => $stat->user_name,
                    'email' => $stat->user_email,
                ],
                'session_count' => (int) $stat->session_count,
                'total_duration' => (int) $stat->total_duration,
                'avg_duration' => (float) $stat->avg_duration,
                'active_sessions' => (int) $stat->active_sessions,
            ];
        });
    }

    /**
     * Get activity statistics by action type with optimized query
     */
    private function getActivityStats()
    {
        return ActivityLog::selectRaw('
            action,
            COUNT(*) as count
        ')
        ->where('created_at', '>=', now()->subDays(30)) // Only last 30 days for better performance
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
