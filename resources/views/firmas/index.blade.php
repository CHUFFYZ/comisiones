@extends('layouts.app')

@section('title', 'Firmas y sellos · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h2 class="font-syne text-dark mb-1">Firmas y sellos</h2>
        <p class="text-muted small mb-0">
            {{ $esSU ? 'Comisiones y constancias de todas las facultades. Se firman con la firma y sello del Director de cada facultad.' : 'Comisiones y constancias de tu facultad. Puedes firmar, sellar o ambos.' }}
        </p>
    </div>

    @if ($errors->any() && !$errors->has('general'))
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-triangle-exclamation"></i><div>{{ $errors->first() }}</div></div>
    @endif
    @if (!$esSU && (!$u->FirmaUrl || !$u->SelloUrl))
        <div class="alert alert-warning">
            Aún no tienes {{ !$u->FirmaUrl && !$u->SelloUrl ? 'firma ni sello' : (!$u->FirmaUrl ? 'firma' : 'sello') }} cargado.
            <a href="{{ route('perfil') }}">Súbelo en Mi perfil</a>.
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
        <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link {{ $ver === 'pendientes' ? 'active' : '' }}" href="{{ route('firmas.index', ['ver' => 'pendientes', 'facultad' => $fac, 'quien' => $quien]) }}">Pendientes</a></li>
            <li class="nav-item"><a class="nav-link {{ $ver === 'completos' ? 'active' : '' }}" href="{{ route('firmas.index', ['ver' => 'completos', 'facultad' => $fac, 'quien' => $quien]) }}">Firmados y sellados</a></li>
        </ul>
        <form method="GET" class="m-0 d-flex flex-wrap gap-2" data-sin-carga>
            <input type="hidden" name="ver" value="{{ $ver }}">
            <select name="quien" class="form-select rounded-pill w-auto" onchange="this.form.submit()" aria-label="Firmado por">
                <option value="todos" @selected($quien === 'todos')>Firmados por: todos</option>
                <option value="director" @selected($quien === 'director')>Firma/sello del Director</option>
                <option value="secretaria" @selected($quien === 'secretaria')>Firma/sello de Sec. Académica</option>
            </select>
            @if ($esSU)
                <select name="facultad" class="form-select rounded-pill w-auto" onchange="this.form.submit()">
                    <option value="">Todas las facultades</option>
                    @foreach ($facultades as $f)<option value="{{ $f->Id }}" @selected($fac == $f->Id)>{{ $f->Clave }} · {{ $f->Nombre }}</option>@endforeach
                </select>
            @endif
        </form>
    </div>
    @if ($quien !== 'todos' && $ver === 'pendientes')
        <div class="small text-body-secondary mb-2">El filtro por firmante solo aplica a documentos ya firmados; usa la pestaña «Firmados y sellados».</div>
    @endif

    <form action="{{ route('firmas.procesar') }}" method="POST" class="card" id="formFirmas">
        @csrf
        <input type="hidden" name="accion" id="accionInput" value="">
        <div class="p-3 border-bottom d-flex flex-wrap gap-2 align-items-center">
            @if ($puedeComoDirector)
                <select name="modo" id="modoFirma" class="form-select rounded-pill w-auto">
                    <option value="propio">Con mi firma y sello ({{ $u->nombre_con_grado }})</option>
                    <option value="director">Como Director ({{ $director?->nombre_con_grado ?? 'sin director' }})</option>
                </select>
            @else
                <input type="hidden" name="modo" value="{{ $esSU ? 'director' : 'propio' }}">
            @endif
            <button type="button" data-accion="firma" class="btn btn-ghost btn-sm"><i class="fa-solid fa-signature me-1"></i> Firmar</button>
            <button type="button" data-accion="sello" class="btn btn-ghost btn-sm"><i class="fa-solid fa-stamp me-1"></i> Sellar</button>
            <button type="button" data-accion="ambos" class="btn btn-primary btn-sm"><i class="fa-solid fa-check me-1"></i> Firmar y sellar</button>
            <div class="dropdown ms-auto">
                <button type="button" class="btn btn-ghost is-danger btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-rotate-left me-1"></i> Retirar</button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                    <li><button type="button" class="dropdown-item small" data-accion="retirar_firma">Retirar solo firma</button></li>
                    <li><button type="button" class="dropdown-item small" data-accion="retirar_sello">Retirar solo sello</button></li>
                    <li><button type="button" class="dropdown-item small text-danger" data-accion="retirar">Retirar firma y sello</button></li>
                </ul>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr>
                    <th style="width:40px"><input type="checkbox" class="form-check-input" id="selTodos" aria-label="Seleccionar todos"></th>
                    <th>Documento</th>@if ($esSU)<th>Facultad</th>@endif<th>Comisión</th><th>Asunto</th><th class="d-none d-md-table-cell">Profesores</th><th>Estado</th><th></th>
                </tr></thead>
                <tbody>
                @forelse ($docs as $x)
                    @php $d = $x['doc']; $c = $x['c']; @endphp
                    <tr>
                        <td><input type="checkbox" class="form-check-input chk-doc" name="items[]" value="{{ $x['tipo'] }}:{{ $d->Id }}"
                                   data-tipo="{{ $x['tipo'] === 'c' ? 'Comisiones' : 'Constancias' }}" data-folio="{{ $c->NOficio }}{{ $x['tipo'] === 'k' ? ' · ' . $x['profes']->pluck('nombre_corto')->join(', ') : '' }}"></td>
                        <td><span class="badge rounded-pill {{ $x['tipo'] === 'c' ? 'bg-primary-subtle text-primary-emphasis' : 'bg-success-subtle text-success-emphasis' }}">{{ $x['tipo'] === 'c' ? 'Comisión' : 'Constancia' }}</span></td>
                        @if ($esSU)<td>{{ $c->facultad->Clave ?? '—' }}</td>@endif
                        <td class="font-monospace small">{{ $c->NOficio }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($c->Asunto, 50) }}</td>
                        <td class="d-none d-md-table-cell small">{{ $x['profes']->pluck('nombre_corto')->join(', ') }}</td>
                        <td>
                            @include('firmas.partials.estado', ['d' => $d])
                            @if ($d->firmado || $d->sellado)
                                <div class="text-xs text-body-secondary mt-1">
                                    {{ $d->DirectorCargo }} · {{ $d->DirectorNombre }}
                                    <span class="badge rounded-pill {{ $d->FirmanteModo === 'director' ? 'bg-primary-subtle text-primary-emphasis' : 'bg-info-subtle text-info-emphasis' }}">{{ $d->FirmanteModo === 'director' ? 'Firma del Director' : 'Firma propia' }}</span>
                                </div>
                                <div class="text-xs text-body-tertiary">
                                    Aplicó: {{ collect([$d->firmaUsuario?->nombre_corto, $d->selloUsuario?->nombre_corto])->filter()->unique()->join(' / ') ?: '—' }}
                                </div>
                            @endif
                        </td>
                        <td class="text-end"><a href="{{ route('firmas.pdf', [$x['tipo'], $d->Id]) }}" target="_blank" class="btn btn-ghost btn-sm"><i class="fa-solid fa-file-pdf me-1"></i> Ver</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-5 text-body-secondary"><i class="fa-solid fa-inbox fs-1 opacity-50 mb-2 d-block"></i> Nada por aquí.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </form>
