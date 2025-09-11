<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role; // Use standard Spatie Role

class RoleSetting extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'role_settings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'role_id',
        'idle_monitoring_enabled',
    ];

    /**
     * The attributes that should be cast.
     */
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
        return $this->belongsTo(Role::class); // Use standard Role
    }

    /**
     * Get or create role settings for a specific role.
     */
    public static function getForRole(int $roleId): self
    {
        return static::firstOrCreate(
            ['role_id' => $roleId],
            ['idle_monitoring_enabled' => true]
        );
    }

    /**
     * Get or create role settings by role name.
     */
    public static function getForRoleName(string $roleName): self
    {
        $role = Role::where('name', $roleName)->firstOrFail(); // Use standard Role
        return static::getForRole($role->id);
    }

    /**
     * Update role settings.
     */
    public static function updateForRole(
        int $roleId,
        ?bool $monitoringEnabled = null
    ): self {
        $settings = static::getForRole($roleId);
        
        $settings->update(array_filter([
            'idle_monitoring_enabled' => $monitoringEnabled,
        ], fn($value) => $value !== null));
        
        return $settings;
    }

    /**
     * Update role settings by role name.
     */
    public static function updateForRoleName(
        string $roleName,
        ?bool $monitoringEnabled = null
    ): self {
        $role = Role::where('name', $roleName)->firstOrFail(); // Use standard Role
        return static::updateForRole($role->id, $monitoringEnabled);
    }

    /**
     * Check if idle monitoring is enabled for this role.
     */
    public function isIdleMonitoringEnabled(): bool
    {
        return $this->idle_monitoring_enabled;
    }
}

