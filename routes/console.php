<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/*
|--------------------------------------------------------------------------
| Planification des tâches
|--------------------------------------------------------------------------
|
| En Laravel 12, `App\Console\Kernel::schedule()` n'est jamais appelé : le
| framework n'instancie ce noyau que si `bootstrap/app.php` le déclare via
| `->withKernels()`, ce que l'application ne fait pas. Toute la planification
| qui vivait dans ce fichier était donc morte (`php artisan schedule:list` ne
| listait que `inspire`). Elle est reprise ici, seul endroit réellement chargé
| — voir `bootstrap/app.php` : `withRouting(commands: routes/console.php)`.
|
*/

// === COURRIER : échéances et relances ===

// Relances J-3 sur les cotations dont l'échéance approche.
Schedule::command('mail:process-deadlines --type=reminders')
    ->hourly()
    ->withoutOverlapping();

// Cotations dont l'échéance est dépassée.
Schedule::command('mail:process-deadlines --type=overdue')
    ->everyTwoHours()
    ->withoutOverlapping();

// Escalade vers le directeur puis le DG.
Schedule::command('mail:process-deadlines --type=escalation')
    ->dailyAt('09:00')
    ->withoutOverlapping();

// === OLLAMA / LLM ===

if (config('ollama.models.auto_sync')) {
    Schedule::command('ollama:sync-models')
        ->hourly()
        ->withoutOverlapping();
}

Schedule::command('ollama:health')
    ->everyFiveMinutes()
    ->onFailure(fn () => Log::warning('Ollama health check failed'));

Schedule::command('llm:aggregate-daily')
    ->dailyAt('00:10')
    ->withoutOverlapping();

Schedule::command('llm:prune --days=90')
    ->weeklyOn(1, '01:00')
    ->withoutOverlapping();
