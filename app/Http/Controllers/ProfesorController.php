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
        $todas = $this->svc->delProfesor($r->user())->get();

        $conteo = $todas->groupBy('IdEstado')->map->count();
        $lista  = $r->filled('estado') ? $todas->where('IdEstado', (int) $r->estado) : $todas;

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

    /** $doc: oficio | constancia · $formato: vista (HTML) | pdf */
    public function documento(Request $r, int $id, string $doc, string $formato)
    {
        $c = $this->mia($r, $id)->load('facultad');
        $k = $doc === 'constancia' ? $c->constancia : null;
        abort_if($doc === 'constancia' && !$k, 404, 'Aún no hay constancia.');

        return $formato === 'pdf'
            ? $this->docs->respuestaPdf($c, $k)
            : response($this->docs->html($c, $k));
    }

    private function mia(Request $r, int $id): Comision
    {
        return $this->svc->delProfesor($r->user())->findOrFail($id);
    }
}
