@extends('layouts.app')

@section('title', 'Mis comisiones · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-3">
        <h2 class="font-syne text-dark mb-1">Mis comisiones</h2>
        <p class="text-muted small mb-0">Las que te asignaron, solas o en grupo. {{ $total }} en total · mostrando {{ $comisiones->count() }}.</p>
    </div>

    <form method="GET" class="d-flex flex-wrap gap-2 align-items-center mb-4">
        {{-- Estado --}}
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
        @if (collect(request()->only(['estado','constancia','fecha','desde','hasta']))->filter()->isNotEmpty())
            <a href="{{ route('profesor.panel') }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-xmark me-1"></i> Limpiar</a>
        @endif
    </form>

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
        @forelse ($comisiones as $c)
            <div class="col">
                <div class="card card-hover card-estado {{ $c->clase_estado }} h-100 p-3 position-relative">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <span class="font-monospace small">{{ $c->NOficio }}</span>
                        <span class="badge rounded-pill badge-estado {{ $c->clase_estado }}">{{ $c->estado->Nombre }}</span>
                    </div>
                    <a href="{{ route('profesor.show', $c->Id) }}" class="stretched-link text-decoration-none">
                        <h6 class="font-syne text-dark mb-1">{{ $c->Asunto }}</h6>
                    </a>
                    <div class="small text-body-secondary mb-2">{{ $c->tipo->Nombre }}</div>
                    <div class="small mt-auto">
                        <i class="fa-regular fa-calendar me-1"></i>{{ $c->FechaInicio->format('d/m/y') }}@if ($c->FechaFin) – {{ $c->FechaFin->format('d/m/y') }}@else <span class="text-body-secondary">· sin fecha final</span>@endif
                    </div>
                    @if ($c->profesores->count() > 1)
                        <div class="small text-body-secondary"><i class="fa-solid fa-users me-1"></i>Grupo de {{ $c->profesores->count() }}</div>
                    @endif
                    <div class="mt-2">
                        @if ($c->mi_constancia)
                            <span class="badge rounded-pill bg-success-subtle text-success-emphasis"><i class="fa-solid fa-certificate me-1"></i>Con constancia</span>
                        @elseif ($c->IdEstado == 3)
                            <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis">Constancia pendiente</span>
                        @else
                            <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">Sin constancia</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-body-secondary">
                <i class="fa-solid fa-inbox fs-1 opacity-50 mb-2 d-block"></i> No hay comisiones con estos filtros.
            </div>
        @endforelse
    </div>
</div>
@endsection
