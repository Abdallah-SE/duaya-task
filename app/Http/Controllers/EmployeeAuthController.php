<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            
            // Fire login event (temporarily disabled to fix login issue)
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
     * Optimized for Inertia.js with proper error handling.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Validate user authentication
        if (!$user) {
            return redirect('/employee/login')->with('error', 'No active session found.');
        }
        
        // Validate employee role
        if (!$user->hasRole('employee')) {
            $this->performLogout($request);
            return redirect('/employee/login')->with('error', 'Employee credentials required.');
        }
        
        try {
            // Log the logout event before performing logout
            $this->logUserLogout($user, $request, 'logout_employee_user');
            
            // Perform logout operations
            $this->performLogout($request);
            
            // Return success response for Inertia.js
            return redirect('/employee/login')->with('message', 'Employee logout successful.');
            
        } catch (\Exception $e) {
            // Log error and still perform logout
            $this->logLogoutError($e, $user, $request, 'Employee logout error');
            $this->performLogout($request);
            
            return redirect('/employee/login')->with('error', 'Logout completed with some issues.');
        }
    }
    
    /**
     * Handle unauthenticated logout attempt.
     */
    private function handleUnauthenticatedLogout(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'No active session found.',
                'redirect' => '/employee/login'
            ], 401);
        }
        
        return redirect('/employee/login')->with('error', 'No active session found.');
    }
    
    /**
     * Handle unauthorized logout attempt.
     */
    private function handleUnauthorizedLogout(Request $request, string $message)
    {
        $this->performLogout($request);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'redirect' => '/employee/login'
            ], 403);
        }
        
        return redirect('/employee/login')->with('error', $message);
    }
    
    /**
     * Log user logout event.
     */
    private function logUserLogout($user, Request $request, string $logoutType): void
    {
        // Temporarily disabled to fix logout issue
        event(new UserLogoutEvent(
            user: $user,
            logoutType: $logoutType,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        ));
    }
    
    /**
     * Perform the actual logout operations.
     */
    private function performLogout(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
    
    /**
     * Handle successful logout response.
     */
    private function handleSuccessfulLogout(Request $request, string $message, string $redirectTo)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => $redirectTo
            ]);
        }
        
        return redirect($redirectTo)->with('message', $message);
    }
    
    /**
     * Handle failed logout response.
     */
    private function handleFailedLogout(Request $request, string $redirectTo)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Logout completed with some issues.',
                'redirect' => $redirectTo
            ]);
        }
        
        return redirect($redirectTo)->with('error', 'Logout completed with some issues.');
    }
    
    /**
     * Log logout error.
     */
    private function logLogoutError(\Exception $e, $user, Request $request, string $context): void
    {
        Log::error($context, [
            'error' => $e->getMessage(),
            'user_id' => $user?->id,
            'ip' => $request->ip(),
            'trace' => $e->getTraceAsString()
        ]);
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
