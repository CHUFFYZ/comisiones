@extends('layouts.app')

@section('title', 'Reportes · ' . $modulo->Nombre)

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <a href="{{ route('modulos.show', $clave) }}" class="small"><i class="fa-solid fa-arrow-left me-1"></i> Resumen</a>
        <h2 class="font-syne text-dark mt-1 mb-1">Reportes · {{ $modulo->Nombre }}</h2>
        <p class="text-muted small mb-0">Al generar el reporte podrás elegir si exportarlo como PDF, Excel o CSV.</p>
    </div>

    <div class="alert alert-info small d-flex align-items-center gap-2">
        <i class="fa-solid fa-circle-info"></i><div><strong>Alcance de tus reportes:</strong> {{ $alcance }}</div>
    </div>

    <form action="{{ route('reportes.imprimir', $clave) }}" method="GET" class="card p-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="reporte">Tipo de reporte</label>
                <select id="reporte" name="reporte" class="form-select">
                    @foreach ($tipos as $k => $nombre)<option value="{{ $k }}">{{ $nombre }}</option>@endforeach
                </select>
            </div>

            @if ($clave === 'MSC')
                <div class="col-md-3">
                    <label class="form-label" for="estado">Estado</label>
                    <select id="estado" name="estado" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($estados as $e)<option value="{{ $e->Id }}">{{ $e->Nombre }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="tipo">Tipo de comisión</label>
                    <select id="tipo" name="tipo" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($tiposComision as $t)<option value="{{ $t->Id }}">{{ $t->Nombre }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="desde">Inicio desde</label>
                    <input type="date" id="desde" name="desde" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="hasta">Inicio hasta</label>
                    <input type="date" id="hasta" name="hasta" class="form-control">
                </div>
                @puede('MSC', 'eliminar')
                    <div class="col-md-3">
                        <label class="form-label" for="anuladas">Incluir</label>
                        <select id="anuladas" name="anuladas" class="form-select">
                            <option value="0">Solo vigentes</option>
                            <option value="1">Solo anuladas</option>
                            <option value="2">Vigentes y anuladas</option>
                        </select>
                    </div>
                @endpuede
            @else
                <div class="col-md-3">
                    <label class="form-label" for="rol">Rol</label>
                    <select id="rol" name="rol" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($roles as $ro)<option value="{{ $ro->Id }}">{{ $ro->Nombre }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="estado_usuario">Estado</label>
                    <select id="estado_usuario" name="estado_usuario" class="form-select">
                        <option value="">Activos y bajas</option>
                        <option value="1">Solo activos</option>
                        <option value="0">Solo bajas</option>
                    </select>
                </div>
            @endif

            @if ($facultades->isNotEmpty())
                <div class="col-md-3">
                    <label class="form-label" for="facultad">Facultad</label>
                    <select id="facultad" name="facultad" class="form-select">
                        <option value="">Todas (global)</option>
                        @foreach ($facultades as $f)<option value="{{ $f->Id }}">{{ $f->Clave }} · {{ $f->Nombre }}</option>@endforeach
                    </select>
                </div>
            @endif

            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-print me-1"></i> Generar reporte</button>
            </div>
        </div>
    </form>
</div>
@endsection
