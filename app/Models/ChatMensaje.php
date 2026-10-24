<?php

namespace App\Models;

class ChatMensaje extends ModeloCati
{
    protected $table = 'chat_mensaje';
    protected $casts = ['LeidoAt' => 'datetime', 'CreatedAt' => 'datetime'];
}
