<?php

namespace App\Http\Middleware;

use App\Services\AccesoService;
use Closure;
use Illuminate\Http\Request;

/** Solo SuperUsuario, Administrador y Secretaría Académica. */
class GestionUsuarios
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(app(AccesoService::class)->puedeGestionarUsuarios($request->user()), 403, 'No tienes permiso para gestionar usuarios.');

        return $next($request);
    }
}
