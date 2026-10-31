@extends('layouts.app')

@section('title', 'Cierre de comisiones · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h2 class="font-syne text-dark mb-1">Cierre de comisiones</h2>
        <p class="text-muted small mb-0">Una comisión cerrada ya no puede modificarse ni eliminarse (solo el super usuario puede reabrirla). Para cerrarla debe estar finalizada, con todas las constancias, y todo firmado y sellado.</p>
    </div>

    @if ($errors->any() && !$errors->has('general'))
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-triangle-exclamation"></i><div>{{ $errors->first() }}</div></div>
    @endif

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><a class="nav-link {{ $ver === 'por-cerrar' ? 'active' : '' }}" href="{{ route('cierre.index') }}">Por cerrar</a></li>
        <li class="nav-item"><a class="nav-link {{ $ver === 'cerradas' ? 'active' : '' }}" href="{{ route('cierre.index', ['ver' => 'cerradas']) }}"><i class="fa-solid fa-lock me-1"></i>Cerradas</a></li>
    </ul>

    @if ($ver === 'cerradas')
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Comisión</th><th>Asunto</th><th class="d-none d-md-table-cell">Profesores</th><th>Cerrada</th></tr></thead>
                    <tbody>
                    @forelse ($lista as $c)
                        <tr class="fila-click" data-href="{{ route('comisiones.show', $c->Id) }}">
                            <td class="font-monospace small">{{ $c->NOficio }}</td>
                            <td>{{ $c->Asunto }}</td>
                            <td class="d-none d-md-table-cell">{{ $c->profesores->pluck('nombre_corto')->join(', ') }}</td>
                            <td class="small text-nowrap">{{ $c->CerradaAt?->format('d/m/y H:i') }}<div class="text-xs text-body-secondary">{{ $c->cierreUsuario?->nombre_corto }}</div></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-5 text-body-secondary"><i class="fa-solid fa-inbox fs-1 opacity-50 mb-2 d-block"></i> No hay comisiones cerradas.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if ($lista->hasPages())<div class="card-footer bg-white">{{ $lista->links() }}</div>@endif
        </div>
    @else
        @php $puedeCerrar = app(\App\Services\AccesoService::class)->puede(auth()->user(), 'MSC', 'actualizar'); @endphp
        <form action="{{ route('cierre.store') }}" method="POST" class="card"
              data-confirm="Las comisiones seleccionadas se cerrarán: nadie podrá modificarlas ni eliminarlas salvo el super usuario. ¿Continuar?"
              data-confirm-titulo="Cerrar comisiones" data-confirm-boton="Cerrar">
            @csrf
            @if ($puedeCerrar)
                <div class="p-3 border-bottom"><button class="btn btn-primary btn-sm"><i class="fa-solid fa-lock me-1"></i> Cerrar seleccionadas</button></div>
            @endif
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr>@if ($puedeCerrar)<th style="width:40px"></th>@endif<th>Comisión</th><th>Asunto</th><th class="d-none d-md-table-cell">Profesores</th><th>Requisitos</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($lista as $x)
                        @php $c = $x['c']; @endphp
                        <tr>
                            @if ($puedeCerrar)<td><input type="checkbox" class="form-check-input" name="ids[]" value="{{ $c->Id }}" @disabled($x['motivo']) aria-label="Seleccionar"></td>@endif
                            <td class="font-monospace small">{{ $c->NOficio }}</td>
                            <td>{{ $c->Asunto }}</td>
                            <td class="d-none d-md-table-cell">{{ $c->profesores->pluck('nombre_corto')->join(', ') }}</td>
                            <td class="small">
                                @if ($x['motivo'])<span class="badge rounded-pill bg-warning-subtle text-warning-emphasis">{{ $x['motivo'] }}</span>
                                @else<span class="badge rounded-pill bg-success-subtle text-success-emphasis"><i class="fa-solid fa-check me-1"></i>Lista para cerrar</span>@endif
                            </td>
                            <td class="text-end"><a href="{{ route('comisiones.show', $c->Id) }}" class="btn btn-ghost btn-sm"><i class="fa-solid fa-eye me-1"></i> Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-body-secondary"><i class="fa-solid fa-inbox fs-1 opacity-50 mb-2 d-block"></i> No hay comisiones finalizadas por cerrar.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    @endif
</div>
@endsection
