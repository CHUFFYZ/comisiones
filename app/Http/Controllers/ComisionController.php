<?php

namespace App\Http\Controllers;

use App\Models\Comision;
use App\Models\EstadoComision;
use App\Models\PlantillaDocumento;
use App\Models\Usuario;
use App\Services\ComisionService;
use App\Services\DocumentoService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ComisionController extends Controller
{
    public function __construct(private ComisionService $svc, private DocumentoService $docs) {}

    public function index(Request $r)    { return $this->listar($r, false); }
    public function anuladas(Request $r) { return $this->listar($r, true); }

    private function listar(Request $r, bool $anuladas)
    {
        $u = $this->actor($r);

        $q = $this->svc->visibles($u, $anuladas)
            ->when($r->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('NOficio', 'like', "%{$r->q}%")->orWhere('Asunto', 'like', "%{$r->q}%")
                ->orWhereHas('profesores', fn ($p) => $p->where(fn ($n) => $n
                    ->where('Nombre', 'like', "%{$r->q}%")->orWhere('Apellido', 'like', "%{$r->q}%")
                    ->orWhereRaw("CONCAT(Nombre, ' ', Apellido) like ?", ["%{$r->q}%"])))))
            ->when($r->filled('estado'), fn ($q) => $q->where('IdEstado', $r->estado))
            ->when($r->filled('tipo'), fn ($q) => $q->where('IdTipoComision', $r->tipo));

        return view('comisiones.index', [
            'comisiones' => $q->paginate(15)->withQueryString(),
            'estados'    => EstadoComision::where('Activo', 1)->get(),
            'tipos'      => $this->svc->tiposPermitidos($u),
            'anuladas'   => $anuladas,
            'totalAlcance' => $this->svc->visibles($u, $anuladas)->count(),
        ]);
    }

    /** Detalle completo de la comisión (sin necesidad de abrir el PDF). */
    public function show(Request $r, int $id)
    {
        $c = $this->buscar($r, $id)->load(['facultad', 'logs.usuario', 'reporteUsuario']);

        return view('comisiones.show', ['c' => $c]);
    }

    public function create(Request $r)
    {
        return $this->formulario($r, null);
    }

    public function edit(Request $r, int $id)
    {
        $c = $this->buscar($r, $id, false);

        return $this->formulario($r, $c);
    }

    private function formulario(Request $r, ?Comision $c)
    {
        $u = $this->actor($r);

        return view('comisiones.form', [
            'comision'   => $c,
            'tipos'      => $this->svc->tiposPermitidos($u),
            'plantillas' => PlantillaDocumento::where('TipoDocumento', 'Comision')->where('Activo', 1)->get(),
            'profesores' => $this->svc->profesores($u),
        ]);
    }

    public function store(Request $r)
    {
        $c = $this->svc->crear($this->actor($r), $this->validar($r));

        return redirect()->route('comisiones.show', $c->Id)->with('ok', "Comisión creada: {$c->NOficio}");
    }

    public function update(Request $r, int $id)
    {
        $c = $this->buscar($r, $id, false);
        $c = $this->svc->actualizar($this->actor($r), $c, $this->validar($r, true));

        return redirect()->route('comisiones.show', $c->Id)->with('ok', 'Comisión actualizada.');
    }

    /** Vista previa en vivo del oficio (HTML), sin guardar. */
    public function previa(Request $r)
    {
        $existente = $r->filled('_id') ? $this->buscar($r, (int) $r->_id, false) : null;

        return response($this->svc->previa($this->actor($r), $r->all(), $existente));
    }

    private function validar(Request $r, bool $edicion = false): array
    {
        $u = $this->actor($r);

        return $r->validate([
            'IdTipoComision' => ['required', Rule::in($this->svc->tiposPermitidos($u)->pluck('Id'))],
            'IdPlantilla'    => ['required', Rule::exists('plantilla_documento', 'Id')->where('TipoDocumento', 'Comision')->where('Activo', 1)],
            'Asunto'         => ['required', 'string', 'min:5', 'max:255'],
            'Contexto'       => ['required', 'string', 'min:10', 'max:1000'],
            'Cierre'         => ['nullable', 'string', 'max:1000'],
            'FechaInicio'    => ['required', 'date'],
            'FechaFin'       => ['nullable', 'date', 'after_or_equal:FechaInicio'],
            'profesores'     => ['required', 'array', 'min:1'],
            'profesores.*'   => [Rule::in($this->svc->profesores($u)->pluck('Id'))],
        ], [
            'IdTipoComision.in'          => 'Ese tipo de comisión no está disponible para ti.',
            'Asunto.min'                 => 'El asunto debe tener al menos 5 caracteres.',
            'Contexto.required'          => 'Describe en qué consiste la comisión (contexto).',
            'Contexto.min'               => 'El contexto debe tener al menos 10 caracteres.',
            'FechaFin.after_or_equal'    => 'La fecha final no puede ser anterior a la inicial.',
            'profesores.required'        => 'Selecciona al menos un profesor.',
            'profesores.*.in'            => 'Hay un profesor que no pertenece a tu facultad.',
        ]);
    }

    /* ───────── estados ───────── */

    public function avanzar(Request $r, int $id)
    {
        $this->svc->avanzar($this->actor($r), $this->buscar($r, $id, false));

        return back()->with('ok', 'Estado actualizado.');
    }

    public function retroceder(Request $r, int $id)
    {
        $this->svc->retroceder($this->actor($r), $this->buscar($r, $id, false));

        return back()->with('ok', 'Se regresó al estado anterior.');
    }

    public function rechazarReporte(Request $r, int $id)
    {
        $data = $r->validate(['Motivo' => 'required|string|min:5|max:1000'], [
            'Motivo.required' => 'Escribe qué debe corregir el profesor.',
            'Motivo.min'      => 'La descripción debe tener al menos 5 caracteres.',
        ]);
        $this->svc->rechazarReporte($this->actor($r), $this->buscar($r, $id, false), $data['Motivo']);

        return back()->with('ok', 'Reporte rechazado; se avisó a los profesores.');
    }

    public function anular(Request $r, int $id)
    {
        $this->svc->anular($this->actor($r), $this->buscar($r, $id, false));

        return redirect()->route('comisiones.index')->with('ok', 'Comisión anulada.');
    }

    /** Deshace una anulación. */
    public function restaurar(Request $r, int $id)
    {
        $c = $this->svc->visibles($this->actor($r), true)->findOrFail($id);
        $this->svc->restaurar($this->actor($r), $c);

        return back()->with('ok', 'Anulación revertida.');
    }

    /* ───────── documentos ───────── */

    public function pdf(Request $r, int $id)
    {
        return $this->docs->respuestaPdf($this->buscar($r, $id)->load('facultad'));
    }

    public function vista(Request $r, int $id)
    {
        return response($this->docs->html($this->buscar($r, $id)));
    }

    public function reporte(Request $r, int $id)
    {
        return $this->docs->descargarReporte($this->buscar($r, $id));
    }

    /** $anuladas: false = solo vigentes (para modificar), null = ambas (para consultar) */
    private function buscar(Request $r, int $id, ?bool $anuladas = null): Comision
    {
        return $this->svc->visibles($this->actor($r), $anuladas)->findOrFail($id);
    }
}
