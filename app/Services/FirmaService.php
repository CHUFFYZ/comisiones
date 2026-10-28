<?php

namespace App\Services;

use App\Models\Constancia;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class FirmaService
{
    public function __construct(
        private DocumentoService $docs,
        private AccesoService $acc,
        private NotificacionService $notif,
    ) {}

    public function director(int $idFacultad): ?Usuario
    {
        return Usuario::deRol('Director')->where('IdFacultad', $idFacultad)->where('Activo', 1)->first();
    }

    /** Campos que dejan el documento sin firma ni sello (bloque de firmante = Director). */
    public function limpio(int $idFacultad): array
    {
        $v = [
            'DirectorFirmaUrl' => null, 'DirectorSelloUrl' => null,
            'FirmaAt' => null, 'FirmaPor' => null, 'SelloAt' => null, 'SelloPor' => null,
            'FirmanteId' => null, 'FirmanteModo' => null,
        ];
        if ($dir = $this->director($idFacultad)) {
            $v['DirectorNombre'] = $this->docs->nombreConGrado($dir);
            $v['DirectorCargo']  = (int) $dir->Genero === 1 ? 'Directora' : 'Director';
        }
        return $v;
    }

    /** $accion: firma | sello | ambos | retirar · $modo: propio | director */
    public function aplicar(Usuario $u, Model $doc, string $accion, string $modo): void
    {
        $this->falla(!$this->acc->puedeFirmar($u), 'No tienes permiso para firmar.');

        $c = $doc instanceof Constancia ? $doc->comision : $doc;
        $this->falla((int) $c->IdFacultad !== (int) $u->IdFacultad, 'El documento es de otra facultad.');
        $this->falla($c->DeletedAt !== null, "La comisión {$c->NOficio} está anulada.");

        if ($accion === 'retirar') {
            $this->retirar($u, $doc, $c);
            return;
        }

        $modo = $u->Rol === 'Director' ? 'director' : $modo;
        if ($modo === 'director' && $u->Rol === 'SecretariaAcademica') {
            $this->falla(!$this->acc->puedeFirmarComoDirector($u), 'Tu facultad no tiene habilitado firmar como Director.');
        }

        $firmante = $modo === 'director' ? $this->director($u->IdFacultad) : $u;
        $this->falla(!$firmante, 'No hay un Director activo en la facultad.');

        if (($doc->firmado || $doc->sellado)
            && ($doc->FirmanteModo !== $modo || (int) $doc->FirmanteId !== (int) $firmante->Id)) {
            $this->falla(true, "{$c->NOficio} ya está firmado/sellado con otra firma. Retírala antes de cambiarla.");
        }

        $firma = in_array($accion, ['firma', 'ambos'], true);
        $sello = in_array($accion, ['sello', 'ambos'], true);
        $this->falla($firma && !$firmante->FirmaUrl, 'Falta la imagen de firma de ' . $firmante->nombre_corto . ' (Mi perfil).');
        $this->falla($sello && !$firmante->SelloUrl, 'Falta la imagen de sello de ' . $firmante->nombre_corto . ' (Mi perfil).');

        $v = [
            'FirmanteId'     => $firmante->Id,
            'FirmanteModo'   => $modo,
            'DirectorNombre' => $this->docs->nombreConGrado($firmante),
            'DirectorCargo'  => $modo === 'director'
                ? ((int) $firmante->Genero === 1 ? 'Directora' : 'Director')
                : (string) $firmante->rol_nombre,
            'UpdatedBy'      => $u->Matricula,
        ];
        if ($firma) $v += ['DirectorFirmaUrl' => $firmante->FirmaUrl, 'FirmaAt' => now(), 'FirmaPor' => $u->Id];
        if ($sello) $v += ['DirectorSelloUrl' => $firmante->SelloUrl, 'SelloAt' => now(), 'SelloPor' => $u->Id];

        $doc->update($v);
        $this->regenerar($doc);
        $this->notif->firmado($c, $u, ($doc instanceof Constancia ? 'Constancia' : 'Oficio') . ' ' . ($firma && $sello ? 'firmado y sellado' : ($firma ? 'firmado' : 'sellado')));
    }

    private function retirar(Usuario $u, Model $doc, $c): void
    {
        if ($u->Rol === 'SecretariaAcademica') {
            $ajena = collect([$doc->FirmaPor, $doc->SelloPor])->filter()->contains(fn ($i) => (int) $i !== (int) $u->Id);
            $this->falla($ajena, "{$c->NOficio}: solo el Director puede retirar lo que él firmó.");
        }
        $doc->update($this->limpio($c->IdFacultad) + ['UpdatedBy' => $u->Matricula]);
        $this->regenerar($doc);
    }

    private function regenerar(Model $doc): void
    {
        if ($doc instanceof Constancia) {
            $c = $doc->comision->load('facultad');
            $this->docs->guardarConstancia($c, $doc->load(['profesores', 'plantilla']));
        } else {
            $this->docs->guardarOficio($doc->load(['facultad', 'tipo', 'profesores', 'plantilla']));
        }
    }

    private function falla(bool $cond, string $msg): void
    {
        if ($cond) throw ValidationException::withMessages(['general' => $msg]);
    }
}