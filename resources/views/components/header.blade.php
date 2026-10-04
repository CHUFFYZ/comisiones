<!-- resources/views/components/header.blade.php -->
<header class="app-header app-header--oscuro d-flex align-items-center justify-content-between px-3">
    <div class="d-flex align-items-center gap-3">
        <!-- Botón Toggle para Offcanvas en vista móvil -->
        <button class="btn btn-logout p-1 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
            <i class="fa-solid fa-bars fs-5"></i>
        </button>

        <!-- Identidad Institucional -->
        <a href="{{ url('/') }}" class="d-flex align-items-center gap-2 text-white">
            <div class="usu-avatar usu-avatar--prof font-syne">FCI</div>
            <div>
                <div class="font-syne lh-1 fw-bold fs-6 text-white">CATI</div>
                <small class="text-white-50" style="font-size: 0.7rem;">FCI · UNACAR</small>
            </div>
        </a>
    </div>

    <!-- Info Usuario & Acciones -->
    <div class="d-flex align-items-center gap-3">
        <div class="d-none d-sm-flex align-items-center gap-2 text-white">
            <div class="usu-avatar usu-avatar--sec">
                {{ strtoupper(substr(auth()->user()->Nombre ?? 'U', 0, 1)) }}
            </div>
            <div class="lh-1 text-end">
                <div class="fw-semibold text-white small">
                    {{ auth()->user()->Nombre ?? 'Usuario' }} {{ auth()->user()->Apellido ?? '' }}
                </div>
                <small class="text-white-50" style="font-size: 0.68rem;">
                    {{ auth()->user()->Matricula ?? 'Invitado' }}
                </small>
            </div>
        </div>

        <form action="{{ route('login') }}" method="GET" class="m-0">
            @csrf
            <button type="submit" class="btn btn-logout btn-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span class="d-none d-md-inline">Salir</span>
            </button>
        </form>
    </div>
</header>