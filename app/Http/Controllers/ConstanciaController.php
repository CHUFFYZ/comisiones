<?php

namespace App\Http\Controllers;

use App\Models\Constancia;
use App\Services\ComisionService;
use Illuminate\Http\Request;

class ConstanciaController extends Controller
{
    public function __construct(private ComisionService $svc) {}

    public function index(Request $r)
    {
        return view('constancias.index', [
            'comisiones' => $this->svc->visibles($r->user())->where('IdEstado', 3)->paginate(15),
        ]);
    }

    public function store(Request $r, int $id)
    {
        $c = $this->svc->visibles($r->user())->findOrFail($id);
        $this->svc->crearConstancia($r->user(), $c);

        return back()->with('ok', 'Constancia generada.');
    }

    public function pdf(Request $r, int $id)
    {
        $k = Constancia::with('plantilla')->where('Activo', 1)->findOrFail($id);
        $c = $this->svc->visibles($r->user(), null)->findOrFail($k->IdComision);

        return $this->svc->pdf($c, $k)->stream('Constancia-' . $c->NOficio . '.pdf');
    }
}