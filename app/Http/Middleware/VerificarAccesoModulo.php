<?php

namespace App\Http\Middleware;

use App\Models\Modulo;
use App\Services\AccesoService;
use Closure;
use Illuminate\Http\Request;

class VerificarAccesoModulo
{
    public function __construct(private AccesoService $acceso) {}

    /**
     * Uso: ->middleware('modulo.acceso:leer')            (toma {modulo} de la ruta)
     *      ->middleware('modulo.acceso:crear,MSC')       (módulo fijo)
     */
    public function handle(Request $request, Closure $next, string $accion = 'leer', ?string $clave = null)
    {
        $clave ??= $request->route('modulo');

        $modulo = Modulo::where('Clave', $clave)->where('Activo', 1)->first();
        abort_if(!$modulo, 404, 'El módulo no existe o está inactivo.');

        abort_unless(
            $this->acceso->puede($request->user(), $modulo->Clave, $accion),
            403,
            'No tienes permiso para esta acción en el módulo ' . $modulo->Nombre . '.'
        );

        $request->attributes->set('modulo', $modulo);

        return $next($request);
    }
}