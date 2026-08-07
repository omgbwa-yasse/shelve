<?php

namespace App\Jobs;

use App\Models\EmailAccount;
use App\Services\Email\EmailSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncEmailAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 120;

    public function __construct(public EmailAccount $emailAccount)
    {
    }

    public function handle(EmailSyncService $syncService): void
    {
        if (! $this->emailAccount->is_active) {
            return;
        }

        $syncService->sync($this->emailAccount);
    }
}
