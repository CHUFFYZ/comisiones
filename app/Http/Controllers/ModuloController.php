<?php

namespace App\Http\Controllers;

use App\Services\AccesoService;
use App\Services\ComisionService;
use Illuminate\Http\Request;

class ModuloController extends Controller
{
    public function __construct(private AccesoService $acceso, private ComisionService $comisiones) {}

    /** "Mis módulos": solo los que el usuario puede ver */
    public function index(Request $request)
    {
        return view('modulos.index', [
            'modulos' => $this->acceso->modulosDe($request->user()),
        ]);
    }

    /** Contenedor de un módulo (el middleware ya validó el acceso) */
    public function show(Request $request)
    {
        $modulo = $request->attributes->get('modulo');
        $user   = $request->user();

        return view('modulos.show', [
            'modulo'   => $modulo,
            'permiso'  => $this->acceso->letrasEn($user, $modulo->Clave),
            'opciones' => $this->acceso->opcionesDe($user, $modulo->Clave),
            // Tablero de comisiones solo para SisComisiones; el resto conserva sus accesos
            'stats'    => $modulo->Clave === 'MSC' ? $this->comisiones->estadisticas($this->actor($request)) : null,
        ]);
    }
}
