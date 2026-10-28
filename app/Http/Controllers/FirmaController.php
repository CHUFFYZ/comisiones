<?php

namespace App\Http\Controllers;

use App\Models\Comision;
use App\Models\Constancia;
use App\Services\AccesoService;
use App\Services\DocumentoService;
use App\Services\FirmaService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Panel de firma y sello: solo Director y Secretaría Académica, solo de su facultad. */
class FirmaController extends Controller
{
    public function __construct(private AccesoService $acc, private FirmaService $svc, private DocumentoService $docs) {}

    private function autorizar(Request $r)
    {
        $u = $r->user();
        abort_unless($this->acc->puedeFirmar($u), 403, 'No tienes permiso para firmar documentos.');

        return $u->load('facultad');
    }

    public function index(Request $r)
    {
        $u   = $this->autorizar($r);
        $ver = $r->query('ver') === 'completos' ? 'completos' : 'pendientes';

        $comisiones = Comision::with('profesores')->where('IdFacultad', $u->IdFacultad)->whereNull('DeletedAt')->orderByDesc('Id')->get();
        $constancias = Constancia::with(['profesores', 'comision'])->where('Activo', 1)
            ->whereHas('comision', fn ($q) => $q->where('IdFacultad', $u->IdFacultad)->whereNull('DeletedAt'))
            ->orderByDesc('Id')->get();

        $docs = $comisiones->map(fn ($c) => ['tipo' => 'c', 'doc' => $c, 'c' => $c, 'profes' => $c->profesores])
            ->concat($constancias->map(fn ($k) => ['tipo' => 'k', 'doc' => $k, 'c' => $k->comision, 'profes' => $k->profesores]))
            ->filter(fn ($x) => $ver === 'completos'
                ? ($x['doc']->firmado && $x['doc']->sellado)
                : !($x['doc']->firmado && $x['doc']->sellado))
            ->take(300)->values();

        return view('firmas.index', [
            'docs' => $docs, 'ver' => $ver, 'u' => $u,
            'puedeComoDirector' => $this->acc->puedeFirmarComoDirector($u),
            'director' => $this->svc->director($u->IdFacultad),
        ]);
    }

    public function procesar(Request $r)
    {
        $u = $this->autorizar($r);
        $d = $r->validate([
            'items'   => 'required|array|min:1',
            'items.*' => ['regex:/^[ck]:\d+$/'],
            'accion'  => 'required|in:firma,sello,ambos,retirar',
            'modo'    => 'nullable|in:propio,director',
        ], ['items.required' => 'Selecciona al menos un documento.']);

        $ok = 0;
        $errores = [];
        foreach ($d['items'] as $item) {
            [$t, $id] = explode(':', $item);
            try {
                $doc = $t === 'c' ? Comision::findOrFail($id) : Constancia::with('comision')->findOrFail($id);
                $this->svc->aplicar($u, $doc, $d['accion'], $d['modo'] ?? 'propio');
                $ok++;
            } catch (ValidationException $e) {
                $errores[] = $e->validator->errors()->first();
            }
        }

        $resp = back();
        if ($ok) $resp->with('ok', $ok . ' documento(s) actualizado(s).');
        if ($errores) $resp->withErrors(['general' => implode(' ', array_unique($errores))]);

        return $resp;
    }

    public function pdf(Request $r, string $tipo, int $id)
    {
        $u = $this->autorizar($r);
        if ($tipo === 'k') {
            $k = Constancia::with('plantilla')->findOrFail($id);
            $c = $k->comision;
        } else {
            $k = null;
            $c = Comision::findOrFail($id);
        }
        abort_unless((int) $c->IdFacultad === (int) $u->IdFacultad, 403);

        return $this->docs->respuestaPdf($c->load('facultad'), $k);
    }
}