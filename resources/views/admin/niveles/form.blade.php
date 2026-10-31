@extends('layouts.app')

@section('title', 'Editar nivel ' . $n->Clave . ' · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h2 class="font-syne text-dark mb-1">Editar nivel {{ $n->Clave }}</h2>
        <p class="text-muted small mb-0">Módulo: {{ $n->modulo->Nombre ?? '—' }}. Los cambios aplican a todos los usuarios con este nivel (permanente o temporal).</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-triangle-exclamation"></i><div>{{ $errors->first() }}</div></div>
    @endif

    <form action="{{ route('admin.niveles.update', $n->Id) }}" method="POST" class="card p-4">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="Clave">Clave</label>
                <input type="text" id="Clave" class="form-control font-monospace" value="{{ $n->Clave }}" disabled>
            </div>
            <div class="col-md-8">
                <label class="form-label" for="Nombre">Nombre <span class="text-danger">*</span></label>
                <input type="text" id="Nombre" name="Nombre" maxlength="50" class="form-control" value="{{ old('Nombre', $n->Nombre) }}" required>
            </div>
            <div class="col-12">
                <label class="form-label" for="Descripcion">Descripción</label>
                <textarea id="Descripcion" name="Descripcion" rows="2" maxlength="500" class="form-control">{{ old('Descripcion', $n->Descripcion) }}</textarea>
            </div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="Activo" name="Activo" value="1" @checked(old('Activo', $n->Activo))>
                    <label class="form-check-label" for="Activo">Nivel activo</label>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label">Áreas (tipos de comisión) que puede gestionar</label>
                @if (($n->modulo->Clave ?? '') !== 'MSC')
                    <div class="form-text mb-2">Este nivel no es del módulo de comisiones; las áreas solo tienen efecto en SisComisiones.</div>
                @endif
                <div class="d-flex flex-wrap gap-3">
                    @php $marc = old('tipos', $marcados); @endphp
                    @foreach ($tipos as $t)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="tipos[]" value="{{ $t->Id }}" id="t{{ $t->Id }}" @checked(in_array($t->Id, $marc))>
                            <label class="form-check-label small" for="t{{ $t->Id }}">{{ $t->Nombre }}</label>
                        </div>
                    @endforeach
                </div>
                <div class="form-text">Se suman a las áreas asignadas directamente al usuario.</div>
            </div>

            <div class="col-12 d-flex gap-2 justify-content-end">
                <a href="{{ route('admin.niveles.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Guardar</button>
            </div>
        </div>
    </form>
</div>
@endsection
