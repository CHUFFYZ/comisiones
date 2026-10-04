<?php

namespace App\Models;

class AsignacionPermiso extends ModeloCati
{
    protected $table = 'asignacion_permiso';

    protected $casts = [
        'IniPermisoTemp' => 'datetime',
        'FinPermisoTemp' => 'datetime',
    ];

    public function nivel()     { return $this->belongsTo(NivelAcceso::class, 'IdNivelAcceso', 'Id'); }
    public function tipo()      { return $this->belongsTo(TipoPermiso::class, 'IdTipoPermiso', 'Id'); }
    public function nivelTemp() { return $this->belongsTo(NivelAcceso::class, 'IdNivelAccesoTemp', 'Id'); }
    public function tipoTemp()  { return $this->belongsTo(TipoPermiso::class, 'IdTipoPermisoTemp', 'Id'); }

    /** ¿El permiso temporal está vigente ahora? */
    public function tempVigente(): bool
    {
        if (!$this->IdTipoPermisoTemp || !$this->IdNivelAccesoTemp || !$this->IniPermisoTemp) {
            return false;
        }
        return $this->IniPermisoTemp->lte(now())
            && (!$this->FinPermisoTemp || $this->FinPermisoTemp->gte(now()));
    }
}