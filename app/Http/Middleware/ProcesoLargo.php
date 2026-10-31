<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Que los procesos (firmas, constancias, etc.) terminen aunque el usuario cierre la ventana. */
class ProcesoLargo
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->isMethod('GET')) {
            ignore_user_abort(true);
            @set_time_limit(0);
        }

        return $next($request);
    }
}
