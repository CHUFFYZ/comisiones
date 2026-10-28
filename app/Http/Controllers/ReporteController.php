<?php

namespace App\Http\Controllers;

use App\Models\EstadoComision;
use App\Models\Facultad;
use App\Models\Rol;
use App\Models\TipoComision;
use App\Services\AccesoService;
use App\Services\ComisionService;
use Illuminate\Http\Request;

/**
 * Reportes imprimibles. El alcance sale de los mismos servicios que las pantallas:
 * secretaria = su área · sec. académica = todas las áreas · admin = su facultad · SU = global.
 */
class ReporteController extends Controller
{
    private const TIPOS = [
        'MSC' => [
            'comisiones'   => 'Listado de comisiones',
            'por-profesor' => 'Comisiones por profesor',
            'resumen'      => 'Resumen por estado y tipo',
        ],
        'MA' => [
            'usuarios'         => 'Listado de usuarios',
            'resumen-usuarios' => 'Resumen de usuarios por rol y facultad',
        ],
    ];

    public function __construct(private AccesoService $acc, private ComisionService $svc) {}

    public function index(Request $r, string $modulo)
    {
        $this->autorizar($r, $modulo);
        $u = $this->quien($r, $modulo);

        return view('reportes.index', [
            'modulo'        => $r->attributes->get('modulo'),
            'clave'         => $modulo,
            'tipos'         => self::TIPOS[$modulo],
            'estados'       => EstadoComision::where('Activo', 1)->orderBy('Id')->get(),
            'tiposComision' => $modulo === 'MSC' ? $this->svc->tiposPermitidos($u) : collect(),
            'roles'         => $modulo === 'MA'
                ? Rol::where('Activo', 1)->whereIn('Clave', $this->acc->rolesCreables($u))->orderBy('Orden')->get() : collect(),
            'facultades'    => $this->acc->esSuperUsuario($u) ? Facultad::where('Activo', 1)->get() : collect(),
            'alcance'       => $this->alcance($r, $modulo),
        ]);
    }

    public function imprimir(Request $r, string $modulo)
    {
        $this->autorizar($r, $modulo);
        $r->validate(['desde' => 'nullable|date', 'hasta' => 'nullable|date|after_or_equal:desde']);

        $rep = $r->query('reporte', array_key_first(self::TIPOS[$modulo]));
        abort_unless(isset(self::TIPOS[$modulo][$rep]), 404);

        $datos = match ($rep) {
            'comisiones'       => $this->comisiones($r),
            'por-profesor'     => $this->porProfesor($r),
            'resumen'          => $this->resumen($r),
            'usuarios'         => $this->usuarios($r),
            'resumen-usuarios' => $this->resumenUsuarios($r),
        };

        $u = $r->user();

        return view('reportes.imprimir', $datos + [
            'titulo'   => self::TIPOS[$modulo][$rep],
            'alcance'  => $this->alcance($r, $modulo),
            'filtros'  => $this->textoFiltros($r),
            'por'      => $u->nombre_con_grado . ' (' . $u->rol_nombre . ')',
            'entidad'  => $this->acc->esSuperUsuario($u) ? 'Reporte global' : ($u->facultad->Nombre ?? ''),
        ]);
    }

    /* ───────── comisiones ───────── */

    private function baseComisiones(Request $r)
    {
        $u    = $this->actor($r);
        $anu  = $r->query('anuladas', '0');
        $anu  = !$this->acc->puede($r->user(), 'MSC', 'eliminar') ? false : match ($anu) { '1' => true, '2' => null, default => false };

        return $this->svc->visibles($u, $anu)->with('facultad')
            ->when($r->filled('estado'), fn ($q) => $q->where('IdEstado', $r->estado))
            ->when($r->filled('tipo'), fn ($q) => $q->where('IdTipoComision', $r->tipo))
            ->when($r->filled('desde'), fn ($q) => $q->whereDate('FechaInicio', '>=', $r->desde))
            ->when($r->filled('hasta'), fn ($q) => $q->whereDate('FechaInicio', '<=', $r->hasta))
            ->when($r->filled('facultad') && $this->acc->esSuperUsuario($u), fn ($q) => $q->where('IdFacultad', $r->facultad));
    }

