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

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->Nombre . ' ' . $this->Apellido);
    }
}