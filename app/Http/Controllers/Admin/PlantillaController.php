<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlantillaDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Plantillas de oficio / constancia (y futuros tipos de documento). */
class PlantillaController extends Controller
{
    public function index()
    {
        return view('admin.plantillas.index', [
            'plantillas' => PlantillaDocumento::orderBy('TipoDocumento')->orderByDesc('Id')->get(),
        ]);
    }

    public function create()
    {
        return $this->formulario(null);
    }

    public function edit(int $id)
    {
        return $this->formulario(PlantillaDocumento::findOrFail($id));
    }

    public function store(Request $r)
    {
        $d = $this->validar($r) + [
            'Activo' => 1, 'CreatedAt' => now(), 'CreatedBy' => $r->user()->Matricula, 'Versionn' => 1,
        ];
        PlantillaDocumento::create($d);

        return redirect()->route('admin.plantillas.index')->with('ok', 'Plantilla creada.');
    }

    public function update(Request $r, int $id)
    {
        $p = PlantillaDocumento::findOrFail($id);
        $p->update($this->validar($r) + [
            'UpdatedAt' => now(), 'UpdatedBy' => $r->user()->Matricula, 'Versionn' => ((int) $p->Versionn) + 1,
        ]);

        return redirect()->route('admin.plantillas.index')->with('ok', 'Plantilla actualizada.');
    }

    /** Baja / reactivación lógica. Siempre debe quedar una activa por tipo de documento. */
    public function estado(Request $r, int $id)
    {
        $p = PlantillaDocumento::findOrFail($id);

        if ($p->Activo) {
            $hayOtra = PlantillaDocumento::where('TipoDocumento', $p->TipoDocumento)
                ->where('Activo', 1)->where('Id', '<>', $p->Id)->exists();
            if (!$hayOtra) {
                return back()->withErrors(['general' => "Debe existir al menos una plantilla activa de tipo {$p->TipoDocumento}."]);
            }
            $p->update(['Activo' => 0, 'DeletedAt' => now(), 'DeletedBy' => $r->user()->Matricula]);

            return back()->with('ok', 'Plantilla desactivada.');
        }

        $p->update(['Activo' => 1, 'DeletedAt' => null, 'DeletedBy' => null, 'UpdatedBy' => $r->user()->Matricula]);

        return back()->with('ok', 'Plantilla activada.');
    }

    private function formulario(?PlantillaDocumento $p)
    {
        return view('admin.plantillas.form', [
            'p'     => $p,
            'tipos' => PlantillaDocumento::distinct()->pluck('TipoDocumento')->merge(['Comision', 'Constancia'])->unique()->values(),
        ]);
    }

    private function validar(Request $r): array
    {
        $d = $r->validate([
            'TipoDocumento'     => 'required|string|max:50',
            'NombreUniversidad' => 'required|string|max:255',
            'Facultad'          => 'required|string|max:255',
            'Lugar'             => 'required|string|max:255',
            'LogoIzquierdo'     => 'nullable|string|max:500',
            'LogoDerecho'       => 'nullable|string|max:500',
            'LogoIzqArchivo'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'LogoDerArchivo'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'Introduccion'      => 'required|string|max:2000',
            'Cierre'            => 'nullable|string|max:2000',
            'Despedida'         => 'nullable|string|max:1000',
        ], [
            'TipoDocumento.required' => 'Indica el tipo de documento.',
            'Introduccion.required'  => 'La introducción es obligatoria.',
            'LogoIzqArchivo.image'   => 'El logo izquierdo debe ser una imagen.',
            'LogoDerArchivo.image'   => 'El logo derecho debe ser una imagen.',
        ]);

        // Logos subidos: se guardan en public/image/plantillas y reemplazan la URL
        foreach (['LogoIzqArchivo' => 'LogoIzquierdo', 'LogoDerArchivo' => 'LogoDerecho'] as $campo => $col) {
            if ($r->hasFile($campo)) {
                $f = $r->file($campo);
                $nombre = Str::slug($d['TipoDocumento']) . "-$col-" . time() . '.' . $f->guessExtension();
                $f->move(public_path('image/plantillas'), $nombre);
                $d[$col] = 'image/plantillas/' . $nombre;
            }
            unset($d[$campo]);
        }

        return $d;
    }
}
