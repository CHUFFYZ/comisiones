<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Avisos de comisiones por vencer / vencidas (requiere `php artisan schedule:work` o el cron de Laravel)
Schedule::command('cati:vencimientos')->dailyAt('07:00');
