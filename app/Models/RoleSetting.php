<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

class RoleSetting extends Model
{
    protected $table = 'role_settings';

    protected $fillable = [
        'role_id',
        'idle_monitoring_enabled',
    ];

    protected function casts(): array
    {
        return [
            'idle_monitoring_enabled' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the role that owns the settings.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get idle monitoring status for a role (simple direct check).
     */
    public static function isIdleMonitoringEnabledForRole(string $roleName): bool
    {
        $role = Role::where('name', $roleName)->first();
        if (!$role) return false;
        
        $setting = static::where('role_id', $role->id)->first();
        return $setting ? $setting->idle_monitoring_enabled : true; // Default to true
    }

    /**
     * Update role setting and clear cache immediately.
     * Prevents updating admin role settings.
     */
    public static function updateSetting(string $roleName, bool $enabled): bool
    {
        // Prevent updating admin role settings
        if ($roleName === 'admin') {
            return false;
        }

        $role = Role::where('name', $roleName)->first();
        if (!$role) return false;

        $setting = static::updateOrCreate(
            ['role_id' => $role->id],
            ['idle_monitoring_enabled' => $enabled]
        );

        // Clear cache immediately for instant effect
        cache()->forget("role_idle_monitoring_{$roleName}");
        
        return true; // Return success status, not the value
    }

    /**
     * Get all role settings efficiently (for admin dashboard).
     * Includes all roles with admin marked as read-only.
     */
    public static function getAllSettings()
    {
        // Get all roles from all guards
        $adminRoles = \Spatie\Permission\Models\Role::where('guard_name', 'admin')->get();
        $webRoles = \Spatie\Permission\Models\Role::where('guard_name', 'web')->get();
        
        // Get existing settings
        $settings = static::with('role:id,name,guard_name')
            ->select('role_id', 'idle_monitoring_enabled')
            ->get()
            ->keyBy('role_id');
        
        // Build result with all roles
        $result = [];
        
        // Add admin guard roles
        foreach ($adminRoles as $role) {
            $setting = $settings->get($role->id);
            $result[] = [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'role_display_name' => ucfirst($role->name),
                'guard_name' => $role->guard_name,
                'idle_monitoring_enabled' => $setting ? $setting->idle_monitoring_enabled : true,
                'is_admin' => $role->name === 'admin',
                'created_at' => $setting ? $setting->created_at : now(),
                'updated_at' => $setting ? $setting->updated_at : now(),
            ];
        }
        
        // Add web guard roles
        foreach ($webRoles as $role) {
            $setting = $settings->get($role->id);
            $result[] = [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'role_display_name' => ucfirst($role->name),
                'guard_name' => $role->guard_name,
                'idle_monitoring_enabled' => $setting ? $setting->idle_monitoring_enabled : true,
                'is_admin' => $role->name === 'admin',
                'created_at' => $setting ? $setting->created_at : now(),
                'updated_at' => $setting ? $setting->updated_at : now(),
            ];
        }
        
        return $result;
    }

    /**
     * Clear all role setting caches.
     */
    public static function clearAllCaches()
    {
        $roles = Role::pluck('name');
        foreach ($roles as $roleName) {
            cache()->forget("role_idle_monitoring_{$roleName}");
        }
    }

    /**
     * Check if idle monitoring is enabled for this role.
     */
    public function isIdleMonitoringEnabled(): bool
    {
        return $this->idle_monitoring_enabled;
    }
}