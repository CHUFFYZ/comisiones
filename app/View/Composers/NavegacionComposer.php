<?php

namespace App\View\Composers;

use App\Models\ChatConversacion;
use App\Models\ChatMensaje;
use App\Models\Facultad;
use App\Models\Modulo;
use App\Models\Notificacion;
use App\Models\Usuario;
use App\Services\AccesoService;
use Illuminate\View\View;
use App\Models\Comision;
use App\Models\Constancia;

class NavegacionComposer
{
    public function __construct(private AccesoService $acceso) {}

    public function compose(View $view): void
    {
        $user = auth()->user();

        // Módulo en el que está navegando (lo fija el middleware)
        $moduloEnUso = request()->attributes->get('modulo');

        // Para el header: módulo en uso o, si no hay, el principal CATI (MP)
        $moduloActual = $moduloEnUso ?? Modulo::where('Clave', 'MP')->first();

        $facultadActual = $user?->facultad
            ?? Facultad::where('Clave', 'FCI')->first();

        $data = [
            'moduloActual'   => $moduloActual,
            'moduloEnUso'    => $moduloEnUso,
            'facultadActual' => $facultadActual,
            'modulosMenu'    => $this->acceso->modulosDe($user),
            'opcionesModulo' => $moduloEnUso
                ? $this->acceso->opcionesDe($user, $moduloEnUso->Clave)
                : collect(),
            'esProfesor'      => false,
            'notifNoLeidas'   => 0,
            'notifRecientes'  => collect(),
            'chatNoLeidos'    => 0,
            'puedeSimular'    => false,
            'simulado'        => null,
            'secretariasSim'  => collect(),
            'puedeGestion'    => false,
            'puedeFirmar' => false, 'firmasPendientes' => 0,
        ];

        if ($user) {
            if ($this->acceso->puedeFirmar($user)) {
            $falta = fn ($q) => $q->whereNull('FirmaAt')->orWhereNull('SelloAt');
            $data['puedeFirmar'] = true;
            $data['firmasPendientes'] =
                Comision::where('IdFacultad', $user->IdFacultad)->whereNull('DeletedAt')->where($falta)->count()
                + Constancia::where('Activo', 1)->where($falta)
                    ->whereHas('comision', fn ($q) => $q->where('IdFacultad', $user->IdFacultad)->whereNull('DeletedAt'))->count();
}
            $data['puedeGestion'] = $this->acceso->puedeGestionarUsuarios($user);
            if ($this->acceso->esSuperUsuario($user)) {
                $data['puedeSimular']   = true;
                $data['simulado']       = ($sid = session('simulando')) ? Usuario::with('facultad')->find($sid) : null;
                $data['secretariasSim'] = Usuario::with('facultad')->where('Activo', 1)
                    ->deRol(['Secretaria', 'SecretariaAcademica'])->orderBy('IdFacultad')->orderBy('Nombre')->get()
                    ->groupBy(fn ($s) => $s->facultad->Clave ?? 'Sin facultad');
            }
            $data['esProfesor']     = $user->Rol === 'Profesor' || $user->comisiones()->exists();
            $data['notifNoLeidas']  = Notificacion::where('IdUsuario', $user->Id)->whereNull('LeidaAt')->count();
            $data['notifRecientes'] = Notificacion::where('IdUsuario', $user->Id)->orderByDesc('Id')->limit(8)->get();
            $data['chatNoLeidos']   = ChatMensaje::whereNull('LeidoAt')->where('IdRemitente', '<>', $user->Id)
                ->whereIn('IdConversacion', ChatConversacion::select('Id')
                    ->where(fn ($q) => $q->where('IdUsuario1', $user->Id)->orWhere('IdUsuario2', $user->Id)))
                ->count();
        }

        $view->with($data);
    }
}
