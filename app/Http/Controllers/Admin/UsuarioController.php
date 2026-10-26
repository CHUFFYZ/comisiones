<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AsignacionPermiso;
use App\Models\Facultad;
use App\Models\Rol;
use App\Models\TipoComision;
use App\Models\TipoPermiso;
use App\Models\Usuario;
use App\Services\AccesoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function __construct(private AccesoService $acc) {}

    public function index(Request $r)  { return $this->listar($r, true); }
    public function bajas(Request $r)  { return $this->listar($r, false); }

    public function show(Request $r, int $id)
    {
        $usuario = $this->acc->usuariosGestionables($r->user())->with(['facultad', 'rolModelo'])->findOrFail($id);
        abort_if($this->acc->esSuperUsuario($usuario) && !$this->acc->esSuperUsuario($r->user()), 403, 'Usuario no disponible.');

        return view('admin.usuarios.show', [
            'usuario' => $usuario,
            'puedeMensaje' => (int) $usuario->Activo === 1
                && (int) $usuario->Id !== (int) $r->user()->Id
                && (!$this->acc->esSuperUsuario($usuario) || $this->acc->esSuperUsuario($r->user())),
        ]);
    }

    private function listar(Request $r, bool $activos)
    {
        $q = $this->acc->usuariosGestionables($r->user())->with('facultad')->where('Activo', $activos ? 1 : 0)
            ->when($r->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('Nombre', 'like', "%{$r->q}%")->orWhere('Apellido', 'like', "%{$r->q}%")
                ->orWhere('Matricula', 'like', "%{$r->q}%")->orWhere('Correo', 'like', "%{$r->q}%")))
            ->orderBy('IdFacultad')->orderBy('Nombre');

        return view('admin.usuarios.index', ['usuarios' => $q->paginate(20)->withQueryString(), 'activos' => $activos]);
    }

    public function create(Request $r)
    {
        return $this->formulario($r, null);
    }

    public function edit(Request $r, int $id)
    {
        return $this->formulario($r, $this->buscar($r, $id));
    }

    public function store(Request $r)
    {
        $u = $r->user();
        $d = $this->validar($r);

        $nuevo = DB::transaction(function () use ($u, $d) {
            $x = Usuario::create([
                'Nombre'         => $d['Nombre'],
                'Apellido'       => $d['Apellido'],
                'Matricula'      => $d['Matricula'],
                'Correo'         => $d['Correo'],
                'Iniciales'      => strtolower(mb_substr($d['Nombre'], 0, 1) . mb_substr($d['Apellido'], 0, 1)),
                'Contrasena'     => Hash::make($d['Contrasena']),
                'IdRol'          => $d['IdRol'],
                'GradoAcademico' => $d['GradoAcademico'] ?? null,
                'Genero'         => $d['Genero'],
                'IdFacultad'     => $d['IdFacultad'],
                'Activo'         => 1,
                'CreatedAt'      => now(),
                'CreatedBy'      => $u->Matricula,
                'Versionn'       => 1,
            ]);
            $this->extras($x, $d, $u);

            return $x;
        });

        return redirect()->route('admin.usuarios.index')->with('ok', "Usuario creado: {$nuevo->nombre_corto}.");
    }

    public function update(Request $r, int $id)
    {
        $u = $r->user();
        $x = $this->buscar($r, $id);
        $d = $this->validar($r, $x);

        DB::transaction(function () use ($u, $x, $d) {
            $campos = [
                'Nombre' => $d['Nombre'], 'Apellido' => $d['Apellido'], 'Matricula' => $d['Matricula'],
                'Correo' => $d['Correo'], 'GradoAcademico' => $d['GradoAcademico'] ?? null, 'Genero' => $d['Genero'],
                'IdRol' => $d['IdRol'], 'IdFacultad' => $d['IdFacultad'],
                'UpdatedAt' => now(), 'UpdatedBy' => $u->Matricula,
            ];
            if (!empty($d['Contrasena'])) $campos['Contrasena'] = Hash::make($d['Contrasena']);
            $x->update($campos);
            $this->extras($x, $d, $u);
        });

        return redirect()->route('admin.usuarios.index')->with('ok', 'Usuario actualizado.');
    }

    public function baja(Request $r, int $id)
    {
        abort_if((int) $id === (int) $r->user()->Id, 422, 'No puedes darte de baja a ti mismo.');
        $this->buscar($r, $id)->update(['Activo' => 0, 'DeletedAt' => now(), 'DeletedBy' => $r->user()->Matricula]);

        return back()->with('ok', 'Usuario dado de baja.');
    }

    public function restaurar(Request $r, int $id)
    {
        $this->buscar($r, $id)->update(['Activo' => 1, 'DeletedAt' => null, 'DeletedBy' => null, 'UpdatedBy' => $r->user()->Matricula]);

        return back()->with('ok', 'Usuario reactivado.');
    }

    /* ───────── internos ───────── */

    private function buscar(Request $r, int $id): Usuario
    {
        return $this->acc->usuariosGestionables($r->user())->findOrFail($id);
    }

    private function formulario(Request $r, ?Usuario $x)
    {
        $u = $r->user();

        return view('admin.usuarios.form', [
            'x'            => $x,
            'roles'        => Rol::where('Activo', 1)->whereIn('Clave', $this->acc->rolesCreables($u))->orderBy('Orden')->get(),
            'facultades'   => $this->acc->esSuperUsuario($u)
                ? Facultad::where('Activo', 1)->get() : Facultad::where('Id', $u->IdFacultad)->get(),
            'niveles'      => $this->acc->nivelesAsignables($u),
            'tipos'        => TipoPermiso::where('Activo', 1)->get(),
            'tiposComision'=> TipoComision::where('Activo', 1)->orderBy('Nombre')->get(),
            'base'         => $x ? AsignacionPermiso::where('IdUsuario', $x->Id)->whereNotNull('IdNivelAcceso')->whereNull('DeletedAt')->first() : null,
            'tiposX'       => $x ? $x->tiposComision()->pluck('tipo_comision.Id')->all() : [],
        ]);
    }

    private function validar(Request $r, ?Usuario $x = null): array
    {
        $u    = $r->user();
        $esSU = $this->acc->esSuperUsuario($u);
        $roles = Rol::where('Activo', 1)->whereIn('Clave', $this->acc->rolesCreables($u))->pluck('Id')->all();

        $d = $r->validate([
            'Nombre'         => 'required|string|max:50',
            'Apellido'       => 'required|string|max:50',
            'Matricula'      => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('usuario', 'Matricula')->ignore($x?->Id, 'Id')],
            'Correo'         => ['required', 'email', 'max:50', Rule::unique('usuario', 'Correo')->ignore($x?->Id, 'Id')],
            'GradoAcademico' => 'nullable|string|max:50',
            'Genero'         => 'required|in:0,1',
            'IdRol'          => ['required', Rule::in($roles)],
            'IdFacultad'     => [$esSU ? 'required' : 'nullable', 'exists:facultad,Id'],
            'Contrasena'     => [$x ? 'nullable' : 'required', 'string', 'min:8'],
            'IdNivelAcceso'  => ['nullable', 'required_with:IdTipoPermiso', Rule::in($this->acc->nivelesAsignables($u)->pluck('Id')->all())],
            'IdTipoPermiso'  => ['nullable', 'required_with:IdNivelAcceso', 'exists:tipo_permiso,Id'],
            'tiposComision'  => 'nullable|array',
            'tiposComision.*'=> 'exists:tipo_comision,Id',
        ], [
            'Matricula.unique' => 'Esa matrícula ya está registrada.',
            'Matricula.regex'  => 'La matrícula solo admite letras, números, guion y guion bajo.',
            'Correo.unique'    => 'Ese correo ya está registrado.',
            'IdRol.in'         => 'No puedes asignar ese rol.',
            'Contrasena.min'   => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        if (!$esSU) $d['IdFacultad'] = $u->IdFacultad;

        return $d;
    }

    /** Tipos de comisión y permiso base (opcional). */
    private function extras(Usuario $x, array $d, Usuario $u): void
    {
        $x->tiposComision()->sync($d['tiposComision'] ?? []);

        if (empty($d['IdNivelAcceso'])) return;

        $vals = ['IdNivelAcceso' => $d['IdNivelAcceso'], 'IdTipoPermiso' => $d['IdTipoPermiso'],
                 'Activo' => 1, 'UpdatedAt' => now(), 'UpdatedBy' => $u->Matricula];

        $fila = AsignacionPermiso::where('IdUsuario', $x->Id)->whereNotNull('IdNivelAcceso')->whereNull('DeletedAt')->first();
        if ($fila) {
            $fila->update($vals);
        } else {
            AsignacionPermiso::create($vals + [
                'IdUsuario' => $x->Id, 'Clave' => 'AP' . strtoupper($x->Matricula),
                'Descripcion' => "Permiso base de {$x->Matricula}",
                'CreatedAt' => now(), 'CreatedBy' => $u->Matricula, 'Versionn' => 1,
            ]);
        }
    }
}
