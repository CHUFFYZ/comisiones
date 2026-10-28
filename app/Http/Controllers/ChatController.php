<?php

namespace App\Http\Controllers;

use App\Models\AsignacionPermiso;
use App\Models\ChatConversacion;
use App\Models\ChatMensaje;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Chat uno a uno entre cualquier par de usuarios (sin importar facultad ni rol).
 * - El superusuario ve todas las conversaciones (pestaña "Supervisión", solo lectura).
 * - Si el superusuario escribe primero, el otro lo ve como "Desconocido" y puede responder.
 * - Nadie más puede iniciar un chat con un superusuario.
 */
class ChatController extends Controller
{
    private ?array $su = null;
    private const MINUTOS_EDICION = 15;

    public function index(Request $r)
    {
        return view('chat.index', ['esSU' => $this->esSU($r->user()), 'miFacultad' => $r->user()->IdFacultad]);
    }

    /* ───────── directorio, perfil y conversaciones ───────── */

    /** Todos los usuarios activos; el cliente los agrupa por facultad y rol. */
    public function usuarios(Request $r): JsonResponse
    {
        $u = $r->user();

        $users = Usuario::with('facultad')->where('Activo', 1)->where('Id', '<>', $u->Id)
            ->when(!$this->esSU($u), fn ($w) => $w->whereNotIn('Id', $this->superIds()))
            ->orderBy('Nombre')->get();

        return response()->json($users->map(fn ($x) => [
            'id' => $x->Id, 'nombre' => $x->nombre_corto,
            'rol' => $x->rol_nombre ?? '—', 'rolOrden' => $x->rolModelo?->Orden ?? 99,
            'facultadId' => $x->IdFacultad, 'facultad' => $x->facultad?->Clave, 'facultadNombre' => $x->facultad?->Nombre,
            'foto' => $x->foto_src,
        ])->values());
    }

    public function perfil(Request $r, int $id): JsonResponse
    {
        $x = Usuario::with('facultad')->where('Activo', 1)->findOrFail($id);
        abort_if($this->esSU($x) && !$this->esSU($r->user()), 403, 'Usuario no disponible.');

        return response()->json([
            'nombreCompleto' => $x->nombre_con_grado,
            'facultad'       => $x->facultad?->Nombre ?? '—',
            'facultadClave'  => $x->facultad?->Clave,
            'matricula'      => $x->Matricula,
            'correo'         => $x->Correo,
            'rol'            => $x->rol_nombre,
            'genero'         => $x->genero_texto,
            'foto'           => $x->foto_src,
        ]);
    }

    public function conversaciones(Request $r): JsonResponse
    {
        $u = $r->user();

        $convs = ChatConversacion::where(fn ($q) => $q->where('IdUsuario1', $u->Id)->orWhere('IdUsuario2', $u->Id))
            ->orderByDesc('UltimoMensajeAt')->orderByDesc('Id')->get();

        $convs = $convs->reject(fn ($c) => $c->ocultaPara((int) $u->Id))->values();

        return response()->json($this->listar($convs, $u, false));
    }

    public function abrir(Request $r): JsonResponse
    {
        $u = $r->user();
        $data = $r->validate(['IdUsuario' => 'required|integer|exists:usuario,Id']);

        $otro = Usuario::where('Activo', 1)->findOrFail($data['IdUsuario']);
        abort_if((int) $otro->Id === (int) $u->Id, 422, 'No puedes chatear contigo mismo.');
        abort_if($this->esSU($otro) && !$this->esSU($u), 403, 'Usuario no disponible.');

        [$a, $b] = $u->Id < $otro->Id ? [$u->Id, $otro->Id] : [$otro->Id, $u->Id];
        $c = ChatConversacion::firstOrCreate(
            ['IdUsuario1' => $a, 'IdUsuario2' => $b],
            ['IniciadoPor' => $u->Id, 'CreatedAt' => now()]
        );

        return response()->json(['id' => $c->Id]);
    }

    /* ───────── mensajes ───────── */

    public function mensajes(Request $r, int $id): JsonResponse
    {
        $u = $r->user();
        $c = $this->propia($u, $id);
        $contacto = $this->contacto($u, $c);

        $msgs = ChatMensaje::with('respuesta')->where('IdConversacion', $c->Id)
            ->when($c->corteDe((int) $u->Id), fn ($q, $corte) => $q->where('CreatedAt', '>', $corte))
            ->orderByDesc('Id')->limit(200)->get()->reverse()->values();

        ChatMensaje::where('IdConversacion', $c->Id)->where('IdRemitente', '<>', $u->Id)
            ->whereNull('LeidoAt')->update(['LeidoAt' => now()]);

        $limite = now()->subMinutes(self::MINUTOS_EDICION);

        return response()->json([
            'mensajes' => $msgs->map(fn ($m) => [
                'id' => $m->Id, 'mio' => (int) $m->IdRemitente === (int) $u->Id,
                'texto' => $m->Mensaje, 'fecha' => $m->CreatedAt->format('d/m/y H:i'),
                'leido' => $m->LeidoAt !== null,
                'editado' => $m->EditadoAt !== null,
                'puedeEditar' => (int) $m->IdRemitente === (int) $u->Id && $m->CreatedAt->gt($limite),
                'respuesta' => $m->respuesta ? [
                    'id'    => $m->respuesta->Id,
                    'autor' => (int) $m->respuesta->IdRemitente === (int) $u->Id ? 'Tú' : $contacto['nombre'],
                    'texto' => mb_substr($m->respuesta->Mensaje, 0, 120),
                ] : null,
            ])->values(),
            'puedeResponder' => $this->puedeEscribir($u, $c),
            'contacto'       => $contacto,
        ]);
    }

