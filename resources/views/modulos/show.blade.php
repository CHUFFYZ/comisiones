@extends('layouts.app')

@section('title', $modulo->Nombre . ' · CATI')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="font-syne text-dark mb-1">{{ $modulo->Nombre }}</h2>
            <p class="text-muted small mb-0">{{ $modulo->Descripcion }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis font-monospace">Permiso: {{ $permiso }}</span>
            @if ($puedeReporte)
                <a href="{{ route('reportes.index', $modulo->Clave) }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-print me-1"></i> Generar reporte</a>
            @endif
            @if ($stats) @puede('MSC', 'crear')
                <a href="{{ route('comisiones.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i> Nueva comisión</a>
            @endpuede @endif
        </div>
    </div>

@if ($stats)
    @php
        $iconos = [1 => 'fa-flag', 2 => 'fa-spinner', 3 => 'fa-circle-check', 4 => 'fa-rotate-left', 5 => 'fa-gavel'];
    @endphp

    {{-- Indicadores por estado --}}
    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-5 g-3 mb-3">
        @foreach ($stats['estados'] as $e)
            <div class="col">
                <a href="{{ route('comisiones.index', ['estado' => $e['id']]) }}" class="text-decoration-none">
                    <div class="card card-hover stat-card h-100">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div>
                                <div class="stat-num">{{ $e['n'] }}</div>
                                <div class="stat-label">{{ $e['nombre'] }}</div>
                            </div>
                            <div class="stat-icon {{ $e['clase'] }}"><i class="fa-solid {{ $iconos[$e['id']] ?? 'fa-circle' }}"></i></div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-3">
        <div class="col">
            <div class="card stat-card h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div><div class="stat-num">{{ $stats['total'] }}</div><div class="stat-label">Comisiones totales</div></div>
                    <div class="stat-icon bg-primary-subtle text-primary"><i class="fa-solid fa-layer-group"></i></div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stat-card h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div><div class="stat-num">{{ $stats['sinConstancia']['n'] }}</div><div class="stat-label">Finalizadas sin constancia</div></div>
                    <div class="stat-icon estado-proceso"><i class="fa-solid fa-certificate"></i></div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stat-card h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div><div class="stat-num">{{ $stats['porVencer']['n'] }}</div><div class="stat-label">Por vencer o vencidas</div></div>
                    <div class="stat-icon estado-no-iniciado"><i class="fa-solid fa-hourglass-half"></i></div>
                </div>
            </div>
        </div>
        <div class="col">
            @php $verAnuladas = app(\App\Services\AccesoService::class)->puede(auth()->user(), 'MSC', 'eliminar'); @endphp
            <a href="{{ $verAnuladas ? route('comisiones.anuladas') : '#' }}" class="text-decoration-none {{ $verAnuladas ? '' : 'pe-none' }}">
                <div class="card card-hover stat-card h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div><div class="stat-num">{{ $stats['anuladas'] }}</div><div class="stat-label">Anuladas ({{ $stats['total'] }} vigentes)</div></div>
                        <div class="stat-icon bg-secondary-subtle text-secondary"><i class="fa-solid fa-ban"></i></div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    @if ($pendFirma)
    <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">
        <div class="col">
            <div class="card stat-card h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div><div class="stat-num">{{ $pendFirma['comisiones']['n'] }}</div><div class="stat-label">Comisiones sin firma o sello</div></div>
                    <div class="stat-icon estado-no-iniciado"><i class="fa-solid fa-signature"></i></div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stat-card h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div><div class="stat-num">{{ $pendFirma['constancias']['n'] }}</div><div class="stat-label">Constancias sin firma o sello</div></div>
                    <div class="stat-icon estado-proceso"><i class="fa-solid fa-stamp"></i></div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Gráficas --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="card p-3 h-100">
                <h6 class="font-syne mb-3">Comisiones por estado</h6>
                <div class="position-relative graf-caja"><canvas id="grafEstados"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card p-3 h-100">
                <h6 class="font-syne mb-3">Por tipo de comisión</h6>
                <div class="position-relative graf-caja"><canvas id="grafTipos"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card p-3 h-100">
                <h6 class="font-syne mb-3">Últimos 6 meses</h6>
                <div class="position-relative graf-caja"><canvas id="grafMeses"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Pendientes --}}
    <div class="row g-3 {{ $pendFirma ? 'mb-3' : '' }}">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white border-0 pt-3 px-4 font-syne fw-bold">Finalizadas sin constancia</div>
                <div class="list-group list-group-flush">
                    @forelse ($stats['sinConstancia']['lista'] as $c)
                        <a href="{{ route('comisiones.show', $c->Id) }}" class="list-group-item list-group-item-action px-4 small">
                            <span class="font-monospace">{{ $c->NOficio }}</span> · {{ \Illuminate\Support\Str::limit($c->Asunto, 55) }}
                        </a>
                    @empty
                        <div class="px-4 py-4 small text-body-secondary">No hay constancias pendientes.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white border-0 pt-3 px-4 font-syne fw-bold">Por vencer o vencidas</div>
                <div class="list-group list-group-flush">
                    @forelse ($stats['porVencer']['lista'] as $c)
                        <a href="{{ route('comisiones.show', $c->Id) }}" class="list-group-item list-group-item-action px-4 small d-flex justify-content-between gap-2">
                            <span><span class="font-monospace">{{ $c->NOficio }}</span> · {{ \Illuminate\Support\Str::limit($c->Asunto, 40) }}</span>
                            <span class="text-nowrap {{ $c->FechaFin->lt(today()) ? 'text-danger' : 'text-body-secondary' }}">{{ $c->FechaFin->format('d/m/y') }}</span>
                        </a>
                    @empty
                        <div class="px-4 py-4 small text-body-secondary">Ninguna comisión próxima a vencer.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @if ($pendFirma)
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white border-0 pt-3 px-4 font-syne fw-bold">Comisiones sin firma o sello</div>
                <div class="list-group list-group-flush">
                    @forelse ($pendFirma['comisiones']['lista'] as $c)
                        <a href="{{ route('comisiones.show', $c->Id) }}" class="list-group-item list-group-item-action px-4 small d-flex justify-content-between align-items-center gap-2">
                            <span><span class="font-monospace">{{ $c->NOficio }}</span> · {{ \Illuminate\Support\Str::limit($c->Asunto, 35) }}</span>
                            <span class="text-nowrap">@include('firmas.partials.estado', ['d' => $c])</span>
                        </a>
                    @empty
                        <div class="px-4 py-4 small text-body-secondary">Todas las comisiones están firmadas y selladas.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white border-0 pt-3 px-4 font-syne fw-bold">Constancias sin firma o sello</div>
                <div class="list-group list-group-flush">
                    @forelse ($pendFirma['constancias']['lista'] as $k)
                        <a href="{{ route('comisiones.show', $k->IdComision) }}" class="list-group-item list-group-item-action px-4 small d-flex justify-content-between align-items-center gap-2">
                            <span><span class="font-monospace">{{ $k->comision->NOficio ?? '—' }}</span> · {{ \Illuminate\Support\Str::limit($k->comision->Asunto ?? '', 35) }}</span>
                            <span class="text-nowrap">@include('firmas.partials.estado', ['d' => $k])</span>
                        </a>
                    @empty
                        <div class="px-4 py-4 small text-body-secondary">Todas las constancias están firmadas y selladas.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif
