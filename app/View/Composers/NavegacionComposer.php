<?php

namespace App\View\Composers;

use App\Models\Facultad;
use App\Models\Modulo;
use App\Services\AccesoService;
use Illuminate\View\View;

class NavegacionComposer
{
    public function __construct(private AccesoService $acceso) {}

    public function compose(View $view): void
    {
        $user = auth()->user();

        // Módulo en el que está navegando (lo fija el middleware)
        $moduloEnUso = request()->attributes->get('modulo');

        // Para el header: módulo en uso o, si no hay, el principal CATI (MP)
        $moduloActual = $moduloEnUso ?? Modulo::where('Clave', 'MP')->first();

        $facultadActual = $user?->facultad
            ?? Facultad::where('Clave', 'FCI')->first();

        $view->with([
            'moduloActual'   => $moduloActual,
            'moduloEnUso'    => $moduloEnUso,
            'facultadActual' => $facultadActual,
            'modulosMenu'    => $this->acceso->modulosDe($user),
            'opcionesModulo' => $moduloEnUso
                ? $this->acceso->opcionesDe($user, $moduloEnUso->Clave)
                : collect(),
        ]);
    }
}