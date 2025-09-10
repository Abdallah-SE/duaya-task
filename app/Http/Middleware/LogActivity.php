<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
        
        // Only log for authenticated users
        if ($request->user()) {
            $this->logRequest($request, $response);
        }
        
        return $response;
    }
    
    private function logRequest(Request $request, Response $response)
    {
        $user = $request->user();
        $method = $request->method();
        $url = $request->fullUrl();
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        
        // Determine the action based on HTTP method and route
        $action = $this->determineAction($method, $request->route());
        
        // Log the activity
        activity()
            ->causedBy($user)
            ->withProperties([
                'method' => $method,
                'url' => $url,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'status_code' => $response->getStatusCode(),
                'route_name' => $request->route()?->getName(),
                'route_parameters' => $request->route()?->parameters(),
            ])
            ->log($action);
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
                'index' => "Viewed {$controllerName} list",
                'show' => "Viewed {$controllerName} details",
                'create' => "Viewed {$controllerName} creation form",
                'store' => "Created {$controllerName}",
                'edit' => "Viewed {$controllerName} edit form",
                'update' => "Updated {$controllerName}",
                'destroy' => "Deleted {$controllerName}",
                default => "Performed {$method} on {$controllerName}",
            };
        }
        
        return "Accessed {$routeName}";
    }
}
