<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Models\ActivityLog;
use App\Models\IdleSetting;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        // If user is already authenticated, redirect to appropriate dashboard
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->hasRole('admin')) {
                return redirect('/admin/dashboard');
            } else {
                return redirect('/employee/dashboard');
            }
        }
        
        return Inertia::render('Auth/Login');
    }
    
    /**
     * Show the admin login form.
     */
    public function showAdminLogin()
    {
        // If user is already authenticated, redirect to appropriate dashboard
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->hasRole('admin')) {
                return redirect('/admin/dashboard');
            } else {
                return redirect('/employee/dashboard');
            }
        }
        
        return Inertia::render('Auth/AdminLogin');
    }
    
    /**
     * Show the employee login form.
     */
    public function showEmployeeLogin()
    {
        // If user is already authenticated, redirect to appropriate dashboard
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->hasRole('admin')) {
                return redirect('/admin/dashboard');
            } else {
                return redirect('/employee/dashboard');
            }
        }
        
        return Inertia::render('Auth/EmployeeLogin');
    }
    
    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'role' => 'required|in:admin,employee',
        ]);
        
        if (Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password']
        ])) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Check if user has the requested role
            if (!$user->hasRole($credentials['role'])) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'You do not have access with the selected role.',
                ]);
            }
            
            // Log login activity
            ActivityLog::logActivity(
                userId: $user->id,
                action: 'login',
                subjectType: 'App\Models\User',
                subjectId: $user->id,
                ipAddress: $request->ip(),
                device: $this->getDeviceInfo($request),
                browser: $this->getBrowserInfo($request)
            );
            
            // Get or create idle settings for the user
            $user->getIdleSettings();
            
            // Redirect based on role
            if ($user->hasRole('admin')) {
                return redirect()->intended('/admin/dashboard');
            } else {
                return redirect()->intended('/employee/dashboard');
            }
        }
        
        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }
    
    /**
     * Handle admin login.
     */
    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Check if user has admin role
            if (!$user->hasRole('admin')) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'You do not have admin access.',
                ]);
            }
            
            $request->session()->regenerate();
            
            // Log login activity
            ActivityLog::logActivity(
                userId: $user->id,
                action: 'admin_login',
                subjectType: 'App\Models\User',
                subjectId: $user->id,
                ipAddress: $request->ip(),
                device: $this->getDeviceInfo($request),
                browser: $this->getBrowserInfo($request)
            );
            
            // Get or create idle settings for the user
            $user->getIdleSettings();
            
            return redirect()->intended('/admin/dashboard');
        }
        
        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }
    
    /**
     * Handle employee login.
     */
    public function employeeLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Check if user has employee role
            if (!$user->hasRole('employee')) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'You do not have employee access.',
                ]);
            }
            
            $request->session()->regenerate();
            
            // Log login activity
            ActivityLog::logActivity(
                userId: $user->id,
                action: 'employee_login',
                subjectType: 'App\Models\User',
                subjectId: $user->id,
                ipAddress: $request->ip(),
                device: $this->getDeviceInfo($request),
                browser: $this->getBrowserInfo($request)
            );
            
            // Get or create idle settings for the user
            $user->getIdleSettings();
            
            return redirect()->intended('/employee/dashboard');
        }
        
        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }
    
    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        if ($user) {
            // Log logout activity
            ActivityLog::logActivity(
                userId: $user->id,
                action: 'logout',
                subjectType: 'App\Models\User',
                subjectId: $user->id,
                ipAddress: $request->ip(),
                device: $this->getDeviceInfo($request),
                browser: $this->getBrowserInfo($request)
            );
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login');
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
