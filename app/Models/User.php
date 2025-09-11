<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

// Removed Spatie Activity Log - using custom activity logging

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'role_names',
        'has_admin_role',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Removed Spatie Activity Log methods - using custom activity logging

    /**
     * Get the penalties for the user.
     */
    public function penalties()
    {
        return $this->hasMany(\App\Models\Penalty::class);
    }

    /**
     * Get the user's idle settings.
     * Note: idle_settings table stores global settings, not per-user settings.
     * Use getIdleSettings() method instead.
     */
    // public function idleSettings()
    // {
    //     return $this->hasOne(\App\Models\IdleSetting::class);
    // }

    /**
     * Get default idle settings.
     */
    public function getIdleSettings()
    {
        return \App\Models\IdleSetting::getDefault();
    }

    /**
     * Get the user's activity logs.
     */
    public function activityLogs()
    {
        return $this->hasMany(\App\Models\ActivityLog::class);
    }

    /**
     * Get the user's idle sessions.
     */
    public function idleSessions()
    {
        return $this->hasMany(\App\Models\IdleSession::class);
    }

    /**
     * Get the employee record for this user.
     */
    public function employee()
    {
        return $this->hasOne(\App\Models\Employee::class);
    }

    /**
     * Check if user is an employee.
     */
    public function isEmployee()
    {
        return $this->employee !== null;
    }

    /**
     * Check if idle monitoring is enabled for this user's role.
     * Optimized version using single query instead of foreach loops.
     */
    public function isIdleMonitoringEnabled(): bool
    {
        // Get user's role IDs in a single query (works with both guards)
        $userRoleIds = $this->roles()->pluck('id');
        
        if ($userRoleIds->isEmpty()) {
            return false;
        }

        // Check if any of the user's roles have monitoring enabled
        return \App\Models\RoleSetting::whereIn('role_id', $userRoleIds)
            ->where('idle_monitoring_enabled', true)
            ->exists();
    }

    /**
     * Check if user can control idle monitoring (admin only).
     */
    public function canControlIdleMonitoring(): bool
    {
        // Check if user has admin role (works with both guards)
        return $this->hasRole('admin');
    }

    /**
     * Get the user's role names for frontend.
     */
    public function getRoleNamesAttribute(): array
    {
        return $this->getRoleNames()->toArray();
    }

    /**
     * Check if user has admin role for frontend.
     */
    public function getHasAdminRoleAttribute(): bool
    {
        return $this->hasRole('admin');
    }
}
