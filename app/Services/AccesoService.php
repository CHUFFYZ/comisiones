<?php

namespace App\Services;

use App\Models\NivelAcceso;
use App\Models\Modulo;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class AccesoService
{
    /** Clave de asignacion_permiso que da acceso total a todos los módulos. */
    public const CLAVE_SUPERUSUARIO = 'APTSU';

    private const LETRAS = [
        'leer' => 'R', 'crear' => 'C', 'actualizar' => 'U', 'eliminar' => 'D',
    ];

    private array $cache = [];
    private array $idsModulo = [];

    /** Superusuario: clave APTSU o un nivel de acceso del módulo Global. */
    public function esSuperUsuario(?Usuario $u): bool
    {
        return $u && $this->asignaciones($u)->contains(
            fn ($a) => $a->Clave === self::CLAVE_SUPERUSUARIO
                || $a->nivel?->modulo?->Clave === config('modulos.modulo_global')
        );
    }

    /** El administrador solo consulta comisiones (módulo MSC en modo lectura). */
    public function soloConsulta(?Usuario $u): bool
    {
        return $u && $u->Rol === 'Administrador' && !$this->esSuperUsuario($u);
    }

    /** Módulos reales (sin Global ni Principal) a los que el usuario tiene acceso. */
    public function modulosDe(?Usuario $u): Collection
    {
        if (!$u) {
            return collect();
        }

        $q = Modulo::where('Activo', 1)
            ->whereNotIn('Clave', config('modulos.ocultos', []))
            ->orderBy('Id');

        if (!$this->esSuperUsuario($u)) {
            $q->whereIn('Id', $this->efectivos($u)->pluck('modulo')->unique()->all());
        }

        return $q->get();
    }

    /** Letras de permiso del usuario en un módulo: subconjunto de "CRUD". */
    public function letrasEn(?Usuario $u, string $claveModulo): string
    {
        if (!$u) {
            return '';
        }
        if ($this->esSuperUsuario($u)) {
            return 'CRUD';
        }

        $idModulo = $this->idModulo($claveModulo);
        if (!$idModulo) {
            return '';
        }

        $letras = $this->efectivos($u)->where('modulo', $idModulo)->pluck('letras')->implode('');
        $res    = implode('', array_filter(str_split('CRUD'), fn ($l) => str_contains($letras, $l)));

        if ($claveModulo === 'MSC' && $this->soloConsulta($u)) {
            return str_contains($res, 'R') ? 'R' : '';
        }

        return $res;
    }

    /** $accion: leer | crear | actualizar | eliminar */
    public function puede(?Usuario $u, string $claveModulo, string $accion = 'leer'): bool
    {
        return str_contains($this->letrasEn($u, $claveModulo), self::LETRAS[$accion] ?? '?');
    }

    /**
     * Opciones del módulo que el usuario puede ver según su permiso.
     * Cada item incluye 'url' (null si la ruta aún no existe).
     */
    public function opcionesDe(?Usuario $u, string $claveModulo): Collection
    {
        return collect(config("modulos.opciones.$claveModulo", []))
            ->filter(fn ($o) => !in_array($u?->Rol, $o['excluye'] ?? [], true))
            ->filter(fn ($o) => $this->puede($u, $claveModulo, $o['accion'] ?? 'leer'))
            ->map(function ($o) {
                $o['url'] = (isset($o['ruta']) && Route::has($o['ruta'])) ? route($o['ruta'], $o['params'] ?? []) : null;
                return $o;
            })
            ->values();
    }

    public function puedeGestionarUsuarios(?Usuario $u): bool
    {
        return $u && ($this->esSuperUsuario($u) || in_array($u->Rol, ['Administrador', 'SecretariaAcademica'], true));
    }

    public function rolesCreables(Usuario $u): array
    {
        return $this->esSuperUsuario($u)
            ? Rol::where('Activo', 1)->pluck('Clave')->all()
            : config('modulos.creables.' . $u->Rol, []);
    }

    /** SU: todos · Admin: su facultad · Sec. Académica: sus secretarias (de su facultad). */
    public function usuariosGestionables(Usuario $u): Builder
    {
        $q = Usuario::query();
        if (!$this->esSuperUsuario($u)) {
            $q->where('IdFacultad', $u->IdFacultad)->deRol($this->rolesCreables($u));
        }
        return $q;
    }

    public function nivelesAsignables(Usuario $u): Collection
    {
        $n = NivelAcceso::with('modulo')->where('Activo', 1)->get();
        if ($this->esSuperUsuario($u)) return $n;
        $mods = config('modulos.asignables.' . $u->Rol, []);
        return $n->filter(fn ($x) => in_array($x->modulo?->Clave, $mods, true))->values();
    }

    public function puedeFirmar(?Usuario $u): bool
    {
        return $u && in_array($u->Rol, ['Director', 'SecretariaAcademica'], true);
    }

    public function puedeFirmarComoDirector(?Usuario $u): bool
    {
        return $u && $u->Rol === 'SecretariaAcademica' && (bool) ($u->facultad?->SAFirmaComoDirector);
    }
    /* ───────── internos ───────── */

    private function idModulo(string $clave): ?int
    {
        return $this->idModulo[$clave] ??= Modulo::where('Clave', $clave)->value('Id');
    }

    private function asignaciones(Usuario $u): Collection
    {
        return $this->cache[$u->Id] ??= $u->asignaciones()
            ->with(['nivel.modulo', 'tipo', 'nivelTemp', 'tipoTemp'])
            ->get();
    }

    /** Permisos base + temporales vigentes: [['modulo' => id, 'letras' => 'CRU'], ...] */
    private function efectivos(Usuario $u): Collection
    {
        $out = collect();

        foreach ($this->asignaciones($u) as $a) {
            if ($a->nivel && $a->tipo) {
                $out->push(['modulo' => $a->nivel->IdModulo, 'letras' => (string) $a->tipo->Nombre]);
            }
            if ($a->tempVigente() && $a->nivelTemp && $a->tipoTemp) {
                $out->push(['modulo' => $a->nivelTemp->IdModulo, 'letras' => (string) $a->tipoTemp->Nombre]);
            }
        }

        // Secretaría Académica gestiona a sus secretarias: acceso implícito al módulo de Administración
        // (el alcance real lo limita AccesoService::usuariosGestionables)
        if ($u->Rol === 'SecretariaAcademica' && ($ma = $this->idModulo('MA'))) {
            $out->push(['modulo' => $ma, 'letras' => 'CRUD']);
        }

        return $out;
    }
}
