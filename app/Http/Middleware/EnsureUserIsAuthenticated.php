<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role = null): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            // Store the intended URL for redirect after login
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Authentication required.',
                    'redirect' => route('login')
                ], 401);
            }
            
            return redirect()->route('login')
                ->with('error', 'Please log in to access this page.')
                ->with('intended', $request->url());
        }

        $user = Auth::user();

        // If a specific role is required, check it
        if ($role && !$user->hasRole($role)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Access denied. Insufficient privileges.',
                    'required_role' => $role,
                    'user_roles' => $user->roles->pluck('name')->toArray()
                ], 403);
            }
            
            return redirect()->route('login')
                ->with('error', "Access denied. {$role} privileges required.");
        }

        // Check if user account is active (if you have such a field)
        if (method_exists($user, 'isActive') && !$user->isActive()) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Your account has been deactivated. Please contact an administrator.');
        }

        return $next($request);
    }
}
