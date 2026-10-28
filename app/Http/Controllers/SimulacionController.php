<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Services\AccesoService;
use Illuminate\Http\Request;

class SimulacionController extends Controller
{
    public function iniciar(Request $r, AccesoService $acceso)
    {
        $real = $r->attributes->get('usuario_real', $r->user());
        abort_unless($acceso->esSuperUsuario($real), 403);
        $d = $r->validate(['IdUsuario' => 'required|integer'], ['IdUsuario.required' => 'Elige un usuario.']);

        $s = Usuario::where('Activo', 1)->findOrFail($d['IdUsuario']);
        abort_if($acceso->esSuperUsuario($s), 422, 'No puedes simular a otro superusuario.');
        $r->session()->put('simulando', $s->Id);

        return redirect($s->Rol === 'Profesor' ? route('profesor.panel') : route('modulos.index'))
            ->with('ok', "Simulando a {$s->nombre_corto}.");
    }

    public function terminar(Request $r)
    {
        $r->session()->forget('simulando');

        return redirect()->route('modulos.index')->with('ok', 'Simulación terminada.');
    }
}
