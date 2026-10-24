<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index(Request $r)
    {
        return view('notificaciones.index', [
            'notificaciones' => Notificacion::where('IdUsuario', $r->user()->Id)->orderByDesc('Id')->paginate(20),
        ]);
    }

    /** Marca como leída y lleva al destino. */
    public function ir(Request $r, int $id)
    {
        $n = Notificacion::where('IdUsuario', $r->user()->Id)->findOrFail($id);
        $n->update(['LeidaAt' => $n->LeidaAt ?? now()]);

        return redirect($n->Url ?: route('notificaciones.index'));
    }

    public function leerTodas(Request $r)
    {
        Notificacion::where('IdUsuario', $r->user()->Id)->whereNull('LeidaAt')->update(['LeidaAt' => now()]);

        return back();
    }
}
