<!-- resources/views/components/header.blade.php -->
<header class="app-header app-header--oscuro d-flex align-items-center justify-content-between px-3">
    <div class="d-flex align-items-center gap-3">
        @auth
        <button class="btn btn-logout p-1 d-lg-none" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
            <i class="fa-solid fa-bars fs-5"></i>
        </button>
        @endauth

        <!-- Logo + módulo (BD) + facultad (BD) -->
        <a href="{{ route('inicio') }}" class="d-flex align-items-center gap-2 text-white">
            <img src="{{ asset('img/logo.png') }}" alt="Logo" class="app-logo"
                 onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none')">
            <i class="fa-solid fa-building-columns fs-4 d-none"></i>
            <div>
                <div class="font-syne lh-1 fw-bold fs-6 text-white">{{ $moduloActual->Nombre ?? 'CATI' }}</div>
                <small class="text-white-50 text-xs" title="{{ $facultadActual->Nombre ?? '' }}">
                    {{ $facultadActual->Clave ?? 'FCI' }}{{ '| UNACAR' }}
                </small>
            </div>
        </a>
    </div>

    <div class="d-flex align-items-center gap-3">
        @auth
            <div class="dropdown">
                {{-- auto-close="outside": el menú no se cierra al abrir/cerrar "Mis módulos" --}}
                <button class="btn btn-logout btn-sm d-flex align-items-center gap-2" type="button"
                        data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                    <div class="usu-avatar usu-avatar--sec">
                        {{ strtoupper(substr(auth()->user()->Nombre ?? 'U', 0, 1)) }}
                    </div>
                    <div class="d-none d-sm-block lh-1 text-start">
                        <div class="fw-semibold small">{{ auth()->user()->nombre_completo }}</div>
                        <small class="text-white-50 text-xs">{{ auth()->user()->Matricula }}</small>
                    </div>
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow border-0 menu-usuario">
                    <li class="px-3 py-2">
                        <div class="fw-semibold small">{{ auth()->user()->nombre_completo }}</div>
                        <small class="text-body-secondary">{{ auth()->user()->Rol }}</small>
                    </li>
                    <li><hr class="dropdown-divider"></li>

                    <li>
                        <a class="dropdown-item small" href="{{ route('inicio') }}">
                            <i class="fa-solid fa-house w-20px"></i> Panel principal
                        </a>
                    </li>

                    <!-- Mis módulos: botón colapsable, los módulos son sus opciones -->
                    <li>
                        <button class="dropdown-item small d-flex align-items-center" type="button"
                                data-bs-toggle="collapse" data-bs-target="#menuModulos"
                                aria-expanded="false" aria-controls="menuModulos">
                            <i class="fa-solid fa-table-cells-large w-20px"></i> Mis módulos
                            <i class="fa-solid fa-chevron-down ms-auto text-xs chevron-collapse"></i>
                        </button>
                        <div class="collapse" id="menuModulos">
                            <ul class="list-unstyled mb-0 ps-3">
                                @forelse ($modulosMenu as $m)
                                    <li>
                                        <a class="dropdown-item small" href="{{ route('modulos.show', $m->Clave) }}">
                                            <i class="fa-solid {{ config('modulos.iconos.' . $m->Clave, config('modulos.icono_default')) }} w-20px"></i>
                                            {{ $m->Nombre }}
                                        </a>
                                    </li>
                                @empty
                                    <li class="dropdown-item-text small text-body-secondary">Sin módulos asignados</li>
                                @endforelse
                            </ul>
                        </div>
                    </li>

                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item small text-danger">
                                <i class="fa-solid fa-right-from-bracket w-20px"></i> Cerrar sesión
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        @else
            <div class="d-none d-sm-flex align-items-center gap-2 text-white">
                <div class="usu-avatar usu-avatar--sec"><i class="fa-solid fa-user"></i></div>
                <div class="lh-1"><div class="fw-semibold small">Invitado</div></div>
            </div>
            <a href="{{ route('login') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-right-to-bracket"></i> Login
            </a>
        @endauth
    </div>
</header>