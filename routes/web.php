<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\FacultadController;
use App\Http\Controllers\Admin\PermisoController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ComisionController;
use App\Http\Controllers\ConstanciaController;
use App\Http\Controllers\FirmaController;
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
    Route::post('/perfil/firma', [PerfilController::class, 'firmaSello'])->name('perfil.firma');

    Route::prefix('firmas')->name('firmas.')->group(function () {
        Route::get('/', [FirmaController::class, 'index'])->name('index');
        Route::post('/', [FirmaController::class, 'procesar'])->name('procesar');
        Route::get('/{tipo}/{id}/pdf', [FirmaController::class, 'pdf'])
            ->where(['tipo' => 'c|k', 'id' => '[0-9]+'])->name('pdf');
    });

    Route::post('/simulacion', [SimulacionController::class, 'iniciar'])->name('simulacion.iniciar');
    Route::delete('/simulacion', [SimulacionController::class, 'terminar'])->name('simulacion.terminar');

    Route::middleware(['gestion.usuarios', 'modulo.acceso:leer,MA'])
        ->prefix('administracion')->name('admin.')->where(['id' => '[0-9]+'])->group(function () {
        Route::get('usuarios',                 [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('usuarios/{id}',             [UsuarioController::class, 'show'])->name('usuarios.show');
        Route::get('usuarios/crear',            [UsuarioController::class, 'create'])->middleware('modulo.acceso:crear,MA')->name('usuarios.create');
        Route::post('usuarios',                [UsuarioController::class, 'store'])->middleware('modulo.acceso:crear,MA')->name('usuarios.store');
        Route::get('usuarios/bajas',           [UsuarioController::class, 'bajas'])->middleware('modulo.acceso:eliminar,MA')->name('usuarios.bajas');
        Route::get('usuarios/{id}/editar',     [UsuarioController::class, 'edit'])->middleware('modulo.acceso:actualizar,MA')->name('usuarios.edit');
        Route::put('usuarios/{id}',            [UsuarioController::class, 'update'])->middleware('modulo.acceso:actualizar,MA')->name('usuarios.update');
        Route::delete('usuarios/{id}',         [UsuarioController::class, 'baja'])->middleware('modulo.acceso:eliminar,MA')->name('usuarios.baja');
        Route::patch('usuarios/{id}/restaurar',[UsuarioController::class, 'restaurar'])->middleware('modulo.acceso:eliminar,MA')->name('usuarios.restaurar');
        Route::get('permisos',                 [PermisoController::class, 'index'])->middleware('modulo.acceso:actualizar,MA')->name('permisos.index');
        Route::post('permisos/{id}',           [PermisoController::class, 'store'])->middleware('modulo.acceso:actualizar,MA')->name('permisos.store');
        Route::delete('permisos/{id}',         [PermisoController::class, 'destroy'])->middleware('modulo.acceso:eliminar,MA')->name('permisos.destroy');
        Route::post('permisos/{id}/permanente', [PermisoController::class, 'storePermanente'])->middleware('modulo.acceso:actualizar,MA')->name('permisos.permanente.store');
        Route::delete('permisos/{id}/permanente/{asignacion}', [PermisoController::class, 'destroyPermanente'])
            ->whereNumber('asignacion')->middleware('modulo.acceso:eliminar,MA')->name('permisos.permanente.destroy');
    });

    Route::put('/perfil/password', [PerfilController::class, 'password'])->name('perfil.password');

    /* ── Reportes imprimibles (MSC = comisiones · MA = usuarios) ── */
    Route::prefix('reportes/{modulo}')->where(['modulo' => 'MSC|MA'])->name('reportes.')->group(function () {
        Route::get('/',         [\App\Http\Controllers\ReporteController::class, 'index'])->middleware('modulo.acceso:leer')->name('index');
        Route::get('/imprimir', [\App\Http\Controllers\ReporteController::class, 'imprimir'])->middleware('modulo.acceso:leer')->name('imprimir');
    });

    /* ── Profesores con sus comisiones (según alcance) ── */
    Route::get('/modulos/MSC/profesores', [\App\Http\Controllers\DirectorioProfesoresController::class, 'index'])
        ->middleware('modulo.acceso:leer,MSC')->name('directorio.profesores');

    /* ── Plantillas de oficio ── */
    Route::middleware(['gestion.usuarios', 'modulo.acceso:actualizar,MA'])
        ->prefix('administracion')->name('admin.')->where(['id' => '[0-9]+'])->group(function () {
        $p = \App\Http\Controllers\Admin\PlantillaController::class;
        Route::get('plantillas',               [$p, 'index'])->name('plantillas.index');
        Route::get('plantillas/crear',         [$p, 'create'])->name('plantillas.create');
        Route::post('plantillas',              [$p, 'store'])->name('plantillas.store');
        Route::get('plantillas/{id}/editar',   [$p, 'edit'])->name('plantillas.edit');
        Route::put('plantillas/{id}',          [$p, 'update'])->name('plantillas.update');
        Route::patch('plantillas/{id}/estado', [$p, 'estado'])->name('plantillas.estado');
        Route::get('facultades', [FacultadController::class, 'index'])->name('facultades.index');
        Route::patch('facultades/{id}/firma-director', [FacultadController::class, 'firmaDirector'])->name('facultades.firma');
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
        Route::get('/{id}/oficio/{formato}', [ProfesorController::class, 'oficio'])
            ->where('formato', 'vista|pdf')->name('oficio');
        Route::get('/{id}/constancia/{k}/{formato}', [ProfesorController::class, 'constancia'])
            ->where(['k' => '[0-9]+', 'formato' => 'vista|pdf'])->name('constancia');
    });

    /* ── Chat ── */
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::patch('/{id}/mensajes/{mensaje}', [ChatController::class, 'editar'])
            ->whereNumber(['id', 'mensaje'])->name('editar');
        Route::delete('/{id}', [ChatController::class, 'eliminar'])->whereNumber('id')->name('eliminar');
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
