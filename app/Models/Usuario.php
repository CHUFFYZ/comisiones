<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'usuario';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $guarded = [];
    protected $hidden = ['Contrasena'];

    public function getAuthPassword()     { return $this->Contrasena; }
    public function getAuthPasswordName() { return 'Contrasena'; }
    public function getRememberTokenName(){ return ''; } // no hay columna remember_token

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'IdFacultad', 'Id');
    }

    public function asignaciones()
    {
        return $this->hasMany(AsignacionPermiso::class, 'IdUsuario', 'Id')
            ->where('Activo', 1)->whereNull('DeletedAt');
    }

    public function tiposComision()
    {
        return $this->belongsToMany(TipoComision::class, 'usuario_tipo_comision', 'IdUsuario', 'IdTipoComision');
    }

    /** Comisiones que le fueron asignadas (solo o en grupo). */
    public function comisiones()
    {
        return $this->belongsToMany(Comision::class, 'comision_profesor', 'IdUsuario', 'IdComision');
    }

    /** Nombre completo (para documentos oficiales). */
    public function getNombreCompletoAttribute(): string
    {
        return trim($this->Nombre . ' ' . $this->Apellido);
    }

    /** Primer nombre + primer apellido (para la interfaz). */
    public function getNombreCortoAttribute(): string
    {
        $n = explode(' ', trim((string) $this->Nombre))[0];
        $a = explode(' ', trim((string) $this->Apellido))[0];

        return trim("$n $a");
    }
}
