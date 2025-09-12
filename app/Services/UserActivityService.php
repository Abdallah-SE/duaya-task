<?php

namespace App\Services;

use App\Events\UserActivityCreatedEvent;
use App\Events\UserActivityUpdatedEvent;
use App\Events\UserActivityDeletedEvent;
use App\Events\UserActivityViewedEvent;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserActivityService
{
    /**
     * Log a user activity event.
     */
    public function logActivity(
        User $user,
        string $action,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?string $ipAddress = null,
        ?string $device = null,
        ?string $browser = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'ip_address' => $ipAddress,
            'device' => $device,
            'browser' => $browser,
        ]);
    }

    /**
     * Log a CRUD operation with proper event dispatching.
     */
    public function logCrudOperation(
        string $operation,
        Model $model,
        User $user,
        array $originalData = [],
        array $updatedData = [],
        array $metadata = []
    ): void {
        $ipAddress = request()->ip();
        $device = $this->getDeviceInfo();
        $browser = $this->getBrowserInfo();

        switch ($operation) {
            case 'created':
                event(new UserActivityCreatedEvent($model, $user, $metadata, $ipAddress, $device, $browser));
                break;
            case 'updated':
                event(new UserActivityUpdatedEvent($model, $user, $originalData, $updatedData, $metadata, $ipAddress, $device, $browser));
                break;
            case 'deleted':
                event(new UserActivityDeletedEvent($model, $user, $originalData, $metadata, $ipAddress, $device, $browser));
                break;
            case 'viewed':
                event(new UserActivityViewedEvent($model, $user, 'view', $metadata, $ipAddress, $device, $browser));
                break;
            default:
                Log::warning('Unknown CRUD operation', ['operation' => $operation, 'model' => get_class($model)]);
        }
    }

    /**
     * Get user activity statistics.
     */
    public function getUserActivityStats(int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = ActivityLog::where('user_id', $userId);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $activities = $query->get();

        return [
            'total_activities' => $activities->count(),
            'activities_by_action' => $activities->groupBy('action')->map->count(),
            'activities_by_subject' => $activities->groupBy('subject_type')->map->count(),
            'recent_activities' => $activities->sortByDesc('created_at')->take(10),
            'most_active_day' => $this->getMostActiveDay($activities),
            'device_usage' => $activities->groupBy('device')->map->count(),
            'browser_usage' => $activities->groupBy('browser')->map->count(),
        ];
    }

    /**
     * Get system-wide activity statistics.
     */
    public function getSystemActivityStats(?string $startDate = null, ?string $endDate = null): array
    {
        $query = ActivityLog::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $activities = $query->get();

        return [
            'total_activities' => $activities->count(),
            'unique_users' => $activities->pluck('user_id')->unique()->count(),
            'activities_by_action' => $activities->groupBy('action')->map->count(),
            'activities_by_subject' => $activities->groupBy('subject_type')->map->count(),
            'most_active_users' => $activities->groupBy('user_id')->map->count()->sortDesc()->take(10),
            'device_usage' => $activities->groupBy('device')->map->count(),
            'browser_usage' => $activities->groupBy('browser')->map->count(),
        ];
    }

    /**
     * Get activities for a specific model.
     */
    public function getModelActivities(string $modelClass, int $modelId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return ActivityLog::where('subject_type', $modelClass)
            ->where('subject_id', $modelId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Clean up old activity logs.
     */
    public function cleanupOldLogs(int $daysToKeep = 90): int
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        return ActivityLog::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Get device information from request.
     */
    private function getDeviceInfo(): string
    {
        $userAgent = request()->userAgent();
        
        if (str_contains($userAgent, 'Mobile')) {
            return 'Mobile';
        } elseif (str_contains($userAgent, 'Tablet')) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }

    /**
     * Get browser information from request.
     */
    private function getBrowserInfo(): string
    {
        $userAgent = request()->userAgent();
        
        if (str_contains($userAgent, 'Chrome')) {
            return 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox')) {
            return 'Firefox';
        } elseif (str_contains($userAgent, 'Safari')) {
            return 'Safari';
        } elseif (str_contains($userAgent, 'Edge')) {
            return 'Edge';
        } else {
            return 'Unknown';
        }
    }

    /**
     * Get the most active day from activities.
     */
    private function getMostActiveDay($activities): ?string
    {
        $dayCounts = $activities->groupBy(function ($activity) {
            return $activity->created_at->format('Y-m-d');
        })->map->count();

        if ($dayCounts->isEmpty()) {
            return null;
        }

        return $dayCounts->sortDesc()->keys()->first();
    }
}
