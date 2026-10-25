<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Services\AccesoService;
use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Usuario "que actúa". Si el superusuario está simulando a una secretaria,
     * todo (alcance, CreatedBy, iniciales, notificaciones) se hace a nombre de ella.
     */
    protected function actor(Request $r): Usuario
    {
        $real = $r->user();
        $id   = $r->session()->get('simulando');

        if ($id && app(AccesoService::class)->esSuperUsuario($real)) {
            $sim = Usuario::where('Activo', 1)->find($id);
            if ($sim) return $sim;
        }

        return $real;
    }
}
