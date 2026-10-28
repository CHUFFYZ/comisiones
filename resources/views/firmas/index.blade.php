@extends('layouts.app')

@section('title', 'Firmas y sellos · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h2 class="font-syne text-dark mb-1">Firmas y sellos</h2>
        <p class="text-muted small mb-0">Oficios y constancias de tu facultad. Puedes firmar, sellar o ambos.</p>
    </div>

    @if ($errors->any() && !$errors->has('general'))
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-triangle-exclamation"></i><div>{{ $errors->first() }}</div></div>
    @endif
    @if (!$u->FirmaUrl || !$u->SelloUrl)
        <div class="alert alert-warning">
            Aún no tienes {{ !$u->FirmaUrl && !$u->SelloUrl ? 'firma ni sello' : (!$u->FirmaUrl ? 'firma' : 'sello') }} cargado.
            <a href="{{ route('perfil') }}">Súbelo en Mi perfil</a>.
        </div>
    @endif

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><a class="nav-link {{ $ver === 'pendientes' ? 'active' : '' }}" href="{{ route('firmas.index', ['ver' => 'pendientes']) }}">Pendientes</a></li>
        <li class="nav-item"><a class="nav-link {{ $ver === 'completos' ? 'active' : '' }}" href="{{ route('firmas.index', ['ver' => 'completos']) }}">Firmados y sellados</a></li>
    </ul>

    <form action="{{ route('firmas.procesar') }}" method="POST" class="card">
        @csrf
        <div class="p-3 border-bottom d-flex flex-wrap gap-2 align-items-center">
            @if ($puedeComoDirector)
                <select name="modo" class="form-select rounded-pill w-auto">
                    <option value="propio">Con mi firma y sello</option>
                    <option value="director">Como Director ({{ $director?->nombre_con_grado ?? 'sin director' }})</option>
                </select>
            @else
                <input type="hidden" name="modo" value="propio">
            @endif
            <button name="accion" value="firma" class="btn btn-ghost btn-sm"><i class="fa-solid fa-signature me-1"></i> Firmar</button>
            <button name="accion" value="sello" class="btn btn-ghost btn-sm"><i class="fa-solid fa-stamp me-1"></i> Sellar</button>
            <button name="accion" value="ambos" class="btn btn-primary btn-sm"><i class="fa-solid fa-check me-1"></i> Firmar y sellar</button>
            <button name="accion" value="retirar" class="btn btn-ghost is-danger btn-sm ms-auto"><i class="fa-solid fa-rotate-left me-1"></i> Retirar firma/sello</button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr>
                    <th style="width:40px"><input type="checkbox" class="form-check-input" id="selTodos" aria-label="Seleccionar todos"></th>
                    <th>Documento</th><th>Oficio</th><th>Asunto</th><th class="d-none d-md-table-cell">Profesores</th><th>Estado</th><th></th>
                </tr></thead>
                <tbody>
                @forelse ($docs as $x)
                    @php $d = $x['doc']; $c = $x['c']; @endphp
                    <tr>
                        <td><input type="checkbox" class="form-check-input chk-doc" name="items[]" value="{{ $x['tipo'] }}:{{ $d->Id }}"></td>
                        <td><span class="badge rounded-pill {{ $x['tipo'] === 'c' ? 'bg-primary-subtle text-primary-emphasis' : 'bg-success-subtle text-success-emphasis' }}">{{ $x['tipo'] === 'c' ? 'Oficio' : 'Constancia' }}</span></td>
                        <td class="font-monospace small">{{ $c->NOficio }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($c->Asunto, 50) }}</td>
                        <td class="d-none d-md-table-cell small">{{ $x['profes']->pluck('nombre_corto')->join(', ') }}</td>
                        <td>
                            @include('firmas.partials.estado', ['d' => $d])
                            @if ($d->firmado || $d->sellado)<div class="text-xs text-body-secondary mt-1">{{ $d->DirectorCargo }} · {{ $d->DirectorNombre }}</div>@endif
                        </td>
                        <td class="text-end"><a href="{{ route('firmas.pdf', [$x['tipo'], $d->Id]) }}" target="_blank" class="btn btn-ghost btn-sm"><i class="fa-solid fa-file-pdf me-1"></i> Ver</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-5 text-body-secondary"><i class="fa-solid fa-inbox fs-1 opacity-50 mb-2 d-block"></i> Nada por aquí.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.getElementById('selTodos')?.addEventListener('change', e =>
        document.querySelectorAll('.chk-doc').forEach(c => c.checked = e.target.checked));
</script>
@endpush
@endsection