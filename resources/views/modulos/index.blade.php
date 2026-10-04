@extends('layouts.app')

@section('title', 'Mis módulos · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h2 class="font-syne text-dark mb-1">Mis módulos</h2>
        <p class="text-muted small mb-0">Hola, {{ auth()->user()->Nombre }}. Elige un módulo para continuar.</p>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
        @forelse ($modulos as $modulo)
            @include('modulos.partials.tarjeta', ['modulo' => $modulo])
        @empty
            <div class="col-12 text-center py-5 text-body-secondary">
                <i class="fa-solid fa-inbox fs-1 opacity-50 mb-2"></i>
                <p class="mb-0">No tienes módulos asignados. Contacta al administrador.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection