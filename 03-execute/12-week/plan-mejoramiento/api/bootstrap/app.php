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

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registra el alias para proteger rutas según el rol.
        $middleware->alias(['role' => EnsureUserHasRole::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Convierte errores de aplicación al formato uniforme del contrato.
        $exceptions->render(function (ApiException $exception, Request $request) {
            if (!$request->is('api/*')) return null;
            return response()->json([
                'code' => $exception->errorCode,
                'message' => $exception->getMessage(),
                'details' => (object) $exception->details,
            ], $exception->httpStatus);
        });

        // Devuelve errores de validación con código y detalles uniformes.
        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (!$request->is('api/*')) return null;
            return response()->json([
                'code' => 'VALIDATION_ERROR',
                'message' => 'Los datos enviados no cumplen las validaciones.',
                'details' => $exception->errors(),
            ], 422);
        });

        // Estandariza las solicitudes sin autenticación válida.
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (!$request->is('api/*')) return null;
            return response()->json([
                'code' => 'UNAUTHENTICATED',
                'message' => 'Debes iniciar sesión para realizar esta operación.',
                'details' => (object) [],
            ], 401);
        });

        // Estandariza los errores de autorización.
        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if (!$request->is('api/*')) return null;
            return response()->json([
                'code' => 'FORBIDDEN_ROLE',
                'message' => 'No tienes permisos para realizar esta operación.',
                'details' => (object) [],
            ], 403);
        });
    })->create();
