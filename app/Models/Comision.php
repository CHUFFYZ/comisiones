<?php

namespace App\Models;
use App\Models\Concerns\Firmable;
use Illuminate\Support\Collection;

class Comision extends ModeloCati
{
    use Firmable;
    protected $table = 'comision';

    /** Clase de badge por estado (1-3 principales, 4 rechazado, 5 refutado). */
    public const CLASES = [
        1 => 'estado-no-iniciado',
        2 => 'estado-proceso',
        3 => 'estado-terminado',
        4 => 'estado-rechazado',
        5 => 'estado-refutado',
    ];

    protected $casts = [
        'FechaEmision' => 'datetime',
        'FechaInicio'  => 'date',
        'FechaFin'     => 'date',
        'ReporteAt'    => 'datetime',
        'DeletedAt'    => 'datetime',
        'CerradaAt'    => 'datetime',
        'FirmaAt'      => 'datetime', 'SelloAt' => 'datetime',
    ];

    public function tipo()           { return $this->belongsTo(TipoComision::class, 'IdTipoComision', 'Id'); }
    public function estado()         { return $this->belongsTo(EstadoComision::class, 'IdEstado', 'Id'); }
    public function plantilla()      { return $this->belongsTo(PlantillaDocumento::class, 'IdPlantilla', 'Id'); }
    public function facultad()       { return $this->belongsTo(Facultad::class, 'IdFacultad', 'Id'); }
    public function reporteUsuario() { return $this->belongsTo(Usuario::class, 'ReporteIdUsuario', 'Id'); }
    public function cierreUsuario()  { return $this->belongsTo(Usuario::class, 'CerradaPor', 'Id'); }
    public function profesores()     { return $this->belongsToMany(Usuario::class, 'comision_profesor', 'IdComision', 'IdUsuario'); }
    public function constancias() { return $this->hasMany(Constancia::class, 'IdComision', 'Id')->where('Activo', 1); }
    public function logs()           { return $this->hasMany(ComisionLog::class, 'IdComision', 'Id')->orderByDesc('Id'); }

    public function getProfesoresCubiertosAttribute(): Collection
    {
        return $this->constancias->flatMap(fn ($k) => $k->profesores->pluck('Id'))->map(fn ($i) => (int) $i)->unique()->values();
    }

    public function getEstadoConstanciasAttribute(): string
    {
        $cub = $this->profesores_cubiertos->count();
        return $cub === 0 ? 'ninguna' : ($cub >= $this->profesores->count() ? 'completa' : 'parcial');
    }
    public function getClaseEstadoAttribute(): string
    {
        return self::CLASES[$this->IdEstado] ?? '';
    }

    public function getEstaAnuladaAttribute(): bool
    {
        return $this->DeletedAt !== null;
    }

    /** Cerrada: ya no puede modificarse (solo el super usuario la reabre). */
    public function getEstaCerradaAttribute(): bool
    {
        return (int) ($this->Cerrada ?? 0) === 1;
    }

    /** Descripción del rechazo/refutación vigente (solo estados 4 y 5). */
    public function getMotivoEstadoAttribute(): ?string
    {
        if (!in_array((int) $this->IdEstado, [4, 5], true)) {
            return null;
        }

        return $this->logs->firstWhere('IdEstado', $this->IdEstado)?->Descripcion;
    }
}
