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

class UserController extends Controller
{
    /**
     * Display a listing of users (Admin and Employee).
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);
        
        $currentUser = Auth::user();
        
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
        
        // Log activity
        ActivityLog::logActivity(
            userId: Auth::id(),
            action: 'view_users',
            subjectType: 'App\Models\User',
            subjectId: null,
            ipAddress: request()->ip(),
            device: $this->getDeviceInfo(request()),
            browser: $this->getBrowserInfo(request())
        );
        
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
     * Show the form for creating a new user.
     */
    public function create()
    {
        $this->authorize('create', User::class);
        
        // For modal-based approach, we don't need a separate page
        $currentUser = Auth::user();
        $redirectRoute = $currentUser->hasRole('admin') ? 'admin.users.index' : 'employee.users.index';
        return redirect()->route($redirectRoute);
    }
    
    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        
        $currentUser = Auth::user();
        
        // Define validation rules based on user role
        $roleValidation = 'required|string|in:employee';
        if ($currentUser->hasRole('admin')) {
            $roleValidation = 'required|string|in:admin,employee';
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => $roleValidation,
        ]);
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        
        // Assign role
        $user->assignRole($validated['role']);
        
        // Create default idle settings
        IdleSetting::getForUser($user->id);
        
        // Log activity
        ActivityLog::logActivity(
            userId: Auth::id(),
            action: 'create_user',
            subjectType: 'App\Models\User',
            subjectId: $user->id,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        );
        
        $redirectRoute = $currentUser->hasRole('admin') ? 'admin.users.index' : 'employee.users.index';
        return redirect()->route($redirectRoute)
            ->with('success', 'User created successfully.');
    }
    
    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        
        // For modal-based approach, we don't need a separate page
        $currentUser = Auth::user();
        $redirectRoute = $currentUser->hasRole('admin') ? 'admin.users.index' : 'employee.users.index';
        return redirect()->route($redirectRoute);
    }
    
    /**
     * Show the form for editing the user.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        
        // For modal-based approach, we don't need a separate page
        $currentUser = Auth::user();
        $redirectRoute = $currentUser->hasRole('admin') ? 'admin.users.index' : 'employee.users.index';
        return redirect()->route($redirectRoute);
    }
    
    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        
        $currentUser = Auth::user();
        
        // Define validation rules based on user role
        $roleValidation = 'required|string|in:employee';
        if ($currentUser->hasRole('admin')) {
            $roleValidation = 'required|string|in:admin,employee';
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => $roleValidation,
        ]);
        
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);
        
        if ($validated['password']) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }
        
        // Update role (only if user has permission to change to that role)
        if ($currentUser->hasRole('admin') || 
            ($currentUser->hasRole('employee') && $validated['role'] === 'employee')) {
            $user->syncRoles([$validated['role']]);
        }
        
        // Log activity
        ActivityLog::logActivity(
            userId: Auth::id(),
            action: 'update_user',
            subjectType: 'App\Models\User',
            subjectId: $user->id,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        );
        
        $redirectRoute = $currentUser->hasRole('admin') ? 'admin.users.index' : 'employee.users.index';
        return redirect()->route($redirectRoute)
            ->with('success', 'User updated successfully.');
    }
    
    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        
        // Log admin activity before deletion
        ActivityLog::logActivity(
            userId: Auth::id(),
            action: 'delete_user',
            subjectType: 'App\Models\User',
            subjectId: $user->id,
            ipAddress: request()->ip(),
            device: $this->getDeviceInfo(request()),
            browser: $this->getBrowserInfo(request())
        );
        
        $user->delete();
        
        $currentUser = Auth::user();
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
