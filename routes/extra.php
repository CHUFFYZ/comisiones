<?php

use App\Http\Controllers\Admin\NivelAccesoController;
use App\Http\Controllers\ChatBusquedaController;
use App\Http\Controllers\ComisionExtraController;
use App\Http\Controllers\SuperUsuarioController;
use App\Http\Middleware\SuperUsuarioDesbloqueado;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->get('/chat/{id}/buscar', ChatBusquedaController::class)
    ->whereNumber('id')->name('chat.buscar');

Route::middleware('auth')->group(function () {

    /* ── Cierre de comisiones y borrado definitivo (SisComisiones) ── */
    Route::prefix('modulos/MSC')->group(function () {
        Route::get('/cierre',  [ComisionExtraController::class, 'cierre'])->middleware('modulo.acceso:leer,MSC')->name('cierre.index');
        Route::post('/cierre', [ComisionExtraController::class, 'cerrar'])->middleware('modulo.acceso:actualizar,MSC')->name('cierre.store');
        Route::delete('/comisiones/{id}/definitivo', [ComisionExtraController::class, 'eliminarDefinitivo'])
            ->whereNumber('id')->middleware('modulo.acceso:eliminar,MSC')->name('comisiones.eliminar_definitivo');
    });

    /* ── Niveles de acceso (Administración) ── */
    Route::middleware(['gestion.usuarios', 'modulo.acceso:actualizar,MA'])
        ->prefix('administracion/niveles')->name('admin.niveles.')->whereNumber('id')->group(function () {
            Route::get('/',             [NivelAccesoController::class, 'index'])->name('index');
            Route::get('/{id}/editar',  [NivelAccesoController::class, 'edit'])->name('edit');
            Route::put('/{id}',         [NivelAccesoController::class, 'update'])->name('update');
        });

    /* ── Zona de super usuario (pide contraseña) ── */
    Route::prefix('superusuario')->name('su.')->group(function () {
        Route::get('/',  [SuperUsuarioController::class, 'desbloquear'])->name('desbloquear');
        Route::post('/', [SuperUsuarioController::class, 'validar'])->middleware('throttle:6,1')->name('validar');

        Route::middleware(SuperUsuarioDesbloqueado::class)->group(function () {
            Route::get('/comisiones',                  [SuperUsuarioController::class, 'index'])->name('index');
            Route::post('/bloquear',                   [SuperUsuarioController::class, 'bloquear'])->name('bloquear');
            Route::patch('/comisiones/{id}/reabrir',   [SuperUsuarioController::class, 'reabrir'])->whereNumber('id')->name('reabrir');
            Route::delete('/comisiones/{id}',          [SuperUsuarioController::class, 'eliminar'])->whereNumber('id')->name('eliminar');
        });
    });
});
