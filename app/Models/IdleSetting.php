<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class IdleSetting extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'idle_settings';
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'idle_timeout',
        'max_idle_warnings',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'idle_timeout' => 'integer',
            'max_idle_warnings' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the settings.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get settings for enabled monitoring.
     * Note: idle_settings table doesn't have idle_monitoring_enabled field
     * This is controlled by role_settings table instead
     */
    public function scopeEnabled(Builder $query): Builder
    {
        // Since idle_settings doesn't have idle_monitoring_enabled field,
        // we return all settings as enabled (the actual control is in role_settings)
        return $query;
    }

    /**
     * Scope to get settings by timeout value.
     */
    public function scopeByTimeout(Builder $query, int $timeout): Builder
    {
        return $query->where('idle_timeout', $timeout);
    }

    /**
     * Scope to get settings with minimum timeout.
     */
    public function scopeWithMinimumTimeout(Builder $query, int $minTimeout): Builder
    {
        return $query->where('idle_timeout', '>=', $minTimeout);
    }

    /**
     * Get or create default idle settings.
     */
    public static function getDefault(): self
    {
        // Get the first (and only) idle settings record
        $setting = static::first();
        
        if (!$setting) {
            $setting = static::create([
                'idle_timeout' => 5,
                'max_idle_warnings' => 3,
            ]);
        }
        
        return $setting;
    }

    /**
     * Update default idle settings.
     */
    public static function updateDefault(
        ?int $idleTimeout = null,
        ?int $maxWarnings = null
    ): self {
        $settings = static::getDefault();
        
        $settings->update(array_filter([
            'idle_timeout' => $idleTimeout,
            'max_idle_warnings' => $maxWarnings,
        ], fn($value) => $value !== null));
        
        return $settings;
    }

    /**
     * Check if monitoring is enabled for the user.
     * Note: idle_settings table doesn't have idle_monitoring_enabled field
     * This is controlled by role_settings table instead
     */
    public function isMonitoringEnabled(): bool
    {
        // Since idle_settings doesn't have idle_monitoring_enabled field,
        // we return true (the actual control is in role_settings)
        return true;
    }

    /**
     * Get timeout in milliseconds.
     */
    public function getTimeoutInMilliseconds(): int
    {
        return $this->idle_timeout * 1000;
    }
}
