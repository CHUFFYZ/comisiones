<?php

namespace App\Models;

class Comision extends ModeloCati
{
    protected $table = 'comision';

    protected $casts = [
        'FechaEmision' => 'datetime',
        'FechaInicio'  => 'date',
        'FechaFin'     => 'date',
    ];

    public function tipo()       { return $this->belongsTo(TipoComision::class, 'IdTipoComision', 'Id'); }
    public function estado()     { return $this->belongsTo(EstadoComision::class, 'IdEstado', 'Id'); }
    public function plantilla()  { return $this->belongsTo(PlantillaDocumento::class, 'IdPlantilla', 'Id'); }
    public function profesores() { return $this->belongsToMany(Usuario::class, 'comision_profesor', 'IdComision', 'IdUsuario'); }
    public function constancia() { return $this->hasOne(Constancia::class, 'IdComision', 'Id')->where('Activo', 1); }
}