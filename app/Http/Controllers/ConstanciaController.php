<?php

namespace App\Http\Controllers;

use App\Models\Constancia;
use App\Services\ComisionService;
use App\Services\DocumentoService;
use Illuminate\Http\Request;

/**
 * store / update / destroy reciben el Id de la COMISIÓN;
 * pdf / vista reciben el Id de la CONSTANCIA.
 */
class ConstanciaController extends Controller
{
    public function __construct(private ComisionService $svc, private DocumentoService $docs) {}

    public function store(Request $r, int $id)
    {
        $d = $r->validate([
            'modo'         => 'required|in:grupo,individual',
            'profesores'   => 'required|array|min:1',
            'profesores.*' => 'integer',
        ], ['profesores.required' => 'Selecciona al menos un profesor.']);

        $c = $this->svc->visibles($this->actor($r))->findOrFail($id);
        $n = $this->svc->crearConstancias($this->actor($r), $c, $d['profesores'], $d['modo'])->count();

        return back()->with('ok', $n === 1 ? 'Constancia generada.' : "$n constancias generadas.");
    }

    public function update(Request $r, int $id)
    {
        $data = $r->validate([
            'constancias'   => 'required|array|min:1',
            'constancias.*' => 'integer',
            'Contexto'      => 'required|string|min:10|max:1000',
            'Cierre'        => 'nullable|string|max:1000',
        ], [
            'constancias.required' => 'Selecciona al menos una constancia.',
            'Contexto.required'    => 'El contexto de la constancia es obligatorio.',
            'Contexto.min'         => 'El contexto debe tener al menos 10 caracteres.',
        ]);
        $c = $this->svc->visibles($this->actor($r))->findOrFail($id);
        $n = $this->svc->actualizarConstancias($this->actor($r), $c, $data['constancias'], $data['Contexto'], $data['Cierre'] ?? null);

        return back()->with('ok', $n === 1 ? 'Constancia actualizada.' : "$n constancias actualizadas.");
    }

    public function destroy(Request $r, int $id)
    {
        $data = $r->validate(['constancias' => 'required|array|min:1', 'constancias.*' => 'integer'],
            ['constancias.required' => 'Selecciona al menos una constancia.']);
        $c = $this->svc->visibles($this->actor($r))->findOrFail($id);
        $n = $this->svc->eliminarConstancias($this->actor($r), $c, $data['constancias']);

        return back()->with('ok', $n === 1 ? 'Constancia eliminada.' : "$n constancias eliminadas.");
    }

    public function pdf(Request $r, int $id)
    {
        [$c, $k] = $this->buscar($r, $id);

        return $this->docs->respuestaPdf($c->load('facultad'), $k);
    }

    public function vista(Request $r, int $id)
    {
        [$c, $k] = $this->buscar($r, $id);

        return response($this->docs->html($c, $k));
    }

    private function buscar(Request $r, int $id): array
    {
        $k = Constancia::with('plantilla')->where('Activo', 1)->findOrFail($id);
        $c = $this->svc->visibles($this->actor($r), null)->findOrFail($k->IdComision);

        return [$c, $k];
    }
}
