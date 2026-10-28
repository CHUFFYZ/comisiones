<?php

namespace App\Models\Concerns;

trait Firmable
{
    public function getFirmadoAttribute(): bool { return $this->FirmaAt !== null; }
    public function getSelladoAttribute(): bool { return $this->SelloAt !== null; }

    /** completa | parcial | pendiente */
    public function getEstadoFirmaAttribute(): string
    {
        return ($this->firmado && $this->sellado) ? 'completa' : (($this->firmado || $this->sellado) ? 'parcial' : 'pendiente');
    }
}