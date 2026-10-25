{{-- Banner de simulación + modal para elegir secretaria (solo superusuario) --}}
@if ($simulado)
    <div class="alert alert-warning rounded-0 mb-0 py-2 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="small">
            <i class="fa-solid fa-user-secret me-1"></i>
            Simulando a <strong>{{ $simulado->nombre_con_grado }}</strong> ({{ $simulado->facultad->Clave ?? '' }} · {{ $simulado->rol_nombre }}).
            Todo lo que hagas quedará a su nombre.
        </span>
        <form action="{{ route('simulacion.terminar') }}" method="POST" class="m-0">
            @csrf @method('DELETE')
            <button class="btn btn-secondary btn-sm">Dejar de simular</button>
        </form>
    </div>
@endif

@if ($puedeSimular)
<div class="modal fade" id="modalSimular" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('simulacion.iniciar') }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Simular secretaria</h5></div>
            <div class="modal-body">
                <label class="form-label" for="IdUsuarioSim">Secretaria <span class="text-danger">*</span></label>
                <select id="IdUsuarioSim" name="IdUsuario" class="form-select" required>
                    <option value="">Selecciona…</option>
                    @foreach ($secretariasSim as $fac => $lista)
                        <optgroup label="{{ $fac }}">
                            @foreach ($lista as $s)
                                <option value="{{ $s->Id }}" @selected($simulado?->Id == $s->Id)>{{ $s->nombre_con_grado }} · {{ $s->rol_nombre }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <div class="form-text">Actuarás con su facultad, tipos de comisión y firma de elaboración.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Simular</button>
            </div>
        </form>
    </div>
</div>
@endif
