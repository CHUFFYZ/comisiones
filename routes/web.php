<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ModuloController;
use App\Http\Controllers\ComisionController;
use App\Http\Controllers\ConstanciaController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('publico.inicio'))->name('inicio');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:6,1')->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/modulos', [ModuloController::class, 'index'])->name('modulos.index');
    Route::get('/modulos/{modulo}', [ModuloController::class, 'show'])
        ->middleware('modulo.acceso:leer')
        ->name('modulos.show');

   Route::prefix('modulos/MSC')->group(function () {
    Route::prefix('comisiones')->name('comisiones.')->group(function () {
        Route::get('/',                [ComisionController::class, 'index'])->middleware('modulo.acceso:leer,MSC')->name('index');
        Route::get('/crear',           [ComisionController::class, 'create'])->middleware('modulo.acceso:crear,MSC')->name('create');
        Route::post('/',               [ComisionController::class, 'store'])->middleware('modulo.acceso:crear,MSC')->name('store');
        Route::get('/anuladas',        [ComisionController::class, 'anuladas'])->middleware('modulo.acceso:eliminar,MSC')->name('anuladas');
        Route::get('/{id}/pdf',        [ComisionController::class, 'pdf'])->whereNumber('id')->middleware('modulo.acceso:leer,MSC')->name('pdf');
        Route::patch('/{id}/estado',   [ComisionController::class, 'estado'])->whereNumber('id')->middleware('modulo.acceso:actualizar,MSC')->name('estado');
        Route::delete('/{id}',         [ComisionController::class, 'anular'])->whereNumber('id')->middleware('modulo.acceso:eliminar,MSC')->name('anular');
    });

    Route::prefix('constancias')->name('constancias.')->group(function () {
        Route::get('/',            [ConstanciaController::class, 'index'])->middleware('modulo.acceso:actualizar,MSC')->name('index');
        Route::post('/{id}',       [ConstanciaController::class, 'store'])->whereNumber('id')->middleware('modulo.acceso:actualizar,MSC')->name('store');
        Route::get('/{id}/pdf',    [ConstanciaController::class, 'pdf'])->whereNumber('id')->middleware('modulo.acceso:leer,MSC')->name('pdf');
    });
});
});