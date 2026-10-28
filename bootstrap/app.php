<?php

use App\Http\Middleware\ActualizarConexion;
use App\Http\Middleware\GestionUsuarios;
use App\Http\Middleware\SimularUsuario;
use App\Http\Middleware\VerificarAccesoModulo;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(base_path('routes/extra.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'modulo.acceso'    => VerificarAccesoModulo::class,
            'gestion.usuarios' => GestionUsuarios::class,
        ]);
        $middleware->appendToGroup('web', ActualizarConexion::class);
        $middleware->appendToGroup('web', SimularUsuario::class); // después de ActualizarConexion
        $middleware->redirectUsersTo(fn () => auth()->user()?->Rol === 'Profesor'
            ? route('profesor.panel')
            : route('modulos.index'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
