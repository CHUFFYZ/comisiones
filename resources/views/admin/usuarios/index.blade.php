@extends('layouts.app')

@section('title', ($activos ? 'Usuarios' : 'Bajas de usuarios') . ' · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="font-syne text-dark mb-1">{{ $activos ? 'Usuarios' : 'Bajas de usuarios' }}</h2>
            <p class="text-muted small mb-0">{{ $activos ? 'Usuarios dentro de tu alcance.' : 'Puedes reactivar a un usuario dado de baja.' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ $activos ? route('admin.usuarios.bajas') : route('admin.usuarios.index') }}" class="btn btn-secondary btn-sm">
                {{ $activos ? 'Ver bajas' : 'Ver activos' }}
            </a>
            <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-user-plus me-1"></i> Nuevo usuario</a>
        </div>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group rounded-pill w-auto" style="max-width:360px">
            <span class="input-group-text bg-transparent"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nombre, matrícula o correo">
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Matrícula</th><th>Nombre</th><th>Rol</th><th class="d-none d-md-table-cell">Facultad</th><th class="d-none d-lg-table-cell">Correo</th><th class="text-end">Acciones</th></tr></thead>
                <tbody>
                @forelse ($usuarios as $x)
                    <tr>
                        <td class="font-monospace small">{{ $x->Matricula }}</td>
                        <td>{{ $x->nombre_con_grado }}</td>
                        <td><span class="badge rounded-pill bg-primary-subtle text-primary-emphasis">{{ $x->rol_nombre }}</span></td>
                        <td class="d-none d-md-table-cell">{{ $x->facultad->Clave ?? '—' }}</td>
                        <td class="d-none d-lg-table-cell">{{ $x->Correo }}</td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                @if ($activos)
                                    <a href="{{ route('admin.usuarios.edit', $x->Id) }}" class="btn btn-ghost btn-sm"><i class="fa-solid fa-pen me-1"></i> Editar</a>
                                    <form action="{{ route('admin.usuarios.baja', $x->Id) }}" method="POST" class="m-0" onsubmit="return confirm('¿Dar de baja a {{ $x->nombre_corto }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-ghost is-danger btn-sm"><i class="fa-solid fa-user-slash me-1"></i> Baja</button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.usuarios.restaurar', $x->Id) }}" method="POST" class="m-0">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-ghost is-success btn-sm"><i class="fa-solid fa-rotate-left me-1"></i> Reactivar</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-5 text-body-secondary"><i class="fa-solid fa-inbox fs-1 opacity-50 mb-2 d-block"></i> Sin usuarios.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($usuarios->hasPages())<div class="card-footer bg-white">{{ $usuarios->links() }}</div>@endif
    </div>
</div>
@endsection
