<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\IdleSession;
use App\Models\Penalty;
use App\Models\IdleSetting;
use App\Models\ActivityLog;
use App\Events\UserActivityEvent;
use App\Events\IdleWarningEvent;
use App\Events\PenaltyAppliedEvent;

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
            action: 'idle_session_started',
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
     * Handle idle warning.
     */
    public function handleIdleWarning(Request $request)
    {
        $user = Auth::user();
        
        \Log::info('Handle idle warning called', [
            'user_id' => $user->id,
            'warning_count' => $request->input('warning_count', 1)
        ]);
        
        // Check if monitoring is enabled for this user's role
        if (!$user->isIdleMonitoringEnabled()) {
            \Log::info('Idle monitoring disabled for user role', ['user_id' => $user->id]);
            return response()->json([
                'success' => false,
                'message' => 'Idle monitoring is disabled for your role',
                'idle_monitoring_enabled' => false
            ], 403);
        }
        
        $warningCount = $request->input('warning_count', 1);
        
        // Create idle session for ALL warnings (1st, 2nd, 3rd)
        $idleSession = IdleSession::startSession($user->id);
        \Log::info('Idle session created for warning', [
            'user_id' => $user->id,
            'warning_count' => $warningCount,
            'session_id' => $idleSession->id
        ]);
        
        $idleSettings = $user->getIdleSettings();
        
        // Fire event for idle warning
        event(new IdleWarningEvent(
            user: $user,
            warningCount: $warningCount,
            maxWarnings: $idleSettings->max_idle_warnings,
            sessionId: $idleSession->id,
            timeoutSeconds: $idleSettings->idle_timeout
        ));
        
        \Log::info('Idle warning event fired', [
            'user_id' => $user->id,
            'warning_count' => $warningCount,
            'max_warnings' => $idleSettings->max_idle_warnings,
            'session_id' => $idleSession->id
        ]);
        
        // Check if this is the third warning (should trigger penalty)
        if ($warningCount >= 3) {
            \Log::info('Third warning reached, applying penalty', [
                'user_id' => $user->id,
                'warning_count' => $warningCount,
                'session_id' => $idleSession->id
            ]);
            return $this->applyPenalty($user, $request, $idleSession->id);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Idle warning recorded',
            'warning_count' => $warningCount,
            'max_warnings' => 3,
            'session_id' => $idleSession->id,
            'next_action' => $warningCount + 1 >= 3 ? 'penalty' : 'warning'
        ]);
    }
    
    /**
     * Apply penalty for excessive idle time.
     */
    private function applyPenalty($user, Request $request, $sessionId)
    {
        try {
            \Log::info('Applying penalty for user: ' . $user->id . ', session: ' . $sessionId);
            
            // Create penalty
            $penalty = Penalty::createPenalty(
                userId: $user->id,
                reason: 'Excessive idle time - auto logout triggered',
                count: 1
            );
            
            \Log::info('Penalty created with ID: ' . $penalty->id);
            
            // End the idle session
            $idleSession = IdleSession::where('id', $sessionId)
                ->where('user_id', $user->id)
                ->first();
            
            if ($idleSession) {
                $idleSession->endSession();
                \Log::info('Idle session ended: ' . $idleSession->id);
            } else {
                \Log::warning('Idle session not found: ' . $sessionId);
            }
            
            // Fire event for penalty applied
            event(new PenaltyAppliedEvent(
                user: $user,
                penalty: $penalty,
                reason: 'Excessive idle time - auto logout triggered'
            ));
            
            \Log::info('Logging out user: ' . $user->id);
            
            // Logout the user
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return response()->json([
                'success' => true,
                'message' => 'Penalty applied and user logged out',
                'penalty_id' => $penalty->id,
                'logout_required' => true
            ], 401);
            
        } catch (\Exception $e) {
            \Log::error('Error applying penalty: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Still logout even if penalty creation fails
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return response()->json([
                'success' => false,
                'message' => 'Error applying penalty but user logged out',
                'logout_required' => true
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
            'idle_monitoring_enabled' => $settings->idle_monitoring_enabled,
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
        
        $roleSetting = \App\Models\RoleSetting::updateForRoleName(
            $validated['role_name'],
            $validated['idle_monitoring_enabled']
        );
        
        // Fire event for settings update
        event(new UserActivityEvent(
            user: $user,
            action: 'update_role_idle_settings',
            subjectType: 'App\Models\RoleSetting',
            subjectId: $roleSetting->id,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        ));
        
        return response()->json([
            'message' => 'Role idle monitoring settings updated successfully',
            'role_setting' => [
                'role_name' => $validated['role_name'],
                'idle_monitoring_enabled' => $roleSetting->idle_monitoring_enabled
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
        
        // Get all roles with their idle monitoring status from role_settings table
        $roles = \App\Models\CustomRole::with('roleSetting')->get()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => ucfirst(str_replace('-', ' ', $role->name)),
                'idle_monitoring_enabled' => $role->roleSetting?->idle_monitoring_enabled ?? true,
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
        $userAgent = $request->userAgent();
        
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
        $userAgent = $request->userAgent();
        
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
