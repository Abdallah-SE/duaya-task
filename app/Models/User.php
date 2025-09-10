<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
        return $this->hasMany(Penalty::class);
    }

    /**
     * Get the user's idle settings.
     */
    public function idleSettings()
    {
        return $this->hasOne(IdleSetting::class);
    }

    /**
     * Get or create user idle settings.
     */
    public function getIdleSettings()
    {
        return $this->idleSettings ?? IdleSetting::getForUser($this->id);
    }

    /**
     * Get the user's activity logs.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get the user's idle sessions.
     */
    public function idleSessions()
    {
        return $this->hasMany(IdleSession::class);
    }

    /**
     * Get the employee record for this user.
     */
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Check if user is an employee.
     */
    public function isEmployee()
    {
        return $this->employee !== null;
    }
}
