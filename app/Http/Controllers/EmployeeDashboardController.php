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

class EmployeeDashboardController extends Controller
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
            return redirect()->route('login')->with('error', 'Please log in to access the employee dashboard.');
        }
        
        // Verify user has employee role (additional safety check)
        if (!$user->hasRole('employee')) {
            return redirect()->route('login')->with('error', 'Access denied. Employee privileges required.');
        }
        
        $userSettings = $user->getIdleSettings();
        
        // Load employee relationship
        $user->load('employee');
        
        $isIdleMonitoringEnabled = $user->isIdleMonitoringEnabled();
        
        // Get simplified statistics for employee
        $stats = $this->getEmployeeStats($user);
        
        // Get employee's recent activities (simplified)
        $myActivities = $this->getEmployeeActivities($user);
        
        // Get employee's penalties (simplified)
        $myPenalties = $this->getEmployeePenalties($user);
        
        // Get employee's idle sessions (simplified)
        $myIdleSessions = $this->getEmployeeIdleSessions($user);
        
        $data = [
            'user' => $user,
            'userSettings' => $userSettings,
            'initialSettings' => $userSettings, // Pass idle settings for IdleMonitor component
            'canControlIdleMonitoring' => $user->canControlIdleMonitoring(),
            'isIdleMonitoringEnabled' => $isIdleMonitoringEnabled,
            'stats' => $stats,
            'myActivities' => $myActivities,
            'myPenalties' => $myPenalties,
            'myIdleSessions' => $myIdleSessions,
            'greeting' => $this->getGreeting($user),
        ];
        
        
        return Inertia::render('Employee/Dashboard', $data);
    }
    
    private function getGreeting($user)
    {
        $hour = now()->hour;
        $name = $user->name;
        
        if ($hour < 12) {
            return "Good morning, {$name}!";
        } elseif ($hour < 17) {
            return "Good afternoon, {$name}!";
        } else {
            return "Good evening, {$name}!";
        }
    }
    
    /**
     * Get optimized employee statistics
     */
    private function getEmployeeStats($user)
    {
        // Single query to get all employee statistics
        $stats = DB::table('activity_logs')
            ->selectRaw('
                COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_activities
            ')
            ->where('user_id', $user->id)
            ->first();
        
        $penaltyCount = DB::table('penalties')
            ->where('user_id', $user->id)
            ->count();
        
        $idleSessionCount = DB::table('idle_sessions')
            ->where('user_id', $user->id)
            ->count();
        
        return [
            'todayActivities' => $stats->today_activities,
            'myPenalties' => $penaltyCount,
            'myIdleSessions' => $idleSessionCount,
        ];
    }
    
    /**
     * Get optimized employee activities
     */
    private function getEmployeeActivities($user)
    {
        return DB::table('activity_logs')
            ->select(['id', 'action', 'created_at'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();
    }
    
    /**
     * Get optimized employee penalties
     */
    private function getEmployeePenalties($user)
    {
        return DB::table('penalties')
            ->where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();
    }
    
    /**
     * Get optimized employee idle sessions
     */
    private function getEmployeeIdleSessions($user)
    {
        return DB::table('idle_sessions')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }
}
