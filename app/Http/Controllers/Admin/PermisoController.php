<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AsignacionPermiso;
use App\Models\TipoPermiso;
use App\Services\AccesoService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Permisos temporales: SU a todos · Admin a su facultad · Sec. Académica a sus secretarias. */
class PermisoController extends Controller
{
    public function __construct(private AccesoService $acc) {}

    public function index(Request $r)
    {
        $u = $r->user();

        $usuarios = $this->acc->usuariosGestionables($u)->where('Activo', 1)->where('Id', '<>', $u->Id)
            ->with([
                'facultad',
                'asignaciones.nivel.modulo', 'asignaciones.tipo',
                'asignaciones.nivelTemp.modulo', 'asignaciones.tipoTemp',
            ])
            ->orderBy('IdFacultad')->orderBy('Nombre')->paginate(20);

        return view('admin.permisos.index', [
            'usuarios' => $usuarios,
            'niveles'  => $this->acc->nivelesAsignables($u),
            'tipos'    => TipoPermiso::where('Activo', 1)->get(),
        ]);
    }

    public function store(Request $r, int $id)
    {
        $u = $r->user();
        $x = $this->acc->usuariosGestionables($u)->where('Id', '<>', $u->Id)->findOrFail($id);

        $d = $r->validate([
            'IdNivelAccesoTemp' => ['required', Rule::in($this->acc->nivelesAsignables($u)->pluck('Id')->all())],
            'IdTipoPermisoTemp' => 'required|exists:tipo_permiso,Id',
            'IniPermisoTemp'    => 'required|date',
            'FinPermisoTemp'    => 'required|date|after:IniPermisoTemp',
        ], [
            'IdNivelAccesoTemp.in'   => 'No puedes asignar ese nivel de acceso.',
            'FinPermisoTemp.after'   => 'El fin debe ser posterior al inicio.',
        ]);

        $vals = $d + ['UpdatedAt' => now(), 'UpdatedBy' => $u->Matricula];
        $fila = AsignacionPermiso::where('IdUsuario', $x->Id)->whereNull('DeletedAt')->orderBy('Id')->first();

        if ($fila) {
            $fila->update($vals);
        } else {
            AsignacionPermiso::create($vals + [
                'IdUsuario' => $x->Id, 'Clave' => 'TEMP-' . $x->Matricula, 'Descripcion' => 'Permiso temporal',
                'Activo' => 1, 'CreatedAt' => now(), 'CreatedBy' => $u->Matricula, 'Versionn' => 1,
            ]);
        }

        return back()->with('ok', "Permiso temporal asignado a {$x->nombre_corto}.");
    }

    public function destroy(Request $r, int $id)
    {
        $u = $r->user();
        $x = $this->acc->usuariosGestionables($u)->findOrFail($id);

        AsignacionPermiso::where('IdUsuario', $x->Id)->whereNull('DeletedAt')->update([
            'IdNivelAccesoTemp' => null, 'IdTipoPermisoTemp' => null,
            'IniPermisoTemp' => null, 'FinPermisoTemp' => null,
            'UpdatedAt' => now(), 'UpdatedBy' => $u->Matricula,
        ]);

        return back()->with('ok', 'Permiso temporal retirado.');
    }

    public function storePermanente(Request $r, int $id)
    {
        $u = $r->user();
        $x = $this->acc->usuariosGestionables($u)->where('Id', '<>', $u->Id)->findOrFail($id);
        $niveles = $this->acc->nivelesAsignables($u);

        $d = $r->validate([
            'IdNivelAcceso' => ['required', Rule::in($niveles->pluck('Id')->all())],
            'IdTipoPermiso' => ['required', Rule::in(TipoPermiso::where('Activo', 1)->pluck('Id')->all())],
        ], [
            'IdNivelAcceso.in' => 'No puedes asignar ese nivel de acceso.',
            'IdTipoPermiso.in' => 'Selecciona un tipo de permiso activo.',
        ]);

        $nivel = $niveles->firstWhere('Id', (int) $d['IdNivelAcceso']);
        $fila = AsignacionPermiso::where('IdUsuario', $x->Id)
            ->where('IdNivelAcceso', $nivel->Id)
            ->whereNull('DeletedAt')
            ->first();

        $valores = [
            'IdNivelAcceso' => $nivel->Id,
            'IdTipoPermiso' => $d['IdTipoPermiso'],
            'Activo' => 1,
            'UpdatedAt' => now(),
            'UpdatedBy' => $u->Matricula,
        ];

        if ($fila) {
            $fila->update($valores);
        } else {
            AsignacionPermiso::create($valores + [
                'IdUsuario' => $x->Id,
                'Clave' => 'AP' . $x->Matricula . '-' . $nivel->Id,
                'Descripcion' => "Permiso permanente de {$x->Matricula}",
                'CreatedAt' => now(),
                'CreatedBy' => $u->Matricula,
                'Versionn' => 1,
            ]);
        }

        return back()->with('ok', "Permiso permanente asignado a {$x->nombre_corto}.");
    }

    public function destroyPermanente(Request $r, int $id, int $asignacion)
    {
        $u = $r->user();
        $x = $this->acc->usuariosGestionables($u)->where('Id', '<>', $u->Id)->findOrFail($id);
        $fila = AsignacionPermiso::where('IdUsuario', $x->Id)
            ->whereNotNull('IdNivelAcceso')
            ->whereNull('DeletedAt')
            ->findOrFail($asignacion);

        abort_unless(
            $this->acc->nivelesAsignables($u)->contains('Id', (int) $fila->IdNivelAcceso),
            403,
            'No puedes retirar este nivel de acceso.'
        );

        $auditoria = ['UpdatedAt' => now(), 'UpdatedBy' => $u->Matricula];
        if ($fila->IdTipoPermisoTemp || $fila->IdNivelAccesoTemp) {
            $fila->update($auditoria + ['IdNivelAcceso' => null, 'IdTipoPermiso' => null]);
        } else {
            $fila->update($auditoria + [
                'IdNivelAcceso' => null,
                'IdTipoPermiso' => null,
                'Activo' => 0,
                'DeletedAt' => now(),
                'DeletedBy' => $u->Matricula,
            ]);
        }

        return back()->with('ok', "Permiso permanente retirado de {$x->nombre_corto}.");
    }
}
