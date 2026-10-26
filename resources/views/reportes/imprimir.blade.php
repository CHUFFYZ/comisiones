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
    .barra { margin-bottom: 14px; display: flex; gap: 8px; }
    .barra button { padding: 8px 16px; border: 0; border-radius: 20px; background: #ff7e5f; color: #fff; cursor: pointer; font-weight: 600; }
    .barra button.sec { background: #eee; color: #333; }
    @media print { .barra { display: none; } body { margin: 0; } }
</style>
</head>
<body>
<div class="barra">
    <button type="button" onclick="window.print()">Imprimir / Guardar PDF</button>
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
    <table>
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
</body>
</html>
