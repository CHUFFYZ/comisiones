<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 40px 70px 100px 70px; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; line-height: 1.5; color: #000; }
    table { border-collapse: collapse; width: 100%; }
    .hdr td { vertical-align: middle; }
    .hdr .logo { width: 90px; }
    .hdr .tit { text-align: center; font-weight: bold; }
    .hdr .uni { font-size: 14px; }
    .hdr .fac { font-size: 12px; }
    .meta { text-align: right; margin-top: 30px; }
    .dest { margin-top: 25px; }
    .just { text-align: justify; margin: 14px 0; }
    .firmas { margin-top: 30px; page-break-inside: avoid; }
    .firma-box { height: 60px; }
    .firma-box img { height: 60px; }
    .linea { border-top: 1px solid #000; width: 230px; margin-bottom: 4px; }
    .sello img { height: 95px; }
    .footer { position: fixed; bottom: -70px; left: 0; right: 0; }
    .ccp { font-size: 9px; margin-bottom: 6px; }
    .addr { border-top: 1px solid #000; padding-top: 4px; text-align: center; font-size: 9px; }
</style>
</head>
<body>

<div class="footer">
    <div class="ccp">{{ $doc->FirmaElaboracion }}</div>
    <div class="addr">Edificio del Campus I. Av. 56 Esquina Av. Concordia Número 4, Colonia Benito Juárez, C.P. 24180, Cd. del Carmen, Campeche.</div>
</div>

<table class="hdr">
    <tr>
        <td class="logo">@if ($logoIzq)<img src="{{ $logoIzq }}" width="80">@endif</td>
        <td class="tit">
            <div class="uni">{{ $p->NombreUniversidad }}</div>
            <div class="fac">{{ $p->Facultad }}</div>
        </td>
        <td class="logo" align="right">@if ($logoDer)<img src="{{ $logoDer }}" width="80">@endif</td>
    </tr>
</table>

<div class="meta">
    {{ $p->Lugar }}, a {{ $fechaDoc }}.<br>
    <strong>Oficio no.:</strong> {{ $c->NOficio }}<br>
    <strong>Asunto:</strong> {{ $asunto }}
</div>

<div class="dest">
    @foreach ($profesores as $nombre)
        <strong>{{ $nombre }}</strong><br>
    @endforeach
    PTC de la {{ $facTitulo }}<br>
    <strong>{{ $esConstancia ? 'A quien corresponda' : 'Presente' }}</strong>
</div>

@unless ($esConstancia)
    <p style="margin-top:14px;">{{ $saludo }}</p>
@endunless

<p class="just">{{ $cuerpo }}</p>
<p class="just">{{ $p->Despedida }}</p>

<div class="firmas">
    <table>
        <tr>
            <td style="width:60%; vertical-align:bottom;">
                <div>Atentamente,</div>
                <div><em>Por la Grandeza de México</em></div>
                <div class="firma-box">@if ($firma)<img src="{{ $firma }}">@endif</div>
                <div class="linea"></div>
                <div><strong>{{ $doc->DirectorNombre }}</strong></div>
                <div>{{ $doc->DirectorCargo ?: 'Director' }} de la {{ $facTitulo }}</div>
            </td>
            <td class="sello" style="vertical-align:bottom; text-align:center;">
                @if ($sello)<img src="{{ $sello }}">@endif
            </td>
        </tr>
    </table>
</div>

</body>
</html>