<?php

namespace App\Http\Controllers;

use App\Models\Comision;
use App\Models\EstadoComision;
use App\Services\ComisionService;
use App\Services\DocumentoService;
use Illuminate\Http\Request;

/** Panel del profesor: comisiones asignadas (solo o en grupo). */
class ProfesorController extends Controller
{
    public function __construct(private ComisionService $svc, private DocumentoService $docs) {}

    public function panel(Request $r)
    {
        $u     = $r->user();
        $todas = $this->svc->delProfesor($u)->get();

        // ¿Tiene constancia propia? (para el indicador y el filtro)
        $todas->each(fn ($c) => $c->setAttribute('mi_constancia',
            $c->constancias->contains(fn ($k) => $k->profesores->contains('Id', $u->Id))));

        $conteo = $todas->groupBy('IdEstado')->map->count();

        $lista = $todas
            ->when($r->filled('estado'), fn ($l) => $l->where('IdEstado', (int) $r->estado))
            ->when($r->constancia === 'con', fn ($l) => $l->where('mi_constancia', true))
            ->when($r->constancia === 'sin', fn ($l) => $l->where('mi_constancia', false))
            ->when($r->filled('desde'), fn ($l) => $l->filter(fn ($c) => $c->FechaInicio && $c->FechaInicio->toDateString() >= $r->desde))
            ->when($r->filled('hasta'), fn ($l) => $l->filter(fn ($c) => $c->FechaInicio && $c->FechaInicio->toDateString() <= $r->hasta))
            ->when($r->fecha === 'sin', fn ($l) => $l->filter(fn ($c) => !$c->FechaFin));

        return view('profesor.panel', [
            'comisiones' => $lista->values(),
            'estados'    => EstadoComision::where('Activo', 1)->get(),
            'conteo'     => $conteo,
            'total'      => $todas->count(),
        ]);
    }

    public function show(Request $r, int $id)
    {
        return view('profesor.show', ['c' => $this->mia($r, $id)->load(['logs.usuario', 'reporteUsuario'])]);
    }

    public function iniciar(Request $r, int $id)
    {
        $this->svc->iniciar($r->user(), $this->mia($r, $id));

        return back()->with('ok', 'Comisión marcada como En proceso.');
    }

    public function reporte(Request $r, int $id)
    {
        $r->validate(['Reporte' => 'required|file|mimes:pdf,doc,docx|max:10240'], [
            'Reporte.required' => 'Selecciona el archivo del reporte.',
            'Reporte.mimes'    => 'El reporte debe ser PDF o Word (.doc, .docx).',
            'Reporte.max'      => 'El reporte no puede pesar más de 10 MB.',
        ]);
        $this->svc->enviarReporte($r->user(), $this->mia($r, $id)->load('facultad'), $r->file('Reporte'));

        return back()->with('ok', 'Reporte enviado.');
    }

    public function descargarReporte(Request $r, int $id)
    {
        return $this->docs->descargarReporte($this->mia($r, $id));
    }

    public function refutar(Request $r, int $id)
    {
        $data = $r->validate(['Motivo' => 'required|string|min:5|max:1000'], [
            'Motivo.required' => 'Explica por qué refutas la constancia.',
            'Motivo.min'      => 'La descripción debe tener al menos 5 caracteres.',
        ]);
        $this->svc->refutarConstancia($r->user(), $this->mia($r, $id), $data['Motivo']);

        return back()->with('ok', 'Constancia refutada; se avisó a Secretaría.');
    }

    /** $formato: vista (HTML) | pdf */
    public function oficio(Request $r, int $id, string $formato)
    {
        $c = $this->mia($r, $id)->load('facultad');

        return $formato === 'pdf' ? $this->docs->respuestaPdf($c) : response($this->docs->html($c));
    }

    /** Solo las constancias en las que el profesor participa. */
    public function constancia(Request $r, int $id, int $k, string $formato)
    {
        $c = $this->mia($r, $id)->load('facultad');
        $const = $c->constancias->first(fn ($x) => (int) $x->Id === $k && $x->profesores->contains('Id', $r->user()->Id));
        abort_unless($const, 404, 'No tienes esa constancia.');

        return $formato === 'pdf' ? $this->docs->respuestaPdf($c, $const) : response($this->docs->html($c, $const));
    }

    private function mia(Request $r, int $id): Comision
    {
        return $this->svc->delProfesor($r->user())->findOrFail($id);
    }
}
