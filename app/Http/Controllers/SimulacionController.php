<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Services\AccesoService;
use Illuminate\Http\Request;

class SimulacionController extends Controller
{
    public function iniciar(Request $r, AccesoService $acceso)
    {
        abort_unless($acceso->esSuperUsuario($r->user()), 403);
        $d = $r->validate(['IdUsuario' => 'required|integer'], ['IdUsuario.required' => 'Elige una secretaria.']);

        $s = Usuario::where('Activo', 1)->deRol(['Secretaria', 'SecretariaAcademica'])->findOrFail($d['IdUsuario']);
        $r->session()->put('simulando', $s->Id);

        return redirect()->route('modulos.show', 'MSC')->with('ok', "Simulando a {$s->nombre_corto}.");
    }

    public function terminar(Request $r)
    {
        $r->session()->forget('simulando');

        return redirect()->route('modulos.index')->with('ok', 'Simulación terminada.');
    }
}
