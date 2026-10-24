<?php

namespace App\Models;

class Constancia extends ModeloCati
{
    protected $table = 'constancia';
    protected $casts = ['FechaElaboracion' => 'datetime'];

    public function plantilla() { return $this->belongsTo(PlantillaDocumento::class, 'IdPlantilla', 'Id'); }
    public function comision()  { return $this->belongsTo(Comision::class, 'IdComision', 'Id'); }
}
