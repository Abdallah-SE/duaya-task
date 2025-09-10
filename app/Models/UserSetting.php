<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdleSetting extends Model
{
    protected $table = 'idle_settings';
    
    protected $fillable = [
        'user_id',
        'idle_timeout',
        'idle_monitoring_enabled',
        'max_idle_warnings',
    ];

    protected function casts(): array
    {
        return [
            'idle_monitoring_enabled' => 'boolean',
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
     * Get or create idle settings for a user.
     */
    public static function getForUser($userId)
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'idle_timeout' => 5,
                'idle_monitoring_enabled' => true,
                'max_idle_warnings' => 2,
            ]
        );
    }
}
