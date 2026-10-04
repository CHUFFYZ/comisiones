<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ModuloController;
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

    // Ejemplo para cuando construyas un módulo real (el permiso se valida solo):
    // Route::prefix('modulos/MSC/comisiones')->name('comisiones.')->group(function () {
    //     Route::get('/',        [ComisionController::class, 'index'])->middleware('modulo.acceso:leer,MSC');
    //     Route::post('/',       [ComisionController::class, 'store'])->middleware('modulo.acceso:crear,MSC');
    //     Route::delete('/{id}', [ComisionController::class, 'destroy'])->middleware('modulo.acceso:eliminar,MSC');
    // });
});