<?php

namespace App\Services;

use App\Models\Comision;
use App\Models\EstadoComision;
use App\Models\Notificacion;
use App\Models\Usuario;
use Illuminate\Support\Collection;

class NotificacionService
{
    /** Crea una notificación por destinatario. $clave evita duplicados (único por usuario). */
    public function enviar(iterable $ids, string $tipo, string $titulo, ?string $mensaje = null, ?string $url = null, ?string $clave = null): void
    {
        foreach (collect($ids)->filter()->unique() as $id) {
            Notificacion::insertOrIgnore([
                'IdUsuario' => $id,
                'Tipo'      => $tipo,
                'Titulo'    => mb_substr($titulo, 0, 150),
                'Mensaje'   => $mensaje ? mb_substr($mensaje, 0, 500) : null,
                'Url'       => $url,
                'Clave'     => $clave,
                'CreatedAt' => now(),
            ]);
        }
    }

    /* ───────── destinatarios ───────── */

    /** Quien elaboró la comisión + Secretaría Académica de la facultad. */
    public function secretaria(Comision $c): Collection
    {
        $ids = Usuario::deRol('SecretariaAcademica')->where('Activo', 1)
            ->where('IdFacultad', $c->IdFacultad)->pluck('Id');
        $creador = Usuario::where('Matricula', $c->CreatedBy)->value('Id');

        return $ids->push($creador)->filter()->unique()->values();
    }

    private function profesores(Comision $c): Collection
    {
        return $c->profesores()->pluck('usuario.Id');
    }

    /* ───────── eventos ───────── */

    public function asignacion(Comision $c, iterable $profesorIds): void
    {
        $this->enviar($profesorIds, 'asignacion', 'Nueva comisión asignada',
            "Se te asignó la comisión {$c->NOficio}: {$c->Asunto}", route('profesor.show', $c->Id));
    }

    /** Avisa a profesores y secretaría (menos a quien hizo el cambio). */
    public function cambioEstado(Comision $c, Usuario $actor, string $accion, ?string $motivo = null): void
    {
        $estado = EstadoComision::find($c->IdEstado)?->Nombre ?? '';
        $profes = $this->profesores($c);
        $sec    = $this->secretaria($c);

        $tipo = match ((int) $c->IdEstado) {
            3 => 'finalizada', 4 => 'rechazada', 5 => 'refutada', default => 'estado',
        };
        $msg = "{$c->NOficio} · {$estado}" . ($motivo ? ": {$motivo}" : '');

        foreach ($profes->merge($sec)->unique()->reject(fn ($id) => (int) $id === (int) $actor->Id) as $id) {
            $url = $profes->contains($id) ? route('profesor.show', $c->Id) : route('comisiones.show', $c->Id);
            $this->enviar([$id], $tipo, $accion, $msg, $url);
        }
    }

    public function constancia(Comision $c, Usuario $actor, string $accion): void
    {
        $this->enviar($this->profesores($c)->reject(fn ($id) => (int) $id === (int) $actor->Id),
            'constancia', "Constancia {$accion}", "Comisión {$c->NOficio}", route('profesor.show', $c->Id));
    }

    /** Avisa de comisiones por vencer (≤ 2 días) o vencidas sin finalizar. Una vez por comisión/fecha. */
    public function vencimientos(): void
    {
        $hoy = now()->toDateString();

        Comision::with('profesores')->whereNull('DeletedAt')->where('IdEstado', '<', 3)
            ->whereNotNull('FechaFin')->whereDate('FechaFin', '<=', now()->addDays(2)->toDateString())
            ->get()->each(function (Comision $c) use ($hoy) {
                $fin     = $c->FechaFin->toDateString();
                $vencida = $fin < $hoy;
                $clave   = "venc:{$c->Id}:{$fin}:" . ($vencida ? 'v' : 'p');
                $texto   = "{$c->NOficio} " . ($vencida ? 'venció el ' : 'vence el ') . $c->FechaFin->format('d/m/y');
                $titulo  = $vencida ? 'Comisión vencida' : 'Comisión por vencer';

                $profes = $c->profesores->pluck('Id');
                $this->enviar($profes, 'vencimiento', $titulo, $texto, route('profesor.show', $c->Id), $clave);
                $this->enviar($this->secretaria($c)->diff($profes), 'vencimiento', $titulo, $texto, route('comisiones.show', $c->Id), $clave);
            });
    }
}
