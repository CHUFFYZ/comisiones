<?php

namespace App\Models;

class NivelAcceso extends ModeloCati
{
    protected $table = 'nivel_acceso';

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'IdModulo', 'Id');
    }
}