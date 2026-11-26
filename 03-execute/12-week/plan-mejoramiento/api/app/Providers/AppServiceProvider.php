<?php
namespace App\Providers;

use App\Application\Ports\Clock;
use App\Application\Ports\PasswordHasher;
use App\Application\Ports\TokenIssuer;
use App\Application\Ports\UserRepository;
use App\Application\Ports\SportsGearRepository;
use App\Infrastructure\Persistence\EloquentUserRepository;
use App\Infrastructure\Security\LaravelPasswordHasher;
use App\Infrastructure\Time\SystemClock;
use App\Infrastructure\Persistence\EloquentSportsGearRepository;
use App\Models\User;
use App\Services\Auth\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Exception;

class AppServiceProvider extends ServiceProvider
{
    /** Registers the concrete implementations of the ports. */
    public function register(): void
    {
        // Connects the application contracts with the Laravel adapters.
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(SportsGearRepository::class, EloquentSportsGearRepository::class);
        $this->app->bind(PasswordHasher::class, LaravelPasswordHasher::class);
        $this->app->bind(TokenIssuer::class, JwtService::class);
        $this->app->bind(Clock::class, SystemClock::class);
    }

    /** Registers the JWT guard that protects the API routes. */
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

            // Reads the current role from the database, not only the token claim.
            return User::find($payload->sub);
        });
    }
}
