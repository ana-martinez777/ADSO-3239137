<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\SportsGearController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {

    // Public routes.
    Route::get('/health', HealthController::class);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Routes that require authentication.
    Route::middleware('auth:api')->group(function (): void {

        // Categories.
        Route::get('/categories', [SportsGearController::class, 'categories']);

        // Equipment queries.
        Route::get('/equipment', [SportsGearController::class, 'equipmentIndex']);
        Route::get('/equipment/{id}', [SportsGearController::class, 'equipmentShow']);

        // Routes allowed for Admin and Coach.
        Route::middleware('role:admin,coach')->group(function (): void {

            // Athlete management.
            Route::get('/athletes', [SportsGearController::class, 'athletesIndex']);
            Route::get('/athletes/{id}', [SportsGearController::class, 'athleteShow']);
            Route::post('/athletes', [SportsGearController::class, 'athleteStore']);

            // Loan queries.
            // The fixed route is declared before /loans/{id}.
            Route::get('/loans/overdue', [SportsGearController::class, 'overdueIndex']);
            Route::get('/loans', [SportsGearController::class, 'loansIndex']);
            Route::get('/loans/{id}', [SportsGearController::class, 'loanShow']);
        });

        // Admin-only operations.
        Route::middleware('role:admin')->group(function (): void {

            // Coach management.
            Route::post('/users/coaches', [CoachController::class, 'store']);
            Route::get('/users/coaches', [CoachController::class, 'index']);

            // Equipment management.
            Route::post('/equipment', [SportsGearController::class, 'equipmentStore']);
            Route::put('/equipment/{id}', [SportsGearController::class, 'equipmentUpdate']);
            Route::delete('/equipment/{id}', [SportsGearController::class, 'equipmentDestroy']);

            // Reportes.
            Route::get('/reports/most-requested', [SportsGearController::class, 'mostRequestedIndex']);
        });

        // Coach-only operations.
        Route::middleware('role:coach')->group(function (): void {

            // Loan registration.
            Route::post('/loans', [SportsGearController::class, 'loanStore']);

            // Equipment returns.
            Route::post(
                '/loans/{loanId}/items/{itemId}/return',
                [SportsGearController::class, 'returnLoanItem']
            );
        });
    });
});