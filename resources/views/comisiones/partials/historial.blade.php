{{-- Historial de estados con sus descripciones. Requiere $c con logs.usuario --}}
<ul class="linea-tiempo">
    @forelse ($c->logs as $l)
        <li>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="fw-semibold small">{{ $l->Accion }}</span>
                <span class="badge rounded-pill badge-estado {{ \App\Models\Comision::CLASES[$l->IdEstado] ?? '' }}">{{ $l->estado->Nombre ?? '' }}</span>
            </div>
            @if ($l->Descripcion)
                <div class="small border-start border-3 ps-2 my-1 text-body-secondary">{{ $l->Descripcion }}</div>
            @endif
            <div class="text-xs text-body-tertiary">
                {{ $l->usuario?->nombre_corto ?? 'Sistema' }} · {{ $l->CreatedAt?->format('d/m/y H:i') }}
            </div>
        </li>
    @empty
        <li class="small text-body-secondary">Sin movimientos registrados.</li>
    @endforelse
</ul>
