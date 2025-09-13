<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\IdleSetting;
use App\Models\Penalty;
use App\Events\UserCreatedEvent;
use App\Events\UserUpdatedEvent;
use App\Events\UserDeletedEvent;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{
    /**
     * Display a listing of users (Admin and Employee).
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);
        
        $currentUser = auth('admin')->user() ?? Auth::user();
        
        // Build query based on user role
        $query = User::with(['roles', 'penalties'])
            ->withCount(['activityLogs', 'idleSessions']);
        
        // If user is employee, only show employees
        if ($currentUser->hasRole('employee') && !$currentUser->hasRole('admin')) {
            $query->whereHas('roles', function($q) {
                $q->where('name', 'employee');
            });
        }
        
        $users = $query->paginate(15);
        
        // Enhanced stats for user management
        $stats = [
            'totalUsers' => $users->total(),
            'activeUsers' => $users->where('activity_logs_count', '>', 0)->count(),
            'totalActivities' => $users->sum('activity_logs_count'),
            'totalPenalties' => $users->sum(function($user) {
                return $user->penalties->count();
            })
        ];
        
        // Determine which component to render based on the route
        $isEmployeeRoute = request()->is('employee/users*');
        
        if ($isEmployeeRoute) {
            // Get user settings for the current user
            $userSettings = $currentUser->getIdleSettings();
            $isIdleMonitoringEnabled = $currentUser->isIdleMonitoringEnabled();
            
            return Inertia::render('Employee/Users/Index', [
                'users' => $users,
                'user' => $currentUser,
                'userSettings' => $userSettings,
                'initialSettings' => $userSettings,
                'canControlIdleMonitoring' => $currentUser->hasRole('admin'),
                'isIdleMonitoringEnabled' => $isIdleMonitoringEnabled,
                'stats' => $stats
            ]);
        } else {
            return Inertia::render('Admin/Users/Index', [
                'users' => $users,
                'currentUser' => $currentUser,
                'stats' => $stats
            ]);
        }
    }
    
    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request)
    {
        $currentUser = auth('admin')->user() ?? Auth::user();
        
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);
        
        // Assign default role as employee
        $user->assignRole('employee');
        
        // Create default idle settings
        $user->getIdleSettings();
        
        // Dispatch event for user creation
        event(new UserCreatedEvent(
            $user,
            Auth::id(),
            $request->getClientIp(),
            $this->getDeviceInfo($request),
            $this->getBrowserInfo($request)
        ));
        
        $redirectRoute = $currentUser->hasRole('admin') ? 'admin.users.index' : 'employee.users.index';
        return redirect()->route($redirectRoute)
            ->with('success', 'User created successfully.');
    }
    
    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $currentUser = auth('admin')->user() ?? Auth::user();
        
        $user->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ]);
        
        // Dispatch event for user update
        event(new UserUpdatedEvent(
            $user,
            Auth::id(),
            $request->getClientIp(),
            $this->getDeviceInfo($request),
            $this->getBrowserInfo($request)
        ));
        
        $redirectRoute = $currentUser->hasRole('admin') ? 'admin.users.index' : 'employee.users.index';
        return redirect()->route($redirectRoute)
            ->with('success', 'User updated successfully.');
    }
    
    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Dispatch event for user deletion (before deleting)
        event(new UserDeletedEvent(
            $user,
            Auth::id(),
            request()->ip(),
            $this->getDeviceInfo(request()),
            $this->getBrowserInfo(request())
        ));
        
        $user->delete();
        
        $currentUser = auth('admin')->user() ?? Auth::user();
        $redirectRoute = $currentUser->hasRole('admin') ? 'admin.users.index' : 'employee.users.index';
        return redirect()->route($redirectRoute)
            ->with('success', 'User deleted successfully.');
    }
    
    /**
     * Get device information from request.
     */
    private function getDeviceInfo(Request $request): string
    {
        $userAgent = $request->userAgent();
        
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
    private function getBrowserInfo(Request $request): string
    {
        $userAgent = $request->userAgent();
        
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
}