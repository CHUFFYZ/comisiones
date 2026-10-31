@extends('layouts.app')

@section('title', 'Comisiones cerradas · Super usuario')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="font-syne text-dark mb-1"><i class="fa-solid fa-user-shield me-2"></i>Comisiones cerradas</h2>
            <p class="text-muted small mb-0">Reabrir permite que las secretarias vuelvan a modificarla. Eliminar borra todo (constancias, reportes y archivos).</p>
        </div>
        <form action="{{ route('su.bloquear') }}" method="POST" class="m-0" data-sin-carga>
            @csrf
            <button class="btn btn-secondary btn-sm"><i class="fa-solid fa-lock me-1"></i> Bloquear</button>
        </form>
    </div>

    <form method="GET" class="mb-3" data-sin-carga>
        <div class="input-group rounded-pill w-auto" style="max-width:360px">
            <span class="input-group-text bg-transparent"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Comisión o asunto">
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Comisión</th><th>Facultad</th><th>Asunto</th><th class="d-none d-md-table-cell">Profesores</th><th>Cerrada</th><th class="text-end">Acciones</th></tr></thead>
                <tbody>
                @forelse ($cerradas as $c)
                    <tr>
                        <td class="font-monospace small"><a href="{{ route('comisiones.show', $c->Id) }}">{{ $c->NOficio }}</a></td>
                        <td>{{ $c->facultad->Clave ?? '—' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($c->Asunto, 50) }}</td>
                        <td class="d-none d-md-table-cell small">{{ $c->profesores->pluck('nombre_corto')->join(', ') }}</td>
                        <td class="small text-nowrap">{{ $c->CerradaAt?->format('d/m/y H:i') }}<div class="text-xs text-body-secondary">{{ $c->cierreUsuario?->nombre_corto }}</div></td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                <form action="{{ route('su.reabrir', $c->Id) }}" method="POST" class="m-0"
                                      data-confirm="¿Reabrir {{ $c->NOficio }}? Volverá a poder modificarse." data-confirm-titulo="Reabrir comisión" data-confirm-boton="Reabrir">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-ghost is-success btn-sm"><i class="fa-solid fa-lock-open me-1"></i> Reabrir</button>
                                </form>
                                <form action="{{ route('su.eliminar', $c->Id) }}" method="POST" class="m-0" data-confirm-boton="Eliminar definitivamente"
                                      data-doble-confirm="¿Eliminar {{ $c->NOficio }} y todos sus archivos?|||ÚLTIMA CONFIRMACIÓN: no se puede deshacer. ¿Eliminar {{ $c->NOficio }} para siempre?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-ghost is-danger btn-sm"><i class="fa-solid fa-trash me-1"></i> Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-5 text-body-secondary"><i class="fa-solid fa-inbox fs-1 opacity-50 mb-2 d-block"></i> No hay comisiones cerradas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($cerradas->hasPages())<div class="card-footer bg-white">{{ $cerradas->links() }}</div>@endif
    </div>
</div>
@endsection
