<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;

class LogActivity
{
    /**
     * Handle an incoming request.
     * 
     * This middleware focuses on logging read operations (index, show) for user activity tracking.
     * CRUD operations (create, update, delete) are handled by specific events to avoid redundancy.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Only log for authenticated users and skip certain routes
        if ($request->user() && $this->shouldLog($request) && $this->isSuccessfulResponse($response)) {
            $this->logRequest($request, $response);
        }
        
        return $response;
    }
    
    private function shouldLog(Request $request): bool
    {
        // Skip logging for certain routes
        $skipRoutes = [
            'login',
            'logout',
            'sanctum.csrf-cookie',
            'ignition.*',
            'telescope.*',
            'horizon.*',
            'api.*', // Skip API routes if not needed
            // Skip CRUD operations handled by events (keep read operations for middleware)
            'admin.users.store',        // Skip - handled by UserActivityCreatedEvent
            'admin.users.update',       // Skip - handled by UserActivityUpdatedEvent
            'admin.users.destroy',      // Skip - handled by UserActivityDeletedEvent
            'employee.users.store',     // Skip - handled by UserActivityCreatedEvent
            'employee.users.update',    // Skip - handled by UserActivityUpdatedEvent
            'employee.users.destroy',   // Skip - handled by UserActivityDeletedEvent
            'admin.employees.store',    // Skip - handled by EmployeeCreatedEvent
            'admin.employees.update',   // Skip - handled by EmployeeUpdatedEvent
            'admin.employees.destroy',  // Skip - handled by EmployeeDeletedEvent
            'employee.employees.store',    // Skip - handled by EmployeeCreatedEvent
            'employee.employees.update',   // Skip - handled by EmployeeUpdatedEvent
            'employee.employees.destroy',  // Skip - handled by EmployeeDeletedEvent
        ];
        
        $routeName = $request->route()?->getName();
        
        foreach ($skipRoutes as $skipRoute) {
            if (str_contains($skipRoute, '*')) {
                $pattern = str_replace('*', '.*', $skipRoute);
                if (preg_match("/^{$pattern}$/", $routeName)) {
                    return false;
                }
            } elseif ($routeName === $skipRoute) {
                return false;
            }
        }
        
        // Skip if it's a GET request to static assets
        if ($request->isMethod('GET') && $this->isStaticAsset($request)) {
            return false;
        }
        
        return true;
    }
    
    private function isStaticAsset(Request $request): bool
    {
        $path = $request->path();
        return str_starts_with($path, 'css/') || 
               str_starts_with($path, 'js/') || 
               str_starts_with($path, 'images/') || 
               str_starts_with($path, 'fonts/') ||
               str_ends_with($path, '.css') ||
               str_ends_with($path, '.js') ||
               str_ends_with($path, '.png') ||
               str_ends_with($path, '.jpg') ||
               str_ends_with($path, '.jpeg') ||
               str_ends_with($path, '.gif') ||
               str_ends_with($path, '.svg');
    }
    
    private function isSuccessfulResponse(Response $response): bool
    {
        return $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
    }
    
    private function logRequest(Request $request, Response $response)
    {
        $user = $request->user();
        $method = $request->method();
        $route = $request->route();
        
        // Determine the action based on HTTP method and route
        $action = $this->determineAction($method, $route);
        
        // Get subject information if available
        $subjectType = $this->getSubjectType($route);
        $subjectId = $this->getSubjectId($route);
        
        // Log activity directly for read operations
        ActivityLog::logActivity(
            userId: $user->id,
            action: $action,
            subjectType: $subjectType,
            subjectId: $subjectId,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        );
    }
    
    private function determineAction(string $method, $route): string
    {
        if (!$route) {
            return "HTTP {$method} request";
        }
        
        $routeName = $route->getName();
        $routeAction = $route->getActionName();
        
        // Extract controller and method from action
        if (str_contains($routeAction, '@')) {
            [$controller, $method] = explode('@', $routeAction);
            $controllerName = class_basename($controller);
            
            // Map controller names to model names for consistency with events
            $modelName = match ($controllerName) {
                'UserController' => 'user',
                'EmployeeController' => 'employee',
                default => strtolower(str_replace('Controller', '', $controllerName))
            };
            
            return match (strtolower($method)) {
                'index' => "view_{$modelName}s",  // view_users, view_employees
                'show' => "view_{$modelName}",    // view_user, view_employee
                'create' => "view_{$modelName}_form",
                'edit' => "view_{$modelName}_edit",
                'store' => "create_{$modelName}",  // create_user, create_employee
                'update' => "update_{$modelName}", // update_user, update_employee
                'destroy' => "delete_{$modelName}", // delete_user, delete_employee
                default => strtolower($method) . "_{$modelName}",
            };
        }
        
        // Fallback for routes without @ in action
        return "access_{$routeName}";
    }
    
    private function getSubjectType($route): ?string
    {
        if (!$route) {
            return null;
        }
        
        $routeAction = $route->getActionName();
        
        // Extract controller name and map to model class
        if (str_contains($routeAction, '@')) {
            [$controller] = explode('@', $routeAction);
            $controllerName = class_basename($controller);
            
            return match ($controllerName) {
                'UserController' => 'App\Models\User',
                'EmployeeController' => 'App\Models\Employee',
                default => null
            };
        }
        
        return null;
    }
    
    private function getSubjectId($route): ?int
    {
        if (!$route || !$route->parameters()) {
            return null;
        }
        
        $parameters = $route->parameters();
        
        // Try to find a model in the route parameters
        foreach ($parameters as $parameter) {
            if (is_object($parameter) && method_exists($parameter, 'getKey')) {
                return $parameter->id;
            }
        }
        
        return null;
    }
    
    private function getModelFromRoute($route, $parameters)
    {
        // Try to find a model in the route parameters
        foreach ($parameters as $parameter) {
            if (is_object($parameter) && method_exists($parameter, 'getKey')) {
                return $parameter;
            }
        }
        
        return null;
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
