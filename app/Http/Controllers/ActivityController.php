<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Penalty;
use App\Models\User;
use App\Models\ActivityLog;

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
    
    public function applyPenalty(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'required|string|max:255',
            'count' => 'integer|min:1',
        ]);
        
        $penalty = Penalty::create([
            'user_id' => $request->user_id,
            'reason' => $request->reason,
            'count' => $request->count ?? 1,
            'penalty_date' => now(),
            'metadata' => $request->metadata ?? [],
        ]);
        
        // Log the penalty application using custom activity logging
        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'penalty_applied',
            'subject_type' => Penalty::class,
            'subject_id' => $penalty->id,
            'ip_address' => $request->ip(),
            'device' => $request->header('User-Agent'),
            'browser' => $request->header('User-Agent'),
        ]);
        
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
