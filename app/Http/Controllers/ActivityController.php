<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Penalty;
use App\Models\User;
use App\Models\ActivityLog;
use App\Events\UserActivityEvent;
use App\Events\PenaltyAppliedEvent;
use App\Http\Requests\ApplyPenaltyRequest;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $activities = ActivityLog::with('user')
            ->latest()
            ->paginate(20)
            ->through(function ($activity) {
                return [
                    'id' => $activity->id,
                    'action' => $activity->action,
                    'subject_type' => $activity->subject_type,
                    'subject_id' => $activity->subject_id,
                    'user' => $activity->user ? [
                        'id' => $activity->user->id,
                        'name' => $activity->user->name,
                        'email' => $activity->user->email,
                    ] : null,
                    'created_at' => $activity->created_at,
                    'ip_address' => $activity->ip_address,
                    'device' => $activity->device,
                    'browser' => $activity->browser,
                ];
            });
        
        return Inertia::render('Activities/Index', [
            'activities' => $activities,
        ]);
    }
    
    public function applyPenalty(ApplyPenaltyRequest $request)
    {
        $penalty = Penalty::create([
            'user_id' => $request->input('user_id'),
            'reason' => $request->input('reason'),
            'count' => $request->input('count', 1),
            'date' => now(),
            'metadata' => $request->input('metadata', []),
        ]);
        
        // Fire event for penalty application
        event(new PenaltyAppliedEvent(
            user: auth()->user(),
            penalty: $penalty,
            reason: $request->input('reason')
        ));
        
        return response()->json([
            'success' => true,
            'penalty' => $penalty,
        ]);
    }
    
    public function getUserActivities(Request $request, User $user)
    {
        $activities = ActivityLog::where('user_id', $user->id)
            ->latest()
            ->paginate(20)
            ->through(function ($activity) {
                return [
                    'id' => $activity->id,
                    'action' => $activity->action,
                    'subject_type' => $activity->subject_type,
                    'subject_id' => $activity->subject_id,
                    'created_at' => $activity->created_at,
                    'ip_address' => $activity->ip_address,
                    'device' => $activity->device,
                    'browser' => $activity->browser,
                ];
            });
        
        return Inertia::render('Activities/UserActivities', [
            'user' => $user,
            'activities' => $activities,
        ]);
    }
}