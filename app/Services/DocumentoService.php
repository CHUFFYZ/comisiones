<?php

namespace App\Services;

use App\Models\Comision;
use App\Models\Constancia;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Genera los documentos (HTML para pantalla, PDF para descarga) y los guarda en:
 *   storage/app/private/comisiones/{FACULTAD}/{NOficio}/oficio.pdf | constancia.pdf | reporte.*
 */
class DocumentoService
{
    private const FMT = 'j \d\e F \d\e Y';

    /* ───────── datos del documento ───────── */

    public function datos(Comision $c, ?Constancia $k = null, bool $web = false): array
    {
        $doc  = $k ?? $c;
        $p    = $doc->plantilla;
        $esC  = $k !== null;
        $profes = $c->profesores->sortBy('Apellido')->values();

        $contexto = trim((string) $doc->Contexto) !== ''
            ? trim((string) $doc->Contexto)
            : $this->minuscula((string) $c->tipo?->Descripcion);
        $cierreTxt = trim((string) $doc->Cierre) !== '' ? (string) $doc->Cierre : (string) $p->Cierre;
        $cierre = preg_replace('/\s*esta actividad se llevará a cabo\s*\{fechas\}\.?/iu', '', $cierreTxt);
        $cierre = trim(str_replace('{fechas}', '', $cierre));
        $fraseFechas = $this->fraseFechas($c, $esC);
        if ($fraseFechas !== '') {
            $cierre = trim($cierre) . ($cierre !== '' ? ' ' : '') . $fraseFechas;
        }
        $separadorCierre = $cierre !== '' && !preg_match('/^[,.;:]/u', $cierre) ? ' ' : '';
        $correoDirector = Usuario::deRol('Director')
            ->where('IdFacultad', $c->IdFacultad)
            ->where('Activo', 1)
            ->value('Correo');

        $saludo = $profes->count() > 1
            ? 'Estimados maestros,'
            : ((int) $profes->first()?->Genero === 1 ? 'Estimada maestra,' : 'Estimado maestro,');

        $fecha = ($esC ? $k->FechaElaboracion : $c->FechaEmision)->copy()->locale('es')->translatedFormat(self::FMT);

        return [
            'p'            => $p,
            'c'            => $c,
            'doc'          => $doc,
            'esConstancia' => $esC,
            'web'          => $web,
            'fechaDoc'     => $fecha,
            'asunto'       => $esC ? 'Constancia de cumplimiento' : $c->Asunto,
            'profesores'   => $profes->map(fn ($x) => $this->nombreConGrado($x)),
            'saludo'       => $saludo,
            'introduccion' => trim((string) $p->Introduccion),
            'contexto'     => $contexto,
            'cierre'       => $cierre,
            'separadorCierre' => $separadorCierre,
            'correoDirector' => $correoDirector,
            'facTitulo'    => $this->titulo((string) $p->Facultad),
            'logoIzq'      => $this->img($p->LogoIzquierdo, $web),
            'logoDer'      => $this->img($p->LogoDerecho, $web),
            'firma'        => $this->img($doc->DirectorFirmaUrl, $web),
            'sello'        => $this->img($doc->DirectorSelloUrl, $web),
        ];
    }

    public function pdf(Comision $c, ?Constancia $k = null)
    {
        return Pdf::loadView('pdf.documento', $this->datos($c, $k, false))
            ->setPaper('letter')->setOption('isRemoteEnabled', true);
    }

    /** HTML listo para mostrar en pantalla (vista previa / detalle). */
    public function html(Comision $c, ?Constancia $k = null): string
    {
        return view('pdf.documento', $this->datos($c, $k, true))->render();
    }

    /* ───────── archivos guardados ───────── */

    public function carpeta(Comision $c): string
    {
        return 'comisiones/' . ($c->facultad?->Clave ?? 'SIN-FACULTAD') . '/' . $c->NOficio;
    }

    public function guardarOficio(Comision $c): void
    {
        Storage::disk('local')->put($this->carpeta($c) . '/oficio.pdf', $this->pdf($c)->output());
    }

    public function guardarConstancia(Comision $c, Constancia $k): void
    {
        Storage::disk('local')->put($this->carpeta($c) . '/constancia.pdf', $this->pdf($c, $k)->output());
    }

    public function borrarConstancia(Comision $c): void
    {
        Storage::disk('local')->delete($this->carpeta($c) . '/constancia.pdf');
    }

