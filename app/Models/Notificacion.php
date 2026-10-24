<?php

namespace App\Models;

class Notificacion extends ModeloCati
{
    protected $table = 'notificacion';
    protected $casts = ['LeidaAt' => 'datetime', 'CreatedAt' => 'datetime'];
}
