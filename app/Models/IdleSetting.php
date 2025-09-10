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
        'user_id',
        'idle_timeout',
        'idle_monitoring_enabled',
        'max_idle_warnings',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'idle_monitoring_enabled' => 'boolean',
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
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('idle_monitoring_enabled', true);
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
     * Get or create idle settings for a user.
     */
    public static function getForUser(int $userId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'idle_timeout' => 5,
                'idle_monitoring_enabled' => true,
                'max_idle_warnings' => 3,
            ]
        );
    }

    /**
     * Update idle settings for a user.
     */
    public static function updateForUser(
        int $userId,
        ?int $idleTimeout = null,
        ?bool $monitoringEnabled = null,
        ?int $maxWarnings = null
    ): self {
        $settings = static::getForUser($userId);
        
        $settings->update(array_filter([
            'idle_timeout' => $idleTimeout,
            'idle_monitoring_enabled' => $monitoringEnabled,
            'max_idle_warnings' => $maxWarnings,
        ], fn($value) => $value !== null));
        
        return $settings;
    }

    /**
     * Check if monitoring is enabled for the user.
     */
    public function isMonitoringEnabled(): bool
    {
        return $this->idle_monitoring_enabled;
    }

    /**
     * Get timeout in milliseconds.
     */
    public function getTimeoutInMilliseconds(): int
    {
        return $this->idle_timeout * 1000;
    }
}
