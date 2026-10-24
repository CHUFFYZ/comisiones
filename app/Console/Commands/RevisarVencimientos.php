<?php

namespace App\Console\Commands;

use App\Services\NotificacionService;
use Illuminate\Console\Command;

class RevisarVencimientos extends Command
{
    protected $signature = 'cati:vencimientos';
    protected $description = 'Notifica las comisiones por vencer o vencidas';

    public function handle(NotificacionService $notif): int
    {
        $notif->vencimientos();
        $this->info('Vencimientos revisados.');

        return self::SUCCESS;
    }
}
