<?php

namespace App\Services\MCP;

use Cloudstudio\Ollama\Facades\Ollama;
use App\Models\Record;
use App\Models\ThesaurusConcept;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class McpThesaurusIndexingService
{
    /**
     * Indexe un record avec les concepts du thésaurus
     */
    public function indexRecord(Record $record): array
    {
        try {
            // 1. Extraire les mots-clés de la fiche
            $keywords = $this->extractKeywords($record);
            
            // 2. Rechercher dans le thésaurus
            $concepts = $this->searchInThesaurus($keywords);
            
            // 3. Associer les concepts trouvés au record
            $this->associateConceptsToRecord($record, $concepts);
            
            Log::info('Indexation thésaurus réussie', [
                'record_id' => $record->id,
                'keywords_extracted' => count($keywords),
                'concepts_found' => $concepts->count()
            ]);
            
            return [
                'keywords_extracted' => $keywords,
                'concepts_found' => $concepts->count(),
                'concepts' => $concepts->map(function ($item) {
                    return [
                        'concept_id' => $item['concept']->id,
                        'preferred_label' => $item['concept']->getPreferredLabel()?->literal_form,
                        'matched_term' => $item['matched_term'],
                        'weight' => $item['weight']
                    ];
                })->toArray()
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur indexation thésaurus MCP', [
                'record_id' => $record->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Extrait les mots-clés du record
     */
    private function extractKeywords(Record $record): array
    {
        $fullText = $this->buildRecordText($record);
        
        $prompt = config('ollama-mcp.prompts.thesaurus_prompt_prefix') . "\n\n";
        $prompt .= "TEXTE À ANALYSER :\n{$fullText}\n\n";
        $prompt .= "CONSIGNES :\n";
        $prompt .= "- Identifiez 5 mots-clés qui représentent les concepts principaux\n";
        $prompt .= "- Pour chaque mot-clé, proposez 3 synonymes ou termes apparentés\n";
        $prompt .= "- Focalisez-vous sur les termes archivistiques, historiques et thématiques\n";
        $prompt .= "- Évitez les mots vides (articles, prépositions, etc.)\n\n";
        $prompt .= "FORMAT DE RÉPONSE (JSON uniquement) :\n";
        $prompt .= "{\n";
        $prompt .= "  \"keywords\": [\n";
        $prompt .= "    {\n";
        $prompt .= "      \"term\": \"mot-clé-1\",\n";
        $prompt .= "      \"synonyms\": [\"synonyme1\", \"synonyme2\", \"synonyme3\"]\n";
        $prompt .= "    }\n";
        $prompt .= "  ]\n";
        $prompt .= "}";

        $response = Ollama::agent('Vous êtes un documentaliste expert en indexation.')
            ->prompt($prompt)
            ->model(config('ollama-mcp.models.keyword_extraction'))
            ->format('json')
            ->options(array_merge(config('ollama-mcp.options'), ['temperature' => 0.1]))
            ->ask();

        return $this->parseKeywordResponse($response['response']);
    }

    /**
     * Construit le texte complet du record pour l'analyse
     */
    private function buildRecordText(Record $record): string
    {
        $text = [];
        
        $text[] = "TITRE: " . $record->name;
        if ($record->content) $text[] = "CONTENU: " . $record->content;
        if ($record->biographical_history) $text[] = "HISTORIQUE: " . $record->biographical_history;
        if ($record->archival_history) $text[] = "HISTORIQUE ARCHIVISTIQUE: " . $record->archival_history;
        if ($record->note) $text[] = "NOTES: " . $record->note;
        
        // Ajouter les informations du contexte
        if ($record->activity) $text[] = "ACTIVITÉ: " . $record->activity->name;
        if ($record->organisation) $text[] = "ORGANISATION: " . $record->organisation->name;
        if ($record->level) $text[] = "NIVEAU: " . $record->level->name;
        if ($record->support) $text[] = "SUPPORT: " . $record->support->name;
        
        return implode("\n\n", $text);
    }

    /**
     * Parse la réponse JSON des mots-clés
     */
    private function parseKeywordResponse(string $response): array
    {
        try {
            $data = json_decode($response, true);
            return $data['keywords'] ?? [];
        } catch (\Exception $e) {
            Log::warning('Échec parsing JSON keywords, extraction manuelle', ['response' => $response]);
            return $this->extractKeywordsManually($response);
        }
    }

    /**
     * Recherche dans le thésaurus
     */
    private function searchInThesaurus(array $keywords): Collection
    {
        $foundConcepts = collect();
        
        foreach ($keywords as $keywordData) {
            $searchTerms = array_merge([$keywordData['term']], $keywordData['synonyms'] ?? []);
            
            foreach ($searchTerms as $term) {
                $concepts = ThesaurusConcept::whereHas('labels', function ($query) use ($term) {
                    $query->where('literal_form', 'LIKE', "%{$term}%");
                })->get();
                
                foreach ($concepts as $concept) {
                    if (!$foundConcepts->contains(function ($item) use ($concept) {
                        return $item['concept']->id === $concept->id;
                    })) {
                        $foundConcepts->push([
                            'concept' => $concept,
                            'matched_term' => $term,
                            'weight' => $this->calculateWeight($term, $concept)
                        ]);
                    }
                }
            }
        }
        
        return $foundConcepts->sortByDesc('weight');
    }

    /**
     * Calcule le poids de correspondance
     */
    private function calculateWeight(string $term, ThesaurusConcept $concept): float
    {
        $preferredLabel = $concept->getPreferredLabel();
        if (!$preferredLabel) return 0.5;
        
        $similarity = similar_text(
            strtolower($term), 
            strtolower($preferredLabel->literal_form), 
            $percent
        );
        
        return $percent / 100;
    }

    /**
     * Associe les concepts au record
     */
    private function associateConceptsToRecord(Record $record, Collection $concepts): void
    {
        $syncData = [];
        
        foreach ($concepts->take(10) as $conceptData) { // Limiter à 10 concepts max
            $syncData[$conceptData['concept']->id] = [
                'weight' => $conceptData['weight'],
                'context' => 'automatic_indexing',
                'extraction_note' => "Extrait automatiquement via MCP - terme: {$conceptData['matched_term']}"
            ];
        }
        
        $record->thesaurusConcepts()->sync($syncData);
    }

    /**
     * Méthode de fallback pour extraire les mots-clés
     */
    private function extractKeywordsManually(string $response): array
    {
        $lines = explode("\n", $response);
        $keywords = [];
        
        foreach ($lines as $line) {
            if (preg_match('/(\w+).*synonymes?\s*:(.+)/i', $line, $matches)) {
                $term = trim($matches[1]);
                $synonyms = array_map('trim', explode(',', $matches[2]));
                $keywords[] = [
                    'term' => $term,
                    'synonyms' => array_slice($synonyms, 0, 3)
                ];
            }
        }
        
        return array_slice($keywords, 0, 5);
    }

    /**
     * Obtient les concepts associés à un record
     */
    public function getRecordConcepts(Record $record): Collection
    {
        return $record->thesaurusConcepts()
            ->wherePivot('context', 'automatic_indexing')
            ->orderByPivot('weight', 'desc')
            ->get();
    }

    /**
     * Supprime l'indexation automatique d'un record
     */
    public function removeAutoIndexing(Record $record): bool
    {
        try {
            $record->thesaurusConcepts()
                ->wherePivot('context', 'automatic_indexing')
                ->detach();
                
            Log::info('Indexation automatique supprimée', ['record_id' => $record->id]);
            return true;
            
        } catch (\Exception $e) {
            Log::error('Erreur suppression indexation', [
                'record_id' => $record->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}