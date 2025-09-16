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
use App\Events\EmployeeCreatedEvent;
use App\Events\EmployeeUpdatedEvent;
use App\Events\EmployeeDeletedEvent;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;

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
        
        // Determine which component to render based on the route
        $isEmployeeRoute = request()->is('employee/employees*');
        
        // Get users for employee creation (only users without employee records, excluding current user and admins)
        $usersWithoutEmployee = User::whereDoesntHave('employee')
            ->where('id', '!=', $currentUser->id) // Exclude current user
            ->whereDoesntHave('roles', function($query) {
                $query->where('name', 'admin'); // Exclude admin users
            })
            ->get(['id', 'name', 'email']);
        
        if ($isEmployeeRoute) {
            // Get user settings for the current user
            $userSettings = $currentUser->getIdleSettings();
            $isIdleMonitoringEnabled = $currentUser->isIdleMonitoringEnabled();
            
            return Inertia::render('Employee/Employees/Index', [
                'employees' => $employees,
                'users' => $usersWithoutEmployee,
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
                'users' => $usersWithoutEmployee,
                'currentUser' => $currentUser,
                'stats' => $stats
            ]);
        }
    }
    
    /**
     * Store a newly created employee.
     */
    public function store(StoreEmployeeRequest $request)
    {
        $currentUser = Auth::user();
        
        // Create employee record
        $employee = Employee::create([
            'user_id' => $request->input('user_id'),
            'job_title' => $request->input('job_title'),
            'department' => $request->input('department'),
            'hire_date' => $request->input('hire_date'),
        ]);
        
        // Dispatch event for employee creation
        event(new EmployeeCreatedEvent(
            $employee,
            Auth::id(),
            $request->getClientIp(),
            $this->getDeviceInfo($request),
            $this->getBrowserInfo($request)
        ));
        
        $redirectRoute = $currentUser->hasRole('admin') ? 'admin.employees.index' : 'employee.employees.index';
        return redirect()->route($redirectRoute)
            ->with('success', 'Employee created successfully.');
    }
    
    /**
     * Update the specified employee.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $currentUser = Auth::user();
        
        // Update employee data only (exclude any user-related fields)
        $employee->update([
            'job_title' => $request->input('job_title'),
            'department' => $request->input('department'),
            'hire_date' => $request->input('hire_date'),
        ]);
        
        // Dispatch event for employee update
        event(new EmployeeUpdatedEvent(
            $employee,
            Auth::id(),
            $request->getClientIp(),
            $this->getDeviceInfo($request),
            $this->getBrowserInfo($request)
        ));
        
        $redirectRoute = $currentUser->hasRole('admin') ? 'admin.employees.index' : 'employee.employees.index';
        return redirect()->route($redirectRoute)
            ->with('success', 'Employee updated successfully.');
    }
    
    /**
     * Remove the specified employee.
     */
    public function destroy(Employee $employee)
    {
        // Dispatch event for employee deletion (before deleting)
        event(new EmployeeDeletedEvent(
            $employee,
            Auth::id(),
            request()->ip(),
            $this->getDeviceInfo(request()),
            $this->getBrowserInfo(request())
        ));
        
        // Delete only the employee record, keep the user
        $employee->delete();
        
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