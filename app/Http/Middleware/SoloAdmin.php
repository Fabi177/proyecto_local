<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deja pasar solo a los administradores (users.role = 'admin').
 *
 * Se usa siempre DESPUÉS de 'auth' (ver el grupo /admin en routes/web.php).
 * Cualquier otra persona recibe 404, igual que en ComercioController::autorizarComercio(),
 * para no revelar que el panel existe.
 */
class SoloAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->esAdmin(), 404);

        return $next($request);
    }
}
