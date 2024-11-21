<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        // Proceso nocturno a las 12 AM
        $schedule->command('subscriptions:check-expiring')
            ->dailyAt('00:00')
            ->withoutOverlapping()
            ->onFailure(function () {
                Log::error('Falló la verificación de suscripciones');
            });

        // Proceso de envío de notificaciones a las 9 AM
        $schedule->command('whatsapp:send-notifications')
            ->dailyAt('09:00')
            ->withoutOverlapping()
            ->onFailure(function () {
                Log::error('Falló el envío de notificaciones WhatsApp');
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');


        require base_path('routes/console.php');
    }
}