    /** Sirve el PDF guardado (si falta, lo genera una sola vez). */
    public function respuestaPdf(Comision $c, ?Constancia $k = null)
    {
        $disk    = Storage::disk('local');
        $archivo = $this->carpeta($c) . ($k ? '/constancia.pdf' : '/oficio.pdf');

        if (!$disk->exists($archivo)) {
            $k ? $this->guardarConstancia($c, $k) : $this->guardarOficio($c);
        }

        $nombre = ($k ? 'Constancia-' : '') . $c->NOficio . '.pdf';

        return response()->file($disk->path($archivo), [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $nombre . '"',
        ]);
    }

    /** Guarda el reporte del profesor; reemplaza el anterior (de él o de otro del grupo). */
    public function guardarReporte(Comision $c, UploadedFile $f, Usuario $u): void
    {
        $disk = Storage::disk('local');
        $dir  = $this->carpeta($c);

        foreach ($disk->files($dir) as $viejo) {
            if (str_starts_with(basename($viejo), 'reporte.')) {
                $disk->delete($viejo);
            }
        }

        $ext = strtolower($f->getClientOriginalExtension() ?: 'pdf');
        $disk->putFileAs($dir, $f, 'reporte.' . $ext);

        $c->update([
            'ReporteNombre'    => $f->getClientOriginalName(),
            'ReporteRuta'      => $dir . '/reporte.' . $ext,
            'ReporteIdUsuario' => $u->Id,
            'ReporteAt'        => now(),
            'UpdatedBy'        => $u->Matricula,
        ]);
    }

    public function descargarReporte(Comision $c)
    {
        $disk = Storage::disk('local');
        abort_unless($c->ReporteRuta && $disk->exists($c->ReporteRuta), 404, 'No hay reporte adjunto.');

        if (str_ends_with($c->ReporteRuta, '.pdf')) {
            return response()->file($disk->path($c->ReporteRuta), [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $c->ReporteNombre . '"',
            ]);
        }

        return $disk->download($c->ReporteRuta, $c->ReporteNombre);
    }

    /* ───────── utilidades de texto ───────── */

    public function nombreConGrado(Usuario $u): string
    {
        $g = trim((string) $u->GradoAcademico);
        if ($g !== '' && !str_ends_with($g, '.')) $g .= '.';

        return preg_replace('/\s+/', ' ', trim("$g {$u->Nombre} {$u->Apellido}"));
    }

    private function fraseFechas(Comision $c, bool $esConstancia = false): string
    {
        if (!$c->FechaInicio) return '';
        $i = $c->FechaInicio->copy()->locale('es');
        $f = $c->FechaFin?->copy()->locale('es');
        $verbo = $esConstancia ? 'Se llevó a cabo' : 'Se llevará a cabo';

        if (!$f) return $verbo . ' el ' . $i->translatedFormat(self::FMT) . '.';

        return $verbo . ' desde ' . $i->translatedFormat(self::FMT)
            . ' hasta ' . $f->translatedFormat(self::FMT) . '.';
    }

    private function minuscula(string $t): string
    {
        return mb_strtolower(mb_substr($t, 0, 1)) . mb_substr($t, 1);
    }

    private function titulo(string $t): string
    {
        $menores = ['de', 'la', 'del', 'y', 'en', 'el', 'los', 'las'];
        $w = explode(' ', mb_strtolower($t));
        foreach ($w as $i => $x) {
            if ($i === 0 || !in_array($x, $menores)) {
                $w[$i] = mb_strtoupper(mb_substr($x, 0, 1)) . mb_substr($x, 1);
            }
        }
        return implode(' ', $w);
    }

    /** URL http(s) tal cual; ruta local → archivo en public/ o storage/app/public (URL si es para pantalla). */
    private function img(?string $u, bool $web = false): ?string
    {
        if (!$u) return null;
        if (preg_match('#^https?://#i', $u)) return $u;

        $rel = ltrim($u, '/');
        $publico = public_path($rel);
        $storage = storage_path('app/public/' . preg_replace('#^storage/#', '', $rel));

        if (is_file($publico)) return $web ? asset($rel) : $publico;
        if (is_file($storage)) return $web ? asset('storage/' . preg_replace('#^storage/#', '', $rel)) : $storage;

        return null;
    }
}
