<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class IdleSession extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'idle_sessions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'idle_started_at',
        'idle_ended_at',
        'duration_seconds',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'idle_started_at' => 'datetime',
            'idle_ended_at' => 'datetime',
            'duration_seconds' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the idle session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get sessions by user.
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get sessions within a date range.
     */
    public function scopeInDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('idle_started_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get sessions with minimum duration.
     */
    public function scopeWithMinimumDuration(Builder $query, int $minDuration): Builder
    {
        return $query->where('duration_seconds', '>=', $minDuration);
    }

    /**
     * Scope to get active sessions (not ended).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('idle_ended_at');
    }

    /**
     * Scope to get completed sessions (ended).
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('idle_ended_at');
    }

    /**
     * Create a new idle session.
     */
    public static function startSession(int $userId): self
    {
        try {
            $session = static::create([
                'user_id' => $userId,
                'idle_started_at' => now(),
            ]);
            
            Log::info('IdleSession::startSession - Session created successfully', [
                'session_id' => $session->id,
                'user_id' => $userId,
                'idle_started_at' => $session->idle_started_at,
                'created_at' => $session->created_at
            ]);
            
            return $session;
        } catch (\Exception $e) {
            Log::error('IdleSession::startSession - Failed to create session', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * End the idle session and calculate duration.
     */
    public function endSession(): self
    {
        $duration = max(0, now()->diffInSeconds($this->idle_started_at));
        
        $this->update([
            'idle_ended_at' => now(),
            'duration_seconds' => $duration,
        ]);

        return $this;
    }

    /**
     * Check if the session is active.
     */
    public function isActive(): bool
    {
        return is_null($this->idle_ended_at);
    }

    /**
     * Get the duration in a human-readable format.
     */
    public function getDurationForHumans(): string
    {
        if (!$this->duration_seconds) {
            return '0 seconds';
        }

        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        $parts = [];
        if ($hours > 0) $parts[] = "{$hours}h";
        if ($minutes > 0) $parts[] = "{$minutes}m";
        if ($seconds > 0) $parts[] = "{$seconds}s";

        return implode(' ', $parts) ?: '0 seconds';
    }

    /**
     * Get the total idle time for a user.
     */
    public static function getTotalIdleTimeForUser(int $userId): int
    {
        return static::where('user_id', $userId)
            ->whereNotNull('duration_seconds')
            ->sum('duration_seconds');
    }

    /**
     * Get the average idle time for a user.
     */
    public static function getAverageIdleTimeForUser(int $userId): float
    {
        $sessions = static::where('user_id', $userId)
            ->whereNotNull('duration_seconds')
            ->get();

        if ($sessions->isEmpty()) {
            return 0.0;
        }

        return $sessions->avg('duration_seconds');
    }
}
