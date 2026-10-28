<?php

namespace App\Http\Controllers;

use App\Models\ChatConversacion;
use App\Models\ChatMensaje;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Busca texto dentro de una conversación del usuario. */
class ChatBusquedaController extends Controller
{
    public function __invoke(Request $r, int $id): JsonResponse
    {
        $u = $r->user();
        $c = ChatConversacion::findOrFail($id);
        abort_unless($c->participa((int) $u->Id), 404);

        $q = trim((string) $r->query('q', ''));
        if (mb_strlen($q) < 2) return response()->json([]);

        $msgs = ChatMensaje::where('IdConversacion', $c->Id)
            ->when($c->corteDe((int) $u->Id), fn ($w, $corte) => $w->where('CreatedAt', '>', $corte))
            ->where('Mensaje', 'like', '%' . addcslashes($q, '\\%_') . '%')
            ->orderByDesc('Id')->limit(50)->get();

        return response()->json($msgs->map(fn ($m) => [
            'id' => $m->Id, 'mio' => (int) $m->IdRemitente === (int) $u->Id,
            'texto' => $m->Mensaje, 'fecha' => $m->CreatedAt->format('d/m/y H:i'),
        ])->values());
    }
}
