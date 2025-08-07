<?php

namespace App\Services\MCP;

use Cloudstudio\Ollama\Facades\Ollama;
use App\Models\Record;
use Illuminate\Support\Facades\Log;

class McpTitleReformulationService
{
    /**
     * Reformule le titre d'un record selon les règles ISAD(G)
     */
    public function reformulateTitle(Record $record): string
    {
        $prompt = $this->buildTitlePrompt($record);
        
        try {
            $response = Ollama::agent(config('ollama-mcp.prompts.system_prompt'))
                ->prompt($prompt)
                ->model(config('ollama-mcp.models.title_reformulation'))
                ->options(config('ollama-mcp.options'))
                ->ask();

            // Vérifier que la réponse contient bien la clé 'response'
            if (!isset($response['response']) || empty($response['response'])) {
                throw new \Exception('Réponse Ollama invalide ou vide');
            }

            $reformulatedTitle = $this->extractTitle($response['response']);
            
            // Mettre à jour le record
            $record->update(['name' => $reformulatedTitle]);
            
            Log::info('Titre reformulé avec succès', [
                'record_id' => $record->id,
                'original_title' => $record->getOriginal('name'),
                'new_title' => $reformulatedTitle
            ]);
            
            return $reformulatedTitle;
            
        } catch (\Exception $e) {
            Log::error('Erreur reformulation titre MCP', [
                'record_id' => $record->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Construit le prompt pour la reformulation du titre
     */
    private function buildTitlePrompt(Record $record): string
    {
        $children = $record->children()->count();
        
        $prompt = config('ollama-mcp.prompts.title_prompt_prefix') . "\n\n";
        $prompt .= "TITRE ORIGINAL : {$record->name}\n";
        $prompt .= "CONTENU : " . substr($record->content ?? '', 0, 500) . "\n";
        $prompt .= "DATES : {$record->date_start} - {$record->date_end}\n";
        $prompt .= "NIVEAU : " . ($record->level?->name ?? 'Non défini') . "\n";
        $prompt .= "NOMBRE D'OBJETS : " . ($children + 1) . "\n\n";

        // Appliquer les règles ISAD(G) selon le nombre d'objets
        if ($children === 0) {
            $prompt .= "RÈGLE À APPLIQUER : Intitulé à un objet\n";
            $prompt .= "STRUCTURE : Objet, action : typologie documentaire. Dates extrêmes\n";
            $prompt .= "EXEMPLE : Personnel de la mairie, attribution de la médaille du travail : liste des bénéficiaires. 1950-1960\n";
        } elseif ($children === 1) {
            $prompt .= "RÈGLE À APPLIQUER : Intitulé à deux objets\n";
            $prompt .= "STRUCTURE : Objet, action (dates) ; autre action (dates). Dates extrêmes\n";
            $prompt .= "EXEMPLE : Gymnase, construction (1958-1962) ; extension (1983). 1958-1983\n";
        } else {
            $prompt .= "RÈGLE À APPLIQUER : Intitulé à trois objets ou plus\n";
            $prompt .= "STRUCTURE : Objet principal. — Objet secondaire : typologie (dates). Autre objet secondaire : typologie (dates). Dates extrêmes\n";
            $prompt .= "EXEMPLE : Édifices communaux. — Mairie, reconstruction : plans (1880-1900), correspondance (1892-1899) ; extension : procès-verbal d'adjudication des travaux (1933). Écoles, aménagement : devis (par ordre alphabétique des entreprises, 1872-1930). 1872-1933\n";
        }

        $prompt .= "\nRÈGLES DE PONCTUATION :\n";
        $prompt .= "• Point-tiret (. —) : sépare l'objet principal du reste\n";
        $prompt .= "• Virgule (,) : sépare des données de niveau équivalent\n";
        $prompt .= "• Point-virgule (;) : sépare des éléments d'analyse de même nature\n";
        $prompt .= "• Deux points (:) : apportent une précision sur la typologie\n";
        $prompt .= "• Point (.) : termine l'analyse\n\n";
        
        $prompt .= "RETOURNEZ UNIQUEMENT LE TITRE REFORMULÉ, SANS EXPLICATION.";
        
        return $prompt;
    }

    /**
     * Extrait le titre reformulé de la réponse
     */
    private function extractTitle(string $response): string
    {
        // Nettoyer la réponse pour extraire uniquement le titre
        $title = trim($response);
        $title = preg_replace('/^(Titre reformulé|Nouveau titre)\s*:\s*/i', '', $title);
        $title = explode("\n", $title)[0]; // Prendre seulement la première ligne
        
        return $title;
    }

    /**
     * Prévisualise la reformulation sans sauvegarder
     */
    public function previewTitleReformulation(Record $record): array
    {
        $originalTitle = $record->name;
        
        try {
            // Créer une copie temporaire pour la reformulation
            $tempRecord = $record->replicate();
            $newTitle = $this->reformulateTitle($tempRecord);
            
            return [
                'original_title' => $originalTitle,
                'suggested_title' => $newTitle,
                'record_id' => $record->id
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur prévisualisation titre MCP', [
                'record_id' => $record->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}