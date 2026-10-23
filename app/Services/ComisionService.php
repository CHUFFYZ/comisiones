<?php

namespace App\Services;

use App\Models\Comision;
use App\Models\Constancia;
use App\Models\PlantillaDocumento;
use App\Models\TipoComision;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ComisionService
{
    private const MESES = ['ENE','FEB','MAR','ABR','MAY','JUN','JUL','AGO','SEP','OCT','NOV','DIC'];
    private const FMT   = 'j \d\e F \d\e Y';

    public function __construct(private AccesoService $acceso) {}

    /** Secretaría Académica y superusuario ven todos los tipos. */
    public function veTodo(Usuario $u): bool
    {
        return $u->Rol === 'SecretariaAcademica' || $this->acceso->esSuperUsuario($u);
    }

    public function tiposPermitidos(Usuario $u): Collection
    {
        return TipoComision::where('Activo', 1)
            ->when(!$this->veTodo($u), fn ($q) => $q->whereIn('Id', $u->tiposComision()->pluck('tipo_comision.Id')))
            ->orderBy('Nombre')->get();
    }

    public function profesores(Usuario $u): Collection
    {
        return Usuario::where('Rol', 'Profesor')->where('Activo', 1)
            ->where('IdFacultad', $u->IdFacultad)
            ->orderBy('Apellido')->orderBy('Nombre')->get();
    }

    /** $anuladas: false = vigentes, true = anuladas, null = ambas */
    public function visibles(Usuario $u, ?bool $anuladas = false): Builder
    {
        $q = Comision::query()->with(['tipo', 'estado', 'profesores', 'constancia']);

        if ($anuladas === true)  $q->whereNotNull('DeletedAt');
        if ($anuladas === false) $q->whereNull('DeletedAt');

        if (!$this->acceso->esSuperUsuario($u)) {
            $q->where('IdFacultad', $u->IdFacultad);
        }
        if (!$this->veTodo($u)) {
            $q->whereIn('IdTipoComision', $u->tiposComision()->pluck('tipo_comision.Id'));
        }

        return $q->orderByDesc('Id');
    }

    public function crear(Usuario $u, array $d): Comision
    {
        $snap = $this->snapshots($u, $u->IdFacultad);

        return DB::transaction(function () use ($u, $d, $snap) {
            $c = Comision::create([
                'IdTipoComision' => $d['IdTipoComision'],
                'IdPlantilla'    => $d['IdPlantilla'],
                'IdEstado'       => 1,
                'IdFacultad'     => $u->IdFacultad,
                'NOficio'        => $this->nuevoOficio($u->facultad->Clave),
                'Asunto'         => $d['Asunto'],
                'FechaEmision'   => now(),
                'FechaInicio'    => $d['FechaInicio'],
                'FechaFin'       => $d['FechaFin'] ?? null,
                ...$snap,
                'Activo'         => 1,
                'CreatedAt'      => now(),
                'CreatedBy'      => $u->Matricula,
            ]);

            $c->profesores()->sync($d['profesores']);

            return $c;
        });
    }

    public function crearConstancia(Usuario $u, Comision $c): Constancia
    {
        abort_unless((int) $c->IdEstado === 3, 422, 'La comisión debe estar finalizada.');

        if ($c->constancia) {
            return $c->constancia;
        }

        $plantilla = PlantillaDocumento::where('TipoDocumento', 'Constancia')
            ->where('Activo', 1)->latest('Id')->firstOrFail();

        return Constancia::create([
            'IdComision'       => $c->Id,
            'IdPlantilla'      => $plantilla->Id,
            'FechaElaboracion' => now(),
            ...$this->snapshots($u, $c->IdFacultad),
            'Activo'           => 1,
            'CreatedAt'        => now(),
            'CreatedBy'        => $u->Matricula,
        ]);
    }

    public function pdf(Comision $c, ?Constancia $k = null)
    {
        $doc  = $k ?? $c;
        $p    = $doc->plantilla;
        $esC  = $k !== null;
        $profes = $c->profesores()->orderBy('Apellido')->get();

        $contexto = $p->Contexto ?: $this->minuscula((string) $c->tipo->Descripcion);
        $cierre   = str_replace('{fechas}', $this->fechas($c), (string) $p->Cierre);

        $saludo = $profes->count() > 1
            ? 'Estimados maestros,'
            : ((int) $profes->first()?->Genero === 1 ? 'Estimada maestra,' : 'Estimado maestro,');

        $fecha = ($esC ? $k->FechaElaboracion : $c->FechaEmision)->locale('es')->translatedFormat(self::FMT);

        return Pdf::loadView('pdf.documento', [
            'p'           => $p,
            'c'           => $c,
            'doc'         => $doc,
            'esConstancia'=> $esC,
            'fechaDoc'    => $fecha,
            'asunto'      => $esC ? 'Constancia de cumplimiento' : $c->Asunto,
            'profesores'  => $profes->map(fn ($x) => $this->nombreConGrado($x)),
            'saludo'      => $saludo,
            'cuerpo'      => trim($p->Introduccion) . ' ' . $contexto . $cierre,
            'facTitulo'   => $this->titulo((string) $p->Facultad),
            'logoIzq'     => $this->img($p->LogoIzquierdo),
            'logoDer'     => $this->img($p->LogoDerecho),
            'firma'       => $this->img($doc->DirectorFirmaUrl),
            'sello'       => $this->img($doc->DirectorSelloUrl),
        ])->setPaper('letter')->setOption('isRemoteEnabled', true);
    }

    public function nombreConGrado(Usuario $u): string
    {
        $g = trim((string) $u->GradoAcademico);
        if ($g !== '' && !str_ends_with($g, '.')) $g .= '.';

        return preg_replace('/\s+/', ' ', trim("$g {$u->Nombre} {$u->Apellido}"));
    }

    /* ───────── internos ───────── */

    /** Snapshots inmutables: director vigente de la facultad + iniciales de quien elabora. */
    private function snapshots(Usuario $u, int $idFacultad): array
    {
        $dir = Usuario::where('Rol', 'Director')->where('IdFacultad', $idFacultad)->where('Activo', 1)->first();

        if (!$dir) {
            throw ValidationException::withMessages(['general' => 'No hay un Director activo registrado en la facultad.']);
        }

        $ini = $u->Iniciales ?: strtolower(mb_substr((string) $u->Nombre, 0, 1) . mb_substr((string) $u->Apellido, 0, 1));

        return [
            'DirectorNombre'   => $this->nombreConGrado($dir),
            'DirectorCargo'    => (int) $dir->Genero === 1 ? 'Directora' : 'Director',
            'DirectorFirmaUrl' => $dir->FirmaUrl,
            'DirectorSelloUrl' => $dir->SelloUrl,
            'FirmaElaboracion' => 'C.c.p. Archivo ' . $u->facultad->Clave . '/' . $ini,
        ];
    }

    /** FCI-COMISION-2025-NOV-005 (consecutivo por facultad/año/mes; no reutiliza anuladas) */
    private function nuevoOficio(string $facultad): string
    {
        $f      = now();
        $prefix = sprintf('%s-COMISION-%d-%s', $facultad, $f->year, self::MESES[$f->month - 1]);

        $ultimo = Comision::where('NOficio', 'like', $prefix . '-%')->lockForUpdate()->pluck('NOficio')
            ->map(fn ($n) => (int) substr($n, strrpos($n, '-') + 1))->max() ?? 0;

        return $prefix . '-' . str_pad($ultimo + 1, 3, '0', STR_PAD_LEFT);
    }

    private function fechas(Comision $c): string
    {
        $i = $c->FechaInicio->copy()->locale('es');
        $f = $c->FechaFin?->copy()->locale('es');

        if (!$f || $f->isSameDay($i))           return 'el ' . $i->translatedFormat(self::FMT);
        if ($i->format('Ym') === $f->format('Ym')) return 'del ' . $i->day . ' al ' . $f->translatedFormat(self::FMT);
        if ($i->year === $f->year)              return 'del ' . $i->translatedFormat('j \d\e F') . ' al ' . $f->translatedFormat(self::FMT);

        return 'del ' . $i->translatedFormat(self::FMT) . ' al ' . $f->translatedFormat(self::FMT);
    }

    private function minuscula(string $t): string
    {
        return mb_strtolower(mb_substr($t, 0, 1)) . mb_substr($t, 1);
    }

    private function titulo(string $t): string
    {
        $menores = ['de', 'la', 'del', 'y', 'en', 'el', 'los', 'las'];
        $w = explode(' ', mb_strtolower($t));
        foreach ($w as $i => $x) {
            if ($i === 0 || !in_array($x, $menores)) {
                $w[$i] = mb_strtoupper(mb_substr($x, 0, 1)) . mb_substr($x, 1);
            }
        }
        return implode(' ', $w);
    }

    /** URL http(s) tal cual; ruta local → archivo en public/ o storage/app/public */
    private function img(?string $u): ?string
    {
        if (!$u) return null;
        if (preg_match('#^https?://#i', $u)) return $u;

        $rel = ltrim($u, '/');
        foreach ([public_path($rel), storage_path('app/public/' . preg_replace('#^storage/#', '', $rel))] as $ruta) {
            if (is_file($ruta)) return $ruta;
        }
        return null;
    }
}