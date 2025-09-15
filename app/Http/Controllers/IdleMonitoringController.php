<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\IdleSession;
use App\Models\Penalty;
use App\Models\IdleSetting;
use App\Models\ActivityLog;
use App\Events\UserActivityEvent;
use App\Events\IdleWarningEvent;
use App\Events\PenaltyAppliedEvent;
use App\Events\UserLogoutEvent;

class IdleMonitoringController extends Controller
{
    /**
     * Start an idle session.
     */
    public function startIdleSession(Request $request)
    {
        $user = Auth::user();
        $idleSettings = $user->getIdleSettings();
        
        // Check if monitoring is enabled for this user's role
        if (!$user->isIdleMonitoringEnabled()) {
            return response()->json(['message' => 'Idle monitoring is disabled for your role'], 403);
        }
        
        // End any existing active session
        IdleSession::where('user_id', $user->id)
            ->whereNull('idle_ended_at')
            ->update(['idle_ended_at' => now()]);
        
        // Start new idle session
        $idleSession = IdleSession::startSession($user->id);
        
        // Fire event for activity logging
        event(new UserActivityEvent(
            user: $user,
            action: 'create_idle_session',
            subjectType: 'App\Models\IdleSession',
            subjectId: $idleSession->id,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        ));
        
        return response()->json([
            'message' => 'Idle session started',
            'session_id' => $idleSession->id,
            'timeout' => $idleSettings->idle_timeout,
            'max_warnings' => $idleSettings->max_idle_warnings
        ]);
    }
    
    /**
     * End an idle session.
     */
    public function endIdleSession(Request $request)
    {
        $user = Auth::user();
        $sessionId = $request->input('session_id');
        
        $idleSession = IdleSession::where('id', $sessionId)
            ->where('user_id', $user->id)
            ->whereNull('idle_ended_at')
            ->first();
        
        if (!$idleSession) {
            return response()->json(['message' => 'Idle session not found'], 404);
        }
        
        $idleSession->endSession();
        
        // Fire event for activity logging
        event(new UserActivityEvent(
            user: $user,
            action: 'idle_session_ended',
            subjectType: 'App\Models\IdleSession',
            subjectId: $idleSession->id,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        ));
        
        return response()->json([
            'message' => 'Idle session ended',
            'duration' => $idleSession->duration_seconds
        ]);
    }
    
