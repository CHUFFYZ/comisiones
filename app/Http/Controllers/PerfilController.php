<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PerfilController extends Controller
{
    public function index(Request $r)
    {
        return view('perfil.index', ['u' => $r->user()->load('facultad')]);
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
