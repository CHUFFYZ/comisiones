<?php

namespace App\Models\Concerns;

use App\Models\Usuario;

trait Firmable
{
    public function getFirmadoAttribute(): bool { return $this->FirmaAt !== null; }
    public function getSelladoAttribute(): bool { return $this->SelloAt !== null; }

    /** Firmado y sellado por completo. */
    public function getFirmaCompletaAttribute(): bool { return $this->firmado && $this->sellado; }

    public function firmaUsuario() { return $this->belongsTo(Usuario::class, 'FirmaPor', 'Id'); }
    public function selloUsuario() { return $this->belongsTo(Usuario::class, 'SelloPor', 'Id'); }

    /** completa | parcial | pendiente */
    public function getEstadoFirmaAttribute(): string
    {
        return ($this->firmado && $this->sellado) ? 'completa' : (($this->firmado || $this->sellado) ? 'parcial' : 'pendiente');
    }
}
