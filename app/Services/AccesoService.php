<?php

namespace App\Services;

use App\Models\Modulo;
use App\Models\Usuario;
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

        return implode('', array_filter(str_split('CRUD'), fn ($l) => str_contains($letras, $l)));
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
            ->filter(fn ($o) => $this->puede($u, $claveModulo, $o['accion'] ?? 'leer'))
            ->map(function ($o) {
                $o['url'] = (isset($o['ruta']) && Route::has($o['ruta'])) ? route($o['ruta']) : null;
                return $o;
            })
            ->values();
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

        return $out;
    }
}