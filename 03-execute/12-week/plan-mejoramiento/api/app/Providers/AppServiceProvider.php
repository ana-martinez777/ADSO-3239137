<?php
namespace App\Providers;

use App\Application\Ports\PasswordHasher;
use App\Application\Ports\TokenIssuer;
use App\Application\Ports\UserRepository;
use App\Infrastructure\Persistence\EloquentUserRepository;
use App\Infrastructure\Security\LaravelPasswordHasher;
use App\Models\User;
use App\Services\Auth\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Exception;

class AppServiceProvider extends ServiceProvider
{
    /** Registra las implementaciones concretas de los puertos. */
    public function register(): void
    {
        // Conecta los contratos de aplicación con los adaptadores de Laravel.
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(PasswordHasher::class, LaravelPasswordHasher::class);
        $this->app->bind(TokenIssuer::class, JwtService::class);
    }

    /** Registra el guard JWT que protege las rutas de la API. */
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

            // Consulta el rol vigente en la base de datos, no solo el claim del token.
            return User::find($payload->sub);
        });
    }
}
