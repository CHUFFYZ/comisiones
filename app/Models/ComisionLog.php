<?php

namespace App\Models;

class ComisionLog extends ModeloCati
{
    protected $table = 'comision_log';
    protected $casts = ['CreatedAt' => 'datetime'];

    public function usuario() { return $this->belongsTo(Usuario::class, 'IdUsuario', 'Id'); }
    public function estado()  { return $this->belongsTo(EstadoComision::class, 'IdEstado', 'Id'); }
}
