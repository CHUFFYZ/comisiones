<!DOCTYPE html>
<html lang="es-MX">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $titulo }}</title>
<style>
    @page { size: letter landscape; margin: 12mm; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #000; margin: 24px; }
    h1 { font-size: 18px; margin: 0; }
    h2 { font-size: 14px; margin: 18px 0 4px; }
    .enc { border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 10px; }
    .enc small { display: block; color: #444; }
    .meta { font-size: 11px; line-height: 1.6; margin-bottom: 8px; }
    table { width: 100%; border-collapse: collapse; margin: 6px 0 4px; }
    th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; vertical-align: top; }
    th { background: #eee; }
    thead { display: table-header-group; }
    tr { page-break-inside: avoid; }
    .total { font-size: 11px; color: #444; margin-bottom: 8px; }
    .barra { margin-bottom: 14px; display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .barra button, .barra select { padding: 8px 16px; border: 0; border-radius: 20px; background: #ff7e5f; color: #fff; cursor: pointer; font-weight: 600; font-size: 12px; }
    .barra select { background: #eee; color: #333; border: 1px solid #ccc; }
    .barra button.sec { background: #eee; color: #333; }
    @media print { .barra { display: none; } body { margin: 0; } }
</style>
</head>
<body>
<div class="barra">
    <label for="formato"><strong>Exportar como:</strong></label>
    <select id="formato">
        <option value="pdf">PDF (imprimir / guardar)</option>
        <option value="xlsx">Excel (.xlsx)</option>
        <option value="csv">CSV (hoja de cálculo)</option>
    </select>
    <button type="button" id="btnExportar">Exportar</button>
    <button type="button" class="sec" onclick="window.close()">Cerrar</button>
</div>

<div class="enc">
    <h1>{{ $titulo }}</h1>
    <small>Universidad Autónoma del Carmen · {{ $entidad }}</small>
</div>

<div class="meta">
    <strong>Alcance:</strong> {{ $alcance }}<br>
    <strong>Filtros:</strong> {{ $filtros }}<br>
    <strong>Generado por:</strong> {{ $por }} · {{ now()->locale('es')->translatedFormat('j \d\e F \d\e Y, H:i') }}
</div>

@foreach ($secciones as $s)
    @if ($s['titulo'])<h2>{{ $s['titulo'] }}</h2>@endif
    <table class="datos" data-nombre="{{ $s['titulo'] ?: 'Reporte' }}">
        <thead><tr>@foreach ($s['columnas'] as $c)<th>{{ $c }}</th>@endforeach</tr></thead>
        <tbody>
        @forelse ($s['filas'] as $fila)
            <tr>@foreach ($fila as $celda)<td>{{ $celda }}</td>@endforeach</tr>
        @empty
            <tr><td colspan="{{ count($s['columnas']) }}" style="text-align:center">Sin registros con estos filtros.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="total">Registros: {{ count($s['filas']) }}</div>
@endforeach

<script defer src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
@php
    $meta = [$titulo, 'Universidad Autónoma del Carmen · ' . $entidad, 'Alcance: ' . $alcance, 'Filtros: ' . $filtros, 'Generado por: ' . $por];
    $archivo = \Illuminate\Support\Str::slug($titulo) . '-' . now()->format('Ymd-His');
@endphp
<script>
    const META = {{ \Illuminate\Support\Js::from($meta) }};
    const ARCHIVO = {{ \Illuminate\Support\Js::from($archivo) }};

    const tablas = () => [...document.querySelectorAll('table.datos')].map(t => ({
        nombre: t.dataset.nombre,
        filas: [...t.querySelectorAll('tr')].map(tr => [...tr.children].map(c => {
            const v = c.textContent.trim();
            return /^(0|[1-9]\d{0,8})$/.test(v) ? Number(v) : v;
        })),
    }));

    function exportarXlsx() {
        if (typeof XLSX === 'undefined') { alert('No se pudo cargar el generador de Excel (revisa tu conexión).'); return; }
        const wb = XLSX.utils.book_new(), usados = new Set();
        tablas().forEach((t, i) => {
            let n = (t.nombre || 'Hoja ' + (i + 1)).replace(/[\\\/?*\[\]:]/g, ' ').slice(0, 28), k = 1, base = n;
            while (usados.has(n.toLowerCase())) n = base + ' ' + (++k);
            usados.add(n.toLowerCase());
            const ws = XLSX.utils.aoa_to_sheet(META.map(m => [m]).concat([[]], t.filas));
            ws['!cols'] = t.filas[0].map((_, c) => ({ wch: Math.min(45, Math.max(10, ...t.filas.map(f => String(f[c] ?? '').length + 2))) }));
            XLSX.utils.book_append_sheet(wb, ws, n);
        });
        XLSX.writeFile(wb, ARCHIVO + '.xlsx');
    }

    function exportarCsv() {
        const celda = v => '"' + String(v ?? '').replace(/"/g, '""') + '"';
        const lineas = META.map(m => celda(m));
        tablas().forEach(t => {
            lineas.push('');
            if (t.nombre !== 'Reporte') lineas.push(celda(t.nombre));
            t.filas.forEach(f => lineas.push(f.map(celda).join(',')));
        });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob(['\ufeff' + lineas.join('\r\n')], { type: 'text/csv;charset=utf-8' }));
        a.download = ARCHIVO + '.csv';
        a.click();
        URL.revokeObjectURL(a.href);
    }

    document.getElementById('btnExportar').addEventListener('click', () => {
        const f = document.getElementById('formato').value;
        if (f === 'xlsx') exportarXlsx(); else if (f === 'csv') exportarCsv(); else window.print();
    });
</script>
</body>
</html>
