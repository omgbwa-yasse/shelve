<?php

namespace App\Console\Commands;

use App\Models\Attachment;
use App\Jobs\GenerateDocumentThumbnail;
use Illuminate\Console\Command;

class RegenerateThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'thumbnails:regenerate {--force : Force regeneration even if thumbnail exists} {--type=digital_document : Type of attachment to regenerate}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Regenerate missing or failed thumbnails for documents';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');
        $type = $this->option('type');

        // Obtenir les attachments qui nécessitent une vignette
        $query = Attachment::where('type', $type);

        if (!$force) {
            // Seulement ceux qui n'ont pas de vignette ou qui ont une erreur
            $query->where(function ($q) {
                $q->whereNull('thumbnail_path')
                  ->orWhereNotNull('thumbnail_error');
            });
        }

        $attachments = $query->get();

        if ($attachments->isEmpty()) {
            $this->info('Aucun attachement à traiter.');
            return 0;
        }

        $this->info("Traitement de {$attachments->count()} attachements...");

        $count = 0;
        foreach ($attachments as $attachment) {
            if (!$attachment->canGenerateThumbnail()) {
                $this->warn("Impossible de générer une vignette pour {$attachment->name} (format non supporté)");
                continue;
            }

            GenerateDocumentThumbnail::dispatch($attachment)->onQueue('default');
            $count++;
        }

        $this->info("Mise en file d'attente de {$count} vignettes à générer.");
        return 0;
    }
}
