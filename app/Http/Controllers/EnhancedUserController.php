<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;
use App\Services\CrudOperationService;
use App\Services\UserActivityService;

class EnhancedUserController extends Controller
{
    protected CrudOperationService $crudService;
    protected UserActivityService $activityService;

    public function __construct(CrudOperationService $crudService, UserActivityService $activityService)
    {
        $this->crudService = $crudService;
        $this->activityService = $activityService;
    }

    /**
     * Display a listing of users (Admin and Employee).
     */
    public function index(Request $request)
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
        
        // Log activity using the service
        $this->activityService->logActivity(
            userId: Auth::id(),
            action: 'view_users',
            subjectType: 'App\Models\User',
            subjectId: null,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        );
        
        // Determine which component to render based on the route
        $isEmployeeRoute = $request->is('employee/users*');
        
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
     * Store a newly created user using the CRUD service.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        
        $currentUser = auth('admin')->user() ?? Auth::user();
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ];
        
        $additionalData = [
            'password' => Hash::make($request->password),
        ];
        
        try {
            $user = $this->crudService->createModel(
                modelClass: User::class,
                request: $request,
                rules: $rules,
                additionalData: $additionalData,
                user: $currentUser
            );
            
            // Assign default role as employee
            $user->assignRole('employee');
            
            // Create default idle settings
            $user->getIdleSettings();
            
            $redirectRoute = $currentUser->hasRole('admin') ? 'admin.users.index' : 'employee.users.index';
            return redirect()->route($redirectRoute)
                ->with('success', 'User created successfully.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }
    
    /**
     * Update the specified user using the CRUD service.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        
        $currentUser = auth('admin')->user() ?? Auth::user();
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ];
        
        try {
            $this->crudService->updateModel(
                model: $user,
                request: $request,
                rules: $rules,
                user: $currentUser
            );
            
            $redirectRoute = $currentUser->hasRole('admin') ? 'admin.users.index' : 'employee.users.index';
            return redirect()->route($redirectRoute)
                ->with('success', 'User updated successfully.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified user using the CRUD service.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        
        $currentUser = auth('admin')->user() ?? Auth::user();
        
        try {
            $this->crudService->deleteModel(
                model: $user,
                metadata: ['deleted_by' => $currentUser->id],
                user: $currentUser
            );
            
            $redirectRoute = $currentUser->hasRole('admin') ? 'admin.users.index' : 'employee.users.index';
            return redirect()->route($redirectRoute)
                ->with('success', 'User deleted successfully.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
    
    /**
     * Show user activity statistics.
     */
    public function activityStats(User $user, Request $request)
    {
        $this->authorize('view', $user);
        
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $stats = $this->activityService->getUserActivityStats(
            $user->id,
            $startDate,
            $endDate
        );
        
        return response()->json($stats);
    }
    
    /**
     * Get system-wide activity statistics.
     */
    public function systemActivityStats(Request $request)
    {
        $this->authorize('viewAny', User::class);
        
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $stats = $this->activityService->getSystemActivityStats(
            $startDate,
            $endDate
        );
        
        return response()->json($stats);
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
