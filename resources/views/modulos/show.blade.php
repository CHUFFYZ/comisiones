@extends('layouts.app')

@section('title', $modulo->Nombre . ' · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-syne text-dark mb-1">{{ $modulo->Nombre }}</h2>
            <p class="text-muted small mb-0">{{ $modulo->Descripcion }}</p>
        </div>
        <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis font-monospace">
            Permiso: {{ $permiso }}
        </span>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3">
        @forelse ($opciones as $op)
            <div class="col">
                <a href="{{ $op['url'] ?? '#' }}" class="text-decoration-none {{ $op['url'] ? '' : 'pe-none opacity-50' }}">
                    <div class="card card-hover h-100 p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle">
                                <i class="fa-solid {{ $op['icono'] }}"></i>
                            </div>
                            <div>
                                <div class="font-syne fw-bold text-dark">{{ $op['nombre'] }}</div>
                                @unless ($op['url']) <small class="text-muted">Próximamente</small> @endunless
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 text-muted">Este módulo aún no tiene opciones disponibles para tu permiso.</div>
        @endforelse
    </div>
</div>
@endsection