@else
    @if ($statsAdmin)
        @php
            $tarjetas = [
                ['Usuarios activos', $statsAdmin['activos'], 'fa-users', 'estado-terminado'],
                ['Bajas', $statsAdmin['bajas'], 'fa-user-slash', 'estado-no-iniciado'],
                ['En línea ahora', $statsAdmin['enLinea'], 'fa-circle', 'estado-proceso'],
                ['Permisos temporales vigentes', $statsAdmin['tempVigentes'], 'fa-clock', 'estado-proceso'],
                ['Plantillas activas', $statsAdmin['plantillas'], 'fa-file-lines', 'estado-terminado'],
                ['Usuarios totales', $statsAdmin['total'], 'fa-layer-group', 'bg-secondary-subtle text-secondary'],
            ];
        @endphp
        <div class="row row-cols-2 row-cols-md-3 row-cols-xl-6 g-3 mb-3">
            @foreach ($tarjetas as [$etq, $n, $ico, $cls])
                <div class="col">
                    <div class="card stat-card h-100">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div><div class="stat-num">{{ $n }}</div><div class="stat-label">{{ $etq }}</div></div>
                            <div class="stat-icon {{ $cls }}"><i class="fa-solid {{ $ico }}"></i></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="row g-3 mb-4">
            <div class="col-lg-4"><div class="card p-3 h-100"><h6 class="font-syne mb-3">Usuarios por rol</h6><div class="position-relative graf-caja"><canvas id="grafRol"></canvas></div></div></div>
            <div class="col-lg-4"><div class="card p-3 h-100"><h6 class="font-syne mb-3">Usuarios por facultad</h6><div class="position-relative graf-caja"><canvas id="grafFac"></canvas></div></div></div>
            <div class="col-lg-4"><div class="card p-3 h-100"><h6 class="font-syne mb-3">Altas · últimos 6 meses</h6><div class="position-relative graf-caja"><canvas id="grafAltas"></canvas></div></div></div>
        </div>
    @endif

    {{-- Módulos sin tablero: accesos a sus opciones --}}
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3">
        @forelse ($opciones as $op)
            <div class="col">
                <a href="{{ $op['url'] ?? '#' }}" class="text-decoration-none {{ $op['url'] ? '' : 'pe-none opacity-50' }}">
                    <div class="card card-hover h-100 p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle">
                                <i class="fa-solid {{ $op['icono'] }}"></i>
                            </div>
                            <div>
                                <div class="font-syne fw-bold text-dark">{{ $op['nombre'] }}</div>
                                @unless ($op['url']) <small class="text-muted">Próximamente</small> @endunless
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 text-muted">Este módulo aún no tiene opciones disponibles para tu permiso.</div>
        @endforelse
    </div>
