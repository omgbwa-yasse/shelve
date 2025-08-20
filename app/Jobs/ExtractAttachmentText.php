<?php

namespace App\Jobs;

use App\Models\Attachment;
use App\Services\AttachmentTextExtractor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExtractAttachmentText implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $attachmentId) {}

    public function handle(AttachmentTextExtractor $extractor): void
    {
        $attachment = Attachment::find($this->attachmentId);
        if (!$attachment) {
            return;
        }

        $relative = $attachment->path; // e.g., attachments/xyz.pdf
        $absolute = storage_path('app/' . ltrim($relative, '/'));
        if (!is_file($absolute)) {
            Log::warning('Attachment file missing for extraction', ['id' => $attachment->id, 'path' => $absolute]);
            return;
        }

        $text = $extractor->extract($absolute, $attachment->mime_type, $attachment->name);
        $attachment->content_text = $text ?: null;
        $attachment->save();

        // Sync to search index
        $attachment->searchable();
    }
}
