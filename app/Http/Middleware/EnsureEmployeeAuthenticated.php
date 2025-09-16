<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployeeAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated using web guard
        if (!Auth::guard('web')->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                    'redirect' => '/employee/login'
                ], 401);
            }
            
            return redirect()->route('employee.login')
                ->with('error', 'Please log in to access this page.')
                ->with('intended', $request->url());
        }

        $user = Auth::guard('web')->user();

        // Check if user has employee role
        if (!$user->hasRole('employee')) {
            Auth::guard('web')->logout();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Employee privileges required.',
                ], 403);
            }
            
            return redirect()->route('employee.login')
                ->with('error', 'Access denied. Employee privileges required.');
        }

        return $next($request);
    }
}
