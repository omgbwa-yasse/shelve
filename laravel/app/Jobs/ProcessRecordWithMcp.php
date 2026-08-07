<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\RecordPhysical;
use App\Services\MCP\McpManagerService;
use Illuminate\Support\Facades\Log;

class ProcessRecordWithMcp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3;
    public $maxExceptions = 2;

    /**
     * Créer une nouvelle instance du job
     */
    public function __construct(
        public RecordPhysical $record,
        public array $features = ['title', 'thesaurus', 'summary']
    ) {}

    /**
     * Exécuter le job
     */
    public function handle(McpManagerService $mcpManager): void
    {
        try {
            Log::info('Début traitement MCP job', [
                'record_id' => $this->record->id,
                'features' => $this->features,
                'attempt' => $this->attempts()
            ]);

            $results = $mcpManager->processRecord($this->record, $this->features);
            
            Log::info('Job MCP terminé avec succès', [
                'record_id' => $this->record->id,
                'features' => $this->features,
                'results_keys' => array_keys($results),
                'duration' => $this->getDuration()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Échec du job MCP', [
                'record_id' => $this->record->id,
                'features' => $this->features,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
                'max_tries' => $this->tries
            ]);
            
            // Relancer le job si ce n'est pas le dernier essai
            if ($this->attempts() < $this->tries) {
                $delay = $this->calculateRetryDelay();
                $this->release($delay);
                return;
            }
            
            // Si tous les essais ont échoué, enregistrer l'échec définitif
            $this->fail($e);
        }
    }

    /**
     * Calcule le délai avant nouvelle tentative (backoff exponentiel)
     */
    private function calculateRetryDelay(): int
    {
        $baseDelay = config('ollama-mcp.performance.retry_delay', 1000) / 1000; // Convertir en secondes
        return (int) ($baseDelay * pow(2, $this->attempts() - 1));
    }

    /**
     * Appelé quand le job échoue définitivement
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job MCP définitivement échoué', [
            'record_id' => $this->record->id,
            'features' => $this->features,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
        
        // Ici vous pouvez ajouter une notification d'admin ou autre action
        // NotificationService::notifyAdmin("Job MCP échoué pour le record {$this->record->id}");
    }

    /**
     * Obtient la durée d'exécution du job
     */
    private function getDuration(): ?float
    {
        // Pour une implémentation plus simple, on peut utiliser les timestamps
        return null; // À implémenter si nécessaire avec des métriques personnalisées
    }

    /**
     * Tags pour le monitoring des jobs
     */
    public function tags(): array
    {
        return [
            'mcp',
            'record:' . $this->record->id,
            'features:' . implode(',', $this->features)
        ];
    }

    /**
     * Détermine la queue à utiliser en fonction des fonctionnalités
     */
    public function viaQueue(): string
    {
        // Utiliser des queues différentes selon la charge
        if (count($this->features) === 1) {
            return 'mcp-light';
        } elseif (count($this->features) === 2) {
            return 'mcp-medium';
        } else {
            return 'mcp-heavy';
        }
    }

    /**
     * Middleware à appliquer au job
     */
    public function middleware(): array
    {
        return [
            // On peut ajouter des middleware personnalisés ici
            // new RateLimited('mcp-processing')
        ];
    }
}
