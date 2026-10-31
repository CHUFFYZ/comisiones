<?php

namespace App\Http\Controllers;

use App\Models\Comision;
use App\Models\Constancia;
use App\Models\Facultad;
use App\Services\AccesoService;
use App\Services\DocumentoService;
use App\Services\FirmaService;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Panel de firma y sello: Director y Sec. Académica (su facultad) · Superusuario (todas). */
class FirmaController extends Controller
{
    public function __construct(
        private AccesoService $acc, private FirmaService $svc,
        private DocumentoService $docs, private NotificacionService $notif,
    ) {}

    private function autorizar(Request $r)
    {
        $u = $r->user();
        abort_unless($this->acc->puedeFirmar($u), 403, 'No tienes permiso para firmar documentos.');

        return $u->load('facultad');
    }

    public function index(Request $r)
    {
        $u    = $this->autorizar($r);
        $esSU = $this->acc->esSuperUsuario($u);
        $ver  = $r->query('ver') === 'completos' ? 'completos' : 'pendientes';
        $quien = in_array($r->query('quien'), ['director', 'secretaria'], true) ? $r->query('quien') : 'todos';
        $fac  = $esSU ? ($r->filled('facultad') ? (int) $r->facultad : null) : (int) $u->IdFacultad;

        $comisiones = Comision::with(['profesores', 'facultad', 'firmaUsuario', 'selloUsuario'])->whereNull('DeletedAt')
            ->when($fac, fn ($q) => $q->where('IdFacultad', $fac))->orderByDesc('Id')->get();
        $constancias = Constancia::with(['profesores', 'comision.facultad', 'firmaUsuario', 'selloUsuario'])->where('Activo', 1)
            ->whereHas('comision', fn ($q) => $q->whereNull('DeletedAt')->when($fac, fn ($w) => $w->where('IdFacultad', $fac)))
            ->orderByDesc('Id')->get();

        $docs = $comisiones->map(fn ($c) => ['tipo' => 'c', 'doc' => $c, 'c' => $c, 'profes' => $c->profesores])
            ->concat($constancias->map(fn ($k) => ['tipo' => 'k', 'doc' => $k, 'c' => $k->comision, 'profes' => $k->profesores]))
            ->filter(fn ($x) => $ver === 'completos'
                ? ($x['doc']->firmado && $x['doc']->sellado)
                : !($x['doc']->firmado && $x['doc']->sellado))
            // Filtro por quién firmó: Director (firma del Director) o Sec. Académica (firma propia)
            ->filter(fn ($x) => match ($quien) {
                'director'   => $x['doc']->FirmanteModo === 'director',
                'secretaria' => $x['doc']->FirmanteModo === 'propio',
                default      => true,
            })
            ->take(300)->values();

        return view('firmas.index', [
            'docs' => $docs, 'ver' => $ver, 'quien' => $quien, 'u' => $u, 'esSU' => $esSU, 'fac' => $fac,
            'facultades' => $esSU ? Facultad::where('Activo', 1)->orderBy('Clave')->get() : collect(),
            'puedeComoDirector' => !$esSU && $this->acc->puedeFirmarComoDirector($u),
            'director' => $this->svc->director($u->IdFacultad),
        ]);
    }

    public function procesar(Request $r)
    {
        // Debe terminar aunque el usuario cierre la ventana
        ignore_user_abort(true);
        @set_time_limit(0);

        $u = $this->autorizar($r);
        $d = $r->validate([
            'items'   => 'required|array|min:1',
            'items.*' => ['regex:/^[ck]:\d+$/'],
            'accion'  => 'required|in:firma,sello,ambos,retirar,retirar_firma,retirar_sello',
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

        $accionTxt = str_starts_with($d['accion'], 'retirar') ? 'retirados' : 'aplicados';
        $this->notif->proceso($u->Id, 'Proceso de firmas terminado',
            "$ok documento(s) con firma/sello $accionTxt" . ($errores ? '; ' . count($errores) . ' con error.' : '.'),
            route('firmas.index'));

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
        abort_unless($this->acc->esSuperUsuario($u) || (int) $c->IdFacultad === (int) $u->IdFacultad, 403);

        return $this->docs->respuestaPdf($c->load('facultad'), $k);
    }
}
