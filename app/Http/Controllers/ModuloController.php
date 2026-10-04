<?php

namespace App\Http\Controllers;

use App\Services\AccesoService;
use Illuminate\Http\Request;

class ModuloController extends Controller
{
    public function __construct(private AccesoService $acceso) {}

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

        return view('modulos.show', [
            'modulo'  => $modulo,
            'permiso' => $this->acceso->letrasEn($request->user(), $modulo->Clave),
        ]);
    }
}