    public function enviar(Request $r, int $id): JsonResponse
    {
        $u = $r->user();
        $c = $this->propia($u, $id);
        $data = $r->validate(['Mensaje' => 'required|string|max:2000', 'IdRespuesta' => 'nullable|integer']);

        abort_unless($this->puedeEscribir($u, $c), 403, 'No puedes responder en esta conversación.');

        // Solo se puede responder a un mensaje de esta misma conversación
        $idResp = empty($data['IdRespuesta']) ? null
            : ChatMensaje::where('IdConversacion', $c->Id)->where('Id', $data['IdRespuesta'])->value('Id');

        $m = ChatMensaje::create([
            'IdConversacion' => $c->Id, 'IdRemitente' => $u->Id, 'IdRespuesta' => $idResp,
            'Mensaje' => trim($data['Mensaje']), 'CreatedAt' => now(),
        ]);
        $c->update(['UltimoMensajeAt' => now()]);

        return response()->json(['id' => $m->Id]);
    }

    /** Solo el autor y solo dentro de los primeros 15 minutos. */
    public function editar(Request $r, int $id, int $mensaje): JsonResponse
    {
        $u = $r->user();
        $c = $this->propia($u, $id);
        $data = $r->validate(['Mensaje' => 'required|string|max:2000']);
        abort_unless($this->puedeEscribir($u, $c), 403, 'No puedes editar en esta conversación.');

        $m = ChatMensaje::where('IdConversacion', $c->Id)->where('IdRemitente', $u->Id)->findOrFail($mensaje);
        abort_if($m->CreatedAt->lt(now()->subMinutes(self::MINUTOS_EDICION)), 422,
            'Solo puedes editar un mensaje durante los primeros ' . self::MINUTOS_EDICION . ' minutos.');

        $texto = trim($data['Mensaje']);
        if ($texto !== $m->Mensaje) {
            $m->update(['Mensaje' => $texto, 'EditadoAt' => now()]);
        }

        return response()->json(['ok' => true]);
    }

    /** Borrado lógico: solo oculta la conversación para quien la elimina (la otra persona y supervisión la siguen viendo). */
    public function eliminar(Request $r, int $id): JsonResponse
    {
        $u = $r->user();
        $c = $this->propia($u, $id);
        $c->update([$c->columnaOculto((int) $u->Id) => now()]);

        return response()->json(['ok' => true]);
    }

    /* ───────── supervisión (solo superusuario) ───────── */

    public function supervision(Request $r): JsonResponse
    {
        abort_unless($this->esSU($r->user()), 403);

        $convs = ChatConversacion::orderByDesc('UltimoMensajeAt')->orderByDesc('Id')->limit(200)->get();

        return response()->json($this->listar($convs, $r->user(), true));
    }

    public function supervisionMensajes(Request $r, int $id): JsonResponse
    {
        abort_unless($this->esSU($r->user()), 403);

        $c = ChatConversacion::findOrFail($id);
        $msgs = ChatMensaje::where('IdConversacion', $c->Id)->orderByDesc('Id')->limit(200)->get()->reverse()->values();
        $nombres = Usuario::whereIn('Id', [$c->IdUsuario1, $c->IdUsuario2])->get()->keyBy('Id');

        return response()->json([
            'mensajes' => $msgs->map(fn ($m) => [
                'id' => $m->Id, 'mio' => false, 'leido' => $m->LeidoAt !== null,
                'autor' => $nombres[$m->IdRemitente]->nombre_corto ?? '—',
                'texto' => $m->Mensaje, 'fecha' => $m->CreatedAt->format('d/m/y H:i'),
                'editado' => $m->EditadoAt !== null, 'puedeEditar' => false,
                'respuesta' => $m->respuesta ? ['id' => $m->respuesta->Id, 'autor' => $nombres[$m->respuesta->IdRemitente]->nombre_corto ?? '—', 'texto' => mb_substr($m->respuesta->Mensaje, 0, 120)] : null,
            ])->values(),
            'puedeResponder' => false,
            'contacto'       => null,
        ]);
    }

