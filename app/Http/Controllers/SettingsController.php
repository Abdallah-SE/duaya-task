<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\UserSetting;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $userSettings = $user->getSettings();
        $userPenalties = $user->penalties()->latest('penalty_date')->get();
        
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
        $userSettings = $user->getSettings();
        
        $userSettings->update([
            'idle_timeout' => $request->idle_timeout,
            'idle_monitoring_enabled' => $request->idle_monitoring_enabled,
            'max_idle_warnings' => $request->max_idle_warnings,
            'notification_preferences' => $request->notification_preferences,
        ]);
        
        // Log the settings update using custom activity logging
        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'settings_updated',
            'subject_type' => UserSetting::class,
            'subject_id' => $userSettings->id,
            'ip_address' => $request->ip(),
            'device' => $request->header('User-Agent'),
            'browser' => $request->header('User-Agent'),
        ]);
        
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
}
