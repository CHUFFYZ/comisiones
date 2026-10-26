<?php

namespace App\Models;

class ChatConversacion extends ModeloCati
{
    protected $table = 'chat_conversacion';
    protected $casts = [
        'UltimoMensajeAt' => 'datetime', 'CreatedAt' => 'datetime',
        'OcultoU1' => 'datetime', 'OcultoU2' => 'datetime',
    ];

    /** Id del otro participante. */
    public function otro(int $yo): int
    {
        return (int) $this->IdUsuario1 === $yo ? (int) $this->IdUsuario2 : (int) $this->IdUsuario1;
    }

    public function participa(int $id): bool
    {
        return (int) $this->IdUsuario1 === $id || (int) $this->IdUsuario2 === $id;
    }

    public function columnaOculto(int $id): string
    {
        return (int) $this->IdUsuario1 === $id ? 'OcultoU1' : 'OcultoU2';
    }

    /** Fecha desde la que el usuario eliminó la conversación (null = nunca). */
    public function corteDe(int $id)
    {
        return $this->{$this->columnaOculto($id)};
    }

    /** Oculta para el usuario si la eliminó y no hubo mensajes nuevos después. */
    public function ocultaPara(int $id): bool
    {
        $corte = $this->corteDe($id);

        return $corte !== null && (!$this->UltimoMensajeAt || $this->UltimoMensajeAt->lte($corte));
    }
}
