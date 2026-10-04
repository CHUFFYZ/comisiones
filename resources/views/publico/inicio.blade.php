<!-- resources/views/publico/inicio.blade.php -->
@extends('layouts.app')

@section('title', 'Inicio · SisComisiones')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-syne text-dark mb-1">Panel Principal</h2>
            <p class="text-muted small mb-0">Bienvenido al sistema de comisiones de la FCI.</p>
        </div>
    </div>

    <div class="card p-4">
        <div class="d-flex align-items-center gap-3">
            <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle fs-3">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <h5 class="mb-1 font-syne">Interfaz Principal Inicializada</h5>
                <p class="text-muted mb-0 small">Estructura base, Bootstrap 5.3, Layout global, Header, Sidebar y Footer cargados correctamente.</p>
            </div>
        </div>
    </div>
</div>
@endsection