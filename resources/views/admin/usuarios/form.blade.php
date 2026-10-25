@extends('layouts.app')

@php $edit = $x !== null; @endphp

@section('title', ($edit ? 'Editar usuario' : 'Nuevo usuario') . ' · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h2 class="font-syne text-dark mb-1">{{ $edit ? 'Editar ' . $x->nombre_corto : 'Nuevo usuario' }}</h2>
        <p class="text-muted small mb-0">Solo verás los roles y facultades que tu cargo permite.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-triangle-exclamation"></i><div>{{ $errors->first() }}</div></div>
    @endif

    <form action="{{ $edit ? route('admin.usuarios.update', $x->Id) : route('admin.usuarios.store') }}" method="POST" class="card p-4">
        @csrf
        @if ($edit) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="Nombre">Nombre(s) <span class="text-danger">*</span></label>
                <input type="text" id="Nombre" name="Nombre" value="{{ old('Nombre', $x->Nombre ?? '') }}" maxlength="50" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="Apellido">Apellidos <span class="text-danger">*</span></label>
                <input type="text" id="Apellido" name="Apellido" value="{{ old('Apellido', $x->Apellido ?? '') }}" maxlength="50" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="Matricula">Matrícula <span class="text-danger">*</span></label>
                <input type="text" id="Matricula" name="Matricula" value="{{ old('Matricula', $x->Matricula ?? '') }}" maxlength="20" class="form-control font-monospace" required>
            </div>
            <div class="col-md-8">
                <label class="form-label" for="Correo">Correo <span class="text-danger">*</span></label>
                <input type="email" id="Correo" name="Correo" value="{{ old('Correo', $x->Correo ?? '') }}" maxlength="50" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="GradoAcademico">Grado académico</label>
                <input type="text" id="GradoAcademico" name="GradoAcademico" value="{{ old('GradoAcademico', $x->GradoAcademico ?? '') }}" maxlength="50" class="form-control" placeholder="Dr, Dra, Mtro, Mtra…">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="Genero">Género <span class="text-danger">*</span></label>
                <select id="Genero" name="Genero" class="form-select" required>
                    <option value="0" @selected(old('Genero', $x->Genero ?? 0) == 0)>Masculino</option>
                    <option value="1" @selected(old('Genero', $x->Genero ?? 0) == 1)>Femenino</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="IdRol">Rol <span class="text-danger">*</span></label>
                <select id="IdRol" name="IdRol" class="form-select" required>
                    @foreach ($roles as $r)
                        <option value="{{ $r->Id }}" @selected(old('IdRol', $x->IdRol ?? null) == $r->Id)>{{ $r->Nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="IdFacultad">Facultad <span class="text-danger">*</span></label>
                <select id="IdFacultad" name="IdFacultad" class="form-select" required>
                    @foreach ($facultades as $f)
                        <option value="{{ $f->Id }}" @selected(old('IdFacultad', $x->IdFacultad ?? null) == $f->Id)>{{ $f->Clave }} · {{ $f->Nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="Contrasena">Contraseña @unless ($edit)<span class="text-danger">*</span>@endunless</label>
                <input type="password" id="Contrasena" name="Contrasena" class="form-control" autocomplete="new-password" minlength="8" @unless ($edit) required @endunless>
                @if ($edit)<div class="form-text">Déjala vacía para no cambiarla.</div>@endif
            </div>

            <div class="col-12"><hr class="my-1"><h6 class="font-syne mb-0">Permiso base (opcional)</h6></div>
            <div class="col-md-6">
                <label class="form-label" for="IdNivelAcceso">Nivel de acceso</label>
                <select id="IdNivelAcceso" name="IdNivelAcceso" class="form-select">
                    <option value="">Sin cambios / sin permiso base</option>
                    @foreach ($niveles as $n)
                        <option value="{{ $n->Id }}" @selected(old('IdNivelAcceso', $base->IdNivelAcceso ?? null) == $n->Id)>{{ $n->modulo->Nombre ?? '' }} · {{ $n->Nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="IdTipoPermiso">Tipo de permiso</label>
                <select id="IdTipoPermiso" name="IdTipoPermiso" class="form-select">
                    <option value="">—</option>
                    @foreach ($tipos as $t)
                        <option value="{{ $t->Id }}" @selected(old('IdTipoPermiso', $base->IdTipoPermiso ?? null) == $t->Id)>{{ $t->Nombre }} · {{ $t->Descripcion }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <label class="form-label">Tipos de comisión que puede gestionar (secretarias)</label>
                <div class="d-flex flex-wrap gap-3">
                    @php $marc = old('tiposComision', $tiposX); @endphp
                    @foreach ($tiposComision as $tc)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="tiposComision[]" value="{{ $tc->Id }}" id="tc{{ $tc->Id }}" @checked(in_array($tc->Id, $marc))>
                            <label class="form-check-label small" for="tc{{ $tc->Id }}">{{ $tc->Nombre }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-12 d-flex gap-2 justify-content-end">
                <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Guardar</button>
            </div>
        </div>
    </form>
</div>
@endsection