    /* ───────── internos ───────── */

    /** Datos del otro participante (rol, facultad, conexión). El superusuario que inició aparece anónimo. */
    private function contacto(Usuario $u, ChatConversacion $c): array
    {
        $o = Usuario::with('facultad')->find($c->otro((int) $u->Id));
        if (!$o) {
            return ['id' => null, 'nombre' => '—', 'rol' => null, 'facultad' => null, 'foto' => null, 'enLinea' => false, 'conexion' => null, 'anonimo' => true];
        }
        if ($this->esSU($o) && !$this->esSU($u)) {
            return ['id' => null, 'nombre' => 'Desconocido', 'rol' => null, 'facultad' => null, 'foto' => null, 'enLinea' => false, 'conexion' => null, 'anonimo' => true];
        }

        $uc = $o->UltimaConexion;

        return [
            'id' => $o->Id, 'nombre' => $o->nombre_corto, 'rol' => $o->rol_nombre,
            'facultad' => $o->facultad?->Clave, 'foto' => $o->foto_src, 'anonimo' => false,
            'enLinea'  => $uc ? $uc->gt(now()->subMinutes(2)) : false,
            'conexion' => $uc?->format('d/m/y H:i'),
        ];
    }

    /** Lista para el panel izquierdo. $todas = vista de supervisión (nombres reales de ambos). */
    private function listar($convs, Usuario $u, bool $todas): array
    {
        $ids = $convs->pluck('Id');
        $usuarios = Usuario::with('facultad')
            ->whereIn('Id', $convs->flatMap(fn ($c) => [$c->IdUsuario1, $c->IdUsuario2])->unique())
            ->get()->keyBy('Id');

        $noLeidos = $todas ? collect() : ChatMensaje::whereIn('IdConversacion', $ids)
            ->where('IdRemitente', '<>', $u->Id)->whereNull('LeidoAt')
            ->selectRaw('IdConversacion, COUNT(*) AS n')->groupBy('IdConversacion')->pluck('n', 'IdConversacion');

        $ultimos = ChatMensaje::whereIn('Id', ChatMensaje::selectRaw('MAX(Id)')
            ->whereIn('IdConversacion', $ids)->groupBy('IdConversacion'))->get()->keyBy('IdConversacion');

        $yoSU = $this->esSU($u);

        return $convs->map(function ($c) use ($u, $todas, $usuarios, $noLeidos, $ultimos, $yoSU) {
            $rol = $fac = $foto = $uid = null;

            if ($todas) {
                $titulo = ($usuarios[$c->IdUsuario1]->nombre_corto ?? '—') . ' ↔ ' . ($usuarios[$c->IdUsuario2]->nombre_corto ?? '—');
            } else {
                $otro = $usuarios[$c->otro($u->Id)] ?? null;
                $anonimo = $otro && !$yoSU && in_array((int) $otro->Id, $this->superIds(), true);
                $titulo = $anonimo ? 'Desconocido' : ($otro->nombre_corto ?? '—');
                if ($otro && !$anonimo) {
                    $uid = $otro->Id; $rol = $otro->rol_nombre; $fac = $otro->facultad?->Clave; $foto = $otro->foto_src;
                }
            }
            $ult = $ultimos[$c->Id] ?? null;

            return [
                'id' => $c->Id, 'titulo' => $titulo, 'rol' => $rol, 'facultad' => $fac, 'foto' => $foto, 'userId' => $uid,
                'ultimo' => $ult ? mb_substr($ult->Mensaje, 0, 60) : 'Sin mensajes',
                'fecha' => $ult?->CreatedAt?->format('d/m H:i'),
                'noLeidos' => (int) ($noLeidos[$c->Id] ?? 0),
            ];
        })->values()->all();
    }

    private function propia(Usuario $u, int $id): ChatConversacion
    {
        $c = ChatConversacion::findOrFail($id);
        abort_unless($c->participa((int) $u->Id), 404);

        return $c;
    }

    private function puedeEscribir(Usuario $u, ChatConversacion $c): bool
    {
        $otro = $c->otro((int) $u->Id);

        // Con un superusuario solo se puede responder si él inició la conversación
        if (in_array($otro, $this->superIds(), true) && !$this->esSU($u)) {
            return (int) $c->IniciadoPor === $otro;
        }

        return true;
    }

    private function superIds(): array
    {
        return $this->su ??= AsignacionPermiso::where('Activo', 1)->whereNull('DeletedAt')
            ->where(fn ($q) => $q->where('Clave', 'APTSU')
                ->orWhereHas('nivel.modulo', fn ($m) => $m->where('Clave', config('modulos.modulo_global'))))
            ->pluck('IdUsuario')->map(fn ($i) => (int) $i)->unique()->values()->all();
    }

    private function esSU(Usuario $u): bool
    {
        return in_array((int) $u->Id, $this->superIds(), true);
    }
}
