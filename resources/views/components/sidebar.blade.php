<!-- resources/views/components/sidebar.blade.php -->
<div class="offcanvas-lg offcanvas-start sidebar-wrapper flex-shrink-0 p-3" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header border-bottom mb-2 pb-2">
        <h5 class="offcanvas-title font-syne text-dark" id="sidebarOffcanvasLabel">Menú Principal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarOffcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body flex-column p-0">
        <div class="px-2 mb-3">
            <span class="form-label text-uppercase text-muted fw-bold">Navegación</span>
        </div>

        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item mb-1">
                <a href="{{ route('modulos.index') }}" class="nav-link {{ request()->routeIs('modulos.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-table-cells-large w-20px"></i>
                    <span>Mis módulos</span>
                </a>
            </li>

            @foreach ($modulosMenu as $m)
                <li class="nav-item mb-1">
                    <a href="{{ route('modulos.show', $m->Clave) }}"
                       class="nav-link {{ request()->routeIs('modulos.show') && ($moduloActual->Clave ?? null) === $m->Clave ? 'active' : '' }}">
                        <i class="fa-solid {{ config('modulos.iconos.' . $m->Clave, config('modulos.icono_default')) }} w-20px"></i>
                        <span>{{ $m->Nombre }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        <hr class="my-3 opacity-10">

        <div class="px-3 py-2 bg-light rounded-3 text-center">
            <small class="text-muted d-block font-monospace text-xs">CATI v1.0</small>
            <small class="text-muted d-block text-xs">{{ $facultadActual->Clave ?? 'FCI' }} · UNACAR 2026</small>
        </div>
    </div>
</div>