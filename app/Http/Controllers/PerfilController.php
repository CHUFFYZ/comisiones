<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use App\Services\AccesoService;


class PerfilController extends Controller
{
    public function index(Request $r)
    {
        return view('perfil.index', ['u' => $r->user()->load('facultad')]);
    }

    /** Cambio de contraseña del propio usuario. */
    public function password(Request $r)
    {
        $r->validate([
            'PasswordActual' => 'required|string',
            'PasswordNueva'  => 'required|string|min:8|confirmed|different:PasswordActual',
        ], [
            'PasswordActual.required'  => 'Escribe tu contraseña actual.',
            'PasswordNueva.required'   => 'Escribe la nueva contraseña.',
            'PasswordNueva.min'        => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'PasswordNueva.confirmed'  => 'La confirmación no coincide con la nueva contraseña.',
            'PasswordNueva.different'  => 'La nueva contraseña debe ser distinta de la actual.',
        ]);

        $u = $r->user();
        if (!Hash::check($r->PasswordActual, $u->Contrasena)) {
            return back()->withErrors(['PasswordActual' => 'La contraseña actual no es correcta.']);
        }

        Usuario::where('Id', $u->Id)->update([
            'Contrasena' => Hash::make($r->PasswordNueva),
            'UpdatedAt'  => now(),
            'UpdatedBy'  => $u->Matricula,
        ]);

        return back()->with('ok', 'Contraseña actualizada.');
    }

    /** public/image/usuarios/{FACULTAD}/{ROL}/{MATRICULA}.ext */
    public function foto(Request $r)
    {
        $r->validate(['Foto' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'], [
            'Foto.required' => 'Selecciona una imagen.',
            'Foto.image'    => 'El archivo debe ser una imagen.',
            'Foto.mimes'    => 'Formatos permitidos: JPG, PNG o WebP.',
            'Foto.max'      => 'La imagen no puede pesar más de 2 MB.',
        ]);

        $u    = $r->user()->load('facultad');
        $f    = $r->file('Foto');
        $dir  = 'image/usuarios/' . $this->limpia($u->facultad?->Clave ?? 'SIN-FACULTAD') . '/' . $this->limpia($u->Rol ?? 'SIN-ROL');
        $mat  = $this->limpia($u->Matricula);
        $ext  = $f->guessExtension() === 'jpeg' ? 'jpg' : $f->guessExtension();

        $this->borrarActual($u);
        File::ensureDirectoryExists(public_path($dir));
        $f->move(public_path($dir), "$mat.$ext");

        Usuario::where('Id', $u->Id)->update(['FotoUrl' => "$dir/$mat.$ext", 'UpdatedAt' => now(), 'UpdatedBy' => $u->Matricula]);

        return back()->with('ok', 'Foto actualizada.');
    }

    public function quitarFoto(Request $r)
    {
        $u = $r->user();
        $this->borrarActual($u);
        Usuario::where('Id', $u->Id)->update(['FotoUrl' => null]);

        return back()->with('ok', 'Foto eliminada.');
    }

    public function firmaSello(Request $r)
    {
        abort_unless(app(AccesoService::class)->puedeFirmar($r->user()), 403);
        $r->validate([
            'Firma' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'Sello' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ], ['Firma.image' => 'La firma debe ser una imagen.', 'Sello.image' => 'El sello debe ser una imagen.']);

        $u = $r->user();
        $mat = $this->limpia($u->Matricula);
        $dir = 'image/firmas';
        $cambios = [];

        foreach (['Firma' => 'FirmaUrl', 'Sello' => 'SelloUrl'] as $campo => $col) {
            if (!$r->hasFile($campo)) continue;
            $f   = $r->file($campo);
            $ext = $f->guessExtension() === 'jpeg' ? 'jpg' : $f->guessExtension();
            if ($u->$col && !preg_match('#^https?://#i', $u->$col) && is_file(public_path($u->$col))) {
                @unlink(public_path($u->$col));
            }
            File::ensureDirectoryExists(public_path($dir));
            $nombre = strtolower($campo) . "-$mat.$ext";
            $f->move(public_path($dir), $nombre);
            $cambios[$col] = "$dir/$nombre";
        }

        if (!$cambios) return back()->withErrors(['general' => 'Selecciona al menos una imagen.']);

        Usuario::where('Id', $u->Id)->update($cambios + ['UpdatedAt' => now(), 'UpdatedBy' => $u->Matricula]);

        return back()->with('ok', 'Firma/sello actualizados. Los documentos ya firmados no cambian.');
    }

    private function borrarActual(Usuario $u): void
    {
        if ($u->FotoUrl && is_file(public_path($u->FotoUrl))) {
            @unlink(public_path($u->FotoUrl));
        }
    }

    private function limpia(string $t): string
    {
        return preg_replace('/[^A-Za-z0-9_-]/', '', $t) ?: 'X';
    }
}
