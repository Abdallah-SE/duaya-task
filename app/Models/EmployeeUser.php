<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class EmployeeUser extends Authenticatable
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
        
        // Add a global scope to only return users with employee role
        static::addGlobalScope('employee', function ($builder) {
            $builder->whereHas('roles', function ($query) {
                $query->where('name', 'employee');
            });
        });
    }

    /**
     * Get the employee record associated with this user
     */
    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
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
     * Check if idle monitoring is enabled for this employee
     */
    public function isIdleMonitoringEnabled(): bool
    {
        // Check if monitoring is enabled for employee role
        $roleSetting = RoleSetting::where('role_name', 'employee')->first();
        return $roleSetting ? $roleSetting->idle_monitoring_enabled : true;
    }

    /**
     * Check if employee can control idle monitoring
     */
    public function canControlIdleMonitoring(): bool
    {
        // Employees can control their own monitoring settings
        return true;
    }

    /**
     * Get employee's activity logs
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }

    /**
     * Get employee's penalties
     */
    public function penalties()
    {
        return $this->hasMany(Penalty::class, 'user_id');
    }

    /**
     * Get employee's idle sessions
     */
    public function idleSessions()
    {
        return $this->hasMany(IdleSession::class, 'user_id');
    }
}
