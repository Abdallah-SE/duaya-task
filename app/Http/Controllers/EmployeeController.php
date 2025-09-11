<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Employee;
use App\Models\ActivityLog;
use App\Models\IdleSetting;
use App\Models\Penalty;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index()
    {
        $this->authorize('viewAny', Employee::class);
        
        $currentUser = Auth::user();
        
        // Build query for employees with their user data
        $query = Employee::with(['user.roles', 'user.penalties', 'user.activityLogs', 'user.idleSessions']);
        
        // If user is employee, only show employees
        if ($currentUser->hasRole('employee') && !$currentUser->hasRole('admin')) {
            $query->whereHas('user.roles', function($q) {
                $q->where('name', 'employee');
            });
        }
        
        $employees = $query->paginate(15);
        
        // Transform data for frontend
        $employees->getCollection()->transform(function ($employee) {
            return [
                'id' => $employee->id,
                'user_id' => $employee->user_id,
                'name' => $employee->user->name,
                'email' => $employee->user->email,
                'job_title' => $employee->job_title,
                'department' => $employee->department,
                'hire_date' => $employee->hire_date,
                'roles' => $employee->user->roles,
                'penalties' => $employee->user->penalties,
                'activity_logs_count' => $employee->user->activityLogs->count(),
                'idle_sessions_count' => $employee->user->idleSessions->count(),
                'created_at' => $employee->created_at,
                'updated_at' => $employee->updated_at,
            ];
        });
        
        // Stats for employee management
        $stats = [
            'totalEmployees' => $employees->total(),
            'activeEmployees' => $employees->getCollection()->where('activity_logs_count', '>', 0)->count(),
            'totalActivities' => $employees->getCollection()->sum('activity_logs_count'),
            'totalPenalties' => $employees->getCollection()->sum(function($emp) {
                return $emp['penalties']->count();
            })
        ];
        
        // Log activity
        ActivityLog::logActivity(
            userId: Auth::id(),
            action: 'view_employees',
            subjectType: 'App\Models\Employee',
            subjectId: null,
            ipAddress: request()->ip(),
            device: $this->getDeviceInfo(request()),
            browser: $this->getBrowserInfo(request())
        );
        
        // Determine which component to render based on the route
        $isEmployeeRoute = request()->is('employee/employees*');
        
        if ($isEmployeeRoute) {
            // Get user settings for the current user
            $userSettings = $currentUser->getIdleSettings();
            $isIdleMonitoringEnabled = $currentUser->isIdleMonitoringEnabled();
            
            return Inertia::render('Employee/Employees/Index', [
                'employees' => $employees,
                'user' => $currentUser,
                'userSettings' => $userSettings,
                'initialSettings' => $userSettings,
                'canControlIdleMonitoring' => $currentUser->hasRole('admin'),
                'isIdleMonitoringEnabled' => $isIdleMonitoringEnabled,
                'stats' => $stats
            ]);
        } else {
            return Inertia::render('Admin/Employees/Index', [
                'employees' => $employees,
                'currentUser' => $currentUser,
                'stats' => $stats
            ]);
        }
    }
    
    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        $this->authorize('create', Employee::class);
        
        // For modal-based approach, we don't need a separate page
        $currentUser = Auth::user();
        $redirectRoute = $currentUser->hasRole('admin') ? 'admin.employees.index' : 'employee.employees.index';
        return redirect()->route($redirectRoute);
    }
    
    /**
     * Store a newly created employee.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Employee::class);
        
        $currentUser = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'job_title' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'hire_date' => 'required|date',
        ]);
        
        DB::transaction(function () use ($validated) {
            // Create user first
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);
            
            // Assign employee role
            $user->assignRole('employee');
            
            // Create employee record
            $employee = Employee::create([
                'user_id' => $user->id,
                'job_title' => $validated['job_title'],
                'department' => $validated['department'],
                'hire_date' => $validated['hire_date'],
            ]);
            
            // Create default idle settings
            IdleSetting::getForUser($user->id);
        });
        
        // Log activity
        ActivityLog::logActivity(
            userId: Auth::id(),
            action: 'create_employee',
            subjectType: 'App\Models\Employee',
            subjectId: null,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        );
        
        $redirectRoute = $currentUser->hasRole('admin') ? 'admin.employees.index' : 'employee.employees.index';
        return redirect()->route($redirectRoute)
            ->with('success', 'Employee created successfully.');
    }
    
    /**
     * Display the specified employee.
     */
    public function show(Employee $employee)
    {
        $this->authorize('view', $employee);
        
        // For modal-based approach, we don't need a separate page
        $currentUser = Auth::user();
        $redirectRoute = $currentUser->hasRole('admin') ? 'admin.employees.index' : 'employee.employees.index';
        return redirect()->route($redirectRoute);
    }
    
    /**
     * Show the form for editing the employee.
     */
    public function edit(Employee $employee)
    {
        $this->authorize('update', $employee);
        
        // For modal-based approach, we don't need a separate page
        $currentUser = Auth::user();
        $redirectRoute = $currentUser->hasRole('admin') ? 'admin.employees.index' : 'employee.employees.index';
        return redirect()->route($redirectRoute);
    }
    
    /**
     * Update the specified employee.
     */
    public function update(Request $request, Employee $employee)
    {
        $this->authorize('update', $employee);
        
        $currentUser = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $employee->user_id,
            'password' => 'nullable|string|min:8|confirmed',
            'job_title' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'hire_date' => 'required|date',
        ]);
        
        DB::transaction(function () use ($validated, $employee) {
            // Update user data
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];
            
            if ($validated['password']) {
                $userData['password'] = Hash::make($validated['password']);
            }
            
            $employee->user->update($userData);
            
            // Update employee data
            $employee->update([
                'job_title' => $validated['job_title'],
                'department' => $validated['department'],
                'hire_date' => $validated['hire_date'],
            ]);
        });
        
        // Log activity
        ActivityLog::logActivity(
            userId: Auth::id(),
            action: 'update_employee',
            subjectType: 'App\Models\Employee',
            subjectId: $employee->id,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        );
        
        $redirectRoute = $currentUser->hasRole('admin') ? 'admin.employees.index' : 'employee.employees.index';
        return redirect()->route($redirectRoute)
            ->with('success', 'Employee updated successfully.');
    }
    
    /**
     * Remove the specified employee.
     */
    public function destroy(Employee $employee)
    {
        $this->authorize('delete', $employee);
        
        // Log admin activity before deletion
        ActivityLog::logActivity(
            userId: Auth::id(),
            action: 'delete_employee',
            subjectType: 'App\Models\Employee',
            subjectId: $employee->id,
            ipAddress: request()->ip(),
            device: $this->getDeviceInfo(request()),
            browser: $this->getBrowserInfo(request())
        );
        
        DB::transaction(function () use ($employee) {
            // Delete employee record first
            $employee->delete();
            
            // Delete associated user
            $employee->user->delete();
        });
        
        $currentUser = Auth::user();
        $redirectRoute = $currentUser->hasRole('admin') ? 'admin.employees.index' : 'employee.employees.index';
        return redirect()->route($redirectRoute)
            ->with('success', 'Employee deleted successfully.');
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
