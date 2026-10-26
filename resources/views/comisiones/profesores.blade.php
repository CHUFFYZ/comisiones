@extends('layouts.app')

@section('title', 'Profesores · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h2 class="font-syne text-dark mb-1">Profesores</h2>
        <p class="text-muted small mb-0">Cada profesor muestra solo las comisiones de tu alcance. Haz clic en una comisión para administrarla.</p>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group rounded-pill w-auto" style="max-width:360px">
            <span class="input-group-text bg-transparent"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Nombre o matrícula">
        </div>
    </form>

    <div class="accordion" id="accProfes">
        @forelse ($profesores as $p)
            @php $lista = $porProfesor[$p->Id] ?? []; @endphp
            <div class="accordion-item mb-2 border rounded-3 overflow-hidden">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#prof{{ $p->Id }}">
                        <span class="fw-semibold">{{ $p->nombre_con_grado }}</span>
                        <span class="font-monospace small text-body-secondary ms-2">{{ $p->Matricula }}</span>
                        @if ($esSU)<span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis ms-2">{{ $p->facultad->Clave ?? '—' }}</span>@endif
                        <span class="badge rounded-pill {{ count($lista) ? 'bg-primary-subtle text-primary-emphasis' : 'bg-light text-body-secondary' }} ms-auto me-2">{{ count($lista) }} comisión(es)</span>
                    </button>
                    <button type="button" class="profesor-foto" data-perfil-profesor="{{ $p->Id }}"
                            aria-label="Ver información de {{ $p->nombre_corto }}" title="Ver información y enviar mensaje">
                        @if ($p->foto_src)
                            <img src="{{ $p->foto_src }}" alt="" class="chat-avatar">
                        @else
                            <span class="chat-avatar chat-avatar--ini">{{ strtoupper(mb_substr($p->Nombre ?? 'P', 0, 1)) }}</span>
                        @endif
                    </button>
                </h2>
                <div id="prof{{ $p->Id }}" class="accordion-collapse collapse" data-bs-parent="#accProfes">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>Oficio</th><th>Asunto</th><th class="d-none d-md-table-cell">Tipo</th><th class="d-none d-md-table-cell">Fechas</th><th>Estado</th></tr></thead>
                            <tbody>
                            @forelse ($lista as $c)
                                <tr class="fila-click" data-href="{{ route('comisiones.show', $c->Id) }}">
                                    <td class="font-monospace small">{{ $c->NOficio }}</td>
                                    <td>{{ $c->Asunto }}</td>
                                    <td class="d-none d-md-table-cell">{{ $c->tipo->Nombre }}</td>
                                    <td class="d-none d-md-table-cell text-nowrap">{{ $c->FechaInicio->format('d/m/y') }}@if ($c->FechaFin) – {{ $c->FechaFin->format('d/m/y') }}@endif</td>
                                    <td><span class="badge rounded-pill badge-estado {{ $c->clase_estado }}">{{ $c->estado->Nombre }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-4 text-body-secondary small">Sin comisiones en tu alcance.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-body-secondary">
                <i class="fa-solid fa-inbox fs-1 opacity-50 mb-2 d-block"></i> No se encontraron profesores.
            </div>
        @endforelse
    </div>
</div>

<div class="modal fade" id="modalPerfilProfesor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center" id="perfilProfesorBody"></div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                <a href="#" id="mensajeProfesor" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-paper-plane me-1"></i> Mandar mensaje
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const perfilProfesorUrl = @json(route('chat.perfil', '__ID__'));
    const chatProfesorUrl = @json(route('chat.index'));
    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[char]));

    document.querySelectorAll('[data-perfil-profesor]').forEach(button => {
        button.addEventListener('click', async () => {
            try {
                const response = await fetch(perfilProfesorUrl.replace('__ID__', button.dataset.perfilProfesor), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const profile = await response.json();
                if (!response.ok) throw new Error(profile.message || 'No se pudo cargar el perfil.');

                const avatar = profile.foto
                    ? `<img src="${escapeHtml(profile.foto)}" alt="" class="chat-avatar chat-avatar--xl">`
                    : `<span class="chat-avatar chat-avatar--ini chat-avatar--xl">${escapeHtml(profile.nombreCompleto.charAt(0).toUpperCase())}</span>`;
                document.getElementById('perfilProfesorBody').innerHTML = `
                    <div class="mb-3">${avatar}</div>
                    <h5 class="font-syne mb-1">${escapeHtml(profile.nombreCompleto)}</h5>
                    <div class="mb-3">
                        <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis">${escapeHtml(profile.rol)}</span>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">${escapeHtml(profile.facultadClave)}</span>
                    </div>
                    <dl class="row small text-start mb-0">
                        <dt class="col-5 text-body-secondary">Facultad</dt><dd class="col-7">${escapeHtml(profile.facultad)}</dd>
                        <dt class="col-5 text-body-secondary">Matrícula</dt><dd class="col-7 font-monospace">${escapeHtml(profile.matricula)}</dd>
                        <dt class="col-5 text-body-secondary">Correo</dt><dd class="col-7 text-break">${escapeHtml(profile.correo)}</dd>
                        <dt class="col-5 text-body-secondary">Rol</dt><dd class="col-7">${escapeHtml(profile.rol)}</dd>
                        <dt class="col-5 text-body-secondary">Género</dt><dd class="col-7">${escapeHtml(profile.genero)}</dd>
                    </dl>`;
                const chatUrl = new URL(chatProfesorUrl, window.location.origin);
                chatUrl.searchParams.set('usuario', button.dataset.perfilProfesor);
                document.getElementById('mensajeProfesor').href = chatUrl.toString();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPerfilProfesor')).show();
            } catch (error) {
                showToast(error.message, 'error');
            }
        });
    });
</script>
@endpush
@endsection
