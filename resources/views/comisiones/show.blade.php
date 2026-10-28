@extends('layouts.app')

@section('title', $c->NOficio . ' · CATI')

@section('content')
@php $vigente = !$c->esta_anulada; @endphp
<div class="container-fluid p-0">

    {{-- Encabezado y acciones --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <a href="{{ $vigente ? route('comisiones.index') : route('comisiones.anuladas') }}" class="small"><i class="fa-solid fa-arrow-left me-1"></i> Volver</a>
            <h2 class="font-syne text-dark mt-1 mb-1"><span class="font-monospace fs-4">{{ $c->NOficio }}</span></h2>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="badge rounded-pill badge-estado {{ $c->clase_estado }}">{{ $c->estado->Nombre }}</span>
                <!-- @if ($c->constancia)
                    <span class="badge rounded-pill bg-success-subtle text-success-emphasis"><i class="fa-solid fa-certificate me-1"></i>Con constancia</span>
                @elseif ($c->IdEstado == 3)
                    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis">Sin constancia</span>
                @endif -->
                @php $ec = $c->estado_constancias; @endphp
                @if ($ec === 'completa')
                    <span class="badge rounded-pill bg-success-subtle text-success-emphasis"><i class="fa-solid fa-certificate me-1"></i>Constancias completas</span>
                @elseif ($ec === 'parcial')
                    <span class="badge rounded-pill bg-info-subtle text-info-emphasis">Constancias {{ $c->profesores_cubiertos->count() }}/{{ $c->profesores->count() }}</span>
                @elseif ($c->IdEstado == 3)
                    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis">Sin constancia</span>
                @endif
                @unless ($vigente)<span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">Anulada</span>@endunless
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('comisiones.pdf', $c->Id) }}" target="_blank" class="btn btn-ghost is-brand btn-sm"><i class="fa-solid fa-file-pdf me-1"></i> Oficio PDF</a>

            @if ($vigente)
                @puede('MSC', 'actualizar')
                    @if ($c->IdEstado > 1)
                        <form action="{{ route('comisiones.retroceder', $c->Id) }}" method="POST" class="m-0">
                            @csrf @method('PATCH')
                            <button class="btn btn-ghost btn-sm" title="Regresar al estado anterior"><i class="fa-solid fa-backward me-1"></i> Retroceder</button>
                        </form>
                    @endif
                    @if ($c->IdEstado < 3)
                        <form action="{{ route('comisiones.avanzar', $c->Id) }}" method="POST" class="m-0">
                            @csrf @method('PATCH')
                            <button class="btn btn-ghost btn-sm"><i class="fa-solid fa-forward me-1"></i> Avanzar</button>
                        </form>
                    @endif
                    <a href="{{ route('comisiones.edit', $c->Id) }}" class="btn btn-ghost btn-sm"><i class="fa-solid fa-pen me-1"></i> Editar</a>
                @endpuede
                @puede('MSC', 'eliminar')
                    <button type="button" class="btn btn-ghost is-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalAnular">
                        <i class="fa-solid fa-ban me-1"></i> Anular
                    </button>
                @endpuede
            @else
                @puede('MSC', 'eliminar')
                    <form action="{{ route('comisiones.restaurar', $c->Id) }}" method="POST" class="m-0">
                        @csrf @method('PATCH')
                        <button class="btn btn-success btn-sm"><i class="fa-solid fa-rotate-left me-1"></i> Revertir anulación</button>
                    </form>
                @endpuede
            @endif
        </div>
    </div>

    @unless ($vigente)
        <div class="alert alert-warning">Anulada el {{ $c->DeletedAt->format('d/m/y H:i') }}. Revierte la anulación para volver a modificarla.</div>
    @endunless

    @if (in_array($c->IdEstado, [4, 5]))
        <div class="alert {{ $c->IdEstado == 4 ? 'alert-warning' : 'alert-info' }}">
            <strong>{{ $c->IdEstado == 4 ? 'Reporte rechazado' : 'Constancia refutada' }}:</strong>
            {{ $c->motivo_estado ?? 'Sin descripción.' }}
            <div class="small mt-1">
                {{ $c->IdEstado == 4 ? 'El profesor debe subir un reporte corregido.' : 'Modifica la constancia para resolver la observación.' }}
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">

            {{-- Datos --}}
            <div class="card p-4 mb-4">
                <h5 class="font-syne mb-3">Información</h5>
                <dl class="row small mb-0">
                    <dt class="col-sm-3 text-body-secondary">Asunto</dt><dd class="col-sm-9">{{ $c->Asunto }}</dd>
                    <dt class="col-sm-3 text-body-secondary">Tipo</dt><dd class="col-sm-9">{{ $c->tipo->Nombre }}</dd>
                    <dt class="col-sm-3 text-body-secondary">Contexto</dt><dd class="col-sm-9">{{ $c->Contexto ?: '—' }}</dd>
                    <dt class="col-sm-3 text-body-secondary">Cierre</dt><dd class="col-sm-9">{{ $c->Cierre ?: '—' }}</dd>
                    <dt class="col-sm-3 text-body-secondary">Fechas</dt>
                    <dd class="col-sm-9">{{ $c->FechaInicio->format('d/m/y') }}@if ($c->FechaFin) – {{ $c->FechaFin->format('d/m/y') }}@endif</dd>
                    <dt class="col-sm-3 text-body-secondary">Profesores</dt>
                    <dd class="col-sm-9">{{ $c->profesores->map(fn ($p) => $p->GradoAcademico . ' ' . $p->nombre_corto)->join(', ') }}</dd>
                    <dt class="col-sm-3 text-body-secondary">Emitida</dt><dd class="col-sm-9">{{ $c->FechaEmision->format('d/m/y H:i') }} · {{ $c->CreatedBy }}</dd>
                    <dt class="col-sm-3 text-body-secondary">Facultad</dt><dd class="col-sm-9">{{ $c->facultad->Nombre ?? '—' }}</dd>
                </dl>
            </div>
            <div class="card p-4 mb-4">
                <h5 class="font-syne mb-2">Firma del oficio</h5>
                @include('firmas.partials.estado', ['d' => $c])
                <div class="small text-body-secondary mt-2">
                    @if ($c->firmado || $c->sellado) {{ $c->DirectorCargo }} · {{ $c->DirectorNombre }}
                    @else Pendiente: la firma y el sello los aplica Dirección o Secretaría Académica desde «Firmas y sellos». @endif
                </div>
            </div>

            {{-- Reporte del profesor --}}
            <div class="card p-4 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="font-syne mb-1">Reporte del profesor</h5>
                        @if ($c->ReporteRuta)
                            <div class="small text-body-secondary">
                                <i class="fa-solid fa-paperclip me-1"></i>{{ $c->ReporteNombre }}
                                · enviado por {{ $c->reporteUsuario?->nombre_corto ?? '—' }} el {{ $c->ReporteAt?->format('d/m/y H:i') }}
                            </div>
                        @else
                            <div class="small text-body-secondary">Aún no se ha enviado un reporte.</div>
                        @endif
                    </div>
                    @if ($c->ReporteRuta)
                        <div class="d-flex gap-2">
                            <a href="{{ route('comisiones.reporte', $c->Id) }}" target="_blank" class="btn btn-brand-soft btn-sm"><i class="fa-solid fa-download me-1"></i> Abrir reporte</a>
                            @if ($vigente && $c->IdEstado == 3)
                                @puede('MSC', 'actualizar')
                                    <button type="button" class="btn btn-ghost is-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalRechazar">
                                        <i class="fa-solid fa-rotate-left me-1"></i> Rechazar
                                    </button>
                                @endpuede
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Documentos --}}
            <div class="card p-4">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabOficio" type="button">Oficio</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabConstancia" type="button">Constancia</button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tabOficio">
                        <iframe class="doc-frame" src="{{ route('comisiones.vista', $c->Id) }}" title="Oficio" loading="lazy"></iframe>
                    </div>
                    <div class="tab-pane fade" id="tabConstancia">
                        @php
                            $cubiertos  = $c->profesores_cubiertos;
                            $pendientes = $c->profesores->reject(fn ($p) => $cubiertos->contains((int) $p->Id));
                        @endphp

                        @if ($c->constancias->isNotEmpty())
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="selTodasK">
                                    <label class="form-check-label small" for="selTodasK">Seleccionar todas</label>
                                </div>
                                @if ($vigente)
                                    @puede('MSC', 'actualizar')
                                        <button type="button" class="btn btn-ghost btn-sm" data-accion-sel="modalConstancia"><i class="fa-solid fa-pen me-1"></i> Modificar seleccionadas</button>
                                    @endpuede
                                    @puede('MSC', 'eliminar')
                                        <button type="button" class="btn btn-ghost is-danger btn-sm" data-accion-sel="modalQuitarConstancia"><i class="fa-solid fa-trash me-1"></i> Eliminar seleccionadas</button>
                                    @endpuede
                                @endif
                            </div>

                            <div class="list-group mb-3">
                                @foreach ($c->constancias as $k)
                                    <div class="list-group-item d-flex flex-wrap align-items-center gap-2">
                                        <input class="form-check-input mt-0 chk-const" type="checkbox" value="{{ $k->Id }}"
                                            data-contexto="{{ $k->Contexto }}" data-cierre="{{ $k->Cierre }}" aria-label="Seleccionar constancia">
                                        <div class="flex-grow-1 small">
                                            <div class="fw-semibold">{{ $k->profesores->map(fn ($p) => $p->GradoAcademico . ' ' . $p->nombre_corto)->join(', ') }}</div>
                                            <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">{{ $k->profesores->count() > 1 ? 'Grupal' : 'Individual' }}</span>
                                            @include('firmas.partials.estado', ['d' => $k])
                                        </div>
                                        <button type="button" class="btn btn-ghost btn-sm" data-ver-const="{{ route('constancias.vista', $k->Id) }}"><i class="fa-solid fa-eye me-1"></i> Ver</button>
                                        <a href="{{ route('constancias.pdf', $k->Id) }}" target="_blank" class="btn btn-ghost is-success btn-sm"><i class="fa-solid fa-file-pdf me-1"></i> PDF</a>
                                    </div>
                                @endforeach
                            </div>
                            <iframe id="frameConst" class="doc-frame d-none mb-3" title="Constancia"></iframe>
                        @endif

                        @if ($pendientes->isNotEmpty())
                            @if ($vigente && $c->IdEstado == 3)
                                @puede('MSC', 'actualizar')
                                    <form action="{{ route('constancias.store', $c->Id) }}" method="POST" class="border rounded-3 p-3">
                                        @csrf
                                        <div class="form-label">Generar constancia para</div>
                                        <div class="mb-3">
                                            @foreach ($pendientes as $p)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="profesores[]" value="{{ $p->Id }}" id="gp{{ $p->Id }}" checked>
                                                    <label class="form-check-label small" for="gp{{ $p->Id }}">{{ $p->GradoAcademico }} {{ $p->nombre_corto }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                        @if ($pendientes->count() > 1)
                                            <div class="d-flex flex-wrap gap-3 mb-3 small">
                                                <div class="form-check"><input class="form-check-input" type="radio" name="modo" value="grupo" id="mGrupo" checked><label class="form-check-label" for="mGrupo">Una constancia en grupo</label></div>
                                                <div class="form-check"><input class="form-check-input" type="radio" name="modo" value="individual" id="mInd"><label class="form-check-label" for="mInd">Una por profesor</label></div>
                                            </div>
                                        @else
                                            <input type="hidden" name="modo" value="individual">
                                        @endif
                                        <button class="btn btn-success btn-sm"><i class="fa-solid fa-certificate me-1"></i> Generar</button>
                                    </form>
                                @endpuede
                            @else
                                <p class="small text-body-secondary mb-0">Se pueden generar cuando la comisión esté finalizada.</p>
                            @endif
                        @elseif ($c->constancias->isEmpty())
                            <div class="text-center py-5 text-body-secondary">
                                <i class="fa-solid fa-certificate fs-1 opacity-50 mb-2 d-block"></i>
                                <p class="small mb-0">Esta comisión aún no tiene constancias.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Historial --}}
        <div class="col-lg-4">
            <div class="card p-4">
                <h5 class="font-syne mb-3">Historial de estados</h5>
                @include('comisiones.partials.historial')
            </div>
        </div>
    </div>
