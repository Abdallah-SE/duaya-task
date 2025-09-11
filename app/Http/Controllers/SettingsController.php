<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
// use App\Models\UserSetting; // Removed - using global settings
use App\Models\IdleSetting;
use App\Models\RoleSetting;
use App\Models\ActivityLog;
use App\Events\UserActivityEvent;

class SettingsController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::guard('web')->user();
        $globalSettings = IdleSetting::getDefault();
        $userPenalties = $user->penalties()->latest('date')->get();
        
        // Check if user can control idle monitoring (admin guard users can)
        $canControlIdleMonitoring = Auth::guard('admin')->check();
        
        // Get all roles and their settings (excluding admin_guard roles)
        $allRoles = \Spatie\Permission\Models\Role::where('guard_name', '!=', 'admin')->get();
        $roleSettings = $allRoles->map(function($role) {
            $setting = \App\Models\RoleSetting::where('role_id', $role->id)->first();
            return [
                'id' => $setting ? $setting->id : null,
                'role_id' => $role->id,
                'role_name' => $role->name,
                'role_display_name' => ucfirst($role->name),
                'guard_name' => $role->guard_name,
                'idle_monitoring_enabled' => $setting ? $setting->idle_monitoring_enabled : true, // Default to true if no setting
                'is_admin' => $role->name === 'admin', // This will be false for all non-admin roles
                'created_at' => $setting ? $setting->created_at : $role->created_at,
                'updated_at' => $setting ? $setting->updated_at : $role->updated_at,
            ];
        });
        
        return Inertia::render('Settings/Index', [
            'user' => $user,
            'globalSettings' => $globalSettings,
            'userPenalties' => $userPenalties,
            'canControlIdleMonitoring' => $canControlIdleMonitoring,
            'isIdleMonitoringEnabled' => $user->isIdleMonitoringEnabled(),
            'roleSettings' => $roleSettings,
        ]);
    }
    
    public function update(Request $request)
    {
        $user = Auth::guard('web')->user();
        
        // Only admin guard users can update settings
        if (!Auth::guard('admin')->check()) {
            return redirect()->back()->with('info', 'Settings are managed by administrators. Please contact your admin to modify these settings.');
        }
        
        $request->validate([
            'idle_timeout' => 'required|integer|min:1|max:300',
            'max_idle_warnings' => 'required|integer|min:1|max:10',
        ]);
        
        // Update global settings
        $settings = IdleSetting::updateDefault(
            $request->idle_timeout,
            $request->max_idle_warnings
        );
        
        // Log activity
        ActivityLog::logActivity(
            userId: $user->id,
            action: 'update_global_idle_settings',
            subjectType: 'App\Models\IdleSetting',
            subjectId: $settings->id,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        );
        
        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
    
    /**
     * Get current settings for API
     */
    public function getSettings(Request $request)
    {
        $user = Auth::guard('web')->user();
        $globalSettings = IdleSetting::getDefault();
        $roleSettings = RoleSetting::getAllSettings();
        
        return response()->json([
            'global' => $globalSettings,
            'roles' => $roleSettings,
            'userCanControl' => Auth::guard('admin')->check(),
        ]);
    }

    /**
     * Update role monitoring settings (Admin only)
     */
    public function updateRoleSettings(Request $request)
    {
        $user = Auth::guard('web')->user();
        
        if (!Auth::guard('admin')->check()) {
            return redirect()->back()->with('error', 'Admin access required');
        }

        $request->validate([
            'role_settings' => 'required|array',
            'role_settings.*.role_id' => 'required|integer|exists:roles,id',
            'role_settings.*.idle_monitoring_enabled' => 'required|boolean',
        ]);

        $updatedRoles = [];
        
        foreach ($request->role_settings as $setting) {
            $role = \Spatie\Permission\Models\Role::findOrFail($setting['role_id']);
            
            RoleSetting::updateOrCreate(
                ['role_id' => $role->id],
                ['idle_monitoring_enabled' => $setting['idle_monitoring_enabled']]
            );
            
            $updatedRoles[] = $role->name;
        }

        // Log admin activity
        ActivityLog::logActivity(
            userId: $user->id,
            action: 'update_role_monitoring_settings',
            subjectType: 'App\Models\RoleSetting',
            subjectId: null,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        );

        return redirect()->back()->with('success', 'Role settings updated successfully.');
    }

    /**
     * Toggle monitoring for a specific role (Admin only)
     */
    public function toggleRoleMonitoring(Request $request)
    {
        $user = Auth::guard('web')->user();
        
        if (!Auth::guard('admin')->check()) {
            return response()->json(['message' => 'Admin access required'], 403);
        }

        $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
            'enabled' => 'required|boolean',
        ]);

        $role = \Spatie\Permission\Models\Role::findOrFail($request->role_id);
        
        $roleSetting = RoleSetting::updateOrCreate(
            ['role_id' => $role->id],
            ['idle_monitoring_enabled' => $request->enabled]
        );

        // Log admin activity
        ActivityLog::logActivity(
            userId: $user->id,
            action: 'toggle_role_monitoring',
            subjectType: 'App\Models\RoleSetting',
            subjectId: $roleSetting->id,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        );

        return response()->json([
            'message' => "Monitoring " . ($request->enabled ? 'enabled' : 'disabled') . " for role: " . $role->name,
            'role_setting' => [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'idle_monitoring_enabled' => $roleSetting->idle_monitoring_enabled,
            ],
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
     * Get user monitoring status
     */
    public function getUserMonitoringStatus(Request $request)
    {
        $user = Auth::guard('web')->user();
        
        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }
        
        $isIdleMonitoringEnabled = $user->isIdleMonitoringEnabled();
        $userSettings = $user->getIdleSettings();
        
        return response()->json([
            'isIdleMonitoringEnabled' => $isIdleMonitoringEnabled,
            'userSettings' => $userSettings,
            'roleSettings' => RoleSetting::getAllSettings(),
        ]);
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
