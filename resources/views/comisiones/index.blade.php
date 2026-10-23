@extends('layouts.app')

@section('title', ($anuladas ? 'Comisiones anuladas' : 'Comisiones') . ' · CATI')

@section('content')
@php $badge = [1 => 'estado-no-iniciado', 2 => 'estado-proceso', 3 => 'estado-terminado']; @endphp
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="font-syne text-dark mb-1">{{ $anuladas ? 'Comisiones anuladas' : 'Comisiones' }}</h2>
            <p class="text-muted small mb-0">Solo lo que tienes asignado dentro de tu facultad.</p>
        </div>
        @unless ($anuladas)
            @puede('MSC', 'crear')
                <a href="{{ route('comisiones.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Nueva comisión</a>
            @endpuede
        @endunless
    </div>

    <form method="GET" class="d-flex flex-wrap gap-2 mb-3">
        <div class="input-group rounded-pill w-auto">
            <span class="input-group-text bg-transparent"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Oficio o asunto">
        </div>
        <select name="tipo" class="form-select rounded-pill w-auto" onchange="this.form.submit()">
            <option value="">Todos los tipos</option>
            @foreach ($tipos as $t)
                <option value="{{ $t->Id }}" @selected(request('tipo') == $t->Id)>{{ $t->Nombre }}</option>
            @endforeach
        </select>
        <select name="estado" class="form-select rounded-pill w-auto" onchange="this.form.submit()">
            <option value="">Todos los estados</option>
            @foreach ($estados as $e)
                <option value="{{ $e->Id }}" @selected(request('estado') == $e->Id)>{{ $e->Nombre }}</option>
            @endforeach
        </select>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Oficio</th>
                        <th>Asunto</th>
                        <th class="d-none d-lg-table-cell">Tipo</th>
                        <th class="d-none d-md-table-cell">Profesores</th>
                        <th class="d-none d-md-table-cell">Fechas</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($comisiones as $c)
                    <tr>
                        <td class="font-monospace small">{{ $c->NOficio }}</td>
                        <td>{{ $c->Asunto }}</td>
                        <td class="d-none d-lg-table-cell">{{ $c->tipo->Nombre }}</td>
                        <td class="d-none d-md-table-cell">{{ $c->profesores->pluck('nombre_completo')->join(', ') }}</td>
                        <td class="d-none d-md-table-cell text-nowrap">
                            {{ $c->FechaInicio->format('d/m/y') }}@if ($c->FechaFin) – {{ $c->FechaFin->format('d/m/y') }}@endif
                        </td>
                        <td><span class="badge rounded-pill badge-estado {{ $badge[$c->IdEstado] ?? '' }}">{{ $c->estado->Nombre }}</span></td>
                        <td>
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('comisiones.pdf', $c->Id) }}" target="_blank" class="btn btn-ghost is-brand btn-sm" title="Ver oficio">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>

                                @unless ($anuladas)
                                    @puede('MSC', 'actualizar')
                                        @if ($c->IdEstado < 3)
                                            <form action="{{ route('comisiones.estado', $c->Id) }}" method="POST" class="m-0">
                                                @csrf @method('PATCH')
                                                <button class="btn btn-ghost btn-sm" title="Avanzar estado"><i class="fa-solid fa-forward"></i></button>
                                            </form>
                                        @elseif ($c->constancia)
                                            <a href="{{ route('constancias.pdf', $c->constancia->Id) }}" target="_blank" class="btn btn-ghost is-success btn-sm" title="Ver constancia">
                                                <i class="fa-solid fa-certificate"></i>
                                            </a>
                                        @else
                                            <form action="{{ route('constancias.store', $c->Id) }}" method="POST" class="m-0">
                                                @csrf
                                                <button class="btn btn-success btn-sm"><i class="fa-solid fa-certificate me-1"></i> Constancia</button>
                                            </form>
                                        @endif
                                    @endpuede

                                    @puede('MSC', 'eliminar')
                                        <button type="button" class="btn btn-ghost is-danger btn-sm" title="Anular"
                                                data-bs-toggle="modal" data-bs-target="#modalAnular"
                                                data-action="{{ route('comisiones.anular', $c->Id) }}" data-oficio="{{ $c->NOficio }}">
                                            <i class="fa-solid fa-ban"></i>
                                        </button>
                                    @endpuede
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-body-secondary">
                            <i class="fa-solid fa-inbox fs-1 opacity-50 mb-2 d-block"></i> Sin comisiones.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($comisiones->hasPages())
            <div class="card-footer bg-white">{{ $comisiones->links() }}</div>
        @endif
    </div>
</div>

@unless ($anuladas)
<div class="modal fade" id="modalAnular" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form method="POST" class="modal-content" id="formAnular">
            @csrf @method('DELETE')
            <div class="modal-header"><h5 class="modal-title">Anular comisión</h5></div>
            <div class="modal-body small">¿Anular la comisión <strong class="font-monospace" id="anularOficio"></strong>?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">Anular</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
    document.getElementById('modalAnular').addEventListener('show.bs.modal', e => {
        document.getElementById('formAnular').action = e.relatedTarget.dataset.action;
        document.getElementById('anularOficio').textContent = e.relatedTarget.dataset.oficio;
    });
</script>
@endpush
@endunless
@endsection