    /**
     * Handle idle warning with 3-warning system.
     * ALL warnings (Alert, Warning, Auto Logout) are stored in idle_sessions table.
     */
    public function handleIdleWarning(Request $request)
    {
        $user = Auth::user();
        $warningCount = $request->input('warning_count', 1);
        
        Log::info('Handle idle warning called', [
            'user_id' => $user->id,
            'warning_count' => $warningCount,
            'warning_type' => $this->getWarningType($warningCount)
        ]);
        
        // Check if monitoring is enabled for this user's role
        if (!$user->isIdleMonitoringEnabled()) {
            Log::info('Idle monitoring disabled for user role', ['user_id' => $user->id]);
            return response()->json([
                'success' => false,
                'message' => 'Idle monitoring is disabled for your role',
                'idle_monitoring_enabled' => false
            ], 403);
        }
        
        // Get idle settings
        $idleSettings = $user->getIdleSettings();
        
        // Create a NEW idle session for EACH alert/warning as per task requirements
        // End any existing active session first
        IdleSession::where('user_id', $user->id)
            ->whereNull('idle_ended_at')
            ->update(['idle_ended_at' => now()]);
        
        // Create new idle session for this specific alert/warning
        $idleSession = IdleSession::startSession($user->id);
        
        Log::info('New idle session created for alert/warning', [
            'user_id' => $user->id,
            'warning_count' => $warningCount,
            'warning_type' => $this->getWarningType($warningCount),
            'session_id' => $idleSession->id,
            'idle_timeout' => $idleSettings->idle_timeout,
            'max_warnings' => $idleSettings->max_idle_warnings
        ]);
        
        Log::info('Processing idle alert/warning', [
            'user_id' => $user->id,
            'warning_count' => $warningCount,
            'warning_type' => $this->getWarningType($warningCount),
            'session_id' => $idleSession->id,
            'idle_timeout' => $idleSettings->idle_timeout,
            'max_warnings' => $idleSettings->max_idle_warnings
        ]);
        
        // Fire event for idle session start (for each alert/warning)
        event(new UserActivityEvent(
            user: $user,
            action: 'create_idle_session',
            subjectType: 'App\Models\IdleSession',
            subjectId: $idleSession->id,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        ));
        
        // Fire event for idle warning
        event(new IdleWarningEvent(
            user: $user,
            warningCount: $warningCount,
            maxWarnings: $idleSettings->max_idle_warnings,
            sessionId: $idleSession->id,
            timeoutSeconds: $idleSettings->idle_timeout
        ));
        
        // Log each alert/warning as an activity with descriptive names
        $actionName = match($warningCount) {
            1 => 'create_idle_alert',
            2 => 'create_idle_warning', 
            3 => 'create_idle_logout',
            default => 'create_idle_warning_' . $warningCount
        };
        
        event(new UserActivityEvent(
            user: $user,
            action: $actionName,
            subjectType: 'App\Models\IdleSession',
            subjectId: $idleSession->id,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        ));
        
        // Check if this is the third warning (should trigger penalty and logout)
        // According to task: 1st=Alert, 2nd=Warning, 3rd=Auto Logout + Penalty
        Log::info('Checking warning count for penalty', [
            'warning_count' => $warningCount,
            'should_apply_penalty' => $warningCount >= 3
        ]);
        
        if ($warningCount >= 3) {
            Log::info('Third warning reached, applying penalty and logout', [
                'user_id' => $user->id,
                'warning_count' => $warningCount,
                'warning_type' => 'Auto Logout',
                'session_id' => $idleSession->id
            ]);
            return $this->applyPenalty($user, $request, $idleSession->id);
        }
        
        // Return response for first and second warnings
        return response()->json([
            'success' => true,
            'message' => $this->getWarningMessage($warningCount),
            'warning_count' => $warningCount,
            'warning_type' => $this->getWarningType($warningCount),
            'max_warnings' => 3, // Fixed to 3 as per task requirements
            'session_id' => $idleSession->id,
            'idle_timeout' => $idleSettings->idle_timeout,
            'next_action' => $warningCount + 1 >= 3 ? 'penalty' : 'warning',
            'logout_required' => false
        ]);
    }
    
    /**
     * Get warning type based on warning count.
     */
    private function getWarningType(int $warningCount): string
    {
        return match($warningCount) {
            1 => 'Alert',
            2 => 'Warning', 
            3 => 'Auto Logout',
            default => 'Unknown'
        };
    }
    
    /**
     * Get warning message based on warning count.
     */
    private function getWarningMessage(int $warningCount): string
    {
        return match($warningCount) {
            1 => 'First alert recorded - you appear to be idle',
            2 => 'Second warning recorded - continued inactivity will result in logout',
            3 => 'Final warning recorded - you will be logged out',
            default => 'Idle warning recorded'
        };
    }
    
