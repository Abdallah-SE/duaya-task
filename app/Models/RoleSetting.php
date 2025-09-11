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
     */
    public static function updateSetting(string $roleName, bool $enabled): bool
    {
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
     */
    public static function getAllSettings()
    {
        return static::with('role:id,name')
            ->select('role_id', 'idle_monitoring_enabled')
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->role->name => $setting->idle_monitoring_enabled];
            });
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