@extends('layouts.app')

@section('title', 'Nueva comisión · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h2 class="font-syne text-dark mb-1">Nueva comisión</h2>
        <p class="text-muted small mb-0">El número de oficio se genera automáticamente.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <form action="{{ route('comisiones.store') }}" method="POST" class="card p-4">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="IdTipoComision">Tipo de comisión <span class="text-danger">*</span></label>
                <select id="IdTipoComision" name="IdTipoComision" class="form-select" required>
                    <option value="">Selecciona…</option>
                    @foreach ($tipos as $t)
                        <option value="{{ $t->Id }}" @selected(old('IdTipoComision') == $t->Id)>{{ $t->Nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="IdPlantilla">Plantilla <span class="text-danger">*</span></label>
                <select id="IdPlantilla" name="IdPlantilla" class="form-select" required>
                    @foreach ($plantillas as $p)
                        <option value="{{ $p->Id }}" @selected(old('IdPlantilla') == $p->Id)>Plantilla #{{ $p->Id }} · {{ $p->Facultad }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label" for="Asunto">Asunto <span class="text-danger">*</span></label>
                <input type="text" id="Asunto" name="Asunto" value="{{ old('Asunto') }}" maxlength="255" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="FechaInicio">Fecha de inicio <span class="text-danger">*</span></label>
                <input type="date" id="FechaInicio" name="FechaInicio" value="{{ old('FechaInicio') }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="FechaFin">Fecha final</label>
                <input type="date" id="FechaFin" name="FechaFin" value="{{ old('FechaFin') }}" class="form-control">
            </div>

            <div class="col-12">
                <label class="form-label">Profesores <span class="text-danger">*</span></label>
                <div class="input-group mb-2">
                    <span class="input-group-text bg-transparent"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="search" id="buscaProf" class="form-control" placeholder="Buscar profesor…">
                </div>
                <div class="border rounded-3 p-2 lista-scroll bg-white">
                    @forelse ($profesores as $p)
                        <div class="form-check prof-item">
                            <input class="form-check-input" type="checkbox" name="profesores[]" value="{{ $p->Id }}" id="p{{ $p->Id }}"
                                   @checked(in_array($p->Id, old('profesores', [])))>
                            <label class="form-check-label small" for="p{{ $p->Id }}">{{ $p->GradoAcademico }} {{ $p->nombre_completo }}</label>
                        </div>
                    @empty
                        <p class="small text-body-secondary mb-0">No hay profesores registrados en tu facultad.</p>
                    @endforelse
                </div>
            </div>

            <div class="col-12 d-flex gap-2 justify-content-end">
                <a href="{{ route('comisiones.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Guardar</button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.getElementById('buscaProf').addEventListener('input', e => {
        const t = e.target.value.toLowerCase();
        document.querySelectorAll('.prof-item').forEach(el =>
            el.classList.toggle('d-none', !el.textContent.toLowerCase().includes(t)));
    });
</script>
@endpush
@endsection