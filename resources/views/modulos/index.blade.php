@extends('layouts.app')

@section('title', 'Mis módulos · CATI')

@section('content')
@php $puedeFirmar = app(\App\Services\AccesoService::class)->puedeFirmar(auth()->user()); @endphp
<div class="container-fluid p-0">
    <div class="mb-4">
        <h2 class="font-syne text-dark mb-1">Mis módulos</h2>
        <p class="text-muted small mb-0">Hola, {{ auth()->user()->nombre_corto }}. Elige un módulo para continuar.</p>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
        @if ($esProfesor)
            <div class="col">
                <a href="{{ route('profesor.panel') }}" class="text-decoration-none">
                    <div class="card card-hover h-100 p-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle fs-4">
                                <i class="fa-solid fa-chalkboard-user"></i>
                            </div>
                            <div>
                                <h5 class="font-syne mb-1">Mis comisiones</h5>
                                <p class="text-muted small mb-0">Comisiones que te asignaron, solo o en grupo.</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif

        @if ($puedeFirmar)
            <div class="col">
                <a href="{{ route('firmas.index') }}" class="text-decoration-none">
                    <div class="card card-hover h-100 p-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle fs-4">
                                <i class="fa-solid fa-signature"></i>
                            </div>
                            <div>
                                <h5 class="font-syne mb-1">Firmas y sellos</h5>
                                <p class="text-muted small mb-0">Firma y sella oficios y constancias.</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif

        @forelse ($modulos as $modulo)
            @include('modulos.partials.tarjeta', ['modulo' => $modulo])
        @empty
            @unless ($esProfesor || $puedeFirmar)
                <div class="col-12 text-center py-5 text-body-secondary">
                    <i class="fa-solid fa-inbox fs-1 opacity-50 mb-2"></i>
                    <p class="mb-0">No tienes módulos asignados. Contacta al administrador.</p>
                </div>
            @endunless
        @endforelse
    </div>
</div>
@endsection
