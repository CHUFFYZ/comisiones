<?php

namespace App\Http\Controllers;

use App\Models\AsignacionPermiso;
use App\Models\PlantillaDocumento;
use App\Models\Usuario;
use App\Services\AccesoService;
use App\Services\ComisionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ModuloController extends Controller
{
    public function __construct(private AccesoService $acceso, private ComisionService $comisiones) {}

    /** "Mis módulos": solo los que el usuario puede ver (+ "Mis comisiones" si es profesor) */
    public function index(Request $request)
    {
        $u = $request->user();

        return view('modulos.index', [
            'modulos'    => $this->acceso->modulosDe($u),
            'esProfesor' => $u->Rol === 'Profesor' || $u->comisiones()->exists(),
        ]);
    }

    /** Contenedor de un módulo (el middleware ya validó el acceso) */
    public function show(Request $request)
    {
        $modulo = $request->attributes->get('modulo');
        $user   = $request->user();

        return view('modulos.show', [
            'modulo'   => $modulo,
            'permiso'  => $this->acceso->letrasEn($user, $modulo->Clave),
            'opciones' => $this->acceso->opcionesDe($user, $modulo->Clave),
            // Tablero de comisiones solo para SisComisiones; Administración tiene el suyo
            'stats'      => $modulo->Clave === 'MSC' ? $this->comisiones->estadisticas($this->actor($request)) : null,
            'statsAdmin' => $modulo->Clave === 'MA' && $this->acceso->puedeGestionarUsuarios($user) ? $this->statsAdmin($user) : null,
            'puedeReporte' => match ($modulo->Clave) {
                'MSC'   => $this->acceso->puede($user, 'MSC', 'leer'),
                'MA'    => $this->acceso->puedeGestionarUsuarios($user),
                default => false,
            },
        ]);
    }

    /** Indicadores de usuarios dentro del alcance de quien consulta. */
    private function statsAdmin(Usuario $u): array
    {
        $us  = $this->acceso->usuariosGestionables($u)->with('facultad')->get();
        $act = $us->where('Activo', 1);

        $meses = collect(range(5, 0))->map(function ($i) use ($us) {
            $m = now()->subMonths($i);
            return [
                'label' => $m->locale('es')->translatedFormat('M y'),
                'n'     => $us->filter(fn ($x) => $x->CreatedAt && Carbon::parse($x->CreatedAt)->format('Y-m') === $m->format('Y-m'))->count(),
            ];
        });

        $temp = AsignacionPermiso::whereIn('IdUsuario', $us->pluck('Id'))->whereNull('DeletedAt')
            ->whereNotNull('IdTipoPermisoTemp')->get()->filter(fn ($a) => $a->tempVigente())->count();

        return [
            'total'        => $us->count(),
            'activos'      => $act->count(),
            'bajas'        => $us->where('Activo', 0)->count(),
            'enLinea'      => $act->filter(fn ($x) => $x->UltimaConexion && $x->UltimaConexion->gt(now()->subMinutes(2)))->count(),
            'tempVigentes' => $temp,
            'plantillas'   => PlantillaDocumento::where('Activo', 1)->count(),
            'porRol'       => $act->groupBy(fn ($x) => $x->rol_nombre ?? 'Sin rol')->map->count()->sortDesc()->all(),
            'porFac'       => $act->groupBy(fn ($x) => $x->facultad->Clave ?? 'Sin facultad')->map->count()->sortDesc()->all(),
            'altas'        => $meses->all(),
        ];
    }
}
