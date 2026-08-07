<?php

namespace App\Console\Commands;

use App\Jobs\ExtractAttachmentText;
use App\Models\Attachment;
use Illuminate\Console\Command;

class AttachmentsReindex extends Command
{
    protected $signature = 'attachments:reindex {--sync : Run synchronously without queue} {--only-missing : Only attachments without content_text}';
    protected $description = 'Extract text from attachments and (re)index for search';

    public function handle(): int
    {
        $query = Attachment::query();
        if ($this->option('only-missing')) {
            $query->whereNull('content_text');
        }

        $count = $query->count();
        $this->info("Processing {$count} attachments...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $query->chunkById(100, function ($chunk) use ($bar) {
            foreach ($chunk as $attachment) {
                if ($this->option('sync')) {
                    // Inline job handle
                    app(\App\Jobs\ExtractAttachmentText::class, ['attachmentId' => $attachment->id])->handle(app(\App\Services\AttachmentTextExtractor::class));
                } else {
                    ExtractAttachmentText::dispatch($attachment->id)->onQueue('default');
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info('Done.');

        return self::SUCCESS;
    }
}
