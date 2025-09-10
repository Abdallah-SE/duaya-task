<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

// Removed Spatie Activity Log - using custom activity logging

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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
     * Get the user settings.
     */
    public function settings()
    {
        return $this->hasOne(UserSetting::class);
    }

    /**
     * Get or create user settings.
     */
    public function getSettings()
    {
        return $this->settings ?? $this->settings()->create([
            'idle_timeout' => 5,
            'idle_monitoring_enabled' => true,
            'max_idle_warnings' => 3,
        ]);
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
