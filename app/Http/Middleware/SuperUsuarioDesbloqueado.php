<?php

namespace App\Http\Middleware;

use App\Services\AccesoService;
use Closure;
use Illuminate\Http\Request;

/** Módulo del super usuario: exige contraseña (se vuelve a pedir tras 15 min de inactividad). */
class SuperUsuarioDesbloqueado
{
    public const MINUTOS = 15;

    public function handle(Request $request, Closure $next)
    {
        $real = $request->attributes->get('usuario_real', $request->user());
        abort_unless(app(AccesoService::class)->esSuperUsuario($real), 403, 'Solo el super usuario.');

        $t = $request->session()->get('su_desbloqueado');
        if (!$t || now()->timestamp - $t > self::MINUTOS * 60) {
            $request->session()->forget('su_desbloqueado');
            return redirect()->route('su.desbloquear');
        }
        $request->session()->put('su_desbloqueado', now()->timestamp);

        return $next($request);
    }
}
