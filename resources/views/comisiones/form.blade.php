@extends('layouts.app')

@php $edit = $comision !== null; @endphp

@section('title', ($edit ? 'Editar comisión' : 'Nueva comisión') . ' · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h2 class="font-syne text-dark mb-1">{{ $edit ? 'Editar comisión ' . $comision->NOficio : 'Nueva comisión' }}</h2>
        <p class="text-muted small mb-0">
            {{ $edit ? 'Al guardar se reescribe el oficio guardado.' : 'El número de oficio se genera automáticamente.' }}
            La vista previa se actualiza mientras escribes.
        </p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <div class="row g-4">
        {{-- Formulario --}}
        <div class="col-xl-6">
            <form id="formComision" action="{{ $edit ? route('comisiones.update', $comision->Id) : route('comisiones.store') }}" method="POST" class="card p-4">
                @csrf
                @if ($edit)
                    @method('PUT')
                    <input type="hidden" name="_id" value="{{ $comision->Id }}">
                @endif
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="IdTipoComision">Tipo de comisión <span class="text-danger">*</span></label>
                        <select id="IdTipoComision" name="IdTipoComision" class="form-select" required>
                            <option value="">Selecciona…</option>
                            @foreach ($tipos as $t)
                                <option value="{{ $t->Id }}" data-desc="{{ $t->Descripcion }}"
                                    @selected(old('IdTipoComision', $comision->IdTipoComision ?? null) == $t->Id)>{{ $t->Nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="IdPlantilla">Plantilla <span class="text-danger">*</span></label>
                        <select id="IdPlantilla" name="IdPlantilla" class="form-select" required>
                            @foreach ($plantillas as $p)
                                <option value="{{ $p->Id }}" @selected(old('IdPlantilla', $comision->IdPlantilla ?? null) == $p->Id)>Plantilla #{{ $p->Id }} · {{ $p->Facultad }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="Asunto">Asunto <span class="text-danger">*</span></label>
                        <input type="text" id="Asunto" name="Asunto" value="{{ old('Asunto', $comision->Asunto ?? '') }}" maxlength="255" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="Contexto">Contexto <span class="text-danger">*</span></label>
                        <textarea id="Contexto" name="Contexto" rows="3" maxlength="1000" class="form-control" required
                                  placeholder="Completa la frase: «…ha sido comisionado para [contexto]»">{{ old('Contexto', $comision->Contexto ?? '') }}</textarea>
                        <div class="form-text">Se propone según el tipo, pero puedes escribir el tuyo (en minúscula inicial, sin punto final).</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="FechaInicio">Fecha de inicio <span class="text-danger">*</span></label>
                        <input type="date" id="FechaInicio" name="FechaInicio" class="form-control" required
                               @unless ($edit) min="{{ now()->toDateString() }}" @endunless
                               value="{{ old('FechaInicio', $comision?->FechaInicio?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="FechaFin">Fecha final</label>
                        <input type="date" id="FechaFin" name="FechaFin" class="form-control"
                               value="{{ old('FechaFin', $comision?->FechaFin?->format('Y-m-d')) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Profesores <span class="text-danger">*</span></label>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-transparent"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="search" id="buscaProf" class="form-control" placeholder="Buscar profesor…">
                        </div>
                        @php $marcados = old('profesores', $edit ? $comision->profesores->pluck('Id')->all() : []); @endphp
                        <div class="border rounded-3 p-2 lista-scroll bg-white">
                            @forelse ($profesores as $p)
                                <div class="form-check prof-item">
                                    <input class="form-check-input" type="checkbox" name="profesores[]" value="{{ $p->Id }}" id="p{{ $p->Id }}"
                                           @checked(in_array($p->Id, $marcados))>
                                    <label class="form-check-label small" for="p{{ $p->Id }}">{{ $p->GradoAcademico }} {{ $p->nombre_corto }}</label>
                                </div>
                            @empty
                                <p class="small text-body-secondary mb-0">No hay profesores registrados en tu facultad.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="col-12 d-flex gap-2 justify-content-end">
                        <a href="{{ $edit ? route('comisiones.show', $comision->Id) : route('comisiones.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Guardar</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Vista previa en vivo --}}
        <div class="col-xl-6">
            <div class="card p-3 sticky-previa">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="font-syne mb-0"><i class="fa-solid fa-eye me-1"></i> Vista previa del oficio</h6>
                    <span id="previaCarga" class="spinner-border spinner-border-sm text-secondary d-none" role="status"></span>
                </div>
                <iframe id="previa" class="doc-frame" title="Vista previa del oficio"></iframe>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const form   = document.getElementById('formComision');
    const marco  = document.getElementById('previa');
    const carga  = document.getElementById('previaCarga');
    const tipo   = document.getElementById('IdTipoComision');
    const ctx    = document.getElementById('Contexto');
    let temporizador, ultimaSugerida = '';

    // Busca de profesores
    document.getElementById('buscaProf').addEventListener('input', e => {
        const t = e.target.value.toLowerCase();
        document.querySelectorAll('.prof-item').forEach(el =>
            el.classList.toggle('d-none', !el.textContent.toLowerCase().includes(t)));
    });

    // Sugerir el contexto según el tipo (solo si está vacío o es la sugerencia anterior)
    tipo.addEventListener('change', () => {
        const d = tipo.selectedOptions[0]?.dataset.desc || '';
        if (ctx.value.trim() === '' || ctx.value === ultimaSugerida) {
            ctx.value = d ? d.charAt(0).toLowerCase() + d.slice(1) : '';
            ultimaSugerida = ctx.value;
        }
    });

    async function actualizarPrevia() {
        carga.classList.remove('d-none');
        try {
            const datos = new FormData(form);
            datos.delete('_method'); // la vista previa siempre es POST
            const r = await fetch(@json(route('comisiones.previa')), {
                method: 'POST', body: datos, headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            marco.srcdoc = r.ok ? await r.text() : '<p style="font-family:sans-serif;padding:20px">No se pudo generar la vista previa.</p>';
        } catch (e) {
            marco.srcdoc = '<p style="font-family:sans-serif;padding:20px">No se pudo generar la vista previa.</p>';
        } finally {
            carga.classList.add('d-none');
        }
    }

    const programar = () => { clearTimeout(temporizador); temporizador = setTimeout(actualizarPrevia, 450); };
    form.addEventListener('input', programar);
    form.addEventListener('change', programar);
    actualizarPrevia();
</script>
@endpush
@endsection
