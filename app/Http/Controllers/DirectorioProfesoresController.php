<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Services\AccesoService;
use App\Services\ComisionService;
use Illuminate\Http\Request;

/**
 * Profesores con sus comisiones, limitadas al alcance de quien consulta:
 * secretaria = solo sus tipos · sec. académica = todas de su facultad · SU = todo.
 * El administrador no entra aquí (solo consulta comisiones).
 */
class DirectorioProfesoresController extends Controller
{
    public function __construct(private ComisionService $svc, private AccesoService $acc) {}

    public function index(Request $r)
    {
        $u = $this->actor($r);
        $q = trim((string) $r->q);

        $profesores = Usuario::deRol('Profesor')->where('Activo', 1)->with('facultad')
            ->when(!$this->acc->esSuperUsuario($u), fn ($w) => $w->where('IdFacultad', $u->IdFacultad))
            ->when($q !== '', fn ($w) => $w->where(fn ($n) => $n
                ->where('Nombre', 'like', "%$q%")->orWhere('Apellido', 'like', "%$q%")
                ->orWhere('Matricula', 'like', "%$q%")
                ->orWhereRaw("CONCAT(Nombre, ' ', Apellido) like ?", ["%$q%"])))
            ->orderBy('Apellido')->orderBy('Nombre')->get();

        $porProfesor = [];
        foreach ($this->svc->visibles($u, false)->get() as $c) {
            foreach ($c->profesores as $p) {
                $porProfesor[$p->Id][] = $c;
            }
        }

        return view('comisiones.profesores', [
            'profesores'  => $profesores,
            'porProfesor' => $porProfesor,
            'q'           => $q,
            'esSU'        => $this->acc->esSuperUsuario($u),
        ]);
    }
}