</div>

{{-- Modales --}}
@puede('MSC', 'eliminar')
<div class="modal fade" id="modalAnular" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form method="POST" action="{{ route('comisiones.anular', $c->Id) }}" class="modal-content">
            @csrf @method('DELETE')
            <div class="modal-header"><h5 class="modal-title">Anular comisión</h5></div>
            <div class="modal-body small">¿Anular la comisión <strong class="font-monospace">{{ $c->NOficio }}</strong>? Podrás revertirlo desde Anulaciones.</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">Anular</button>
            </div>
        </form>
    </div>
</div>

@if ($c->constancias->isNotEmpty())
<div class="modal fade" id="modalQuitarConstancia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form method="POST" action="{{ route('constancias.destroy', $c->Id) }}" class="modal-content">
            @csrf @method('DELETE')
            <div class="modal-header"><h5 class="modal-title">Eliminar constancias</h5></div>
            <div class="modal-body small">
                <div data-sel-inputs></div>
                Se eliminarán <strong data-sel-n>0</strong> constancia(s) y sus archivos. Después podrás generarlas de nuevo.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </div>
        </form>
    </div>
</div>
@endif
@endpuede

@puede('MSC', 'actualizar')
<div class="modal fade" id="modalRechazar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('comisiones.rechazar', $c->Id) }}" class="modal-content">
            @csrf @method('PATCH')
            <div class="modal-header"><h5 class="modal-title">Rechazar reporte</h5></div>
            <div class="modal-body">
                <label class="form-label" for="Motivo">¿Qué debe corregir el profesor? <span class="text-danger">*</span></label>
                <textarea id="Motivo" name="Motivo" rows="4" maxlength="1000" class="form-control" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">Rechazar reporte</button>
            </div>
        </form>
    </div>
