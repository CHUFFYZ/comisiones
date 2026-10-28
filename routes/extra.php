<?php

use App\Http\Controllers\ChatBusquedaController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->get('/chat/{id}/buscar', ChatBusquedaController::class)
    ->whereNumber('id')->name('chat.buscar');
