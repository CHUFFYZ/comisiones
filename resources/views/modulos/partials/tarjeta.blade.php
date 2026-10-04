<div class="col">
    <a href="{{ route('modulos.show', $modulo->Clave) }}" class="text-decoration-none">
        <div class="card card-hover h-100 p-4">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle fs-4">
                    <i class="fa-solid {{ config('modulos.iconos.' . $modulo->Clave, config('modulos.icono_default')) }}"></i>
                </div>
                <div>
                    <h5 class="font-syne mb-1">{{ $modulo->Nombre }}</h5>
                    <p class="text-muted small mb-0">{{ $modulo->Descripcion }}</p>
                </div>
            </div>
        </div>
    </a>
</div>