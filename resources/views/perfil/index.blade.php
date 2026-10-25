@extends('layouts.app')

@section('title', 'Mi perfil · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4"><h2 class="font-syne text-dark mb-1">Mi perfil</h2></div>

    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-triangle-exclamation"></i><div>{{ $errors->first() }}</div></div>
    @endif

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card p-4 text-center">
                @if ($u->foto_src)
                    <img src="{{ $u->foto_src }}" alt="" class="chat-avatar chat-avatar--xl mx-auto mb-3">
                @else
                    <span class="chat-avatar chat-avatar--ini chat-avatar--xl mx-auto mb-3">{{ strtoupper(mb_substr($u->Nombre, 0, 1)) }}</span>
                @endif
                <form action="{{ route('perfil.foto') }}" method="POST" enctype="multipart/form-data" class="text-start">
                    @csrf
                    <label class="form-label" for="Foto">Foto de perfil (JPG, PNG o WebP · máx. 2 MB)</label>
                    <input type="file" id="Foto" name="Foto" accept=".jpg,.jpeg,.png,.webp" class="form-control mb-2" required>
                    <button class="btn btn-primary btn-sm w-100">Guardar foto</button>
                </form>
                @if ($u->foto_src)
                    <form action="{{ route('perfil.foto.quitar') }}" method="POST" class="mt-2">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost is-danger btn-sm w-100">Quitar foto</button>
                    </form>
                @endif
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card p-4">
                <h5 class="font-syne mb-3">{{ $u->nombre_con_grado }}</h5>
                <dl class="row small mb-0">
                    <dt class="col-sm-3 text-body-secondary">Matrícula</dt><dd class="col-sm-9 font-monospace">{{ $u->Matricula }}</dd>
                    <dt class="col-sm-3 text-body-secondary">Correo</dt><dd class="col-sm-9">{{ $u->Correo }}</dd>
                    <dt class="col-sm-3 text-body-secondary">Rol</dt><dd class="col-sm-9">{{ $u->rol_nombre }}</dd>
                    <dt class="col-sm-3 text-body-secondary">Facultad</dt><dd class="col-sm-9">{{ $u->facultad->Nombre ?? '—' }} ({{ $u->facultad->Clave ?? '—' }})</dd>
                    <dt class="col-sm-3 text-body-secondary">Género</dt><dd class="col-sm-9">{{ $u->genero_texto }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
