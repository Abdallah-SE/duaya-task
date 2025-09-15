<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Models\IdleSetting;
use App\Events\UserLoginEvent;
use App\Events\UserLogoutEvent;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
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
                    'email' => 'Access denied. You do not have permission for this role.',
                ]);
            }
            
            // Fire login event
            event(new UserLoginEvent(
                user: $user,
                loginType: 'login_user',
                ipAddress: $request->ip(),
                device: $this->getDeviceInfo($request),
                browser: $this->getBrowserInfo($request)
            ));
            
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
            'email' => 'Invalid email or password. Please check your credentials and try again.',
        ]);
    }
    
    
    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        if ($user) {
            // Determine logout type based on user role
            $logoutType = $user->hasRole('admin') ? 'logout_admin_user' : 'logout_employee_user';
            
            // Fire logout event
            event(new UserLogoutEvent(
                user: $user,
                logoutType: $logoutType,
                ipAddress: $request->ip(),
                device: $this->getDeviceInfo($request),
                browser: $this->getBrowserInfo($request)
            ));
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login')->with('message', 'You have been successfully logged out.');
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
