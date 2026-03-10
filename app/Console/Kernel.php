<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Nettoyer les PDFs exportés expirés tous les jours à 2h du matin
        $schedule->command('exports:cleanup')
            ->dailyAt('02:00')
            ->description('Nettoyer les PDFs exportés de plus de 7 jours')
            ->onFailure(function () {
                \Log::error('Erreur lors du cleanup des exports');
            })
            ->onSuccess(function () {
                \Log::info('Cleanup des exports complété');
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
