<?php

namespace App\Console;
use Illuminate\Console\Scheduling\Schedule;


protected function schedule(Schedule $schedule)
{
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