<?php

namespace App\Models;

class NivelAcceso extends ModeloCati
{
    protected $table = 'nivel_acceso';

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'IdModulo', 'Id');
    }

    /** Áreas (tipos de comisión) que otorga este nivel. */
    public function tipos()
    {
        return $this->belongsToMany(TipoComision::class, 'nivel_acceso_tipo_comision', 'IdNivelAcceso', 'IdTipoComision');
    }
}
