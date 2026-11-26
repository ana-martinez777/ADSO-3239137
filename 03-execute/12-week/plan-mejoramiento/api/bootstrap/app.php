<?php
use App\Exceptions\ApiException;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registers the alias used to protect routes by role.
        $middleware->alias(['role' => EnsureUserHasRole::class]);

        // API guests are never redirected to a login page: they receive a 401.
        // Without this, a request without "Accept: application/json" tried to
        // build the missing "login" route and ended as a 500 error.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('api/*') ? null : '/');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Converts application errors to the contract's uniform format.
        $exceptions->render(function (ApiException $exception, Request $request) {
            if (!$request->is('api/*')) return null;
            return response()->json([
                'code' => $exception->errorCode,
                'message' => $exception->getMessage(),
                'details' => (object) $exception->details,
            ], $exception->httpStatus);
        });

        // Returns validation errors with a uniform code and details.
        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (!$request->is('api/*')) return null;
            return response()->json([
                'code' => 'VALIDATION_ERROR',
                'message' => 'Los datos enviados no cumplen las validaciones.',
                'details' => $exception->errors(),
            ], 422);
        });

        // Standardizes requests without valid authentication.
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (!$request->is('api/*')) return null;
            return response()->json([
                'code' => 'UNAUTHENTICATED',
                'message' => 'Debes iniciar sesión para realizar esta operación.',
                'details' => (object) [],
            ], 401);
        });

        // Standardizes authorization errors.
        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if (!$request->is('api/*')) return null;
            return response()->json([
                'code' => 'FORBIDDEN_ROLE',
                'message' => 'No tienes permisos para realizar esta operación.',
                'details' => (object) [],
            ], 403);
        });

        // Last resort: any unhandled API error responds with the
        // uniform format, without exposing SQL, traces or configuration (RNF-04).
        // Laravel logs the technical detail in storage/logs/laravel.log.
        $exceptions->render(function (\Throwable $exception, Request $request) {
            if (!$request->is('api/*')) return null;

            if ($exception instanceof HttpExceptionInterface) {
                $status = $exception->getStatusCode();

                return response()->json([
                    'code' => $status === 404 ? 'NOT_FOUND' : 'HTTP_ERROR',
                    'message' => $status === 404
                        ? 'El recurso solicitado no existe.'
                        : 'La solicitud no puede procesarse.',
                    'details' => (object) [],
                ], $status);
            }

            return response()->json([
                'code' => 'INTERNAL_ERROR',
                'message' => 'Ocurrió un error interno. Intenta nuevamente más tarde.',
                'details' => (object) [],
            ], 500);
        });
    })->create();
