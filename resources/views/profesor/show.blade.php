@extends('layouts.app')

@section('title', $c->NOficio . ' · Mis comisiones')

@section('content')
@php $misConst = $c->constancias->filter(fn ($k) => $k->profesores->contains('Id', auth()->id())); @endphp
@php($constancias = $c->constancias->filter(fn ($k) => $k->profesores->contains('Id', auth()->user()->Id)))
<div class="container-fluid p-0">
    <div class="mb-3">
        <a href="{{ route('profesor.panel') }}" class="small"><i class="fa-solid fa-arrow-left me-1"></i> Mis comisiones</a>
        <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
            <h2 class="font-syne text-dark mb-0 fs-4 font-monospace">{{ $c->NOficio }}</h2>
            <span class="badge rounded-pill badge-estado {{ $c->clase_estado }}">{{ $c->estado->Nombre }}</span>
        </div>
    </div>

    @if (in_array($c->IdEstado, [4, 5]))
        <div class="alert {{ $c->IdEstado == 4 ? 'alert-warning' : 'alert-info' }}">
            <strong>{{ $c->IdEstado == 4 ? 'Tu reporte fue rechazado' : 'Refutaste la constancia' }}:</strong> {{ $c->motivo_estado ?? 'Sin descripción.' }}
            <div class="small mt-1">{{ $c->IdEstado == 4 ? 'Sube un reporte corregido para finalizar la comisión.' : 'Secretaría corregirá la constancia.' }}</div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card p-4 mb-4">
                <h5 class="font-syne mb-3">Información</h5>
                <dl class="row small mb-0">
                    <dt class="col-sm-3 text-body-secondary">Asunto</dt><dd class="col-sm-9">{{ $c->Asunto }}</dd>
                    <dt class="col-sm-3 text-body-secondary">Tipo</dt><dd class="col-sm-9">{{ $c->tipo->Nombre }}</dd>
                    <dt class="col-sm-3 text-body-secondary">Contexto</dt><dd class="col-sm-9">{{ $c->Contexto ?: '—' }}</dd>
                    <dt class="col-sm-3 text-body-secondary">Fechas</dt>
                    <dd class="col-sm-9">{{ $c->FechaInicio->format('d/m/y') }}@if ($c->FechaFin) – {{ $c->FechaFin->format('d/m/y') }}@endif</dd>
                    <dt class="col-sm-3 text-body-secondary">Profesores</dt>
                    <dd class="col-sm-9">{{ $c->profesores->map(fn ($p) => $p->GradoAcademico . ' ' . $p->nombre_corto)->join(', ') }}</dd>
                </dl>
            </div>

            {{-- Acciones del profesor --}}
            <div class="card p-4 mb-4">
                <h5 class="font-syne mb-3">Reporte</h5>

                @if ($c->ReporteRuta)
                    <p class="small mb-3">
                        <i class="fa-solid fa-paperclip me-1"></i>
                        <a href="{{ route('profesor.reporte.descargar', $c->Id) }}" target="_blank">{{ $c->ReporteNombre }}</a>
                        · enviado por {{ $c->reporteUsuario?->nombre_corto ?? '—' }} el {{ $c->ReporteAt?->format('d/m/y H:i') }}
                    </p>
                @else
                    <p class="small text-body-secondary mb-3">Aún no hay reporte.</p>
                @endif

                @if ($c->IdEstado == 1)
                    <form action="{{ route('profesor.iniciar', $c->Id) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="btn btn-primary btn-sm"><i class="fa-solid fa-play me-1"></i> Marcar como En proceso</button>
                    </form>
                @else
                    <form action="{{ route('profesor.reporte', $c->Id) }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-8">
                            <label class="form-label" for="Reporte">{{ $c->ReporteRuta ? 'Reemplazar reporte' : 'Subir reporte' }} (PDF o Word)</label>
                            <input type="file" id="Reporte" name="Reporte" accept=".pdf,.doc,.docx" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary btn-sm w-100">
                                <i class="fa-solid fa-paper-plane me-1"></i>
                                {{ $c->IdEstado == 2 ? 'Enviar y finalizar' : ($c->IdEstado == 4 ? 'Enviar corrección' : 'Reemplazar') }}
                            </button>
                        </div>
                        @if ($c->profesores->count() > 1)
                            <div class="col-12 form-text">Es una comisión en grupo: basta con que un profesor envíe el reporte; si otro lo sube, reemplaza al anterior.</div>
                        @endif
                    </form>
                    @error('Reporte')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                @endif
            </div>

            <div class="card p-4">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabOficio" type="button">Oficio</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabConstancia" type="button">Constancia</button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tabOficio">
                        <a href="{{ route('profesor.oficio', [$c->Id, 'pdf']) }}" target="_blank" class="btn btn-ghost is-brand btn-sm mb-3"><i class="fa-solid fa-file-pdf me-1"></i> Oficio PDF</a>
                        <iframe class="doc-frame" src="{{ route('profesor.oficio', [$c->Id, 'vista']) }}" title="Oficio" loading="lazy"></iframe>
                    </div>
                    <div class="tab-pane fade" id="tabConstancia">
                        @if ($misConst->isNotEmpty())
                            <div class="list-group mb-3">
                                @foreach ($misConst as $k)
                                    <div class="list-group-item d-flex flex-wrap align-items-center gap-2 small">
                                        <div class="flex-grow-1">
                                            <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">{{ $k->profesores->count() > 1 ? 'Constancia grupal' : 'Constancia individual' }}</span>
                                            @include('firmas.partials.estado', ['d' => $k])
                                        </div>
                                        <button type="button" class="btn btn-ghost btn-sm" data-ver-const="{{ route('profesor.constancia', [$c->Id, $k->Id, 'vista']) }}"><i class="fa-solid fa-eye me-1"></i> Ver</button>
                                        <a href="{{ route('profesor.constancia', [$c->Id, $k->Id, 'pdf']) }}" target="_blank" class="btn btn-ghost is-success btn-sm"><i class="fa-solid fa-file-pdf me-1"></i> PDF</a>
                                    </div>
                                @endforeach
                            </div>
                            @if ($c->IdEstado == 3)
                                <button type="button" class="btn btn-ghost is-danger btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#modalRefutar"><i class="fa-solid fa-gavel me-1"></i> Refutar constancia</button>
                            @endif
                            <iframe id="frameConst" class="doc-frame d-none" title="Constancia"></iframe>
                        @else
                            <div class="text-center py-5 text-body-secondary small">Secretaría aún no genera tu constancia.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card p-4">
                <h5 class="font-syne mb-3">Historial</h5>
                @include('comisiones.partials.historial')
            </div>
        </div>
    </div>
</div>

@if ($misConst->isNotEmpty() && $c->IdEstado == 3)
<div class="modal fade" id="modalRefutar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('profesor.refutar', $c->Id) }}" class="modal-content">
            @csrf @method('PATCH')
            <div class="modal-header"><h5 class="modal-title">Refutar constancia</h5></div>
            <div class="modal-body">
                <label class="form-label" for="Motivo">¿Qué está incorrecto? <span class="text-danger">*</span></label>
                <textarea id="Motivo" name="Motivo" rows="4" maxlength="1000" class="form-control" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">Refutar</button>
            </div>
        </form>
    </div>
</div>
@endif

@if ($errors->has('Motivo') || $errors->has('Reporte'))
@push('scripts')
<script>showToast(@json($errors->first()), 'error');</script>
@endpush
@endif
@push('scripts')
<script>document.querySelectorAll('[data-ver-const]').forEach(b=>b.addEventListener('click',()=>{const f=document.getElementById('frameConst');f.src=b.dataset.verConst;f.classList.remove('d-none');}));</script>
@endpush
@endsection
