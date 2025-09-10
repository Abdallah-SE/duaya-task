<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Penalty extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'penalties';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'reason',
        'count',
        'date',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'count' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the penalty.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get penalties by user.
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get penalties by date range.
     */
    public function scopeInDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to get penalties by count.
     */
    public function scopeByCount(Builder $query, int $count): Builder
    {
        return $query->where('count', $count);
    }

    /**
     * Scope to get penalties with minimum count.
     */
    public function scopeWithMinimumCount(Builder $query, int $minCount): Builder
    {
        return $query->where('count', '>=', $minCount);
    }

    /**
     * Create a new penalty for a user.
     */
    public static function createPenalty(
        int $userId,
        string $reason,
        int $count = 1,
        ?string $date = null
    ): self {
        return static::create([
            'user_id' => $userId,
            'reason' => $reason,
            'count' => $count,
            'date' => $date ?? now()->toDateString(),
        ]);
    }

    /**
     * Get the total penalty count for a user.
     */
    public static function getTotalCountForUser(int $userId): int
    {
        return static::where('user_id', $userId)->sum('count');
    }
}
