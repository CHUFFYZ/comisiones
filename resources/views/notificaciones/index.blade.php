@extends('layouts.app')

@section('title', 'Notificaciones · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="font-syne text-dark mb-1">Notificaciones</h2>
            <p class="text-muted small mb-0">Asignaciones, cambios de estado y comisiones por vencer.</p>
        </div>
        <form action="{{ route('notificaciones.leer_todas') }}" method="POST">
            @csrf
            <button class="btn btn-secondary btn-sm"><i class="fa-solid fa-check-double me-1"></i> Marcar todas como leídas</button>
        </form>
    </div>

    <div class="card">
        <div class="list-group list-group-flush">
            @forelse ($notificaciones as $n)
                <a href="{{ route('notificaciones.ir', $n->Id) }}" class="list-group-item list-group-item-action notif-item px-4 py-3 {{ $n->LeidaAt ? '' : 'notif-nueva' }}">
                    <div class="d-flex justify-content-between gap-2">
                        <span class="fw-semibold small">{{ $n->Titulo }}</span>
                        <span class="text-xs text-body-tertiary text-nowrap">{{ $n->CreatedAt->format('d/m/y H:i') }}</span>
                    </div>
                    @if ($n->Mensaje)<div class="small text-body-secondary">{{ $n->Mensaje }}</div>@endif
                </a>
            @empty
                <div class="text-center py-5 text-body-secondary">
                    <i class="fa-solid fa-bell-slash fs-1 opacity-50 mb-2 d-block"></i> Sin notificaciones.
                </div>
            @endforelse
        </div>
        @if ($notificaciones->hasPages())
            <div class="card-footer bg-white">{{ $notificaciones->links() }}</div>
        @endif
    </div>
</div>
@endsection
