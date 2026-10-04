@extends('layouts.app')

@section('title', $modulo->Nombre . ' · FCI CATI')

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

    <div class="card p-4">
        <p class="mb-0 text-muted">Contenedor del módulo <strong>{{ $modulo->Clave }}</strong>. Aquí irá su contenido.</p>
    </div>
</div>
@endsection