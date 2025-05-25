<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle reporting (logging) of exceptions
        $exceptions->report(function (Throwable $e) {
            // Don't log validation exceptions as errors
            if (!$e instanceof ValidationException) {
                Log::error('Application error', [
                    'user_id' => Auth::id(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'url' => request()->fullUrl(),
                    'method' => request()->method(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        });

        // Handle rendering of exceptions for API responses
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid parameters',
                    'errors' => $e->errors()
                ], 422);
            }
        });

        // Handle generic exceptions for API
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {

                if ($e instanceof ValidationException) {
                    return null;
                }

                $statusCode = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

                return response()->json([
                    'success' => false,
                    'message' => getErrorMessage($e),
                    'errors' => config('app.debug') ? ['error' => $e->getMessage()] : []
                ], $statusCode);
            }
        });
    })
    ->create();

// Helper function for error messages
function getErrorMessage(Throwable $e): string
{
    // Customize based on exception type
    return match (get_class($e)) {
        'Illuminate\Database\Eloquent\ModelNotFoundException' => 'Resource not found',
        'Illuminate\Auth\AuthenticationException' => 'Authentication required',
        'Illuminate\Auth\Access\AuthorizationException' => 'Access denied',
        default => config('app.debug') ? $e->getMessage() : 'Something went wrong'
    };
}
