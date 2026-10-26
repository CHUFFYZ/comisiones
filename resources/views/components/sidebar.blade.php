<!-- resources/views/components/sidebar.blade.php -->
<aside class="sidebar-wrapper" id="sidebar" aria-label="Menú principal">
    <!-- Marca: ocupa la parte superior del lado izquierdo -->
    <div class="sidebar-brand d-flex align-items-center justify-content-between px-3">
        <a href="{{ route('inicio') }}" class="d-flex align-items-center gap-2 text-white">
            <img src="{{ asset('img/logo.png') }}" alt="Logo" class="app-logo"
                 onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none')">
            <i class="fa-solid fa-building-columns fs-4 d-none"></i>
            <div class="lh-1">
                <div class="font-syne fw-bold fs-6 text-white">CATI</div>
                <small class="text-white-50 text-xs" title="{{ $facultadActual->Nombre ?? '' }}">{{ $facultadActual->Clave ?? 'FCI' }} | UNACAR</small>
            </div>
        </a>
        <button type="button" class="btn-close btn-close-white d-lg-none" data-menu-toggle aria-label="Cerrar menú"></button>
    </div>

    <div class="sidebar-body">

        {{-- Navegación global --}}
        <ul class="nav nav-pills flex-column mb-2">
            <li class="nav-item mb-1">
                <a href="{{ route('inicio') }}" class="nav-link {{ request()->routeIs('inicio') ? 'active' : '' }}">
                    <i class="fa-solid fa-house w-20px"></i>
                    <span>Panel principal</span>
                </a>
            </li>

            @if ($esProfesor)
                <li class="nav-item mb-1">
                    <a href="{{ route('profesor.panel') }}" class="nav-link {{ request()->routeIs('profesor.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-chalkboard-user w-20px"></i>
                        <span>Mis comisiones</span>
                    </a>
                </li>
            @endif

            <li class="nav-item mb-1">
                <a href="{{ route('chat.index') }}" class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-comments w-20px"></i>
                    <span>Chat</span>
                    @if ($chatNoLeidos > 0)<span class="badge rounded-pill bg-danger ms-auto">{{ $chatNoLeidos }}</span>@endif
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="{{ route('notificaciones.index') }}" class="nav-link {{ request()->routeIs('notificaciones.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-bell w-20px"></i>
                    <span>Notificaciones</span>
                    @if ($notifNoLeidas > 0)<span class="badge rounded-pill bg-danger ms-auto">{{ $notifNoLeidas }}</span>@endif
                </a>
            </li>

            <li class="nav-item">
                <button type="button" id="btnMisModulos"
                        class="nav-link w-100 border-0 bg-transparent text-start"
                        data-bs-toggle="collapse" data-bs-target="#sidebarModulos"
                        aria-expanded="true" aria-controls="sidebarModulos">
                    <i class="fa-solid fa-table-cells-large w-20px"></i>
                    <span>Mis módulos</span>
                    <i class="fa-solid fa-chevron-down ms-auto text-xs chevron-collapse"></i>
                </button>

                <div class="collapse show" id="sidebarModulos">
                    <ul class="nav nav-pills flex-column ps-3 mt-1 submenu-modulos">
                        @forelse ($modulosMenu as $m)
                            <li class="nav-item mb-1">
                                <a href="{{ route('modulos.show', $m->Clave) }}"
                                   class="nav-link py-2 small {{ ($moduloEnUso->Clave ?? null) === $m->Clave ? 'active' : '' }}">
                                    <i class="fa-solid {{ config('modulos.iconos.' . $m->Clave, config('modulos.icono_default')) }} w-20px"></i>
                                    <span>{{ $m->Nombre }}</span>
                                </a>
                            </li>
                        @empty
                            <li class="small text-body-secondary px-3 py-2">Sin módulos asignados</li>
                        @endforelse
                    </ul>
                </div>
            </li>
        </ul>

        {{-- Opciones del módulo actual, filtradas por permiso --}}
        @if ($moduloEnUso)
            <hr class="my-2 opacity-10">

            <div class="px-2 mt-2 mb-2">
                <span class="form-label text-uppercase text-muted fw-bold">{{ $moduloEnUso->Nombre }}</span>
            </div>

            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item mb-1">
                    <a href="{{ route('modulos.show', $moduloEnUso->Clave) }}"
                       class="nav-link {{ request()->routeIs('modulos.show') ? 'active' : '' }}">
                        <i class="fa-solid fa-gauge w-20px"></i>
                        <span>Resumen</span>
                    </a>
                </li>

                @foreach ($opcionesModulo as $op)
                    <li class="nav-item mb-1">
                        @if ($op['url'])
                            <a href="{{ $op['url'] }}"
                               class="nav-link {{ request()->routeIs($op['ruta'], $op['ruta'] . '.*') ? 'active' : '' }}">
                                <i class="fa-solid {{ $op['icono'] }} w-20px"></i>
                                <span>{{ $op['nombre'] }}</span>
                            </a>
                        @else
                            <span class="nav-link text-body-tertiary pe-none opacity-50">
                                <i class="fa-solid {{ $op['icono'] }} w-20px"></i>
                                <span>{{ $op['nombre'] }} <small>(Próximamente)</small></span>
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="px-2 mt-3 small text-body-secondary">
                Elige un módulo para ver sus opciones.
            </p>
        @endif

        <hr class="my-3 opacity-10">

        <div class="px-3 py-2 bg-light rounded-3 text-center">
            <small class="text-muted d-block font-monospace text-xs">CATI v1.0</small>
            <small class="text-muted d-block text-xs">{{ $facultadActual->Clave ?? 'FCI' }} · UNACAR 2026</small>
        </div>
    </div>
</aside>

@push('scripts')
<script>
    // Recuerda si "Mis módulos" quedó colapsado entre páginas
    (function () {
        const panel = document.getElementById('sidebarModulos');
        const btn   = document.getElementById('btnMisModulos');
        if (!panel || !btn) return;
        const KEY = 'cati.sidebar.modulos';

        try {
            if (localStorage.getItem(KEY) === 'cerrado') {
                panel.classList.remove('show');
                btn.setAttribute('aria-expanded', 'false');
            }
        } catch (e) {}

        panel.addEventListener('hidden.bs.collapse', () => { try { localStorage.setItem(KEY, 'cerrado'); } catch (e) {} });
        panel.addEventListener('shown.bs.collapse',  () => { try { localStorage.setItem(KEY, 'abierto'); } catch (e) {} });
    })();
</script>
@endpush
