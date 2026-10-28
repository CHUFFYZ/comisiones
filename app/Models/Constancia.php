<?php

namespace App\Models;

use App\Models\Concerns\Firmable;

class Constancia extends ModeloCati
{
    use Firmable;

    protected $table = 'constancia';
    protected $casts = ['FechaElaboracion' => 'datetime', 'FirmaAt' => 'datetime', 'SelloAt' => 'datetime'];

    public function plantilla()  { return $this->belongsTo(PlantillaDocumento::class, 'IdPlantilla', 'Id'); }
    public function comision()   { return $this->belongsTo(Comision::class, 'IdComision', 'Id'); }
    public function profesores() { return $this->belongsToMany(Usuario::class, 'constancia_profesor', 'IdConstancia', 'IdUsuario'); }
}