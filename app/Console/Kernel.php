<?php

namespace App\Console;
use Illuminate\Console\Scheduling\Schedule;


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

    // Nettoyage des anciennes notifications (tous les dimanches)
    $schedule->call(function () {
        app(\App\Services\MailNotificationService::class)->cleanupOldNotifications(30);
    })->weekly()->sundays()->at('02:00');

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
