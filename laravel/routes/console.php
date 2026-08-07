<?php

use App\Jobs\SyncEmailAccountJob;
use App\Models\EmailAccount;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/**
 * Synchronise chaque compte de messagerie actif (INBOX + Sent) toutes les
 * 5 minutes — une synchro par job pour qu'un compte en échec (identifiants
 * invalides, serveur injoignable) n'empêche pas les autres de tourner.
 */
Schedule::call(function () {
    EmailAccount::query()->where('is_active', true)->each(
        fn (EmailAccount $account) => SyncEmailAccountJob::dispatch($account)
    );
})->everyFiveMinutes()->name('email-accounts-sync')->withoutOverlapping();