</div>

@if ($c->constancias->isNotEmpty())
<div class="modal fade" id="modalConstancia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('constancias.update', $c->Id) }}" class="modal-content">
            @csrf @method('PATCH')
            <div class="modal-header"><h5 class="modal-title">Modificar constancias</h5></div>
            <div class="modal-body">
                <div data-sel-inputs></div>
                <label class="form-label" for="ContextoConst">Contexto <span class="text-danger">*</span></label>
                <textarea id="ContextoConst" name="Contexto" rows="4" maxlength="1000" class="form-control" required></textarea>
                <label class="form-label mt-3" for="CierreConst">Cierre</label>
                <textarea id="CierreConst" name="Cierre" rows="3" maxlength="1000" class="form-control"></textarea>
                <div class="form-text">Se aplicará a <strong data-sel-n>0</strong> constancia(s) y se reescribirán sus PDF. Deberán volver a firmarse{{ $c->IdEstado == 5 ? '; la comisión vuelve a Finalizada' : '' }}.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endif
@endpuede

@if ($errors->any() && !$errors->has('general'))
@push('scripts')
<script>showToast(@json($errors->first()), 'error');</script>
@endpush
@endif

@push('scripts')
<script>
(() => {
    const chks = () => [...document.querySelectorAll('.chk-const')];
    const sel  = () => chks().filter(x => x.checked);

    document.getElementById('selTodasK')?.addEventListener('change', e => chks().forEach(x => x.checked = e.target.checked));

    document.querySelectorAll('[data-accion-sel]').forEach(b => b.addEventListener('click', () => {
        const s = sel();
        if (!s.length) { showToast('Selecciona al menos una constancia.', 'error'); return; }
        const modal = document.getElementById(b.dataset.accionSel);
        modal.querySelector('[data-sel-inputs]').innerHTML = s.map(x => `<input type="hidden" name="constancias[]" value="${x.value}">`).join('');
        modal.querySelector('[data-sel-n]').textContent = s.length;
        const ctx = modal.querySelector('[name=Contexto]');
        if (ctx) {
            ctx.value = s[0].dataset.contexto || '';
            modal.querySelector('[name=Cierre]').value = s[0].dataset.cierre || '';
        }
        bootstrap.Modal.getOrCreateInstance(modal).show();
    }));

    document.querySelectorAll('[data-ver-const]').forEach(b => b.addEventListener('click', () => {
        const f = document.getElementById('frameConst');
        f.src = b.dataset.verConst;
        f.classList.remove('d-none');
    }));
})();
</script>
@endpush
@endsection
