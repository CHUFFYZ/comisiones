@extends('layouts.app')

@section('title', 'Permisos temporales · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h2 class="font-syne text-dark mb-1">Permisos temporales</h2>
        <p class="text-muted small mb-0">Acceso con fecha de inicio y fin para los usuarios de tu alcance.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-triangle-exclamation"></i><div>{{ $errors->first() }}</div></div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Usuario</th><th>Rol</th><th class="d-none d-md-table-cell">Facultad</th><th>Permiso temporal</th><th class="text-end">Acciones</th></tr></thead>
                <tbody>
                @forelse ($usuarios as $x)
                    @php $a = $x->asignaciones->first(fn ($a) => $a->IdTipoPermisoTemp && $a->IdNivelAccesoTemp); @endphp
                    <tr>
                        <td>{{ $x->nombre_con_grado }}<div class="small font-monospace text-body-secondary">{{ $x->Matricula }}</div></td>
                        <td>{{ $x->rol_nombre }}</td>
                        <td class="d-none d-md-table-cell">{{ $x->facultad->Clave ?? '—' }}</td>
                        <td class="small">
                            @if ($a)
                                {{ $a->nivelTemp->modulo->Nombre ?? '' }} · <span class="font-monospace">{{ $a->tipoTemp->Nombre ?? '' }}</span><br>
                                {{ $a->IniPermisoTemp?->format('d/m/y H:i') }} – {{ $a->FinPermisoTemp?->format('d/m/y H:i') }}
                                <span class="badge rounded-pill {{ $a->tempVigente() ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">
                                    {{ $a->tempVigente() ? 'Vigente' : 'No vigente' }}
                                </span>
                            @else
                                <span class="text-body-tertiary">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-ghost is-brand btn-sm" data-asignar="{{ $x->Id }}" data-nombre="{{ $x->nombre_corto }}">
                                    <i class="fa-solid fa-clock me-1"></i> Asignar
                                </button>
                                @if ($a)
                                    <form action="{{ route('admin.permisos.destroy', $x->Id) }}" method="POST" class="m-0">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-ghost is-danger btn-sm"><i class="fa-solid fa-xmark me-1"></i> Quitar</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-5 text-body-secondary"><i class="fa-solid fa-inbox fs-1 opacity-50 mb-2 d-block"></i> Sin usuarios.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($usuarios->hasPages())<div class="card-footer bg-white">{{ $usuarios->links() }}</div>@endif
    </div>
</div>

<div class="modal fade" id="modalPermiso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="formPermiso" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Permiso temporal · <span id="permisoNombre"></span></h5></div>
            <div class="modal-body row g-3">
                <div class="col-md-8">
                    <label class="form-label" for="IdNivelAccesoTemp">Nivel de acceso <span class="text-danger">*</span></label>
                    <select id="IdNivelAccesoTemp" name="IdNivelAccesoTemp" class="form-select" required>
                        @foreach ($niveles as $n)
                            <option value="{{ $n->Id }}">{{ $n->modulo->Nombre ?? '' }} · {{ $n->Nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="IdTipoPermisoTemp">Permiso <span class="text-danger">*</span></label>
                    <select id="IdTipoPermisoTemp" name="IdTipoPermisoTemp" class="form-select" required>
                        @foreach ($tipos as $t)<option value="{{ $t->Id }}">{{ $t->Nombre }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="IniPermisoTemp">Inicio <span class="text-danger">*</span></label>
                    <input type="datetime-local" id="IniPermisoTemp" name="IniPermisoTemp" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="FinPermisoTemp">Fin <span class="text-danger">*</span></label>
                    <input type="datetime-local" id="FinPermisoTemp" name="FinPermisoTemp" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Asignar</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const rutaPermiso = @json(route('admin.permisos.store', '__ID__'));
    document.querySelectorAll('[data-asignar]').forEach(b => b.addEventListener('click', () => {
        document.getElementById('formPermiso').action = rutaPermiso.replace('__ID__', b.dataset.asignar);
        document.getElementById('permisoNombre').textContent = b.dataset.nombre;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPermiso')).show();
    }));
</script>
@endpush
@endsection
