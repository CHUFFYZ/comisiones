<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;

/** Guarda la última conexión (como máximo una vez por minuto). */
class ActualizarConexion
{
    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();

        if ($u && (!$u->UltimaConexion || $u->UltimaConexion->lt(now()->subMinute()))) {
            Usuario::where('Id', $u->Id)->update(['UltimaConexion' => now()]);
        }

        return $next($request);
    }
}
