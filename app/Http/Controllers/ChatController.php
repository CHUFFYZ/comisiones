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

    public function index(Request $r)
    {
        return view('chat.index', ['esSU' => $this->esSU($r->user())]);
    }

    /* ───────── directorio y conversaciones ───────── */

    public function usuarios(Request $r): JsonResponse
    {
        $u = $r->user();
        $q = trim((string) $r->q);

        $users = Usuario::where('Activo', 1)->where('Id', '<>', $u->Id)
            ->when(!$this->esSU($u), fn ($w) => $w->whereNotIn('Id', $this->superIds()))
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x
                ->where('Nombre', 'like', "%$q%")->orWhere('Apellido', 'like', "%$q%")->orWhere('Matricula', 'like', "%$q%")))
            ->orderBy('Nombre')->limit(30)->get();

        return response()->json($users->map(fn ($x) => [
            'id' => $x->Id, 'nombre' => $x->nombre_corto, 'rol' => $x->Rol,
        ])->values());
    }

    public function conversaciones(Request $r): JsonResponse
    {
        $u = $r->user();

        $convs = ChatConversacion::where(fn ($q) => $q->where('IdUsuario1', $u->Id)->orWhere('IdUsuario2', $u->Id))
            ->orderByDesc('UltimoMensajeAt')->orderByDesc('Id')->get();

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

        $q = ChatMensaje::where('IdConversacion', $c->Id);
        $msgs = $r->filled('despues')
            ? $q->where('Id', '>', (int) $r->despues)->orderBy('Id')->limit(200)->get()
            : $q->orderByDesc('Id')->limit(200)->get()->reverse()->values();

        ChatMensaje::where('IdConversacion', $c->Id)->where('IdRemitente', '<>', $u->Id)
            ->whereNull('LeidoAt')->update(['LeidoAt' => now()]);

        return response()->json([
            'mensajes'       => $msgs->map(fn ($m) => [
                'id' => $m->Id, 'mio' => (int) $m->IdRemitente === (int) $u->Id,
                'texto' => $m->Mensaje, 'fecha' => $m->CreatedAt->format('d/m/y H:i'),
            ])->values(),
            'puedeResponder' => $this->puedeEscribir($u, $c),
        ]);
    }

    public function enviar(Request $r, int $id): JsonResponse
    {
        $u = $r->user();
        $c = $this->propia($u, $id);
        $data = $r->validate(['Mensaje' => 'required|string|max:2000']);

        abort_unless($this->puedeEscribir($u, $c), 403, 'No puedes responder en esta conversación.');

        $m = ChatMensaje::create([
            'IdConversacion' => $c->Id, 'IdRemitente' => $u->Id,
            'Mensaje' => trim($data['Mensaje']), 'CreatedAt' => now(),
        ]);
        $c->update(['UltimoMensajeAt' => now()]);

        return response()->json(['id' => $m->Id]);
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
                'id' => $m->Id, 'mio' => false,
                'autor' => $nombres[$m->IdRemitente]->nombre_corto ?? '—',
                'texto' => $m->Mensaje, 'fecha' => $m->CreatedAt->format('d/m/y H:i'),
            ])->values(),
            'puedeResponder' => false,
        ]);
    }

    /* ───────── internos ───────── */

    /** Lista para el panel izquierdo. $todas = vista de supervisión (nombres reales de ambos). */
    private function listar($convs, Usuario $u, bool $todas): array
    {
        $ids = $convs->pluck('Id');
        $usuarios = Usuario::whereIn('Id', $convs->flatMap(fn ($c) => [$c->IdUsuario1, $c->IdUsuario2])->unique())
            ->get()->keyBy('Id');

        $noLeidos = $todas ? collect() : ChatMensaje::whereIn('IdConversacion', $ids)
            ->where('IdRemitente', '<>', $u->Id)->whereNull('LeidoAt')
            ->selectRaw('IdConversacion, COUNT(*) AS n')->groupBy('IdConversacion')->pluck('n', 'IdConversacion');

        $ultimos = ChatMensaje::whereIn('Id', ChatMensaje::selectRaw('MAX(Id)')
            ->whereIn('IdConversacion', $ids)->groupBy('IdConversacion'))->get()->keyBy('IdConversacion');

        $yoSU = $this->esSU($u);

        return $convs->map(function ($c) use ($u, $todas, $usuarios, $noLeidos, $ultimos, $yoSU) {
            if ($todas) {
                $titulo = ($usuarios[$c->IdUsuario1]->nombre_corto ?? '—') . ' ↔ ' . ($usuarios[$c->IdUsuario2]->nombre_corto ?? '—');
                $rol = null;
            } else {
                $otro = $usuarios[$c->otro($u->Id)] ?? null;
                // El superusuario aparece como "Desconocido" para quien recibe su mensaje
                $anonimo = $otro && !$yoSU && in_array((int) $otro->Id, $this->superIds(), true);
                $titulo = $anonimo ? 'Desconocido' : ($otro->nombre_corto ?? '—');
                $rol = $anonimo ? null : ($otro->Rol ?? null);
            }
            $ult = $ultimos[$c->Id] ?? null;

            return [
                'id' => $c->Id, 'titulo' => $titulo, 'rol' => $rol,
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
