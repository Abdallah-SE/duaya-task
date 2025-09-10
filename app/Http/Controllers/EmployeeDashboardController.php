<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $userSettings = $user->getIdleSettings();
        
        // Load employee relationship
        $user->load('employee');
        
        // Get limited statistics for employee
        $stats = [
            'myActivities' => ActivityLog::where('user_id', $user->id)->count(),
            'myPenalties' => $user->penalties()->count(),
            'myIdleSessions' => $user->idleSessions()->count(),
        ];
        
        // Get employee's recent activities only
        $myActivities = ActivityLog::where('user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'action' => $activity->action,
                    'ip_address' => $activity->ip_address,
                    'device' => $activity->device,
                    'browser' => $activity->browser,
                    'created_at' => $activity->created_at,
                ];
            });
        
        // Get employee's penalties
        $myPenalties = $user->penalties()
            ->latest('date')
            ->limit(5)
            ->get();
        
        // Get employee's idle sessions
        $myIdleSessions = $user->idleSessions()
            ->latest()
            ->limit(5)
            ->get();
        
        return Inertia::render('Employee/Dashboard', [
            'user' => $user,
            'userSettings' => $userSettings,
            'stats' => $stats,
            'myActivities' => $myActivities,
            'myPenalties' => $myPenalties,
            'myIdleSessions' => $myIdleSessions,
            'greeting' => $this->getGreeting($user),
        ]);
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
}
