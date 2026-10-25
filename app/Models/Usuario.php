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
    protected $with = ['rolModelo'];
    protected $casts = ['UltimaConexion' => 'datetime'];

    public function getAuthPassword()     { return $this->Contrasena; }
    public function getAuthPasswordName() { return 'Contrasena'; }
    public function getRememberTokenName(){ return ''; } // no hay columna remember_token

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'IdFacultad', 'Id');
    }

    /** (No se llama "rol" para no chocar con el accesor $u->Rol) */
    public function rolModelo()
    {
        return $this->belongsTo(Rol::class, 'IdRol', 'Id');
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

    /** Usuario::deRol('Profesor') | deRol(['Secretaria','SecretariaAcademica']) */
    public function scopeDeRol($q, string|array $claves)
    {
        return $q->whereHas('rolModelo', fn ($r) => $r->whereIn('Clave', (array) $claves));
    }

    /** $u->Rol  => clave del rol (compatibilidad con el código anterior). */
    public function getRolAttribute(): ?string
    {
        return $this->rolModelo?->Clave;
    }

    public function getRolNombreAttribute(): ?string
    {
        return $this->rolModelo?->Nombre;
    }

    /** Nombre completo (para documentos oficiales). */
    public function getNombreCompletoAttribute(): string
    {
        return trim($this->Nombre . ' ' . $this->Apellido);
    }

    /** "Dra. Saide Dariola Duran Martin" */
    public function getNombreConGradoAttribute(): string
    {
        $g = trim((string) $this->GradoAcademico);
        if ($g !== '' && !str_ends_with($g, '.')) $g .= '.';

        return preg_replace('/\s+/', ' ', trim("$g {$this->Nombre} {$this->Apellido}"));
    }

    /** Primer nombre + primer apellido (para la interfaz). */
    public function getNombreCortoAttribute(): string
    {
        $n = explode(' ', trim((string) $this->Nombre))[0];
        $a = explode(' ', trim((string) $this->Apellido))[0];

        return trim("$n $a");
    }

    public function getGeneroTextoAttribute(): string
    {
        return $this->Genero === null ? '—' : ((int) $this->Genero === 1 ? 'Femenino' : 'Masculino');
    }

    /** URL pública de la foto o null si no tiene / no existe el archivo. */
    public function getFotoSrcAttribute(): ?string
    {
        if (!$this->FotoUrl) return null;
        $ruta = public_path($this->FotoUrl);

        return is_file($ruta) ? asset($this->FotoUrl) . '?v=' . filemtime($ruta) : null;
    }
}
