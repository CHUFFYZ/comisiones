<?php

namespace App\Providers;

use App\Services\AccesoService;
use App\View\Composers\NavegacionComposer;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
// ¡ESTA ES LA LÍNEA QUE TE FALTA!
use Illuminate\Pagination\Paginator;

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

        // Ahora Laravel sabrá exactamente de dónde viene esta clase
        Paginator::useBootstrapFive();
    }
}
