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
        
        // Check if monitoring is enabled for this user
        if (!$idleSettings->idle_monitoring_enabled) {
            return response()->json(['message' => 'Idle monitoring is disabled for this user'], 403);
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
        $warningCount = $request->input('warning_count', 1);
        $sessionId = $request->input('session_id');
        
        $idleSettings = $user->getIdleSettings();
        
        // Fire event for idle warning
        event(new IdleWarningEvent(
            user: $user,
            warningCount: $warningCount,
            maxWarnings: $idleSettings->max_idle_warnings,
            sessionId: $sessionId,
            timeoutSeconds: $idleSettings->idle_timeout
        ));
        
        // Check if this is the third warning (should trigger penalty)
        if ($warningCount >= 3) {
            return $this->applyPenalty($user, $request, $sessionId);
        }
        
        return response()->json([
            'message' => 'Idle warning recorded',
            'warning_count' => $warningCount,
            'max_warnings' => 3,
            'next_action' => $warningCount + 1 >= 3 ? 'penalty' : 'warning'
        ]);
    }
    
    /**
     * Apply penalty for excessive idle time.
     */
    private function applyPenalty($user, Request $request, $sessionId)
    {
        // Create penalty
        $penalty = Penalty::createPenalty(
            userId: $user->id,
            reason: 'Excessive idle time - auto logout triggered',
            count: 1
        );
        
        // End the idle session
        $idleSession = IdleSession::where('id', $sessionId)
            ->where('user_id', $user->id)
            ->first();
        
        if ($idleSession) {
            $idleSession->endSession();
        }
        
        // Fire event for penalty applied
        event(new PenaltyAppliedEvent(
            user: $user,
            penalty: $penalty,
            reason: 'Excessive idle time - auto logout triggered'
        ));
        
        // Logout the user
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return response()->json([
            'message' => 'Penalty applied and user logged out',
            'penalty_id' => $penalty->id,
            'logout_required' => true
        ], 401);
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
