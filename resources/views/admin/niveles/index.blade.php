@extends('layouts.app')

@section('title', 'Niveles de acceso · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h2 class="font-syne text-dark mb-1">Niveles de acceso</h2>
        <p class="text-muted small mb-0">Define qué áreas (tipos de comisión) puede gestionar quien tenga cada nivel.</p>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Clave</th><th>Módulo</th><th class="d-none d-md-table-cell">Descripción</th><th>Áreas de comisión</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
                <tbody>
                @forelse ($niveles as $n)
                    <tr>
                        <td class="font-monospace small">{{ $n->Clave }}</td>
                        <td>{{ $n->modulo->Nombre ?? '—' }}</td>
                        <td class="d-none d-md-table-cell small text-body-secondary">{{ \Illuminate\Support\Str::limit($n->Descripcion, 60) }}</td>
                        <td class="small">
                            @forelse ($n->tipos as $t)<span class="badge rounded-pill bg-primary-subtle text-primary-emphasis me-1">{{ $t->Nombre }}</span>@empty<span class="text-body-tertiary">—</span>@endforelse
                        </td>
                        <td><span class="badge rounded-pill badge-estado {{ $n->Activo ? 'estado-terminado' : 'estado-no-iniciado' }}">{{ $n->Activo ? 'Activo' : 'Inactivo' }}</span></td>
                        <td><div class="d-flex justify-content-end"><a href="{{ route('admin.niveles.edit', $n->Id) }}" class="btn btn-ghost btn-sm"><i class="fa-solid fa-pen me-1"></i> Editar</a></div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-5 text-body-secondary"><i class="fa-solid fa-inbox fs-1 opacity-50 mb-2 d-block"></i> Sin niveles.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
