<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Events\UserLoginEvent;
use App\Events\UserLogoutEvent;

class EmployeeAuthController extends Controller
{
    /**
     * Show the employee login form.
     */
    public function showLogin()
    {
        return Inertia::render('Auth/EmployeeLogin');
    }
    
    /**
     * Handle employee login.
     */
    public function login(Request $request)
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
                    'email' => 'Access denied. Employee credentials required.',
                ]);
            }
            
            $request->session()->regenerate();
            
            // Fire login event
            event(new UserLoginEvent(
                user: $user,
                loginType: 'login_employee_user',
                ipAddress: $request->ip(),
                device: $this->getDeviceInfo($request),
                browser: $this->getBrowserInfo($request)
            ));
            
            // Get or create idle settings for the user
            $user->getIdleSettings();
            
            return redirect()->intended('/employee/dashboard');
        }
        
        throw ValidationException::withMessages([
            'email' => 'Invalid email or password. Please check your credentials and try again.',
        ]);
    }
    
    /**
     * Handle employee logout.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        if ($user) {
            // Fire logout event
            event(new UserLogoutEvent(
                user: $user,
                logoutType: 'logout_employee_user',
                ipAddress: $request->ip(),
                device: $this->getDeviceInfo($request),
                browser: $this->getBrowserInfo($request)
            ));
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/employee/login')->with('message', 'You have been successfully logged out.');
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
