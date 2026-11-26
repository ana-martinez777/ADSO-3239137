<?php
namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /** Restricts the route to the given roles. */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Checks the role of the user resolved by the JWT guard.
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