    private function comisiones(Request $r): array
    {
        $esSU = $this->acc->esSuperUsuario($this->actor($r));
        $cols = ['Oficio', 'Asunto', 'Tipo', 'Profesores', 'Fechas', 'Estado', 'Constancia'];
        if ($esSU) array_unshift($cols, 'Facultad');

        $filas = $this->baseComisiones($r)->get()->map(function ($c) use ($esSU) {
            $f = [
                $c->NOficio, $c->Asunto, $c->tipo->Nombre ?? '—',
                $c->profesores->pluck('nombre_corto')->join(', '),
                $c->FechaInicio->format('d/m/y') . ($c->FechaFin ? ' – ' . $c->FechaFin->format('d/m/y') : ''),
                $c->estado->Nombre ?? '—', ['completa' => 'Sí', 'parcial' => 'Parcial', 'ninguna' => 'No'][$c->estado_constancias],
            ];
            if ($esSU) array_unshift($f, $c->facultad->Clave ?? '—');
            return $f;
        })->all();

        return ['secciones' => [['titulo' => null, 'columnas' => $cols, 'filas' => $filas]]];
    }

    private function porProfesor(Request $r): array
    {
        $esSU    = $this->acc->esSuperUsuario($this->actor($r));
        $estados = EstadoComision::where('Activo', 1)->orderBy('Id')->get();

        $acum = [];
        foreach ($this->baseComisiones($r)->get() as $c) {
            foreach ($c->profesores as $p) {
                $acum[$p->Id]['p'] = $p;
                $acum[$p->Id]['n'][$c->IdEstado] = ($acum[$p->Id]['n'][$c->IdEstado] ?? 0) + 1;
            }
        }

        $cols = array_merge(['Profesor'], $esSU ? ['Facultad'] : [], ['Total'], $estados->pluck('Nombre')->all());
        $filas = collect($acum)->sortBy(fn ($a) => $a['p']->Apellido . $a['p']->Nombre)->map(function ($a) use ($estados, $esSU) {
            $n = $a['n'];
            return array_merge(
                [$a['p']->nombre_con_grado],
                $esSU ? [$a['p']->facultad->Clave ?? '—'] : [],
                [array_sum($n)],
                $estados->map(fn ($e) => $n[$e->Id] ?? 0)->all()
            );
        })->values()->all();

        return ['secciones' => [['titulo' => null, 'columnas' => $cols, 'filas' => $filas]]];
    }

    private function resumen(Request $r): array
    {
        $com     = $this->baseComisiones($r)->get();
        $estados = EstadoComision::where('Activo', 1)->orderBy('Id')->get();

        $porEstado = $estados->map(fn ($e) => [$e->Nombre, $com->where('IdEstado', $e->Id)->count()])->all();
        $porEstado[] = ['Total', $com->count()];

        $porTipo = $com->groupBy(fn ($c) => $c->tipo->Nombre ?? '—')->map(fn ($g, $k) => [$k, $g->count()])->values()->all();

        $fin = $com->where('IdEstado', 3);
        $indic = [
            ['Finalizadas con constancias completas', $fin->filter(fn ($c) => $c->estado_constancias === 'completa')->count()],
            ['Finalizadas con constancias parciales', $fin->filter(fn ($c) => $c->estado_constancias === 'parcial')->count()],
            ['Finalizadas sin constancia', $fin->filter(fn ($c) => $c->estado_constancias === 'ninguna')->count()],
            ['Vencidas sin finalizar', $com->filter(fn ($c) => $c->IdEstado < 3 && $c->FechaFin && $c->FechaFin->lt(today()))->count()],
        ];

        return ['secciones' => [
            ['titulo' => 'Por estado',  'columnas' => ['Estado', 'Comisiones'], 'filas' => $porEstado],
            ['titulo' => 'Por tipo',    'columnas' => ['Tipo', 'Comisiones'],   'filas' => $porTipo],
            ['titulo' => 'Indicadores', 'columnas' => ['Indicador', 'Total'],   'filas' => $indic],
        ]];
    }

    /* ───────── usuarios ───────── */

