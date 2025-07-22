<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\UpdateCodesToNewFormat::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
    // === MAIL NOTIFICATIONS ===

    // Vérifier les échéances approchantes toutes les heures
    $schedule->command('mail:process-notifications --type=deadlines --hours=24')
             ->hourly()
             ->withoutOverlapping()
             ->runInBackground();

    // Vérifier les courriers en retard toutes les 2 heures
    $schedule->command('mail:process-notifications --type=overdue')
             ->everyTwoHours()
             ->withoutOverlapping()
             ->runInBackground();

    // Vérifier les actions à effectuer toutes les 4 heures
    $schedule->command('mail:process-notifications --type=actions')
             ->everyFourHours()
             ->withoutOverlapping()
             ->runInBackground();

    // Escalade automatique quotidienne
    $schedule->command('mail:process-notifications --type=escalation')
             ->daily()
             ->at('09:00')
             ->withoutOverlapping()
             ->runInBackground();

    // === EXISTING OLLAMA TASKS ===

    // Auto-sync des modèles Ollama
    if (config('ollama.models.auto_sync')) {
        $schedule->command('ollama:sync-models')
            ->everyHours(config('ollama.models.sync_interval') / 3600);
    }

    // Health check régulier
    $schedule->command('ollama:health')
        ->everyFiveMinutes()
        ->onFailure(function () {
            // Notifier en cas de problème
            \Log::warning('Ollama health check failed');
        });
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
