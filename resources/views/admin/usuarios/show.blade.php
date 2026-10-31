@extends('layouts.app')

@section('title', $usuario->nombre_corto . ' · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="font-syne text-dark mb-1">Información del usuario</h2>
            <p class="text-muted small mb-0">Datos de contacto y perfil.</p>
        </div>
        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver a usuarios
        </a>
    </div>

    <div class="card p-4">
        <div class="row g-4 align-items-center">
            <div class="col-auto">
                @if ($usuario->foto_src)
                    <img src="{{ $usuario->foto_src }}" alt="Foto de {{ $usuario->nombre_corto }}"
                         data-ampliar="{{ $usuario->foto_src }}" title="Clic para ampliar"
                         class="rounded-circle object-fit-cover" width="112" height="112">
                @else
                    <div class="rounded-circle bg-primary-subtle text-primary-emphasis d-flex align-items-center justify-content-center fs-1 fw-semibold"
                         style="width:112px;height:112px" aria-hidden="true">
                        {{ strtoupper(mb_substr($usuario->Nombre ?? 'U', 0, 1)) }}
                    </div>
                @endif
            </div>
            <div class="col">
                <h3 class="font-syne mb-1">{{ $usuario->nombre_con_grado }}</h3>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis">{{ $usuario->rol_nombre }}</span>
                    @if (!(int) $usuario->Activo)
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">Baja</span>
                    @endif
                </div>
            </div>
            @if ($puedeMensaje)
                <div class="col-12 col-lg-auto">
                    <a href="{{ route('chat.index', ['usuario' => $usuario->Id]) }}" class="btn btn-primary">
                        <i class="fa-solid fa-paper-plane me-1"></i> Mandar mensaje
                    </a>
                </div>
            @endif
        </div>

        <hr class="my-4">

        <dl class="row mb-0">
            <dt class="col-sm-3 text-body-secondary">Facultad</dt>
            <dd class="col-sm-9">{{ $usuario->facultad?->Nombre ?? '—' }}</dd>

            <dt class="col-sm-3 text-body-secondary">Matrícula</dt>
            <dd class="col-sm-9 font-monospace">{{ $usuario->Matricula ?? '—' }}</dd>

            <dt class="col-sm-3 text-body-secondary">Correo</dt>
            <dd class="col-sm-9 text-break">
                @if ($usuario->Correo)
                    <a href="mailto:{{ $usuario->Correo }}">{{ $usuario->Correo }}</a>
                @else
                    —
                @endif
            </dd>

            <dt class="col-sm-3 text-body-secondary">Rol</dt>
            <dd class="col-sm-9">{{ $usuario->rol_nombre ?? '—' }}</dd>

            <dt class="col-sm-3 text-body-secondary">Género</dt>
            <dd class="col-sm-9">{{ $usuario->genero_texto }}</dd>
        </dl>
    </div>
</div>
@endsection
