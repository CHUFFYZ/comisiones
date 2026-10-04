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
                <a href="{{ route('inicio') }}" class="nav-link {{ request()->routeIs('inicio') ? 'active' : '' }}">
                    <i class="fa-solid fa-house w-20px"></i>
                    <span>Inicio / Noticias</span>
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="{{ route('admin.permisos.index') }}" class="nav-link {{ request()->routeIs('admin.permisos.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-shield w-20px"></i>
                    <span>Administrar Permisos</span>
                </a>
            </li>
            
            <li class="nav-item mb-1">
                <a href="#" class="nav-link text-body-tertiary pe-none opacity-50">
                    <i class="fa-solid fa-file-contract w-20px"></i>
                    <span>Comisiones (Próximamente)</span>
                </a>
            </li>
        </ul>

        <hr class="my-3 opacity-10">

        <div class="px-3 py-2 bg-light rounded-3 text-center">
            <small class="text-muted d-block font-monospace" style="font-size: 0.7rem;">SisComisiones v1.0</small>
            <small class="text-muted d-block" style="font-size: 0.65rem;">FCI · UNACAR 2026</small>
        </div>
    </div>
</div>