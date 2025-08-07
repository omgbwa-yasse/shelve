<?php

namespace App\Services\MCP;

use Cloudstudio\Ollama\Facades\Ollama;
use App\Models\Record;
use Illuminate\Support\Facades\Log;

class McpContentSummarizationService
{
    /**
     * Génère un résumé ISAD(G) pour un record
     */
    public function generateSummary(Record $record): string
    {
        try {
            // Récupérer les données contextuelles
            $contextData = $this->gatherContextData($record);
            
            // Construire le prompt selon les règles ISAD(G)
            $prompt = $this->buildSummaryPrompt($record, $contextData);
            
            // Générer le résumé
            $response = Ollama::agent(config('ollama-mcp.prompts.system_prompt'))
                ->prompt($prompt)
                ->model(config('ollama-mcp.models.content_summarization'))
                ->options(config('ollama-mcp.options'))
                ->ask();
            
            // Vérifier que la réponse contient bien la clé 'response'
            if (!isset($response['response']) || empty($response['response'])) {
                throw new \Exception('Réponse Ollama invalide ou vide pour le résumé');
            }

            $summary = $this->cleanSummaryResponse($response['response']);
            
            // Mettre à jour le champ content du record
            $record->update(['content' => $summary]);
            
            Log::info('Résumé ISAD(G) généré avec succès', [
                'record_id' => $record->id,
                'summary_length' => strlen($summary)
            ]);
            
            return $summary;
            
        } catch (\Exception $e) {
            Log::error('Erreur génération résumé MCP', [
                'record_id' => $record->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Rassemble les données contextuelles du record
     */
    private function gatherContextData(Record $record): array
    {
        return [
            'parent' => $record->parent,
            'children' => $record->children()->with(['level', 'activity'])->get(),
            'activity' => $record->activity,
            'organisation' => $record->organisation,
            'level' => $record->level,
            'support' => $record->support
        ];
    }

    /**
     * Construit le prompt pour le résumé ISAD(G)
     */
    private function buildSummaryPrompt(Record $record, array $context): string
    {
        $prompt = config('ollama-mcp.prompts.summary_prompt_prefix') . "\n\n";
        
        // Informations principales du record
        $prompt .= "RECORD PRINCIPAL :\n";
        $prompt .= "- Titre : {$record->name}\n";
        $prompt .= "- Niveau : " . ($context['level']->name ?? 'Non défini') . "\n";
        $prompt .= "- Dates : {$record->date_start} - {$record->date_end}\n";
        $prompt .= "- Largeur : {$record->width} - {$record->width_description}\n";
        $prompt .= "- Support : " . ($context['support']->name ?? 'Non défini') . "\n";
        $prompt .= "- Activité : " . ($context['activity']->name ?? 'Non définie') . "\n";
        
        if ($record->biographical_history) {
            $prompt .= "- Historique biographique : " . substr($record->biographical_history, 0, 300) . "\n";
        }
        
        if ($record->archival_history) {
            $prompt .= "- Historique archivistique : " . substr($record->archival_history, 0, 300) . "\n";
        }
        
        // Contexte parent
        if ($context['parent']) {
            $prompt .= "\nRECORD PARENT :\n";
            $prompt .= "- Titre : {$context['parent']->name}\n";
            $prompt .= "- Niveau : " . ($context['parent']->level->name ?? 'Non défini') . "\n";
            if ($context['parent']->content) {
                $prompt .= "- Contenu : " . substr($context['parent']->content, 0, 200) . "...\n";
            }
        }
        
        // Dossiers enfants
        if ($context['children']->isNotEmpty()) {
            $prompt .= "\nDOSSIERS ENFANTS :\n";
            foreach ($context['children']->take(5) as $child) {
                $prompt .= "- {$child->name}";
                if ($child->date_start) $prompt .= " ({$child->date_start})";
                $prompt .= "\n";
            }
            if ($context['children']->count() > 5) {
                $prompt .= "... et " . ($context['children']->count() - 5) . " autres dossiers\n";
            }
        }
        
        // Règles ISAD(G) spécifiques au niveau
        $prompt .= "\nRÈGLES ISAD(G) À APPLIQUER :\n";
        
        $levelName = $context['level']->name ?? 'dossier';
        
        if (stripos($levelName, 'fonds') !== false) {
            $prompt .= "- NIVEAU FONDS : Vue d'ensemble, informations communes, nature générale des documents, activités principales du producteur, périodes couvertes et zones géographiques\n";
        } elseif (stripos($levelName, 'série') !== false) {
            $prompt .= "- NIVEAU SÉRIE : Fonction administrative ou activité spécifique, types de documents présents, organisation interne sommaire, sujets particuliers couverts\n";
        } else {
            $prompt .= "- NIVEAU DOSSIER : Objet précis du dossier, contenu spécifique des pièces, chronologie des événements si pertinente, acteurs impliqués\n";
        }
        
        $prompt .= "\nSTRUCTURE RECOMMANDÉE :\n";
        $prompt .= "Format : 'La série/Le dossier comprend/contient [typologie] concernant [objet/sujet] et couvrant [période/périmètre]'\n";
        $prompt .= "\nORDRE DE PRÉSENTATION :\n";
        $prompt .= "1. Nature/typologie des documents\n";
        $prompt .= "2. Objet/sujet principal\n";
        $prompt .= "3. Complément d'information (géographique, chronologique)\n";
        $prompt .= "4. Éléments remarquables (avec 'avec', 'dont', 'notamment', 'en particulier')\n";
        
        $prompt .= "\nMOTS-OUTILS À UTILISER :\n";
        $prompt .= "- 'comprend', 'contient' : pour énumérer le contenu\n";
        $prompt .= "- 'concerne' : pour indiquer les sujets traités\n";
        $prompt .= "- 'avec'/'dont' : pour signaler des éléments particuliers\n";
        $prompt .= "- 'notamment', 'en particulier' : pour les aspects saillants\n";
        
        $prompt .= "\nTYPOLOGIES DOCUMENTAIRES PRÉCISES À PRIVILÉGIER :\n";
        $prompt .= "- Correspondance (plutôt que 'lettres')\n";
        $prompt .= "- Procès-verbaux (plutôt que 'comptes-rendus')\n";
        $prompt .= "- États nominatifs (plutôt que 'listes')\n";
        $prompt .= "- Rapports d'inspection (plutôt que 'rapports')\n";
        
        $prompt .= "\nGÉNÉREZ UNIQUEMENT LA DESCRIPTION DE CONTENU, SANS TITRE NI EXPLICATION SUPPLÉMENTAIRE.";
        
        return $prompt;
    }

    /**
     * Nettoie la réponse du résumé
     */
    private function cleanSummaryResponse(string $response): string
    {
        // Nettoyer la réponse
        $summary = trim($response);
        
        // Supprimer les préfixes indésirables
        $summary = preg_replace('/^(Description|Contenu|Résumé)\s*:\s*/i', '', $summary);
        $summary = preg_replace('/^(La description|Le contenu)\s+est\s+la\s+suivante\s*:\s*/i', '', $summary);
        
        // S'assurer que ça commence par une majuscule
        $summary = ucfirst($summary);
        
        // Supprimer les guillemets de début et fin si présents
        $summary = trim($summary, '"\'');
        
        return $summary;
    }

    /**
     * Génère un aperçu du résumé sans sauvegarder
     */
    public function previewSummary(Record $record): array
    {
        $originalContent = $record->content;
        
        try {
            $contextData = $this->gatherContextData($record);
            $prompt = $this->buildSummaryPrompt($record, $contextData);
            
            $response = Ollama::agent(config('ollama-mcp.prompts.system_prompt'))
                ->prompt($prompt)
                ->model(config('ollama-mcp.models.content_summarization'))
                ->options(config('ollama-mcp.options'))
                ->ask();
            
            // Vérifier que la réponse contient bien la clé 'response'
            if (!isset($response['response']) || empty($response['response'])) {
                throw new \Exception('Réponse Ollama invalide ou vide pour la prévisualisation');
            }

            $suggestedSummary = $this->cleanSummaryResponse($response['response']);
            
            return [
                'original_content' => $originalContent,
                'suggested_summary' => $suggestedSummary,
                'record_id' => $record->id,
                'level' => $record->level?->name,
                'children_count' => $record->children()->count()
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur prévisualisation résumé MCP', [
                'record_id' => $record->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Valide qu'un record peut être traité
     */
    public function canProcessRecord(Record $record): bool
    {
        // Validation très permissive pour les tests
        return !empty($record->name) && strlen(trim($record->name)) >= 3;
    }
}