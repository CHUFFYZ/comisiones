@extends('layouts.app')

@section('title', ($anuladas ? 'Comisiones anuladas' : 'Comisiones') . ' · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="font-syne text-dark mb-1">{{ $anuladas ? 'Comisiones anuladas' : 'Comisiones' }}</h2>
            <p class="text-muted small mb-0">
                {{ $anuladas ? 'Puedes revertir una anulación para devolver la comisión a la lista activa.' : 'Haz clic en una comisión para ver todo su detalle.' }}
            </p>
        </div>
        @unless ($anuladas)
            @puede('MSC', 'crear')
                <a href="{{ route('comisiones.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Nueva comisión</a>
            @endpuede
        @endunless
    </div>

    <form method="GET" class="d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="input-group rounded-pill w-auto">
            <span class="input-group-text bg-transparent"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Oficio, asunto o profesor">
        </div>
        <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis align-self-center px-3 py-2"><i class="fa-solid fa-layer-group me-1"></i>Total: {{ $totalAlcance }}</span>
        @if ($comisiones->total() !== $totalAlcance)<span class="small text-body-secondary align-self-center">{{ $comisiones->total() }} coincidencias</span>@endif
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
        <select name="constancia" class="form-select rounded-pill w-auto" onchange="this.form.submit()">
            <option value="">Con y sin constancia</option>
            <option value="con" @selected(request('constancia') === 'con')>Con constancia</option>
            <option value="sin" @selected(request('constancia') === 'sin')>Sin constancia</option>
        </select>
        <select name="fecha" class="form-select rounded-pill w-auto" onchange="this.form.submit()">
            <option value="">Con o sin fecha final</option>
            <option value="sin" @selected(request('fecha') === 'sin')>Sin fecha final</option>
        </select>
        <div class="d-flex align-items-center gap-1 small text-body-secondary">
            Inicio desde
            <input type="date" name="desde" value="{{ request('desde') }}" class="form-control form-control-sm rounded-pill w-auto" onchange="this.form.submit()">
            hasta
            <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control form-control-sm rounded-pill w-auto" onchange="this.form.submit()">
        </div>
        @if (request()->hasAny(['q','tipo','estado','constancia','fecha','desde','hasta']) && collect(request()->only(['q','tipo','estado','constancia','fecha','desde','hasta']))->filter()->isNotEmpty())
            <a href="{{ url()->current() }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-xmark me-1"></i> Limpiar</a>
        @endif
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
                        <th>Constancia</th>
                        @if ($anuladas)<th class="text-end">Acciones</th>@endif
                    </tr>
                </thead>
                <tbody>
                @forelse ($comisiones as $c)
                    <tr class="fila-click" data-href="{{ route('comisiones.show', $c->Id) }}">
                        <td class="font-monospace small">{{ $c->NOficio }}</td>
                        <td>{{ $c->Asunto }}</td>
                        <td class="d-none d-lg-table-cell">{{ $c->tipo->Nombre }}</td>
                        <td class="d-none d-md-table-cell">{{ $c->profesores->pluck('nombre_corto')->join(', ') }}</td>
                        <td class="d-none d-md-table-cell text-nowrap">
                            {{ $c->FechaInicio->format('d/m/y') }}@if ($c->FechaFin) – {{ $c->FechaFin->format('d/m/y') }}@else <span class="text-body-tertiary">· sin fecha final</span>@endif
                        </td>
                        <td><span class="badge rounded-pill badge-estado {{ $c->clase_estado }}">{{ $c->estado->Nombre }}</span></td>
                        <td>
                            @php $ec = $c->estado_constancias; @endphp
                            @if ($ec === 'completa')
                                <span class="badge rounded-pill bg-success-subtle text-success-emphasis"><i class="fa-solid fa-certificate me-1"></i>Con constancia</span>
                            @elseif ($ec === 'parcial')
                                <span class="badge rounded-pill bg-info-subtle text-info-emphasis">Parcial {{ $c->profesores_cubiertos->count() }}/{{ $c->profesores->count() }}</span>
                            @elseif ($c->IdEstado == 3)
                                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis">Sin constancia</span>
                            @else
                                <span class="text-body-tertiary">—</span>
                            @endif
                        </td>

                        @if ($anuladas)
                            <td>
                                <div class="d-flex justify-content-end">
                                    <form action="{{ route('comisiones.restaurar', $c->Id) }}" method="POST" class="m-0">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-ghost is-success btn-sm"><i class="fa-solid fa-rotate-left me-1"></i> Revertir</button>
                                    </form>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $anuladas ? 8 : 7 }}" class="text-center py-5 text-body-secondary">
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
@endsection
