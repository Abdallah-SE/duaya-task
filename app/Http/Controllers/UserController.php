<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\IdleSetting;

class UserController extends Controller
{
    /**
     * Display a listing of users (Admin only).
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);
        
        $users = User::with(['roles', 'idleSettings', 'penalties'])
            ->withCount(['activityLogs', 'idleSessions'])
            ->paginate(15);
        
        // Log admin activity
        ActivityLog::logActivity(
            userId: auth()->id(),
            action: 'view_users',
            subjectType: 'App\Models\User',
            subjectId: null,
            ipAddress: request()->ip(),
            device: $this->getDeviceInfo(request()),
            browser: $this->getBrowserInfo(request())
        );
        
        return Inertia::render('Admin/Users/Index', [
            'users' => $users
        ]);
    }
    
    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $this->authorize('create', User::class);
        
        return Inertia::render('Admin/Users/Create');
    }
    
    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,employee',
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
        
        // Log admin activity
        ActivityLog::logActivity(
            userId: auth()->id(),
            action: 'create_user',
            subjectType: 'App\Models\User',
            subjectId: $user->id,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        );
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }
    
    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        
        $user->load(['roles', 'idleSettings', 'penalties', 'activityLogs' => function($query) {
            $query->latest()->limit(10);
        }, 'idleSessions' => function($query) {
            $query->latest()->limit(10);
        }]);
        
        // Log admin activity
        ActivityLog::logActivity(
            userId: auth()->id(),
            action: 'view_user',
            subjectType: 'App\Models\User',
            subjectId: $user->id,
            ipAddress: request()->ip(),
            device: $this->getDeviceInfo(request()),
            browser: $this->getBrowserInfo(request())
        );
        
        return Inertia::render('Admin/Users/Show', [
            'user' => $user
        ]);
    }
    
    /**
     * Show the form for editing the user.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        
        $user->load('roles');
        
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user
        ]);
    }
    
    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|in:admin,employee',
        ]);
        
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);
        
        if ($validated['password']) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }
        
        // Update role
        $user->syncRoles([$validated['role']]);
        
        // Log admin activity
        ActivityLog::logActivity(
            userId: auth()->id(),
            action: 'update_user',
            subjectType: 'App\Models\User',
            subjectId: $user->id,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        );
        
        return redirect()->route('admin.users.index')
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
            userId: auth()->id(),
            action: 'delete_user',
            subjectType: 'App\Models\User',
            subjectId: $user->id,
            ipAddress: request()->ip(),
            device: $this->getDeviceInfo(request()),
            browser: $this->getBrowserInfo(request())
        );
        
        $user->delete();
        
        return redirect()->route('admin.users.index')
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