@endif
</div>

@if ($stats)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    const css = n => getComputedStyle(document.documentElement).getPropertyValue(n).trim();
    const estados = @json($stats['estados']);
    const tipos   = @json($stats['tipos']);
    const meses   = @json($stats['meses']);
    const colEst  = { 1: css('--rojo'), 2: css('--amarillo'), 3: css('--verde'), 4: css('--naranja-osc'), 5: css('--morado') };
    const opciones = { responsive: true, maintainAspectRatio: false };

    new Chart(document.getElementById('grafEstados'), {
        type: 'doughnut',
        data: {
            labels: estados.map(e => e.nombre),
            datasets: [{ data: estados.map(e => e.n), backgroundColor: estados.map(e => colEst[e.id]), borderWidth: 0 }]
        },
        options: { ...opciones, plugins: { legend: { position: 'bottom' } } }
    });

    new Chart(document.getElementById('grafTipos'), {
        type: 'bar',
        data: { labels: Object.keys(tipos), datasets: [{ data: Object.values(tipos), backgroundColor: css('--naranja'), borderRadius: 8 }] },
        options: { ...opciones, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    new Chart(document.getElementById('grafMeses'), {
        type: 'bar',
        data: { labels: meses.map(m => m.label), datasets: [{ data: meses.map(m => m.n), backgroundColor: css('--naranja2'), borderRadius: 8 }] },
        options: { ...opciones, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
</script>
@endpush
@endif
@if ($statsAdmin)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    (() => {
        const css = n => getComputedStyle(document.documentElement).getPropertyValue(n).trim();
        const porRol = @json($statsAdmin['porRol']);
        const porFac = @json($statsAdmin['porFac']);
        const altas  = @json($statsAdmin['altas']);
        const base = { responsive: true, maintainAspectRatio: false };
        const ejes = { y: { beginAtZero: true, ticks: { precision: 0 } } };
        new Chart(document.getElementById('grafRol'), {
            type: 'doughnut',
            data: { labels: Object.keys(porRol), datasets: [{ data: Object.values(porRol), borderWidth: 0,
                backgroundColor: [css('--naranja'), css('--naranja2'), css('--azul-m'), css('--verde'), css('--morado'), css('--amarillo')] }] },
            options: { ...base, plugins: { legend: { position: 'bottom' } } }
        });
        new Chart(document.getElementById('grafFac'), {
            type: 'bar',
            data: { labels: Object.keys(porFac), datasets: [{ data: Object.values(porFac), backgroundColor: css('--naranja'), borderRadius: 8 }] },
            options: { ...base, plugins: { legend: { display: false } }, scales: ejes }
        });
        new Chart(document.getElementById('grafAltas'), {
            type: 'bar',
            data: { labels: altas.map(m => m.label), datasets: [{ data: altas.map(m => m.n), backgroundColor: css('--naranja2'), borderRadius: 8 }] },
            options: { ...base, plugins: { legend: { display: false } }, scales: ejes }
        });
    })();
</script>
@endpush
@endif
@endsection
