<?php

namespace App\Models;

class ChatConversacion extends ModeloCati
{
    protected $table = 'chat_conversacion';
    protected $casts = ['UltimoMensajeAt' => 'datetime', 'CreatedAt' => 'datetime'];

    /** Id del otro participante. */
    public function otro(int $yo): int
    {
        return (int) $this->IdUsuario1 === $yo ? (int) $this->IdUsuario2 : (int) $this->IdUsuario1;
    }

    public function participa(int $id): bool
    {
        return (int) $this->IdUsuario1 === $id || (int) $this->IdUsuario2 === $id;
    }
}
