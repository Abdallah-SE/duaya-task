<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\IdleSetting;
use App\Models\RoleSetting;
use App\Models\ActivityLog;
use Spatie\Permission\Models\Role;
use App\Events\GlobalIdleSettingsUpdatedEvent;
use App\Events\RoleMonitoringToggledEvent;
use App\Events\Settings\RoleSettingsUpdatedEvent;
use App\Events\Settings\SettingsViewedEvent;
use App\Events\Settings\SettingsResetEvent;

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
        
        // Dispatch settings viewed event
        SettingsViewedEvent::dispatch(
            $user,
            $request->ip(),
            $this->getDeviceInfo($request),
            $this->getBrowserInfo($request),
            'admin'
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

        // Get old settings for comparison
        $oldSettings = IdleSetting::getDefault();
        
        $settings = IdleSetting::updateDefault(
            $request->idle_timeout,
            $request->max_idle_warnings
        );

        // Dispatch global settings updated event
        event(new GlobalIdleSettingsUpdatedEvent(
            $settings,
            Auth::id(),
            $request->getClientIp(),
            $this->getDeviceInfo($request),
            $this->getBrowserInfo($request),
            $oldSettings
        ));

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

        // Get old settings for comparison
        $oldSettings = IdleSetting::getDefault();
        
        // Update global settings (only timeout, keep max_warnings as default)
        $settings = IdleSetting::updateDefault(
            $request->idle_timeout,
            2 // Keep max_warnings as 2 (fixed value)
        );

        // Dispatch global settings updated event
        event(new GlobalIdleSettingsUpdatedEvent(
            $settings,
            Auth::id(),
            $request->getClientIp(),
            $this->getDeviceInfo($request),
            $this->getBrowserInfo($request),
            $oldSettings
        ));

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

        // Dispatch role settings updated event
        RoleSettingsUpdatedEvent::dispatch(
            $user,
            $request->role_settings,
            $request->ip(),
            $this->getDeviceInfo($request),
            $this->getBrowserInfo($request),
            $updatedRoles
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

        // Dispatch event for role monitoring toggle
        event(new RoleMonitoringToggledEvent(
            $roleSetting,
            Auth::id(),
            $request->getClientIp(),
            $this->getDeviceInfo($request),
            $this->getBrowserInfo($request),
            $request->enabled
        ));

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

        // Dispatch settings reset event
        SettingsResetEvent::dispatch(
            $user,
            $request->ip(),
            $this->getDeviceInfo($request),
            $this->getBrowserInfo($request),
            'all'
        );

        return redirect()->back()->with('success', 'Settings reset to defaults successfully.');
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
}
