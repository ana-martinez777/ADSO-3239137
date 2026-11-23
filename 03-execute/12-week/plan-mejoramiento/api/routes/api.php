<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

// Rutas públicas que no requieren autenticación.
Route::prefix('v1')->group(function () {
    Route::get('/health', HealthController::class);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Las rutas restantes del bloque requieren un JWT válido.
    Route::middleware('auth:api')->group(function () {
        // Solo el administrador puede registrar y consultar entrenadores.
        Route::middleware('role:admin')->group(function () {
            Route::post('/users/coaches', [CoachController::class, 'store']);
            Route::get('/users/coaches', [CoachController::class, 'index']);
        });
    });
});
