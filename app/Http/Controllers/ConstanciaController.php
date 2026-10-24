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
        $c = $this->svc->visibles($r->user())->findOrFail($id);
        $this->svc->crearConstancia($r->user(), $c);

        return back()->with('ok', 'Constancia generada.');
    }

    public function update(Request $r, int $id)
    {
        $data = $r->validate(['Contexto' => 'required|string|min:10|max:1000'], [
            'Contexto.required' => 'El contexto de la constancia es obligatorio.',
            'Contexto.min'      => 'El contexto debe tener al menos 10 caracteres.',
        ]);
        $c = $this->svc->visibles($r->user())->findOrFail($id);
        $this->svc->actualizarConstancia($r->user(), $c, $data['Contexto']);

        return back()->with('ok', 'Constancia actualizada.');
    }

    public function destroy(Request $r, int $id)
    {
        $c = $this->svc->visibles($r->user())->findOrFail($id);
        $this->svc->eliminarConstancia($r->user(), $c);

        return back()->with('ok', 'Constancia eliminada.');
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
        $c = $this->svc->visibles($r->user(), null)->findOrFail($k->IdComision);

        return [$c, $k];
    }
}
