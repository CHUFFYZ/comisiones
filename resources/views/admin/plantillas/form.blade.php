@extends('layouts.app')

@php $edit = $p !== null; @endphp

@section('title', ($edit ? 'Editar plantilla' : 'Nueva plantilla') . ' · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h2 class="font-syne text-dark mb-1">{{ $edit ? 'Editar plantilla #' . $p->Id : 'Nueva plantilla' }}</h2>
        <p class="text-muted small mb-0">El texto del documento se arma así: <em>introducción</em> + <strong>contexto de la comisión</strong> + <em>cierre</em> (las fechas se agregan solas al final del cierre).</p>
    </div>

    @if ($errors->any() && !$errors->has('general'))
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-triangle-exclamation"></i><div>{{ $errors->first() }}</div></div>
    @endif

    <form action="{{ $edit ? route('admin.plantillas.update', $p->Id) : route('admin.plantillas.store') }}" method="POST" enctype="multipart/form-data" class="card p-4">
        @csrf
        @if ($edit) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="TipoDocumento">Tipo de documento <span class="text-danger">*</span></label>
                <input type="text" id="TipoDocumento" name="TipoDocumento" list="tiposDoc" maxlength="50" class="form-control"
                       value="{{ old('TipoDocumento', $p->TipoDocumento ?? '') }}" required>
                <datalist id="tiposDoc">@foreach ($tipos as $t)<option value="{{ $t }}">@endforeach</datalist>
                <div class="form-text">«Comision» y «Constancia» se usan hoy; escribe otro nombre para un tipo nuevo.</div>
            </div>
            <div class="col-md-8">
                <label class="form-label" for="NombreUniversidad">Universidad <span class="text-danger">*</span></label>
                <input type="text" id="NombreUniversidad" name="NombreUniversidad" maxlength="255" class="form-control"
                       value="{{ old('NombreUniversidad', $p->NombreUniversidad ?? 'UNIVERSIDAD AUTÓNOMA DEL CARMEN') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="Facultad">Facultad <span class="text-danger">*</span></label>
                <input type="text" id="Facultad" name="Facultad" maxlength="255" class="form-control"
                       value="{{ old('Facultad', $p->Facultad ?? 'FACULTAD DE CIENCIAS DE LA INFORMACIÓN') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="Lugar">Lugar <span class="text-danger">*</span></label>
                <input type="text" id="Lugar" name="Lugar" maxlength="255" class="form-control"
                       value="{{ old('Lugar', $p->Lugar ?? 'Ciudad del Carmen, Campeche') }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="LogoIzquierdo">Logo izquierdo (URL o ruta)</label>
                <input type="text" id="LogoIzquierdo" name="LogoIzquierdo" maxlength="500" class="form-control" value="{{ old('LogoIzquierdo', $p->LogoIzquierdo ?? '') }}">
                <input type="file" name="LogoIzqArchivo" accept=".jpg,.jpeg,.png,.webp" class="form-control mt-2">
                <div class="form-text">O sube una imagen (reemplaza la URL).</div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="LogoDerecho">Logo derecho (URL o ruta)</label>
                <input type="text" id="LogoDerecho" name="LogoDerecho" maxlength="500" class="form-control" value="{{ old('LogoDerecho', $p->LogoDerecho ?? '') }}">
                <input type="file" name="LogoDerArchivo" accept=".jpg,.jpeg,.png,.webp" class="form-control mt-2">
            </div>

            <div class="col-12">
                <label class="form-label" for="Introduccion">Introducción <span class="text-danger">*</span></label>
                <textarea id="Introduccion" name="Introduccion" rows="2" maxlength="2000" class="form-control" required>{{ old('Introduccion', $p->Introduccion ?? '') }}</textarea>
                <div class="form-text">Debe terminar de forma que el contexto continúe la frase (p. ej. «…ha sido comisionado para»).</div>
            </div>
            <div class="col-12">
                <label class="form-label" for="Cierre">Cierre</label>
                <textarea id="Cierre" name="Cierre" rows="3" maxlength="2000" class="form-control">{{ old('Cierre', $p->Cierre ?? '') }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label" for="Despedida">Despedida</label>
                <textarea id="Despedida" name="Despedida" rows="2" maxlength="1000" class="form-control">{{ old('Despedida', $p->Despedida ?? '') }}</textarea>
            </div>

            <div class="col-12 d-flex gap-2 justify-content-end">
                <a href="{{ route('admin.plantillas.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Guardar</button>
            </div>
        </div>
    </form>
</div>
@endsection