    /**
     * Apply penalty for excessive idle time (third warning).
     */
    private function applyPenalty($user, Request $request, $sessionId)
    {
        $warningCount = $request->input('warning_count', 1);
        
        try {
            Log::info('Applying penalty for max warning - user: ' . $user->id . ', session: ' . $sessionId . ', warning count: ' . $warningCount);
            
            // Create penalty for third warning
            $penalty = Penalty::createPenalty(
                userId: $user->id,
                reason: 'Third idle warning - automatic logout triggered',
                count: 1
            );
            
            Log::info('Penalty created with ID: ' . $penalty->id);
            
            // End the idle session
            $idleSession = IdleSession::where('id', $sessionId)
                ->where('user_id', $user->id)
                ->first();
            
            if ($idleSession) {
                $idleSession->endSession();
                Log::info('Idle session ended: ' . $idleSession->id);
            } else {
                Log::warning('Idle session not found: ' . $sessionId);
            }
            
            // Fire event for penalty applied
            event(new PenaltyAppliedEvent(
                user: $user,
                penalty: $penalty,
                reason: 'Third idle warning - automatic logout triggered'
            ));
            
            // Fire event for activity logging
            event(new UserActivityEvent(
                user: $user,
                action: 'idle_penalty_applied',
                subjectType: 'App\Models\Penalty',
                subjectId: $penalty->id,
                ipAddress: $request->ip(),
                device: $this->getDeviceInfo($request),
                browser: $this->getBrowserInfo($request)
            ));
            
            Log::info('Logging out user due to third warning: ' . $user->id);
            
            // Fire logout event for activity logging
            event(new UserLogoutEvent(
                user: $user,
                logoutType: 'auto_logout_employee_user',
                ipAddress: $request->ip(),
                device: $this->getDeviceInfo($request),
                browser: $this->getBrowserInfo($request)
            ));
            
            // Logout the user
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return response()->json([
                'success' => true,
                'message' => 'Third warning - penalty applied and user logged out',
                'penalty_id' => $penalty->id,
                'logout_required' => true,
                'warning_count' => $warningCount
            ], 401);
            
        } catch (\Exception $e) {
            Log::error('Error applying penalty: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fire logout event for activity logging (even if penalty creation failed)
            event(new UserLogoutEvent(
                user: $user,
                logoutType: 'auto_logout_employee_user',
                ipAddress: $request->ip(),
                device: $this->getDeviceInfo($request),
                browser: $this->getBrowserInfo($request)
            ));
            
            // Still logout even if penalty creation fails
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return response()->json([
                'success' => false,
                'message' => 'Error applying penalty but user logged out due to third warning',
                'logout_required' => true,
                'warning_count' => $warningCount
            ], 401);
        }
    }
    
    /**
     * Get user's idle monitoring settings.
     */
    public function getSettings(Request $request)
    {
        $user = Auth::user();
        $settings = $user->getIdleSettings();
        
        return response()->json([
            'idle_timeout' => $settings->idle_timeout,
            'idle_monitoring_enabled' => $user->isIdleMonitoringEnabled(), // Get from role settings
            'max_idle_warnings' => $settings->max_idle_warnings,
            'timeout_milliseconds' => $settings->getTimeoutInMilliseconds()
        ]);
    }
    
    /**
     * Update user's idle monitoring settings (Admin only).
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission to update settings
        if (!$user->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'idle_timeout' => 'required|integer|min:1|max:300', // 1 second to 5 minutes
            'idle_monitoring_enabled' => 'required|boolean',
            'max_idle_warnings' => 'required|integer|min:1|max:10'
        ]);
        
        $targetUser = \App\Models\User::findOrFail($validated['user_id']);
        $settings = IdleSetting::updateForUser(
            $targetUser->id,
            $validated['idle_timeout'],
            $validated['idle_monitoring_enabled'],
            $validated['max_idle_warnings']
        );
        
        // Fire event for settings update
        event(new UserActivityEvent(
            user: $user,
            action: 'update_idle_settings',
            subjectType: 'App\Models\IdleSetting',
            subjectId: $settings->id,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        ));
        
        return response()->json([
            'message' => 'Settings updated successfully',
            'settings' => $settings
        ]);
    }

    /**
     * Update role idle monitoring settings (Admin only).
     */
    public function updateRoleSettings(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission to control idle monitoring
        if (!$user->canControlIdleMonitoring()) {
            return response()->json(['message' => 'You do not have permission to control idle monitoring'], 403);
        }
        
        $validated = $request->validate([
            'role_name' => 'required|string|exists:roles,name',
            'idle_monitoring_enabled' => 'required|boolean'
        ]);
        
        $success = \App\Models\RoleSetting::updateSetting(
            $validated['role_name'],
            $validated['idle_monitoring_enabled']
        );
        
        if (!$success) {
            return response()->json(['message' => 'Failed to update role settings'], 400);
        }
        
        // Get the updated role setting for the response
        $role = \Spatie\Permission\Models\Role::where('name', $validated['role_name'])->first();
        $roleSetting = \App\Models\RoleSetting::where('role_id', $role->id)->first();
        
        // Fire event for settings update
        event(new UserActivityEvent(
            user: $user,
            action: 'update_role_idle_settings',
            subjectType: 'App\Models\RoleSetting',
            subjectId: $roleSetting ? $roleSetting->id : null,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        ));
        
        return response()->json([
            'message' => 'Role idle monitoring settings updated successfully',
            'role_setting' => [
                'role_name' => $validated['role_name'],
                'idle_monitoring_enabled' => $validated['idle_monitoring_enabled']
            ]
        ]);
    }

    /**
     * Get all role idle monitoring settings (Admin only).
     */
    public function getRoleSettings(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission to view settings
        if (!$user->canControlIdleMonitoring()) {
            return response()->json(['message' => 'You do not have permission to view role settings'], 403);
        }
        
        // Get all roles with their idle monitoring status efficiently
        $roles = \Spatie\Permission\Models\Role::all()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => ucfirst(str_replace('-', ' ', $role->name)),
                'idle_monitoring_enabled' => \App\Models\RoleSetting::isIdleMonitoringEnabledForRole($role->name),
                'guard_name' => $role->guard_name,
            ];
        });
        
        return response()->json([
            'roles' => $roles
        ]);
    }
    
    /**
     * Get device information from request.
     */
    private function getDeviceInfo(Request $request): string
    {
        $userAgent = $request->userAgent() ?? '';
        
        if (str_contains($userAgent, 'Mobile')) {
            return 'Mobile';
        } elseif (str_contains($userAgent, 'Tablet')) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }
    
    /**
     * Get browser information from request.
     */
    private function getBrowserInfo(Request $request): string
    {
        $userAgent = $request->userAgent() ?? '';
        
        if (str_contains($userAgent, 'Chrome')) {
            return 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox')) {
            return 'Firefox';
        } elseif (str_contains($userAgent, 'Safari')) {
            return 'Safari';
        } elseif (str_contains($userAgent, 'Edge')) {
            return 'Edge';
        } else {
            return 'Unknown';
        }
    }

    /**
     * Get idle monitoring statistics.
     */
    public function getIdleStats(Request $request)
    {
        $user = Auth::user();
        
        // Only admin can view detailed stats
        if (!$user->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $stats = [
            'total_sessions' => IdleSession::count(),
            'active_sessions' => IdleSession::whereNull('idle_ended_at')->count(),
            'completed_sessions' => IdleSession::whereNotNull('idle_ended_at')->count(),
            'total_idle_time_seconds' => IdleSession::whereNotNull('duration_seconds')->sum('duration_seconds'),
            'sessions_by_user' => IdleSession::with('user')
                ->selectRaw('user_id, COUNT(*) as session_count, SUM(duration_seconds) as total_duration')
                ->whereNotNull('idle_ended_at')
                ->groupBy('user_id')
                ->orderBy('session_count', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'user_id' => $item->user_id,
                        'user_name' => $item->user->name ?? 'Unknown',
                        'session_count' => $item->session_count,
                        'total_duration_seconds' => $item->total_duration ?? 0,
                    ];
                }),
        ];
        
        return response()->json($stats);
    }

    /**
     * Test database operations.
     */
    public function testDatabase(Request $request)
    {
        $user = Auth::user();
        
        try {
            // Test penalty creation
            $penalty = Penalty::createPenalty(
                userId: $user->id,
                reason: 'Test penalty from API',
                count: 1
            );
            
            // Test idle session creation
            $session = IdleSession::startSession($user->id);
            
            return response()->json([
                'success' => true,
                'penalty_id' => $penalty->id,
                'session_id' => $session->id,
                'message' => 'Database operations successful'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Database operations failed'
            ], 500);
        }
    }
}
