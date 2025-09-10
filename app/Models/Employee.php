<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// Removed Spatie Activity Log - using custom activity logging

class Employee extends Model
{
    // Removed Spatie Activity Log trait - using custom activity logging

    protected $fillable = [
        'user_id',
        'job_title',
        'department',
        'hire_date',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
        ];
    }

    /**
     * Get the user that owns the employee record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Removed manager/subordinates relationships for clean structure

    /**
     * Get the penalties for this employee.
     */
    public function penalties(): HasMany
    {
        return $this->hasMany(Penalty::class, 'user_id', 'user_id');
    }

    /**
     * Get the user settings for this employee.
     */
    public function settings()
    {
        return $this->user->settings();
    }

    /**
     * Get or create employee settings.
     */
    public function getSettings()
    {
        return $this->user->getSettings();
    }

    // Removed Spatie Activity Log methods - using custom activity logging

    /**
     * Scope to get employees by department.
     */
    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }
}
