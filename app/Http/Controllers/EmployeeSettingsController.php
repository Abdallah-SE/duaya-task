<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\IdleSetting;
use App\Models\RoleSetting;
use App\Models\ActivityLog;
use App\Events\Settings\SettingsViewedEvent;

class EmployeeSettingsController extends Controller
{

    /**
     * Display employee settings page
     */
    public function index(Request $request)
    {
        // Check web guard for employee user
        $user = Auth::user();
        
        if (!$user || !$user->hasRole('employee')) {
            abort(403, 'Employee access required');
        }
        
        $globalSettings = IdleSetting::getDefault();
        $userPenalties = $user->penalties()->latest('date')->get();
        
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
        
        // Dispatch settings viewed event
        SettingsViewedEvent::dispatch(
            $user,
            $request->ip(),
            $this->getDeviceInfo($request),
            $this->getBrowserInfo($request),
            'employee'
        );
        
        return Inertia::render('Settings/Index', [
            'user' => $user,
            'globalSettings' => $globalSettings,
            'userPenalties' => $userPenalties,
            'canControlIdleMonitoring' => false, // Employees cannot control settings
            'isIdleMonitoringEnabled' => $user->isIdleMonitoringEnabled(),
            'roleSettings' => $roleSettings,
        ]);
    }
    
    /**
     * Update settings (Employee cannot update - read only)
     */
    public function update(Request $request)
    {
        // Employees cannot update settings - this is read-only for them
        return redirect()->back()->with('info', 'Settings are managed by administrators. Please contact your admin to modify these settings.');
    }
    
    /**
     * Get current settings for API
     */
    public function getSettings(Request $request)
    {
        $user = Auth::user();
        $globalSettings = IdleSetting::getDefault();
        $roleSettings = RoleSetting::getAllSettings();
        
        return response()->json([
            'global' => $globalSettings,
            'roles' => $roleSettings,
            'userCanControl' => false, // Employees cannot control settings
        ]);
    }

    /**
     * Get user monitoring status
     */
    public function getUserMonitoringStatus(Request $request)
    {
        $user = Auth::user();
        
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
