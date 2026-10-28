<!-- resources/views/components/header.blade.php -->
<header class="app-header app-header--oscuro d-flex align-items-center justify-content-between px-3">
    <div class="d-flex align-items-center gap-3">
        @auth
            <button class="btn btn-logout px-2 py-1" type="button" data-menu-toggle aria-label="Mostrar u ocultar menú">
                <i class="fa-solid fa-bars fs-5"></i>
            </button>
            <div class="lh-1">
                <div class="font-syne fw-bold fs-6 text-white">{{ $moduloActual->Nombre ?? 'CATI' }}</div>
                <small class="text-white-50 text-xs" title="{{ $facultadActual->Nombre ?? '' }}">{{ $facultadActual->Clave ?? 'FCI' }} | UNACAR</small>
            </div>
        @else
            <a href="{{ route('inicio') }}" class="d-flex align-items-center gap-2 text-white">
                <img src="{{ asset('img/logo.png') }}" alt="Logo" class="app-logo"
                     onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none')">
                <i class="fa-solid fa-building-columns fs-4 d-none"></i>
                <div>
                    <div class="font-syne lh-1 fw-bold fs-6 text-white">{{ $moduloActual->Nombre ?? 'CATI' }}</div>
                    <small class="text-white-50 text-xs">{{ $facultadActual->Clave ?? 'FCI' }} | UNACAR</small>
                </div>
            </a>
        @endauth
    </div>

    <div class="d-flex align-items-center gap-2">
        @auth
            <!-- Chat -->
            <a href="{{ route('chat.index') }}" class="btn btn-logout px-2 py-1 position-relative" title="Chat" aria-label="Chat">
                <i class="fa-solid fa-comments"></i>
                @if ($chatNoLeidos > 0)
                    <span class="badge rounded-pill bg-danger badge-contador">{{ $chatNoLeidos > 99 ? '99+' : $chatNoLeidos }}</span>
                @endif
            </a>

            <!-- Notificaciones -->
            <div class="dropdown">
                <button class="btn btn-logout px-2 py-1 position-relative" type="button" data-bs-toggle="dropdown"
                        data-bs-auto-close="outside" aria-expanded="false" aria-label="Notificaciones" title="Notificaciones">
                    <i class="fa-solid fa-bell"></i>
                    @if ($notifNoLeidas > 0)
                        <span class="badge rounded-pill bg-danger badge-contador">{{ $notifNoLeidas > 99 ? '99+' : $notifNoLeidas }}</span>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 menu-notif p-0">
                    <li class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                        <span class="fw-semibold small">Notificaciones</span>
                        @if ($notifNoLeidas > 0)
                            <form action="{{ route('notificaciones.leer_todas') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-link btn-sm p-0">Marcar como leídas</button>
                            </form>
                        @endif
                    </li>
                    @forelse ($notifRecientes as $n)
                        <li>
                            <a class="dropdown-item notif-item py-2 {{ $n->LeidaAt ? '' : 'notif-nueva' }}" href="{{ route('notificaciones.ir', $n->Id) }}">
                                <div class="fw-semibold small">{{ $n->Titulo }}</div>
                                @if ($n->Mensaje)<div class="small text-body-secondary">{{ \Illuminate\Support\Str::limit($n->Mensaje, 90) }}</div>@endif
                                <div class="text-xs text-body-tertiary">{{ $n->CreatedAt->locale('es')->diffForHumans() }}</div>
                            </a>
                        </li>
                    @empty
                        <li class="px-3 py-4 text-center small text-body-secondary">No tienes notificaciones.</li>
                    @endforelse
                    <li class="border-top text-center"><a class="dropdown-item small py-2" href="{{ route('notificaciones.index') }}">Ver todas</a></li>
                </ul>
            </div>

            <!-- Usuario -->
            <div class="dropdown">
                <button class="btn btn-logout btn-sm d-flex align-items-center gap-2" type="button"
                        data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                    @if (auth()->user()->foto_src)
                        <img src="{{ auth()->user()->foto_src }}" alt="" class="usu-avatar">
                    @else
                        <div class="usu-avatar usu-avatar--sec">{{ strtoupper(substr(auth()->user()->Nombre ?? 'U', 0, 1)) }}</div>
                    @endif
                    <div class="d-none d-sm-block lh-1 text-start">
                        <div class="fw-semibold small">{{ auth()->user()->nombre_corto }}</div>
                        <small class="text-white-50 text-xs">{{ auth()->user()->Matricula }}</small>
                    </div>
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow border-0 menu-usuario">
                    <li class="px-3 py-2">
                        <div class="fw-semibold small">{{ auth()->user()->nombre_corto }}</div>
                        <small class="text-body-secondary">{{ auth()->user()->rol_nombre }}</small>
                    </li>
                    <li><hr class="dropdown-divider"></li>

                    <li>
                        <a class="dropdown-item small" href="{{ route('inicio') }}">
                            <i class="fa-solid fa-house w-20px"></i> Panel principal
                        </a>
                    </li>
                    <li><a class="dropdown-item small" href="{{ route('perfil') }}"><i class="fa-solid fa-id-card w-20px"></i> Mi perfil</a></li>
                    @if ($puedeSimular)
                        <li><button type="button" class="dropdown-item small" data-bs-toggle="modal" data-bs-target="#modalSimular">
                            <i class="fa-solid fa-user-secret w-20px"></i> Simular usuario</button></li>
                    @endif

                    <li>
                        <button class="dropdown-item small d-flex align-items-center" type="button"
                                data-bs-toggle="collapse" data-bs-target="#menuModulos"
                                aria-expanded="false" aria-controls="menuModulos">
                            <i class="fa-solid fa-table-cells-large w-20px"></i> Mis módulos
                            <i class="fa-solid fa-chevron-down ms-auto text-xs chevron-collapse"></i>
                        </button>
                        <div class="collapse" id="menuModulos">
                            <ul class="list-unstyled mb-0 ps-3">
                                @if ($esProfesor)
                                    <li>
                                        <a class="dropdown-item small" href="{{ route('profesor.panel') }}">
                                            <i class="fa-solid fa-chalkboard-user w-20px"></i> Mis comisiones
                                        </a>
                                    </li>
                                @endif
                                @if ($puedeFirmar)
                                    <li>
                                        <a class="dropdown-item small" href="{{ route('firmas.index') }}">
                                            <i class="fa-solid fa-signature w-20px"></i> Firmas y sellos
                                            @if ($firmasPendientes > 0)<span class="badge rounded-pill bg-danger ms-1">{{ $firmasPendientes }}</span>@endif
                                        </a>
                                    </li>
                                @endif
                                @forelse ($modulosMenu as $m)
                                    <li>
                                        <a class="dropdown-item small" href="{{ route('modulos.show', $m->Clave) }}">
                                            <i class="fa-solid {{ config('modulos.iconos.' . $m->Clave, config('modulos.icono_default')) }} w-20px"></i>
                                            {{ $m->Nombre }}
                                        </a>
                                    </li>
                                @empty
                                    @unless ($esProfesor || $puedeFirmar)<li class="dropdown-item-text small text-body-secondary">Sin módulos asignados</li>@endunless
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
