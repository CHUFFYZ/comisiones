<?php

namespace App\Http\Controllers;

use App\Models\EstadoComision;
use App\Models\PlantillaDocumento;
use App\Services\ComisionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ComisionController extends Controller
{
    public function __construct(private ComisionService $svc) {}

    public function index(Request $r)    { return $this->listar($r, false); }
    public function anuladas(Request $r) { return $this->listar($r, true); }

    private function listar(Request $r, bool $anuladas)
    {
        $u = $r->user();

        $q = $this->svc->visibles($u, $anuladas)
            ->when($r->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('NOficio', 'like', "%{$r->q}%")->orWhere('Asunto', 'like', "%{$r->q}%")))
            ->when($r->filled('estado'), fn ($q) => $q->where('IdEstado', $r->estado))
            ->when($r->filled('tipo'), fn ($q) => $q->where('IdTipoComision', $r->tipo));

        return view('comisiones.index', [
            'comisiones' => $q->paginate(15)->withQueryString(),
            'estados'    => EstadoComision::where('Activo', 1)->get(),
            'tipos'      => $this->svc->tiposPermitidos($u),
            'anuladas'   => $anuladas,
        ]);
    }

    public function create(Request $r)
    {
        $u = $r->user();

        return view('comisiones.create', [
            'tipos'      => $this->svc->tiposPermitidos($u),
            'plantillas' => PlantillaDocumento::where('TipoDocumento', 'Comision')->where('Activo', 1)->get(),
            'profesores' => $this->svc->profesores($u),
        ]);
    }

    public function store(Request $r)
    {
        $u = $r->user();

        $data = $r->validate([
            'IdTipoComision' => ['required', Rule::in($this->svc->tiposPermitidos($u)->pluck('Id'))],
            'IdPlantilla'    => ['required', Rule::exists('plantilla_documento', 'Id')->where('TipoDocumento', 'Comision')],
            'Asunto'         => 'required|string|max:255',
            'FechaInicio'    => 'required|date',
            'FechaFin'       => 'nullable|date|after_or_equal:FechaInicio',
            'profesores'     => 'required|array|min:1',
            'profesores.*'   => [Rule::in($this->svc->profesores($u)->pluck('Id'))],
        ], [
            'profesores.required' => 'Selecciona al menos un profesor.',
            'FechaFin.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
        ]);

        $c = $this->svc->crear($u, $data);

        return redirect()->route('comisiones.index')->with('ok', "Comisión creada: {$c->NOficio}");
    }

    public function estado(Request $r, int $id)
    {
        $u = $r->user();
        $c = $this->svc->visibles($u)->findOrFail($id);

        abort_if((int) $c->IdEstado >= 3, 422, 'La comisión ya está finalizada.');

        $c->update(['IdEstado' => $c->IdEstado + 1, 'UpdatedBy' => $u->Matricula]);

        return back()->with('ok', 'Estado actualizado.');
    }

    public function anular(Request $r, int $id)
    {
        $u = $r->user();
        $c = $this->svc->visibles($u)->findOrFail($id);

        $c->update(['Activo' => 0, 'DeletedAt' => now(), 'DeletedBy' => $u->Matricula]);

        return back()->with('ok', 'Comisión anulada.');
    }

    public function pdf(Request $r, int $id)
    {
        $c = $this->svc->visibles($r->user(), null)->findOrFail($id);

        return $this->svc->pdf($c)->stream($c->NOficio . '.pdf');
    }
}