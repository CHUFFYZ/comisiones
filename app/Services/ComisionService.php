<?php

namespace App\Services;

use App\Models\Comision;
use App\Models\ComisionLog;
use App\Models\Constancia;
use App\Models\EstadoComision;
use App\Models\PlantillaDocumento;
use App\Models\TipoComision;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ComisionService
{
    private const MESES = ['ENE','FEB','MAR','ABR','MAY','JUN','JUL','AGO','SEP','OCT','NOV','DIC'];

    public function __construct(
        private AccesoService $acceso,
        private DocumentoService $docs,
        private NotificacionService $notif,
        private FirmaService $firmas,
    ) {}

    /* ───────── alcance / consultas ───────── */

    /** Secretaría Académica y superusuario ven todos los tipos. */
    public function veTodo(Usuario $u): bool
    {
        return in_array($u->Rol, ['SecretariaAcademica', 'Administrador'], true) || $this->acceso->esSuperUsuario($u);
    }

    public function tiposPermitidos(Usuario $u): Collection
    {
        return TipoComision::where('Activo', 1)
            ->when(!$this->veTodo($u), fn ($q) => $q->whereIn('Id', $u->tiposComision()->pluck('tipo_comision.Id')))
            ->orderBy('Nombre')->get();
    }

    public function profesores(Usuario $u): Collection
    {
        return Usuario::deRol('Profesor')->where('Activo', 1)
            ->where('IdFacultad', $u->IdFacultad)
            ->orderBy('Apellido')->orderBy('Nombre')->get();
    }

    private function alcance(Usuario $u): Builder
    {
        $q = Comision::query();

        if (!$this->acceso->esSuperUsuario($u)) {
            $q->where('IdFacultad', $u->IdFacultad);
        }
        if (!$this->veTodo($u)) {
            $q->whereIn('IdTipoComision', $u->tiposComision()->pluck('tipo_comision.Id'));
        }

        return $q;
    }

    /** $anuladas: false = vigentes, true = anuladas, null = ambas */
    public function visibles(Usuario $u, ?bool $anuladas = false): Builder
    {
        $q = $this->alcance($u)->with(['tipo', 'estado', 'profesores', 'constancias.profesores']);

        if ($anuladas === true)  $q->whereNotNull('DeletedAt');
        if ($anuladas === false) $q->whereNull('DeletedAt');

        return $q->orderByDesc('Id');
    }

    /** Comisiones asignadas al profesor (solo o en grupo). */
    public function delProfesor(Usuario $u): Builder
    {
        return Comision::query()->whereNull('DeletedAt')
            ->whereHas('profesores', fn ($q) => $q->where('usuario.Id', $u->Id))
            ->with(['tipo', 'estado', 'profesores', 'constancias.profesores', 'facultad'])
            ->orderByDesc('Id');
    }

    /* ───────── crear / modificar ───────── */

    public function crear(Usuario $u, array $d): Comision
    {
        $snap = $this->snapshots($u, $u->IdFacultad);

        $c = DB::transaction(function () use ($u, $d, $snap) {
            $c = Comision::create([
                'IdTipoComision' => $d['IdTipoComision'],
                'IdPlantilla'    => $d['IdPlantilla'],
                'IdEstado'       => 1,
                'IdFacultad'     => $u->IdFacultad,
                'NOficio'        => $this->nuevoOficio($u->facultad->Clave),
                'Asunto'         => $d['Asunto'],
                'Contexto'       => $d['Contexto'],
                'Cierre'         => $this->cierre($d),
                'FechaEmision'   => now(),
                'FechaInicio'    => $d['FechaInicio'],
                'FechaFin'       => $d['FechaFin'] ?? null,
                ...$snap,
                'Activo'         => 1,
                'CreatedAt'      => now(),
                'CreatedBy'      => $u->Matricula,
            ]);

            $c->profesores()->sync($d['profesores']);
            $this->log($c, $u, 'Comisión creada');

            return $c;
        });

        $c->load(['facultad', 'tipo', 'profesores', 'plantilla']);
        $this->docs->guardarOficio($c);
        $this->notif->asignacion($c, $c->profesores->pluck('Id'));
        $this->notif->porFirmar($c, 'Oficio');

        return $c;
    }

    public function actualizar(Usuario $u, Comision $c, array $d): Comision
    {
        $antes = $c->profesores()->pluck('usuario.Id')->map(fn ($i) => (int) $i);
        $teniaFirma = $c->firmado || $c->sellado;

        DB::transaction(function () use ($u, $c, $d, $teniaFirma) {
            $c->update([
                'IdTipoComision' => $d['IdTipoComision'],
                'IdPlantilla'    => $d['IdPlantilla'],
                'Asunto'         => $d['Asunto'],
                'Contexto'       => $d['Contexto'],
                'Cierre'         => $this->cierre($d),
                'FechaInicio'    => $d['FechaInicio'],
                'FechaFin'       => $d['FechaFin'] ?? null,
                'UpdatedBy'      => $u->Matricula,
                ...($teniaFirma ? $this->firmas->limpio($c->IdFacultad) : []),
            ]);
            $c->profesores()->sync($d['profesores']);
            $this->log($c, $u, 'Comisión modificada');
        });

        $c = $c->fresh(['facultad', 'tipo', 'profesores', 'plantilla', 'constancias.profesores']);
        $this->docs->guardarOficio($c);
        foreach ($c->constancias as $k) {
            $this->docs->guardarConstancia($c, $k);
        }
        if ($teniaFirma) {
            $this->log($c, $u, 'Firma retirada por modificación');
            $this->notif->porFirmar($c, 'Oficio');
        }

        $nuevos = collect($d['profesores'])->map(fn ($i) => (int) $i)->diff($antes);
        if ($nuevos->isNotEmpty()) {
            $this->notif->asignacion($c, $nuevos);
        }

        return $c;
    }

    /** Vista previa HTML sin guardar nada. $existente = comisión que se está editando. */
    public function previa(Usuario $u, array $in, ?Comision $existente = null): string
    {
        $c = $existente ? $existente->replicate() : new Comision();

        if (!$existente) {
            $c->fill([
                ...$this->snapshots($u, $u->IdFacultad, false),
                'NOficio'      => $this->nuevoOficio($u->facultad->Clave, false),
                'FechaEmision' => now(),
            ]);
        }

        $c->fill([
            'Asunto'      => $in['Asunto'] ?? '',
            'Contexto'    => $in['Contexto'] ?? '',
            'Cierre'      => $in['Cierre'] ?? '',
            'FechaInicio' => !empty($in['FechaInicio']) ? $in['FechaInicio'] : null,
            'FechaFin'    => !empty($in['FechaFin']) ? $in['FechaFin'] : null,
        ]);

        $tipo = TipoComision::find($in['IdTipoComision'] ?? 0) ?? new TipoComision(['Nombre' => '', 'Descripcion' => '']);
        $plantilla = PlantillaDocumento::find($in['IdPlantilla'] ?? 0)
            ?? PlantillaDocumento::where('TipoDocumento', 'Comision')->where('Activo', 1)->firstOrFail();
        $ids = collect($in['profesores'] ?? [])->map(fn ($i) => (int) $i);

        $c->setRelation('tipo', $tipo);
        $c->setRelation('plantilla', $plantilla);
        $c->setRelation('facultad', $u->facultad);
        $c->setRelation('profesores', $this->profesores($u)->whereIn('Id', $ids)->values());

        return $this->docs->html($c);
    }

    /* ───────── estados ───────── */

    /** Secretaría: 1→2→3 (solo estados principales). */
    public function avanzar(Usuario $u, Comision $c): void
    {
        abort_unless(in_array((int) $c->IdEstado, [1, 2], true), 422, 'No se puede avanzar desde este estado.');
        $this->cambiar($c, $u, (int) $c->IdEstado + 1, 'Estado avanzado');
    }

    /** 3→2→1; si está rechazado/refutado vuelve a Finalizado. */
    public function retroceder(Usuario $u, Comision $c): void
    {
        $nuevo = match ((int) $c->IdEstado) {
            2 => 1, 3 => 2, 4, 5 => 3,
            default => abort(422, 'La comisión ya está en el primer estado.'),
        };
        $this->cambiar($c, $u, $nuevo, 'Estado revertido');
    }

    /** Profesor: Iniciado → En proceso. */
    public function iniciar(Usuario $u, Comision $c): void
    {
        abort_unless((int) $c->IdEstado === 1, 422, 'La comisión ya fue iniciada.');
        $this->cambiar($c, $u, 2, 'Comisión iniciada');
    }

    /**
     * Profesor: sube el reporte (reemplaza el anterior, aunque sea de otro del grupo).
     * En proceso o Rechazado → Finalizado.
     */
    public function enviarReporte(Usuario $u, Comision $c, UploadedFile $f): void
    {
        abort_if((int) $c->IdEstado === 1, 422, 'Primero marca la comisión como En proceso.');

        $estabaRechazado = (int) $c->IdEstado === 4;
        $this->docs->guardarReporte($c, $f, $u);

        if (in_array((int) $c->IdEstado, [2, 4], true)) {
            $this->cambiar($c, $u, 3, $estabaRechazado ? 'Reporte corregido' : 'Reporte enviado');
        } else {
            $this->log($c, $u, 'Reporte reemplazado');
        }
    }

    /** Secretaría: rechaza el reporte con descripción → Rechazado. */
    public function rechazarReporte(Usuario $u, Comision $c, string $motivo): void
    {
        abort_unless((int) $c->IdEstado === 3 && $c->ReporteRuta, 422, 'No hay un reporte finalizado que rechazar.');
        $this->cambiar($c, $u, 4, 'Reporte rechazado', $motivo);
    }

    /** Profesor: refuta la constancia con descripción → Refutado. */
    public function refutarConstancia(Usuario $u, Comision $c, string $motivo): void
    {
        abort_unless(
            (int) $c->IdEstado === 3 && $c->constancias->contains(fn ($k) => $k->profesores->contains('Id', $u->Id)),
            422, 'No hay una constancia tuya que refutar.'
        );
        $this->cambiar($c, $u, 5, 'Constancia refutada', $motivo);
    }

    public function anular(Usuario $u, Comision $c): void
    {
        $c->update(['Activo' => 0, 'DeletedAt' => now(), 'DeletedBy' => $u->Matricula]);
        $this->log($c, $u, 'Comisión anulada');
    }

    public function restaurar(Usuario $u, Comision $c): void
    {
        $c->update(['Activo' => 1, 'DeletedAt' => null, 'DeletedBy' => null, 'UpdatedBy' => $u->Matricula]);
        $this->log($c, $u, 'Anulación revertida');
    }

    private function cambiar(Comision $c, Usuario $u, int $estado, string $accion, ?string $desc = null): void
    {
        $c->update(['IdEstado' => $estado, 'UpdatedBy' => $u->Matricula]);
        $this->log($c, $u, $accion, $desc);
        $this->notif->cambioEstado($c, $u, $accion, $desc);
    }

    public function log(Comision $c, Usuario $u, string $accion, ?string $desc = null): void
    {
        ComisionLog::create([
            'IdComision'  => $c->Id,
            'IdEstado'    => $c->IdEstado,
            'Accion'      => $accion,
            'Descripcion' => $desc,
            'IdUsuario'   => $u->Id,
            'CreatedAt'   => now(),
        ]);
    }

    /* ───────── constancias ───────── */

    /** $modo: grupo = una constancia con todos los elegidos · individual = una por profesor. */
    public function crearConstancias(Usuario $u, Comision $c, array $ids, string $modo): Collection
    {
        abort_unless((int) $c->IdEstado === 3, 422, 'La comisión debe estar finalizada.');
        $c->loadMissing(['profesores', 'constancias.profesores', 'facultad']);

        $pendientes = $c->profesores->pluck('Id')->map(fn ($i) => (int) $i)->diff($c->profesores_cubiertos);
        $ids = collect($ids)->map(fn ($i) => (int) $i)->unique()->intersect($pendientes)->values();

        if ($ids->isEmpty()) {
            throw ValidationException::withMessages(['general' => 'Selecciona al menos un profesor que aún no tenga constancia.']);
        }

        $plantilla = PlantillaDocumento::where('TipoDocumento', 'Constancia')->where('Activo', 1)->latest('Id')->firstOrFail();
        $snap      = $this->snapshots($u, $c->IdFacultad);
        $grupos    = $modo === 'grupo' ? [$ids->all()] : $ids->map(fn ($i) => [$i])->all();

        $creadas = DB::transaction(fn () => collect($grupos)->map(function ($g) use ($c, $plantilla, $snap, $u) {
            $k = Constancia::create([
                'IdComision'       => $c->Id,
                'IdPlantilla'      => $plantilla->Id,
                'Contexto'         => $c->Contexto,
                'Cierre'           => $plantilla->Cierre,
                'FechaElaboracion' => now(),
                ...$snap,
                'Activo'           => 1,
                'CreatedAt'        => now(),
                'CreatedBy'        => $u->Matricula,
            ]);
            $k->profesores()->sync($g);
            return $k;
        }));

        foreach ($creadas as $k) {
            $this->docs->guardarConstancia($c, $k->load('profesores'));
        }
        $c->unsetRelation('constancias');

        $nombres = $c->profesores->whereIn('Id', $ids->all())->map(fn ($p) => $p->nombre_corto)->join(', ');
        $this->log($c, $u, 'Constancia generada', ($modo === 'grupo' ? 'Grupal' : 'Individual') . ": $nombres");
        $this->notif->constancia($c, $u, 'generada', $ids);
        $this->notif->porFirmar($c, 'Constancia');

        return $creadas;
    }

    /** Modifica las constancias seleccionadas (ids de constancia). */
    public function actualizarConstancias(Usuario $u, Comision $c, array $ids, string $contexto, ?string $cierre = null): int
    {
        $c->loadMissing(['facultad', 'constancias.profesores']);
        $ks = $c->constancias->whereIn('Id', array_map('intval', $ids));
        abort_if($ks->isEmpty(), 404, 'No se encontraron las constancias seleccionadas.');

        foreach ($ks as $k) {
            $k->update([
                'Contexto'         => $contexto,
                'Cierre'           => ($cierre !== null && trim($cierre) !== '') ? $cierre : $k->Cierre,
                'FechaElaboracion' => now(),
                'UpdatedBy'        => $u->Matricula,
                ...$this->firmas->limpio($c->IdFacultad),   // al cambiar el texto hay que volver a firmar
            ]);
            $this->docs->guardarConstancia($c, $k->fresh('profesores'));
        }

        if ((int) $c->IdEstado === 5) {
            $this->cambiar($c, $u, 3, 'Constancia corregida');
        } else {
            $this->log($c, $u, 'Constancia modificada', $ks->count() . ' constancia(s)');
        }
        $this->notif->constancia($c, $u, 'actualizada', $ks->flatMap(fn ($k) => $k->profesores->pluck('Id'))->unique());
        $this->notif->porFirmar($c, 'Constancia');

        return $ks->count();
    }

    public function eliminarConstancias(Usuario $u, Comision $c, array $ids): int
    {
        $c->loadMissing(['facultad', 'constancias.profesores']);
        $ks = $c->constancias->whereIn('Id', array_map('intval', $ids));
        abort_if($ks->isEmpty(), 404, 'No se encontraron las constancias seleccionadas.');

        foreach ($ks as $k) {
            $k->update(['Activo' => 0, 'DeletedAt' => now(), 'DeletedBy' => $u->Matricula]);
            $this->docs->borrarConstancia($c, $k);
        }

        if ((int) $c->IdEstado === 5) {
            $this->cambiar($c, $u, 3, 'Constancia eliminada');
        } else {
            $this->log($c, $u, 'Constancia eliminada', $ks->count() . ' constancia(s)');
        }

        return $ks->count();
    }

    /* ───────── estadísticas del módulo ───────── */

    public function estadisticas(Usuario $u): array
    {
        $vig = fn () => $this->alcance($u)->whereNull('DeletedAt');

        $porEstado = $vig()->selectRaw('IdEstado, COUNT(*) AS n')->groupBy('IdEstado')->pluck('n', 'IdEstado');
        $estados = EstadoComision::where('Activo', 1)->orderBy('Id')->get()->map(fn ($e) => [
            'id' => $e->Id, 'nombre' => $e->Nombre, 'n' => (int) ($porEstado[$e->Id] ?? 0),
            'clase' => Comision::CLASES[$e->Id] ?? '',
        ]);

        $nombresTipo = TipoComision::pluck('Nombre', 'Id');
        $tipos = $vig()->selectRaw('IdTipoComision, COUNT(*) AS n')->groupBy('IdTipoComision')->pluck('n', 'IdTipoComision')
            ->mapWithKeys(fn ($n, $id) => [($nombresTipo[$id] ?? "Tipo $id") => (int) $n]);

        $porMes = $vig()->where('FechaInicio', '>=', now()->subMonths(5)->startOfMonth()->toDateString())
            ->selectRaw("DATE_FORMAT(FechaInicio, '%Y-%m') AS ym, COUNT(*) AS n")->groupBy('ym')->pluck('n', 'ym');
        $meses = collect(range(5, 0))->map(function ($i) use ($porMes) {
            $m = now()->subMonths($i);
            return ['label' => self::MESES[$m->month - 1] . ' ' . $m->format('y'), 'n' => (int) ($porMes[$m->format('Y-m')] ?? 0)];
        });

        $sinConstancia = $vig()->where('IdEstado', 3)->whereHas('profesores', fn ($p) => $p->whereNotExists(
            fn ($q) => $q->select(DB::raw(1))->from('constancia_profesor as cp')
                ->join('constancia as k', 'k.Id', '=', 'cp.IdConstancia')
                ->whereColumn('cp.IdUsuario', 'usuario.Id')
                ->whereColumn('k.IdComision', 'comision.Id')
                ->where('k.Activo', 1)
        ));
        $porVencer = $vig()->where('IdEstado', '<', 3)->whereNotNull('FechaFin')
            ->whereDate('FechaFin', '<=', now()->addDays(3)->toDateString());

        return [
            'total'         => (int) $porEstado->sum(),
            'estados'       => $estados,
            'tipos'         => $tipos,
            'meses'         => $meses,
            'sinConstancia' => ['n' => (clone $sinConstancia)->count(), 'lista' => $sinConstancia->orderByDesc('Id')->limit(5)->get()],
            'porVencer'     => ['n' => (clone $porVencer)->count(), 'lista' => $porVencer->orderBy('FechaFin')->limit(5)->get()],
            'anuladas'      => $this->alcance($u)->whereNotNull('DeletedAt')->count(),
        ];
    }

    /** Cierre capturado; si viene vacío, el de la plantilla. */
    private function cierre(array $d): string
    {
        return trim((string) ($d['Cierre'] ?? '')) !== ''
            ? trim($d['Cierre'])
            : (string) (PlantillaDocumento::find($d['IdPlantilla'])?->Cierre ?? '');
    }

    /* ───────── internos ───────── */

    /** Snapshots inmutables: director vigente de la facultad + iniciales de quien elabora. */
    private function snapshots(Usuario $u, int $idFacultad, bool $estricto = true): array
    {
        $dir = Usuario::deRol('Director')->where('IdFacultad', $idFacultad)->where('Activo', 1)->first();

        if (!$dir && $estricto) {
            throw ValidationException::withMessages(['general' => 'No hay un Director activo registrado en la facultad.']);
        }

        $ini = $u->Iniciales ?: strtolower(mb_substr((string) $u->Nombre, 0, 1) . mb_substr((string) $u->Apellido, 0, 1));

        return [
            'DirectorNombre'   => $dir ? $this->docs->nombreConGrado($dir) : '',
            'DirectorCargo'    => $dir && (int) $dir->Genero === 1 ? 'Directora' : 'Director',
            'DirectorFirmaUrl' => null,
            'DirectorSelloUrl' => null,
            'FirmaElaboracion' => 'C.c.p. Archivo ' . $u->facultad->Clave . '/' . $ini,
        ];
    }

    /** FCI-COMISION-2026-NOV-005 (consecutivo por facultad/año/mes; no reutiliza anuladas) */
    private function nuevoOficio(string $facultad, bool $bloquear = true): string
    {
        $f      = now();
        $prefix = sprintf('%s-COMISION-%d-%s', $facultad, $f->year, self::MESES[$f->month - 1]);

        $q = Comision::where('NOficio', 'like', $prefix . '-%');
        if ($bloquear) $q->lockForUpdate();

        $ultimo = $q->pluck('NOficio')
            ->map(fn ($n) => (int) substr($n, strrpos($n, '-') + 1))->max() ?? 0;

        return $prefix . '-' . str_pad($ultimo + 1, 3, '0', STR_PAD_LEFT);
    }
}
