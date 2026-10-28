<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use App\Services\AccesoService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Si el superusuario está simulando a alguien, durante la petición el usuario
 * autenticado ES el simulado (permisos, módulos, menú, todo). El real queda en
 * $request->attributes['usuario_real'].
 */
class SimularUsuario
{
    public function handle(Request $request, Closure $next)
    {
        $real = $request->user();
        $id   = $request->session()->get('simulando');

        if ($real && $id) {
            $sim = app(AccesoService::class)->esSuperUsuario($real) ? Usuario::where('Activo', 1)->find($id) : null;
            if ($sim) {
                $request->attributes->set('usuario_real', $real);
                Auth::setUser($sim);
            } else {
                $request->session()->forget('simulando');
            }
        }

        return $next($request);
    }
}
