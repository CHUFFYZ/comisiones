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

        // Módulo actual (lo fija el middleware); si no hay, el módulo principal CATI (MP)
        $moduloActual = request()->attributes->get('modulo')
            ?? Modulo::where('Clave', 'MP')->first();

        // Facultad del usuario; sin sesión, FCI
        $facultadActual = $user?->facultad
            ?? Facultad::where('Clave', 'FCI')->first();

        $view->with([
            'moduloActual'   => $moduloActual,
            'facultadActual' => $facultadActual,
            'modulosMenu'    => $this->acceso->modulosDe($user),
        ]);
    }
}