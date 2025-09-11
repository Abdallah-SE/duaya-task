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
     */
    public function idleSettings()
    {
        return $this->hasOne(\App\Models\IdleSetting::class);
    }

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
     */
    public function isIdleMonitoringEnabled(): bool
    {
        // Simple direct database check - no caching, no complexity
        $userRoles = $this->getRoleNames();
        Log::info('userRoles' );
        Log::info($userRoles );
        foreach ($userRoles as $roleName) {
            $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
            if ($role) {
                $setting = \App\Models\RoleSetting::where('role_id', $role->id)->first();
                if ($setting && $setting->idle_monitoring_enabled) {
                    Log::info('true' );
                    Log::info(true );
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Check if user can control idle monitoring (admin only).
     */
    public function canControlIdleMonitoring(): bool
    {
        return $this->hasRole('admin');
    }
}
