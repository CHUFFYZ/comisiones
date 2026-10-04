<?php

namespace App\Services;

use App\Models\Modulo;
use App\Models\Usuario;
use Illuminate\Support\Collection;

class AccesoService
{
    /** Clave de asignación_permiso que da acceso total a todos los módulos. */
    public const CLAVE_SUPERUSUARIO = 'APTSU';

    private const LETRAS = [
        'leer' => 'R', 'crear' => 'C', 'actualizar' => 'U', 'eliminar' => 'D',
    ];

    private array $cache = [];

    public function esSuperUsuario(?Usuario $u): bool
    {
        return $u && $this->asignaciones($u)
            ->contains(fn ($a) => $a->Clave === self::CLAVE_SUPERUSUARIO);
    }

    /** Módulos activos a los que el usuario tiene acceso. */
    public function modulosDe(?Usuario $u): Collection
    {
        if (!$u) {
            return collect();
        }

        $q = Modulo::where('Activo', 1)->orderBy('Id');

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

        $idModulo = Modulo::where('Clave', $claveModulo)->value('Id');
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

    /* ───────── internos ───────── */

    private function asignaciones(Usuario $u): Collection
    {
        return $this->cache[$u->Id] ??= $u->asignaciones()
            ->with(['nivel', 'tipo', 'nivelTemp', 'tipoTemp'])
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