<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\PermisoController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ComisionController;
use App\Http\Controllers\ConstanciaController;
use App\Http\Controllers\ModuloController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ProfesorController;
use App\Http\Controllers\SimulacionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('publico.inicio'))->name('inicio');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:6,1')->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil');
    Route::post('/perfil/foto', [PerfilController::class, 'foto'])->name('perfil.foto');
    Route::delete('/perfil/foto', [PerfilController::class, 'quitarFoto'])->name('perfil.foto.quitar');

    Route::post('/simulacion', [SimulacionController::class, 'iniciar'])->name('simulacion.iniciar');
    Route::delete('/simulacion', [SimulacionController::class, 'terminar'])->name('simulacion.terminar');

    Route::middleware('gestion.usuarios')->prefix('administracion')->name('admin.')->where(['id' => '[0-9]+'])->group(function () {
        Route::get('usuarios',                 [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('usuarios/crear',           [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('usuarios',                [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('usuarios/bajas',           [UsuarioController::class, 'bajas'])->name('usuarios.bajas');
        Route::get('usuarios/{id}/editar',     [UsuarioController::class, 'edit'])->name('usuarios.edit');
        Route::put('usuarios/{id}',            [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::delete('usuarios/{id}',         [UsuarioController::class, 'baja'])->name('usuarios.baja');
        Route::patch('usuarios/{id}/restaurar',[UsuarioController::class, 'restaurar'])->name('usuarios.restaurar');
        Route::get('permisos',                 [PermisoController::class, 'index'])->name('permisos.index');
        Route::post('permisos/{id}',           [PermisoController::class, 'store'])->name('permisos.store');
        Route::delete('permisos/{id}',         [PermisoController::class, 'destroy'])->name('permisos.destroy');
    });

    Route::get('/modulos', [ModuloController::class, 'index'])->name('modulos.index');
    Route::get('/modulos/{modulo}', [ModuloController::class, 'show'])
        ->middleware('modulo.acceso:leer')
        ->name('modulos.show');

    /* ── SisComisiones (secretaría) ── */
    Route::prefix('modulos/MSC')->group(function () {
        Route::prefix('comisiones')->name('comisiones.')->where(['id' => '[0-9]+'])->group(function () {
            Route::get('/',               [ComisionController::class, 'index'])->middleware('modulo.acceso:leer,MSC')->name('index');
            Route::get('/crear',          [ComisionController::class, 'create'])->middleware('modulo.acceso:crear,MSC')->name('create');
            Route::post('/',              [ComisionController::class, 'store'])->middleware('modulo.acceso:crear,MSC')->name('store');
            Route::post('/previa',        [ComisionController::class, 'previa'])->middleware('modulo.acceso:crear,MSC')->name('previa');
            Route::get('/anuladas',       [ComisionController::class, 'anuladas'])->middleware('modulo.acceso:eliminar,MSC')->name('anuladas');

            Route::get('/{id}',           [ComisionController::class, 'show'])->middleware('modulo.acceso:leer,MSC')->name('show');
            Route::get('/{id}/editar',    [ComisionController::class, 'edit'])->middleware('modulo.acceso:actualizar,MSC')->name('edit');
            Route::put('/{id}',           [ComisionController::class, 'update'])->middleware('modulo.acceso:actualizar,MSC')->name('update');
            Route::get('/{id}/pdf',       [ComisionController::class, 'pdf'])->middleware('modulo.acceso:leer,MSC')->name('pdf');
            Route::get('/{id}/vista',     [ComisionController::class, 'vista'])->middleware('modulo.acceso:leer,MSC')->name('vista');
            Route::get('/{id}/reporte',   [ComisionController::class, 'reporte'])->middleware('modulo.acceso:leer,MSC')->name('reporte');

            Route::patch('/{id}/avanzar',         [ComisionController::class, 'avanzar'])->middleware('modulo.acceso:actualizar,MSC')->name('avanzar');
            Route::patch('/{id}/retroceder',      [ComisionController::class, 'retroceder'])->middleware('modulo.acceso:actualizar,MSC')->name('retroceder');
            Route::patch('/{id}/rechazar-reporte',[ComisionController::class, 'rechazarReporte'])->middleware('modulo.acceso:actualizar,MSC')->name('rechazar');
            Route::patch('/{id}/restaurar',       [ComisionController::class, 'restaurar'])->middleware('modulo.acceso:eliminar,MSC')->name('restaurar');
            Route::delete('/{id}',                [ComisionController::class, 'anular'])->middleware('modulo.acceso:eliminar,MSC')->name('anular');
        });

        // store/update/destroy: {id} = comisión · pdf/vista: {id} = constancia
        Route::prefix('constancias')->name('constancias.')->where(['id' => '[0-9]+'])->group(function () {
            Route::post('/{id}',     [ConstanciaController::class, 'store'])->middleware('modulo.acceso:actualizar,MSC')->name('store');
            Route::patch('/{id}',    [ConstanciaController::class, 'update'])->middleware('modulo.acceso:actualizar,MSC')->name('update');
            Route::delete('/{id}',   [ConstanciaController::class, 'destroy'])->middleware('modulo.acceso:eliminar,MSC')->name('destroy');
            Route::get('/{id}/pdf',  [ConstanciaController::class, 'pdf'])->middleware('modulo.acceso:leer,MSC')->name('pdf');
            Route::get('/{id}/vista',[ConstanciaController::class, 'vista'])->middleware('modulo.acceso:leer,MSC')->name('vista');
        });
    });

    /* ── Panel del profesor (solo ve lo que se le asignó) ── */
    Route::prefix('mi-panel')->name('profesor.')->where(['id' => '[0-9]+'])->group(function () {
        Route::get('/',                       [ProfesorController::class, 'panel'])->name('panel');
        Route::get('/{id}',                   [ProfesorController::class, 'show'])->name('show');
        Route::patch('/{id}/iniciar',         [ProfesorController::class, 'iniciar'])->name('iniciar');
        Route::post('/{id}/reporte',          [ProfesorController::class, 'reporte'])->name('reporte');
        Route::get('/{id}/reporte',           [ProfesorController::class, 'descargarReporte'])->name('reporte.descargar');
        Route::patch('/{id}/refutar',         [ProfesorController::class, 'refutar'])->name('refutar');
        Route::get('/{id}/{doc}/{formato}',   [ProfesorController::class, 'documento'])
            ->where(['doc' => 'oficio|constancia', 'formato' => 'vista|pdf'])->name('documento');
    });

    /* ── Chat ── */
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/',                          [ChatController::class, 'index'])->name('index');
        Route::get('/usuarios',                  [ChatController::class, 'usuarios'])->name('usuarios');
        Route::get('/perfil/{id}',               [ChatController::class, 'perfil'])->whereNumber('id')->name('perfil');
        Route::get('/conversaciones',            [ChatController::class, 'conversaciones'])->name('conversaciones');
        Route::post('/conversaciones',           [ChatController::class, 'abrir'])->name('abrir');
        Route::get('/supervision',               [ChatController::class, 'supervision'])->name('supervision');
        Route::get('/supervision/{id}',          [ChatController::class, 'supervisionMensajes'])->whereNumber('id')->name('supervision.mensajes');
        Route::get('/{id}/mensajes',             [ChatController::class, 'mensajes'])->whereNumber('id')->name('mensajes');
        Route::post('/{id}/mensajes',            [ChatController::class, 'enviar'])->whereNumber('id')->name('enviar');
    });

    /* ── Notificaciones ── */
    Route::prefix('notificaciones')->name('notificaciones.')->group(function () {
        Route::get('/',                 [NotificacionController::class, 'index'])->name('index');
        Route::post('/leer-todas',      [NotificacionController::class, 'leerTodas'])->name('leer_todas');
        Route::get('/{id}/ir',          [NotificacionController::class, 'ir'])->whereNumber('id')->name('ir');
    });
});
