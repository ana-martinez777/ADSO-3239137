<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\SportsGearController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {

    // Rutas públicas.
    Route::get('/health', HealthController::class);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Rutas que requieren autenticación.
    Route::middleware('auth:api')->group(function (): void {

        // Categorías.
        Route::get('/categories', [SportsGearController::class, 'categories']);

        // Consulta de implementos.
        Route::get('/equipment', [SportsGearController::class, 'equipmentIndex']);
        Route::get('/equipment/{id}', [SportsGearController::class, 'equipmentShow']);

        // Rutas permitidas para Admin y Coach.
        Route::middleware('role:admin,coach')->group(function (): void {

            // Gestión de deportistas.
            Route::get('/athletes', [SportsGearController::class, 'athletesIndex']);
            Route::get('/athletes/{id}', [SportsGearController::class, 'athleteShow']);
            Route::post('/athletes', [SportsGearController::class, 'athleteStore']);

            // Consulta de préstamos.
            // La ruta fija se declara antes de /loans/{id}.
            Route::get('/loans/overdue', [SportsGearController::class, 'overdueIndex']);
            Route::get('/loans', [SportsGearController::class, 'loansIndex']);
            Route::get('/loans/{id}', [SportsGearController::class, 'loanShow']);
        });

        // Operaciones exclusivas del Admin.
        Route::middleware('role:admin')->group(function (): void {

            // Gestión de entrenadores.
            Route::post('/users/coaches', [CoachController::class, 'store']);
            Route::get('/users/coaches', [CoachController::class, 'index']);

            // Gestión de implementos.
            Route::post('/equipment', [SportsGearController::class, 'equipmentStore']);
            Route::put('/equipment/{id}', [SportsGearController::class, 'equipmentUpdate']);
            Route::delete('/equipment/{id}', [SportsGearController::class, 'equipmentDestroy']);

            // Reportes.
            Route::get('/reports/most-requested', [SportsGearController::class, 'mostRequestedIndex']);
        });

        // Operaciones exclusivas del Coach.
        Route::middleware('role:coach')->group(function (): void {

            // Registro de préstamos.
            Route::post('/loans', [SportsGearController::class, 'loanStore']);

            // Devolución de implementos.
            Route::post(
                '/loans/{loanId}/items/{itemId}/return',
                [SportsGearController::class, 'returnLoanItem']
            );
        });
    });
});