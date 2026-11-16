<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Uso de las rutas (siempre después de 'auth'):
 *  ->middleware('role:admin')
 *  ->middleware('role:admin,gestora')
 * 
 * Los parámetros son los valores de UserRole
 */
final class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $allowed = array_map(UserRole::from(...), $roles);
        if (! $request->user()?->hasRole(...$allowed)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }
        return $next($request);
    }
}
