<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login'    => 'required|string|max:50',
            'password' => 'required|string',
        ], [
            'login.required'    => 'Ingresa tu correo o matrícula.',
            'password.required' => 'Ingresa tu contraseña.',
        ]);

        $usuario = Usuario::where('Activo', 1)
            ->where(fn ($q) => $q->where('Correo', $data['login'])
                                 ->orWhere('Matricula', $data['login']))
            ->first();

        if (!$usuario || !Hash::check($data['password'], $usuario->Contrasena)) {
            return back()->withErrors(['login' => 'Credenciales incorrectas.'])->onlyInput('login');
        }

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->intended(route('modulos.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('inicio');
    }
}