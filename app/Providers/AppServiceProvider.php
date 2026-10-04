<?php

namespace App\Providers;

use App\Services\AccesoService;
use App\View\Composers\NavegacionComposer;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AccesoService::class);
    }

    public function boot(): void
    {
        View::composer(['components.header', 'components.sidebar'], NavegacionComposer::class);

        // @puede('MSC', 'crear') ... @endpuede
        Blade::if('puede', fn (string $modulo, string $accion = 'leer') =>
            app(AccesoService::class)->puede(auth()->user(), $modulo, $accion));
    }
}