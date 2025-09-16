<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class HandleThrottleRequests extends ThrottleRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int|string  $maxAttempts
     * @param  float|int  $decayMinutes
     * @param  string  $prefix
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Http\Exceptions\ThrottleRequestsException
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = '')
    {
        try {
            return parent::handle($request, $next, $maxAttempts, $decayMinutes, $prefix);
        } catch (ThrottleRequestsException $e) {
            // Handle throttle exception with Inertia response
            return $this->handleThrottleException($request, $e);
        }
    }

    /**
     * Handle throttle exception with Inertia response.
     */
    protected function handleThrottleException(Request $request, ThrottleRequestsException $e)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $e->getHeaders()['Retry-After'] ?? 60
            ], 429);
        }

        // Return Inertia response for web requests
        return Inertia::render('Errors/429', [
            'retryAfter' => $e->getHeaders()['Retry-After'] ?? 60,
            'appName' => config('app.name', 'Duaya Task')
        ])->toResponse($request)->setStatusCode(429);
    }
}


