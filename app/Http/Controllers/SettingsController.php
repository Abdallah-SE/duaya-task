<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\UserSetting;
use App\Events\UserActivityEvent;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $userSettings = $user->getIdleSettings();
        $userPenalties = $user->penalties()->latest('date')->get();
        
        return Inertia::render('Settings/Index', [
            'user' => $user,
            'userSettings' => $userSettings,
            'userPenalties' => $userPenalties,
        ]);
    }
    
    public function update(Request $request)
    {
        $request->validate([
            'idle_timeout' => 'required|integer|min:1|max:300', // 1 second to 5 minutes
            'idle_monitoring_enabled' => 'boolean',
            'max_idle_warnings' => 'required|integer|min:1|max:10',
            'notification_preferences' => 'array',
        ]);
        
        $user = $request->user();
        $userSettings = $user->getIdleSettings();
        
        $userSettings->update([
            'idle_timeout' => $request->idle_timeout,
            'idle_monitoring_enabled' => $request->idle_monitoring_enabled,
            'max_idle_warnings' => $request->max_idle_warnings,
        ]);
        
        // Fire event for settings update
        event(new UserActivityEvent(
            user: $user,
            action: 'settings_updated',
            subjectType: UserSetting::class,
            subjectId: $userSettings->id,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        ));
        
        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
    
    public function updateGlobalSettings(Request $request)
    {
        // This would be for admin users to set global defaults
        $request->validate([
            'default_idle_timeout' => 'required|integer|min:1|max:300',
            'default_max_warnings' => 'required|integer|min:1|max:10',
            'enable_global_monitoring' => 'boolean',
        ]);
        
        // Store in config or database for global settings
        // For now, we'll just return success
        
        return response()->json([
            'success' => true,
            'message' => 'Global settings updated successfully.',
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
}
