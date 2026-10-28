@extends('layouts.app')

@section('title', 'Firma de Sec. Académica · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h2 class="font-syne text-dark mb-1">Firma de Secretaría Académica</h2>
        <p class="text-muted small mb-0">Decide en qué facultades la Secretaría Académica puede firmar y sellar usando la firma y sello del Director.</p>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Facultad</th><th>Secretaría Académica</th><th class="text-end">Puede firmar como Director</th></tr></thead>
                <tbody>
                @foreach ($facultades as $f)
                    <tr>
                        <td><span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis me-1">{{ $f->Clave }}</span>{{ $f->Nombre }}</td>
                        <td class="small">{{ ($secretarias[$f->Id] ?? collect())->map->nombre_con_grado->join(', ') ?: '—' }}</td>
                        <td>
                            <form action="{{ route('admin.facultades.firma', $f->Id) }}" method="POST" class="m-0 d-flex justify-content-end">
                                @csrf @method('PATCH')
                                <input type="hidden" name="valor" value="0">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" name="valor" value="1"
                                           @checked($f->SAFirmaComoDirector) onchange="this.form.submit()" aria-label="Permitir a {{ $f->Clave }}">
                                </div>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection