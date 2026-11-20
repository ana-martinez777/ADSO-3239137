<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Auth\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Exception;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::viaRequest('jwt', function (Request $request) {
            $token = $request->bearerToken();

            if (!$token) {
                return null;
            }

            try {
                $payload = app(JwtService::class)->validate($token);
            } catch (Exception $exception) {
                return null;
            }

            if (!isset($payload->sub) || !is_string($payload->sub)) {
                return null;
            }

            return User::find($payload->sub);
        });
    }
}
