@extends('layouts.app')

@section('title', 'Mis comisiones · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="font-syne text-dark mb-1">Mis comisiones</h2>
            <p class="text-muted small mb-0">Las que te asignaron, solas o en grupo. {{ $total }} en total.</p>
        </div>
        <form method="GET">
            <select name="estado" class="form-select rounded-pill w-auto" onchange="this.form.submit()">
                <option value="">Todos los estados ({{ $total }})</option>
                @foreach ($estados as $e)
                    <option value="{{ $e->Id }}" @selected(request('estado') == $e->Id)>{{ $e->Nombre }} ({{ $conteo[$e->Id] ?? 0 }})</option>
                @endforeach
            </select>
        </form>
    </div>

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
                        <i class="fa-regular fa-calendar me-1"></i>{{ $c->FechaInicio->format('d/m/y') }}@if ($c->FechaFin) – {{ $c->FechaFin->format('d/m/y') }}@endif
                    </div>
                    @if ($c->profesores->count() > 1)
                        <div class="small text-body-secondary"><i class="fa-solid fa-users me-1"></i>Grupo de {{ $c->profesores->count() }}</div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-body-secondary">
                <i class="fa-solid fa-inbox fs-1 opacity-50 mb-2 d-block"></i> No tienes comisiones{{ request('estado') ? ' en este estado' : ' asignadas' }}.
            </div>
        @endforelse
    </div>
</div>
@endsection
