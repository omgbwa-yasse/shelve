<?php

namespace App\Observers;

use App\Models\RecordPhysical;
use App\Jobs\ProcessRecordWithMcp;
use Illuminate\Support\Facades\Log;

class RecordObserver
{
    /**
     * Handle the Record "created" event.
     */
    public function created(RecordPhysical $record): void
    {
        // Traitement automatique à la création si activé
        if (config('ollama-mcp.auto_processing.enabled_on_create', false)) {
            $this->autoProcess($record, 'created');
        }
    }

    /**
     * Handle the Record "updated" event.
     */
    public function updated(RecordPhysical $record): void
    {
        // Traitement automatique à la mise à jour si activé et si des champs importants ont changé
        if (config('ollama-mcp.auto_processing.enabled_on_update', false)) {
            
            // Vérifier si des champs pertinents ont changé
            $relevantFields = ['name', 'content', 'date_start', 'date_end'];
            $hasRelevantChanges = false;
            
            foreach ($relevantFields as $field) {
                if ($record->isDirty($field)) {
                    $hasRelevantChanges = true;
                    break;
                }
            }
            
            if ($hasRelevantChanges) {
                $this->autoProcess($record, 'updated');
            }
        }
    }

    /**
     * Handle the Record "deleted" event.
     */
    public function deleted(RecordPhysical $record): void
    {
        // Optionnel : nettoyer les données MCP associées
        if (config('ollama-mcp.auto_processing.cleanup_on_delete', false)) {
            $this->cleanupMcpData($record);
        }
    }

    /**
     * Handle the Record "restored" event.
     */
    public function restored(RecordPhysical $record): void
    {
        // Retraiter automatiquement après restauration si configuré
        if (config('ollama-mcp.auto_processing.enabled_on_restore', false)) {
            $this->autoProcess($record, 'restored');
        }
    }

    /**
     * Handle the Record "force deleted" event.
     */
    public function forceDeleted(RecordPhysical $record): void
    {
        // Nettoyage définitif des données MCP
        $this->cleanupMcpData($record);
    }

    /**
     * Déclenche le traitement automatique MCP
     */
    private function autoProcess(RecordPhysical $record, string $event): void
    {
        try {
            // Vérifier si le record peut être traité
            if (!$this->canProcessRecord($record)) {
                Log::debug('Record ignoré pour traitement MCP automatique', [
                    'record_id' => $record->id,
                    'event' => $event,
                    'reason' => 'conditions non remplies'
                ]);
                return;
            }

            // Déterminer les fonctionnalités à appliquer selon la configuration
            $features = $this->getAutoProcessingFeatures($event);
            
            if (empty($features)) {
                return;
            }

            // Délai configurable avant traitement
            $delay = config('ollama-mcp.auto_processing.delay_minutes', 1);
            
            // Lancer le job en arrière-plan
            ProcessRecordWithMcp::dispatch($record, $features)
                ->delay(now()->addMinutes($delay))
                ->onQueue('mcp-auto');

            Log::info('Traitement MCP automatique programmé', [
                'record_id' => $record->id,
                'event' => $event,
                'features' => $features,
                'delay_minutes' => $delay
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du traitement MCP automatique', [
                'record_id' => $record->id,
                'event' => $event,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Vérifie si le record peut être traité automatiquement
     */
    private function canProcessRecord(RecordPhysical $record): bool
    {
        // Vérifications de base
        if (empty($record->name) || strlen($record->name) < 3) {
            return false;
        }

        // Éviter les doublons récents
        $minInterval = config('ollama-mcp.auto_processing.min_interval_hours', 24);
        $recentlyProcessed = $record->updated_at && 
            $record->updated_at->diffInHours(now()) < $minInterval;
            
        if ($recentlyProcessed) {
            return false;
        }

        // Filtres configurables par niveau, organisation, etc.
        $allowedLevels = config('ollama-mcp.auto_processing.allowed_levels', []);
        if (!empty($allowedLevels) && !in_array($record->level_id, $allowedLevels)) {
            return false;
        }

        $allowedOrganisations = config('ollama-mcp.auto_processing.allowed_organisations', []);
        if (!empty($allowedOrganisations) && !in_array($record->organisation_id, $allowedOrganisations)) {
            return false;
        }

        return true;
    }

    /**
     * Détermine les fonctionnalités à appliquer selon l'événement
     */
    private function getAutoProcessingFeatures(string $event): array
    {
        $defaultFeatures = config('ollama-mcp.auto_processing.default_features', ['thesaurus']);
        
        return match($event) {
            'created' => config('ollama-mcp.auto_processing.features_on_create', $defaultFeatures),
            'updated' => config('ollama-mcp.auto_processing.features_on_update', ['thesaurus']),
            'restored' => config('ollama-mcp.auto_processing.features_on_restore', $defaultFeatures),
            default => []
        };
    }

    /**
     * Nettoie les données MCP associées à un record supprimé
     */
    private function cleanupMcpData(RecordPhysical $record): void
    {
        try {
            // Supprimer les associations thésaurus automatiques
            if (method_exists($record, 'thesaurusConcepts')) {
                $record->thesaurusConcepts()->detach();
            }

            Log::info('Données MCP nettoyées', [
                'record_id' => $record->id
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du nettoyage MCP', [
                'record_id' => $record->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
