<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\IdleSetting;
use App\Models\RoleSetting;
use App\Models\ActivityLog;
use Spatie\Permission\Models\Role;

class AdminSettingsController extends Controller
{

    /**
     * Display admin settings page
     */
    public function index(Request $request)
    {
        // Check web guard for admin user
        $user = Auth::user();
        
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Admin access required');
        }
        
        // Get global idle settings
        $globalSettings = IdleSetting::getDefault();
        
        // Get role-based settings (excluding admin role)
        $allRoles = Role::where('name', '!=', 'admin')->get();
        $roleSettings = $allRoles->mapWithKeys(function($role) {
            $setting = RoleSetting::where('role_id', $role->id)->first();
            return [
                $role->name => $setting ? $setting->idle_monitoring_enabled : true
            ];
        });
        
        // Get all available roles (excluding admin_guard)
        $roles = $allRoles->select('id', 'name');
        
        // Log admin activity
        ActivityLog::logActivity(
            userId: $user->id,
            action: 'view_admin_settings',
            subjectType: 'App\Models\IdleSetting',
            subjectId: null,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        );
        
        return Inertia::render('Admin/Settings/Index', [
            'user' => $user,
            'globalSettings' => $globalSettings,
            'roleSettings' => $roleSettings,
            'roles' => $roles,
        ]);
    }

    /**
     * Update global idle settings
     */
    public function updateGlobalSettings(Request $request)
    {
        $request->validate([
            'idle_timeout' => 'required|integer|min:1|max:300',
            'max_idle_warnings' => 'required|integer|min:1|max:10',
        ]);

        // Check web guard for admin user
        $user = Auth::user();
        
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Admin access required');
        }

        $settings = IdleSetting::updateDefault(
            $request->idle_timeout,
            $request->max_idle_warnings
        );

        // Log admin activity
        ActivityLog::logActivity(
            userId: $user->id,
            action: 'update_global_idle_settings',
            subjectType: 'App\Models\IdleSetting',
            subjectId: $settings->id,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        );

        return redirect()->back()->with('success', 'Global settings updated successfully.');
    }

    /**
     * Update global idle timeout (Admin only)
     */
    public function updateTimeout(Request $request)
    {
        $request->validate([
            'idle_timeout' => 'required|integer|min:1|max:300',
        ]);

        // Check web guard for admin user
        $user = Auth::user();
        
        if (!$user || !$user->hasRole('admin')) {
            return response()->json(['error' => 'Admin access required'], 403);
        }

        // Update global settings (only timeout, keep max_warnings as default)
        $settings = IdleSetting::updateDefault(
            $request->idle_timeout,
            2 // Keep max_warnings as 2 (fixed value)
        );

        // Log admin activity
        ActivityLog::logActivity(
            userId: $user->id,
            action: 'update_idle_timeout',
            subjectType: 'App\Models\IdleSetting',
            subjectId: $settings->id,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        );

        return redirect()->back()->with('success', 'Idle timeout updated successfully.');
    }

    /**
     * Update role-based idle monitoring settings
     */
    public function updateRoleSettings(Request $request)
    {
        $request->validate([
            'role_settings' => 'required|array',
            'role_settings.*' => 'boolean',
        ]);

        // Check web guard for admin user
        $user = Auth::user();
        
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Admin access required');
        }

        $updatedRoles = [];
        
        foreach ($request->role_settings as $roleName => $enabled) {
            if (RoleSetting::updateSetting($roleName, $enabled)) {
                $updatedRoles[] = $roleName;
            }
        }

        // Log admin activity
        ActivityLog::logActivity(
            userId: $user->id,
            action: 'update_role_idle_settings',
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
        $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
            'enabled' => 'required|boolean',
        ]);

        // Check web guard for admin user
        $user = Auth::user();
        
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Admin access required');
        }

        $role = Role::findOrFail($request->role_id);
        
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

        return redirect()->back()->with('success', "Monitoring " . ($request->enabled ? 'enabled' : 'disabled') . " for role: {$role->name}");
    }

    /**
     * Get current settings for API
     */
    public function getSettings()
    {
        $globalSettings = IdleSetting::getDefault();
        $roleSettings = RoleSetting::getAllSettings();
        
        return response()->json([
            'global' => $globalSettings,
            'roles' => $roleSettings,
        ]);
    }

    /**
     * Reset settings to defaults
     */
    public function resetToDefaults(Request $request)
    {
        // Check web guard for admin user
        $user = Auth::user();
        
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Admin access required');
        }
        
        // Reset global settings
        IdleSetting::updateDefault(5, 3);
        
        // Reset all role settings to enabled (excluding admin role)
        $roles = Role::where('name', '!=', 'admin')->get();
        foreach ($roles as $role) {
            RoleSetting::updateSetting($role->name, true);
        }

        // Log admin activity
        ActivityLog::logActivity(
            userId: $user->id,
            action: 'reset_idle_settings_to_defaults',
            subjectType: 'App\Models\IdleSetting',
            subjectId: null,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        );

        return redirect()->back()->with('success', 'Settings reset to defaults successfully.');
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
