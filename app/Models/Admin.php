<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    protected $table = 'users';
    
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        // Add a global scope to only return users with admin role
        static::addGlobalScope('admin', function ($builder) {
            $builder->whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            });
        });
    }

    /**
     * Get the user's idle settings
     */
    public function getIdleSettings()
    {
        return IdleSetting::firstOrCreate(
            ['user_id' => $this->id],
            [
                'idle_timeout' => 5,
                'max_idle_warnings' => 3,
            ]
        );
    }

    /**
     * Check if idle monitoring is enabled for this admin
     */
    public function isIdleMonitoringEnabled(): bool
    {
        return true; // Admins always have monitoring enabled
    }

    /**
     * Check if admin can control idle monitoring
     */
    public function canControlIdleMonitoring(): bool
    {
        return true; // Admins can always control monitoring
    }

    /**
     * Get admin's activity logs
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }

    /**
     * Get admin's penalties
     */
    public function penalties()
    {
        return $this->hasMany(Penalty::class, 'user_id');
    }

    /**
     * Get admin's idle sessions
     */
    public function idleSessions()
    {
        return $this->hasMany(IdleSession::class, 'user_id');
    }
}
