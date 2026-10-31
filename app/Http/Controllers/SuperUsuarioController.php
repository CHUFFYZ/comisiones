<?php

namespace App\Http\Controllers;

use App\Models\Comision;
use App\Services\AccesoService;
use App\Services\ComisionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/** Módulo independiente (con contraseña): reabrir o eliminar comisiones cerradas. */
class SuperUsuarioController extends Controller
{
    public function __construct(private ComisionService $svc, private AccesoService $acc) {}

    private function real(Request $r)
    {
        $real = $r->attributes->get('usuario_real', $r->user());
        abort_unless($this->acc->esSuperUsuario($real), 403, 'Solo el super usuario.');

        return $real;
    }

    public function desbloquear(Request $r)
    {
        $this->real($r);

        return view('superusuario.desbloquear');
    }

    public function validar(Request $r)
    {
        $real = $this->real($r);
        $r->validate(['password' => 'required|string'], ['password.required' => 'Escribe tu contraseña.']);

        if (!Hash::check($r->password, $real->Contrasena)) {
            return back()->withErrors(['password' => 'Contraseña incorrecta.']);
        }
        $r->session()->put('su_desbloqueado', now()->timestamp);

        return redirect()->route('su.index');
    }

    public function bloquear(Request $r)
    {
        $r->session()->forget('su_desbloqueado');

        return redirect()->route('modulos.index')->with('ok', 'Módulo de super usuario bloqueado.');
    }

    public function index(Request $r)
    {
        $this->real($r);
        $q = trim((string) $r->query('q'));

        $cerradas = Comision::with(['tipo', 'facultad', 'profesores', 'cierreUsuario'])->where('Cerrada', 1)
            ->when($q !== '', fn ($w) => $w->where(fn ($n) => $n->where('NOficio', 'like', "%$q%")->orWhere('Asunto', 'like', "%$q%")))
            ->orderByDesc('CerradaAt')->paginate(20)->withQueryString();

        return view('superusuario.index', ['cerradas' => $cerradas, 'q' => $q]);
    }

    public function reabrir(Request $r, int $id)
    {
        $real = $this->real($r);
        $c = Comision::where('Cerrada', 1)->findOrFail($id);
        $this->svc->reabrir($real, $c);

        return back()->with('ok', "Comisión {$c->NOficio} reabierta: las secretarias ya pueden modificarla.");
    }

    public function eliminar(Request $r, int $id)
    {
        ignore_user_abort(true);
        $real = $this->real($r);
        $c = Comision::findOrFail($id);
        $this->svc->eliminarDefinitivo($real, $c, true);

        return back()->with('ok', "Comisión {$c->NOficio} eliminada definitivamente.");
    }
}
