<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;
use App\Events\UserActivityEvent;

class LogActivity
{
    /**
     * Handle an incoming request.
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
        $subjectType = null;
        $subjectId = null;
        
        if ($route && $route->parameters()) {
            $parameters = $route->parameters();
            $model = $this->getModelFromRoute($route, $parameters);
            if ($model) {
                $subjectType = get_class($model);
                $subjectId = $model->id;
            }
        }
        
        // Fire event for activity logging
        event(new UserActivityEvent(
            user: $user,
            action: $action,
            subjectType: $subjectType,
            subjectId: $subjectId,
            ipAddress: $request->ip(),
            device: $this->getDeviceInfo($request),
            browser: $this->getBrowserInfo($request)
        ));
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
            
            return match (strtolower($method)) {
                'index' => "view_{$controllerName}",
                'show' => "view_{$controllerName}",
                'create' => "view_{$controllerName}_form",
                'store' => "create_{$controllerName}",
                'edit' => "view_{$controllerName}_edit",
                'update' => "update_{$controllerName}",
                'destroy' => "delete_{$controllerName}",
                default => strtolower($method) . "_{$controllerName}",
            };
        }
        
        return "access_{$routeName}";
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
