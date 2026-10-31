@extends('layouts.app')

@section('title', 'Plantillas de documentos · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="font-syne text-dark mb-1">Plantillas de documentos</h2>
            <p class="text-muted small mb-0">Encabezado, introducción y cierre de cada tipo de documento. Puedes crear tipos nuevos.</p>
        </div>
        <a href="{{ route('admin.plantillas.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i> Nueva plantilla</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>#</th><th>Tipo</th><th>Facultad</th><th class="d-none d-md-table-cell">Introducción</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
                <tbody>
                @forelse ($plantillas as $p)
                    <tr>
                        <td class="font-monospace small">{{ $p->Id }}</td>
                        <td><span class="badge rounded-pill bg-primary-subtle text-primary-emphasis">{{ $p->TipoDocumento }}</span></td>
                        <td class="small">{{ $p->Facultad }}</td>
                        <td class="d-none d-md-table-cell small text-body-secondary">{{ \Illuminate\Support\Str::limit($p->Introduccion, 70) }}</td>
                        <td><span class="badge rounded-pill badge-estado {{ $p->Activo ? 'estado-terminado' : 'estado-no-iniciado' }}">{{ $p->Activo ? 'Activa' : 'Inactiva' }}</span></td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.plantillas.edit', $p->Id) }}" class="btn btn-ghost btn-sm"><i class="fa-solid fa-pen me-1"></i> Editar</a>
                                <form action="{{ route('admin.plantillas.estado', $p->Id) }}" method="POST" class="m-0"
                                      @if ($p->Activo) data-confirm="¿Desactivar esta plantilla? Dejará de ofrecerse al crear documentos." data-confirm-titulo="Desactivar plantilla" data-confirm-boton="Desactivar" @endif>
                                    @csrf @method('PATCH')
                                    <button class="btn btn-ghost {{ $p->Activo ? 'is-danger' : 'is-success' }} btn-sm">
                                        <i class="fa-solid {{ $p->Activo ? 'fa-ban' : 'fa-rotate-left' }} me-1"></i> {{ $p->Activo ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-5 text-body-secondary"><i class="fa-solid fa-inbox fs-1 opacity-50 mb-2 d-block"></i> Sin plantillas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
