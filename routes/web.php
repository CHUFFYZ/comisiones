<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PermisoController;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
    return view('publico.inicio');
})->name('inicio');

// Ruta de Login (resuelve el RouteNotFoundException)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

// Rutas de administración
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/permisos', [PermisoController::class, 'index'])->name('permisos.index');
});