<?php
namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /** Restringe la ruta a los roles indicados. */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Comprueba el rol del usuario recuperado por el guard JWT.
        if (!$user || !in_array($user->role, $roles, true)) {
            throw new ApiException(
                'FORBIDDEN_ROLE',
                'No tienes permisos para realizar esta operación.',
                403
            );
        }

        return $next($request);
    }
}