    private function baseUsuarios(Request $r)
    {
        $u = $r->user();

        return $this->acc->usuariosGestionables($u)->with('facultad')
            ->when($r->filled('rol'), fn ($q) => $q->where('IdRol', $r->rol))
            ->when(in_array($r->query('estado_usuario'), ['0', '1'], true), fn ($q) => $q->where('Activo', (int) $r->query('estado_usuario')))
            ->when($r->filled('facultad') && $this->acc->esSuperUsuario($u), fn ($q) => $q->where('IdFacultad', $r->facultad))
            ->orderBy('IdFacultad')->orderBy('Apellido')->orderBy('Nombre');
    }

    private function usuarios(Request $r): array
    {
        $filas = $this->baseUsuarios($r)->get()->map(fn ($x) => [
            $x->Matricula, $x->nombre_con_grado, $x->rol_nombre ?? '—', $x->facultad->Clave ?? '—', $x->Correo,
            $x->Activo ? 'Activo' : 'Baja', $x->UltimaConexion?->format('d/m/y H:i') ?? '—',
        ])->all();

        return ['secciones' => [[
            'titulo' => null,
            'columnas' => ['Matrícula', 'Nombre', 'Rol', 'Facultad', 'Correo', 'Estado', 'Últ. conexión'],
            'filas' => $filas,
        ]]];
    }

    private function resumenUsuarios(Request $r): array
    {
        $us  = $this->baseUsuarios($r)->get();
        $agr = fn ($clave) => $us->groupBy($clave)->map(fn ($g, $k) => [
            $k, $g->where('Activo', 1)->count(), $g->where('Activo', 0)->count(), $g->count(),
        ])->values()->all();

        return ['secciones' => [
            ['titulo' => 'Por rol',      'columnas' => ['Rol', 'Activos', 'Bajas', 'Total'],      'filas' => $agr(fn ($x) => $x->rol_nombre ?? 'Sin rol')],
            ['titulo' => 'Por facultad', 'columnas' => ['Facultad', 'Activos', 'Bajas', 'Total'], 'filas' => $agr(fn ($x) => $x->facultad->Clave ?? 'Sin facultad')],
        ]];
    }

    /* ───────── internos ───────── */

    private function autorizar(Request $r, string $modulo): void
    {
        abort_unless(isset(self::TIPOS[$modulo]), 404);
        if ($modulo === 'MA') {
            abort_unless($this->acc->puedeGestionarUsuarios($r->user()), 403, 'No tienes permiso para reportes de usuarios.');
        }
    }

    private function quien(Request $r, string $modulo)
    {
        return $modulo === 'MSC' ? $this->actor($r) : $r->user();
    }

    private function alcance(Request $r, string $modulo): string
    {
        $u = $this->quien($r, $modulo);
        $f = $u->facultad->Clave ?? '';

        if ($this->acc->esSuperUsuario($u)) return 'Global · todas las facultades';
        if ($modulo === 'MA') {
            return $u->Rol === 'Administrador' ? "Facultad completa ($f)" : "Secretarias de la facultad ($f)";
        }

        return match ($u->Rol) {
            'Administrador'       => "Facultad completa ($f) · modo consulta",
            'SecretariaAcademica' => "Todas las áreas de secretarias ($f)",
            default               => "Mi área ($f): " . $this->svc->tiposPermitidos($u)->pluck('Nombre')->join(', '),
        };
    }

    private function textoFiltros(Request $r): string
    {
        $t = [];
        if ($r->filled('estado'))   $t[] = 'Estado: ' . (EstadoComision::find($r->estado)->Nombre ?? '—');
        if ($r->filled('tipo'))     $t[] = 'Tipo: ' . (TipoComision::find($r->tipo)->Nombre ?? '—');
        if ($r->filled('desde'))    $t[] = 'Desde: ' . $r->desde;
        if ($r->filled('hasta'))    $t[] = 'Hasta: ' . $r->hasta;
        if ($r->filled('facultad')) $t[] = 'Facultad: ' . (Facultad::find($r->facultad)->Clave ?? '—');
        if ($r->filled('rol'))      $t[] = 'Rol: ' . (Rol::find($r->rol)->Nombre ?? '—');
        if (in_array($r->query('estado_usuario'), ['0', '1'], true)) $t[] = $r->query('estado_usuario') ? 'Solo activos' : 'Solo bajas';
        if ($r->query('anuladas') === '1') $t[] = 'Solo anuladas';
        if ($r->query('anuladas') === '2') $t[] = 'Vigentes y anuladas';

        return $t ? implode(' · ', $t) : 'Sin filtros';
    }
}
