<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facultad;
use App\Models\Usuario;
use App\Services\AccesoService;
use Illuminate\Http\Request;

class FacultadController extends Controller
{
    public function __construct(private AccesoService $acc) {}

    public function index(Request $r)
    {
        abort_unless($this->acc->esSuperUsuario($r->user()), 403, 'Solo el Super Usuario.');

        return view('admin.facultades.index', [
            'facultades'  => Facultad::where('Activo', 1)->orderBy('Clave')->get(),
            'secretarias' => Usuario::where('Activo', 1)->deRol('SecretariaAcademica')->get()->groupBy('IdFacultad'),
        ]);
    }

    public function firmaDirector(Request $r, int $id)
    {
        abort_unless($this->acc->esSuperUsuario($r->user()), 403, 'Solo el Super Usuario.');
        $f = Facultad::findOrFail($id);
        $on = $r->boolean('valor');
        $f->update(['SAFirmaComoDirector' => $on ? 1 : 0, 'UpdatedAt' => now(), 'UpdatedBy' => $r->user()->Matricula]);

        return back()->with('ok', $on
            ? "{$f->Clave}: la Sec. Académica ya puede firmar como Director."
            : "{$f->Clave}: la Sec. Académica ya no puede firmar como Director.");
    }
}