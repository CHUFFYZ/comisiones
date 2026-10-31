<?php

namespace App\Http\Controllers;

use App\Services\ComisionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Cierre de comisiones y borrado definitivo de anuladas. */
class ComisionExtraController extends Controller
{
    public function __construct(private ComisionService $svc) {}

    /** Pestañas: por cerrar (requiere permiso de actualizar) · cerradas (solo consulta). */
    public function cierre(Request $r)
    {
        $u   = $this->actor($r);
        $ver = $r->query('ver') === 'cerradas' ? 'cerradas' : 'por-cerrar';

        if ($ver === 'cerradas') {
            $lista = $this->svc->visibles($u, false)->with('cierreUsuario')->where('Cerrada', 1)
                ->reorder()->orderByDesc('CerradaAt')->paginate(20)->withQueryString();
        } else {
            $lista = $this->svc->visibles($u, false)->where('IdEstado', 3)->where('Cerrada', 0)
                ->limit(300)->get()->map(fn ($c) => ['c' => $c, 'motivo' => $this->svc->motivoNoCerrable($c)]);
        }

        return view('comisiones.cierre', ['ver' => $ver, 'lista' => $lista]);
    }

    public function cerrar(Request $r)
    {
        ignore_user_abort(true);
        $d = $r->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer'],
            ['ids.required' => 'Selecciona al menos una comisión.']);
        $u = $this->actor($r);

        $ok = 0; $errores = [];
        foreach ($d['ids'] as $id) {
            try {
                $this->svc->cerrar($u, $this->svc->visibles($u, false)->findOrFail($id));
                $ok++;
            } catch (ValidationException $e) {
                $errores[] = $e->validator->errors()->first();
            }
        }

        $resp = back();
        if ($ok) $resp->with('ok', $ok === 1 ? 'Comisión cerrada.' : "$ok comisiones cerradas.");
        if ($errores) $resp->withErrors(['general' => implode(' ', $errores)]);

        return $resp;
    }

    /** Solo comisiones anuladas; se confirma dos veces en pantalla. */
    public function eliminarDefinitivo(Request $r, int $id)
    {
        ignore_user_abort(true);
        $u = $this->actor($r);
        $c = $this->svc->visibles($u, true)->findOrFail($id);
        $this->svc->eliminarDefinitivo($u, $c);

        return redirect()->route('comisiones.anuladas')->with('ok', "Comisión {$c->NOficio} eliminada definitivamente.");
    }
}
