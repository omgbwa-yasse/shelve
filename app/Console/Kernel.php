<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\UpdateCodesToNewFormat::class,
    \App\Console\Commands\LlmAggregateDaily::class,
    \App\Console\Commands\LlmPrune::class,
    \App\Console\Commands\ExportRecordSeda::class,
    \App\Console\Commands\ExportSlipSeda::class,
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
            Log::warning('Ollama health check failed');
        });

    // Agrégation quotidienne LLM (quelques minutes après minuit)
    $schedule->command('llm:aggregate-daily')
        ->dailyAt('00:10')
        ->withoutOverlapping();

    // Purge hebdomadaire (rotation des interactions > 90 jours)
    $schedule->command('llm:prune --days=90')
        ->weeklyOn(1, '01:00') // Lundi 01:00
        ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

    // Chargement des routes console (pattern Laravel standard)
    require_once base_path('routes/console.php'); // phpcs:ignore
    }
}
