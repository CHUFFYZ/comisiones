<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NivelAcceso;
use App\Models\TipoComision;
use App\Services\AccesoService;
use Illuminate\Http\Request;

/** Edición de niveles de acceso existentes (p. ej. MCNA1): nombre, estado y áreas de comisión que otorgan. */
class NivelAccesoController extends Controller
{
    public function __construct(private AccesoService $acc) {}

    private function autorizar(Request $r)
    {
        $u = $r->user();
        abort_unless($this->acc->esSuperUsuario($u) || $u->Rol === 'Administrador', 403, 'Solo Administrador o Super usuario.');

        return $u;
    }

    private function niveles(Request $r)
    {
        $u = $this->autorizar($r);

        return NivelAcceso::with(['modulo', 'tipos'])
            ->when(!$this->acc->esSuperUsuario($u), fn ($q) => $q->whereHas('modulo', fn ($m) => $m->whereIn('Clave', config('modulos.asignables.Administrador', []))))
            ->orderBy('IdModulo')->orderBy('Clave');
    }

    public function index(Request $r)
    {
        return view('admin.niveles.index', ['niveles' => $this->niveles($r)->get()]);
    }

    public function edit(Request $r, int $id)
    {
        $n = $this->niveles($r)->findOrFail($id);

        return view('admin.niveles.form', [
            'n'      => $n,
            'tipos'  => TipoComision::where('Activo', 1)->orderBy('Nombre')->get(),
            'marcados' => $n->tipos->pluck('Id')->all(),
        ]);
    }

    public function update(Request $r, int $id)
    {
        $n = $this->niveles($r)->findOrFail($id);
        $d = $r->validate([
            'Nombre'      => 'required|string|max:50',
            'Descripcion' => 'nullable|string|max:500',
            'Activo'      => 'nullable|boolean',
            'tipos'       => 'nullable|array',
            'tipos.*'     => 'exists:tipo_comision,Id',
        ], ['Nombre.required' => 'El nombre es obligatorio.']);

        $n->update([
            'Nombre'      => $d['Nombre'],
            'Descripcion' => $d['Descripcion'] ?? null,
            'Activo'      => $r->boolean('Activo') ? 1 : 0,
            'UpdatedAt'   => now(),
            'UpdatedBy'   => $r->user()->Matricula,
            'Versionn'    => ((int) $n->Versionn) + 1,
        ]);
        $n->tipos()->sync($d['tipos'] ?? []);

        return redirect()->route('admin.niveles.index')->with('ok', "Nivel {$n->Clave} actualizado.");
    }
}