</div>

{{-- Confirmación de firma / retiro --}}
<div class="modal fade" id="modalFirmar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="mfTitulo"></h5></div>
            <div class="modal-body">
                <div class="alert alert-warning small mb-3" id="mfQuien"></div>
                <div id="mfLista" class="small"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="mfOk">Confirmar</button>
            </div>
        </div>
    </div>
</div>

@php
    $quienFijo = $esSU
        ? 'Como Director de la facultad de cada documento'
        : ($u->Rol === 'Director' ? 'Como Director (' . $u->nombre_con_grado . ')' : 'Con mi firma y sello (' . $u->nombre_con_grado . ')');
@endphp
@push('scripts')
<script>
(() => {
    const QUIEN_FIJO = @json($quienFijo);
    const form = document.getElementById('formFirmas'), modalEl = document.getElementById('modalFirmar');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    const TXT = {
        firma:         { t: 'Firmar documentos',                      ok: 'Confirmar y firmar',          p: 'Se aplicará la FIRMA a:' },
        sello:         { t: 'Sellar documentos',                      ok: 'Confirmar y sellar',          p: 'Se aplicará el SELLO a:' },
        ambos:         { t: 'Firmar y sellar documentos',             ok: 'Confirmar y firmar y sellar', p: 'Se aplicará FIRMA y SELLO a:' },
        retirar_firma: { t: 'Retirar firma',                          ok: 'Sí, continuar',               p: '¿Seguro que deseas RETIRAR LA FIRMA de:' },
        retirar_sello: { t: 'Retirar sello',                          ok: 'Sí, continuar',               p: '¿Seguro que deseas RETIRAR EL SELLO de:' },
        retirar:       { t: 'Retirar firma y sello',                  ok: 'Sí, continuar',               p: '¿Seguro que deseas RETIRAR FIRMA Y SELLO de:' },
    };
    let accion = null, paso = 1, sel = [];

    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const quien = () => { const s = document.getElementById('modoFirma'); return s ? s.selectedOptions[0].text : QUIEN_FIJO; };

    function pintar() {
        const x = TXT[accion], retirar = accion.startsWith('retirar');
        document.getElementById('mfTitulo').textContent = x.t + (retirar ? ` (paso ${paso} de 2)` : '');
        document.getElementById('mfQuien').innerHTML = retirar
            ? (paso === 1 ? 'Revisa los documentos antes de continuar.' : '<strong>Confirmación final.</strong> Los documentos quedarán sin ' + (accion === 'retirar_firma' ? 'firma' : accion === 'retirar_sello' ? 'sello' : 'firma ni sello') + ' y, si ya no están firmados y sellados, dejarán de verse en el panel del profesor.')
            : '<i class="fa-solid fa-user-pen me-1"></i> Estás firmando: <strong>' + esc(quien()) + '</strong>';
        const grupos = {};
        sel.forEach(c => (grupos[c.dataset.tipo] ??= []).push(c.dataset.folio));
        document.getElementById('mfLista').innerHTML = '<p class="mb-2">' + x.p + '</p>' + Object.entries(grupos).map(([t, l]) =>
            `<div class="fw-semibold mt-2">${esc(t)} (${l.length})</div><ul class="mb-1">${l.map(f => `<li class="font-monospace">${esc(f)}</li>`).join('')}</ul>`).join('');
        const ok = document.getElementById('mfOk');
        ok.textContent = retirar && paso === 2 ? 'Retirar definitivamente' : x.ok;
        ok.className = 'btn ' + (retirar ? 'btn-danger' : 'btn-primary');
    }

    document.querySelectorAll('[data-accion]').forEach(b => b.addEventListener('click', () => {
        sel = [...document.querySelectorAll('.chk-doc:checked')];
        if (!sel.length) { showToast('Selecciona al menos un documento.', 'error'); return; }
        accion = b.dataset.accion; paso = 1; pintar(); modal.show();
    }));

    document.getElementById('mfOk').addEventListener('click', () => {
        if (accion.startsWith('retirar') && paso === 1) { paso = 2; pintar(); return; }
        document.getElementById('accionInput').value = accion;
        modal.hide();
        form.requestSubmit();
    });

    document.getElementById('selTodos')?.addEventListener('change', e =>
        document.querySelectorAll('.chk-doc').forEach(c => c.checked = e.target.checked));
})();
</script>
@endpush
@endsection
