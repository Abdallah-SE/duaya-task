<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Inertia\Inertia;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        // Handle 404 Not Found with Inertia
        if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return $this->renderNotFound($request, $e);
        }
        
        // Handle 429 Too Many Requests with Inertia
        if ($e instanceof ThrottleRequestsException) {
            return $this->renderThrottleException($request, $e);
        }

        // Handle 419 CSRF Token Mismatch with Inertia
        if ($e instanceof \Illuminate\Session\TokenMismatchException) {
            return $this->renderCsrfException($request, $e);
        }

        // Handle 500 Server Error with Inertia (only for actual server errors, not validation or auth)
        if ($e instanceof \Exception && 
            !($e instanceof \Illuminate\Validation\ValidationException) &&
            !($e instanceof \Illuminate\Auth\AuthenticationException) &&
            !($e instanceof \Illuminate\Auth\Access\AuthorizationException) &&
            !($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException)) {
            return $this->renderServerError($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Render throttle exception with Inertia.
     */
    protected function renderThrottleException(Request $request, ThrottleRequestsException $e)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $e->getHeaders()['Retry-After'] ?? 60
            ], 429);
        }

        return Inertia::render('Errors/429', [
            'retryAfter' => $e->getHeaders()['Retry-After'] ?? 60,
            'appName' => config('app.name', 'Duaya Task')
        ])->toResponse($request)->setStatusCode(429);
    }

    /**
     * Render CSRF exception with Inertia.
     */
    protected function renderCsrfException(Request $request, \Illuminate\Session\TokenMismatchException $e)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'CSRF token mismatch. Please refresh the page and try again.'
            ], 419);
        }

        return Inertia::render('Errors/419', [
            'appName' => config('app.name', 'Duaya Task')
        ])->toResponse($request)->setStatusCode(419);
    }

    /**
     * Render not found error with Inertia.
     */
    protected function renderNotFound(Request $request, \Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'The requested resource was not found.'
            ], 404);
        }

        return Inertia::render('Errors/404', [
            'appName' => config('app.name', 'Duaya Task')
        ])->toResponse($request)->setStatusCode(404);
    }

    /**
     * Render server error with Inertia.
     */
    protected function renderServerError(Request $request, \Exception $e)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Internal server error. Please try again later.'
            ], 500);
        }

        return Inertia::render('Errors/500', [
            'appName' => config('app.name', 'Duaya Task')
        ])->toResponse($request)->setStatusCode(500);
    }
}