# Documentation Complète : Utilisation d'Ollama avec Laravel 11

## Table des Matières

1. [Introduction à Ollama et Laravel](#introduction)
2. [Installation et Configuration](#installation-configuration)
3. [Fonctionnalités MCP avec Ollama](#fonctionnalites-mcp)
4. [Implémentation des Services IA](#services-ia)
5. [Intégration avec vos Modèles Existants](#integration-modeles)
6. [Exemples Pratiques](#exemples-pratiques)
7. [Meilleures Pratiques et Sécurité](#meilleures-pratiques)
8. [Tests et Débogage](#tests-debogage)
9. [Optimisation des Performances](#optimisation-performances)
10. [Déploiement en Production](#deploiement-production)

---

## 1. Introduction à Ollama et Laravel {#introduction}

### Qu'est-ce qu'Ollama ?

Ollama est un framework léger et extensible pour l'exécution de grands modèles de langage (LLM) localement. Il permet d'utiliser des modèles comme Llama 2, Mistral, CodeLlama directement sur votre serveur sans dépendre d'APIs externes.

### Avantages pour votre système d'archivage

- **Confidentialité** : Vos données archivistiques restent sur vos serveurs
- **Coût maîtrisé** : Pas de frais par token ou requête API
- **Performance** : Traitement local rapide pour l'analyse de documents
- **Personnalisation** : Modèles adaptés à vos besoins archivistiques

### Modèles recommandés

Pour vos fonctionnalités MCP, voici les modèles Ollama recommandés :

- **llama3.1:8b** : Excellent pour l'analyse de texte et la reformulation
- **mistral:7b** : Performant pour l'extraction d'informations
- **codellama:7b** : Optimal pour la génération de code
- **nuextract** : Spécialisé dans l'extraction structurée d'informations
- **nomic-embed-text** : Génération d'embeddings pour la recherche sémantique

---

## 2. Installation et Configuration {#installation-configuration}

### Prérequis

- PHP 8.2+
- Laravel 11+
- Composer
- Docker (optionnel mais recommandé)

### Installation d'Ollama

#### Sur macOS/Linux

```bash
# Télécharger et installer Ollama
curl -fsSL https://ollama.ai/install.sh | sh

# Démarrer le service
sudo systemctl start ollama
sudo systemctl enable ollama

# Vérifier l'installation
ollama --version
```

#### Téléchargement des modèles

```bash
# Modèles essentiels pour vos fonctionnalités MCP
ollama pull llama3.1:8b
ollama pull mistral:7b
ollama pull codellama:7b
ollama pull nuextract
ollama pull nomic-embed-text

# Vérifier les modèles installés
ollama list
```

### Installation du package Laravel

Le package `cloudstudio/ollama-laravel` est le plus mature et maintenu activement :

```bash
# Installer le package principal
composer require cloudstudio/ollama-laravel

# Publier la configuration
php artisan vendor:publish --tag="ollama-laravel-config"
```

### Configuration Laravel

#### Configuration de base `.env`

```env
# Configuration Ollama
OLLAMA_MODEL=llama3.1:8b
OLLAMA_URL=http://127.0.0.1:11434
OLLAMA_DEFAULT_PROMPT="Vous êtes un assistant archiviste expert."
OLLAMA_CONNECTION_TIMEOUT=300

# Configuration pour Docker (si applicable)
OLLAMA_URL=http://host.docker.internal:11434
```

#### Configuration avancée `config/ollama.php`

```php
<?php

return [
    'model' => env('OLLAMA_MODEL', 'llama3.1:8b'),
    'url' => env('OLLAMA_URL', 'http://127.0.0.1:11434'),
    'default_prompt' => env('OLLAMA_DEFAULT_PROMPT', 'Vous êtes un assistant archiviste expert.'),
    'connection' => [
        'timeout' => env('OLLAMA_CONNECTION_TIMEOUT', 300),
    ],
    
    // Configuration spécifique aux fonctionnalités MCP
    'mcp' => [
        'models' => [
            'title_reformulation' => env('OLLAMA_MCP_TITLE_MODEL', 'llama3.1:8b'),
            'thesaurus_indexing' => env('OLLAMA_MCP_THESAURUS_MODEL', 'mistral:7b'),
            'content_summarization' => env('OLLAMA_MCP_SUMMARY_MODEL', 'llama3.1:8b'),
            'keyword_extraction' => env('OLLAMA_MCP_KEYWORD_MODEL', 'nuextract'),
        ],
        'options' => [
            'temperature' => 0.2, // Plus déterministe pour les tâches archivistiques
            'top_p' => 0.9,
            'max_tokens' => 2000,
        ]
    ]
];
```

---

## 3. Fonctionnalités MCP avec Ollama {#fonctionnalites-mcp}

### Architecture des Services MCP

Créons une architecture modulaire pour vos trois fonctionnalités MCP :

```bash
php artisan make:service McpTitleReformulationService
php artisan make:service McpThesaurusIndexingService
php artisan make:service McpContentSummarizationService
```

### Fonctionnalité 1 : Reformulation du Titre Record

#### Service de Reformulation

```php
<?php

namespace App\Services\MCP;

use Cloudstudio\Ollama\Facades\Ollama;
use App\Models\Record;
use Illuminate\Support\Facades\Log;

class McpTitleReformulationService
{
    public function reformulateTitle(Record $record): string
    {
        $prompt = $this->buildTitlePrompt($record);
        
        try {
            $response = Ollama::agent('Vous êtes un archiviste expert spécialisé dans les règles ISAD(G).')
                ->prompt($prompt)
                ->model(config('ollama.mcp.models.title_reformulation'))
                ->options(config('ollama.mcp.options'))
                ->ask();

            $reformulatedTitle = $this->extractTitle($response['response']);
            
            // Mettre à jour le record
            $record->update(['name' => $reformulatedTitle]);
            
            return $reformulatedTitle;
        } catch (\Exception $e) {
            Log::error('Erreur reformulation titre MCP', [
                'record_id' => $record->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function buildTitlePrompt(Record $record): string
    {
        $children = $record->children()->count();
        
        $prompt = "Reformulez ce titre d'archive selon les règles ISAD(G) :\n\n";
        $prompt .= "TITRE ORIGINAL : {$record->name}\n";
        $prompt .= "CONTENU : " . substr($record->content ?? '', 0, 500) . "\n";
        $prompt .= "DATES : {$record->date_start} - {$record->date_end}\n";
        $prompt .= "NIVEAU : {$record->level?->name}\n";
        $prompt .= "NOMBRE D'OBJETS : " . ($children + 1) . "\n\n";

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

        $prompt .= "\nRETOURNEZ UNIQUEMENT LE TITRE REFORMULÉ, SANS EXPLICATION.";
        
        return $prompt;
    }

    private function extractTitle(string $response): string
    {
        // Nettoyer la réponse pour extraire uniquement le titre
        return trim(preg_replace('/^(Titre reformulé|Nouveau titre)\s*:\s*/i', '', $response));
    }
}
```

### Fonctionnalité 2 : Indexation Thésaurus

#### Service d'Indexation

```php
<?php

namespace App\Services\MCP;

use Cloudstudio\Ollama\Facades\Ollama;
use App\Models\Record;
use App\Models\ThesaurusConcept;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class McpThesaurusIndexingService
{
    public function indexRecord(Record $record): array
    {
        try {
            // 1. Extraire les mots-clés de la fiche
            $keywords = $this->extractKeywords($record);
            
            // 2. Rechercher dans le thésaurus
            $concepts = $this->searchInThesaurus($keywords);
            
            // 3. Associer les concepts trouvés au record
            $this->associateConceptsToRecord($record, $concepts);
            
            return [
                'keywords_extracted' => $keywords,
                'concepts_found' => $concepts->count(),
                'concepts' => $concepts->toArray()
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur indexation thésaurus MCP', [
                'record_id' => $record->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function extractKeywords(Record $record): array
    {
        $fullText = $this->buildRecordText($record);
        
        $prompt = "Analysez ce texte archivistique et extrayez exactement 5 mots-clés principaux avec 3 synonymes chacun.

TEXTE À ANALYSER :
{$fullText}

CONSIGNES :
- Identifiez 5 mots-clés qui représentent les concepts principaux
- Pour chaque mot-clé, proposez 3 synonymes ou termes apparentés
- Focalisez-vous sur les termes archivistiques, historiques et thématiques
- Évitez les mots vides (articles, prépositions, etc.)

FORMAT DE RÉPONSE (JSON uniquement) :
{
  \"keywords\": [
    {
      \"term\": \"mot-clé-1\",
      \"synonyms\": [\"synonyme1\", \"synonyme2\", \"synonyme3\"]
    }
  ]
}";

        $response = Ollama::agent('Vous êtes un documentaliste expert en indexation.')
            ->prompt($prompt)
            ->model(config('ollama.mcp.models.keyword_extraction'))
            ->format('json')
            ->options(['temperature' => 0.1])
            ->ask();

        return $this->parseKeywordResponse($response['response']);
    }

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
        
        return implode("\n\n", $text);
    }

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

    private function searchInThesaurus(array $keywords): Collection
    {
        $foundConcepts = collect();
        
        foreach ($keywords as $keywordData) {
            $searchTerms = array_merge([$keywordData['term']], $keywordData['synonyms']);
            
            foreach ($searchTerms as $term) {
                $concepts = ThesaurusConcept::whereHas('labels', function ($query) use ($term) {
                    $query->where('literal_form', 'LIKE', "%{$term}%");
                })->get();
                
                foreach ($concepts as $concept) {
                    if (!$foundConcepts->contains('id', $concept->id)) {
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

    private function calculateWeight(string $term, ThesaurusConcept $concept): float
    {
        // Calculer un poids basé sur la correspondance
        $preferredLabel = $concept->getPreferredLabel();
        if (!$preferredLabel) return 0.5;
        
        $similarity = similar_text(
            strtolower($term), 
            strtolower($preferredLabel->literal_form), 
            $percent
        );
        
        return $percent / 100;
    }

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

    private function extractKeywordsManually(string $response): array
    {
        // Méthode de fallback pour extraire les mots-clés si le JSON échoue
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
}
```

### Fonctionnalité 3 : Résumé ISAD(G)

#### Service de Résumé

```php
<?php

namespace App\Services\MCP;

use Cloudstudio\Ollama\Facades\Ollama;
use App\Models\Record;
use Illuminate\Support\Facades\Log;

class McpContentSummarizationService
{
    public function generateSummary(Record $record): string
    {
        try {
            // Récupérer les données contextuelles
            $contextData = $this->gatherContextData($record);
            
            // Construire le prompt selon les règles ISAD(G)
            $prompt = $this->buildSummaryPrompt($record, $contextData);
            
            // Générer le résumé
            $response = Ollama::agent('Vous êtes un archiviste expert en description selon la norme ISAD(G).')
                ->prompt($prompt)
                ->model(config('ollama.mcp.models.content_summarization'))
                ->options(config('ollama.mcp.options'))
                ->ask();
            
            $summary = $this->cleanSummaryResponse($response['response']);
            
            // Mettre à jour le champ content du record
            $record->update(['content' => $summary]);
            
            return $summary;
            
        } catch (\Exception $e) {
            Log::error('Erreur génération résumé MCP', [
                'record_id' => $record->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

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

    private function buildSummaryPrompt(Record $record, array $context): string
    {
        $prompt = "Rédigez une description de contenu selon la norme ISAD(G) élément 3.3.1 'Portée et contenu'.\n\n";
        
        // Informations principales du record
        $prompt .= "RECORD PRINCIPAL :\n";
        $prompt .= "- Titre : {$record->name}\n";
        $prompt .= "- Niveau : " . ($context['level']->name ?? 'Non défini') . "\n";
        $prompt .= "- Dates : {$record->date_start} - {$record->date_end}\n";
        $prompt .= "- Largeur : {$record->width} - {$record->width_description}\n";
        $prompt .= "- Support : " . ($context['support']->name ?? 'Non défini') . "\n";
        $prompt .= "- Activité : " . ($context['activity']->name ?? 'Non définie') . "\n";
        
        if ($record->biographical_history) {
            $prompt .= "- Historique biographique : {$record->biographical_history}\n";
        }
        
        if ($record->archival_history) {
            $prompt .= "- Historique archivistique : {$record->archival_history}\n";
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
        
        if (strpos(strtolower($levelName), 'fonds') !== false) {
            $prompt .= "- NIVEAU FONDS : Vue d'ensemble, informations communes, nature générale des documents, activités principales du producteur, périodes couvertes et zones géographiques\n";
        } elseif (strpos(strtolower($levelName), 'série') !== false) {
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
        
        $prompt .= "\nGÉNÉREZ UNIQUEMENT LA DESCRIPTION DE CONTENU, SANS TITRE NI EXPLICATION SUPPLÉMENTAIRE.";
        
        return $prompt;
    }

    private function cleanSummaryResponse(string $response): string
    {
        // Nettoyer la réponse
        $summary = trim($response);
        
        // Supprimer les préfixes indésirables
        $summary = preg_replace('/^(Description|Contenu|Résumé)\s*:\s*/i', '', $summary);
        $summary = preg_replace('/^(La description|Le contenu)\s+est\s+la\s+suivante\s*:\s*/i', '', $summary);
        
        // S'assurer que ça commence par une majuscule
        $summary = ucfirst($summary);
        
        return $summary;
    }
}
```

---

## 4. Implémentation des Services IA {#services-ia}

### Service Manager Principal

Créons un service manager pour orchestrer les trois fonctionnalités MCP :

```php
<?php

namespace App\Services\MCP;

use App\Models\Record;
use App\Services\MCP\McpTitleReformulationService;
use App\Services\MCP\McpThesaurusIndexingService;
use App\Services\MCP\McpContentSummarizationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class McpManagerService
{
    protected McpTitleReformulationService $titleService;
    protected McpThesaurusIndexingService $thesaurusService;
    protected McpContentSummarizationService $summaryService;

    public function __construct(
        McpTitleReformulationService $titleService,
        McpThesaurusIndexingService $thesaurusService,
        McpContentSummarizationService $summaryService
    ) {
        $this->titleService = $titleService;
        $this->thesaurusService = $thesaurusService;
        $this->summaryService = $summaryService;
    }

    public function processRecord(Record $record, array $features = ['title', 'thesaurus', 'summary']): array
    {
        $results = [];
        
        DB::beginTransaction();
        
        try {
            // Fonctionnalité 1 : Reformulation du titre
            if (in_array('title', $features)) {
                $results['title'] = $this->titleService->reformulateTitle($record);
            }
            
            // Fonctionnalité 2 : Indexation thésaurus
            if (in_array('thesaurus', $features)) {
                $results['thesaurus'] = $this->thesaurusService->indexRecord($record);
            }
            
            // Fonctionnalité 3 : Génération du résumé
            if (in_array('summary', $features)) {
                $results['summary'] = $this->summaryService->generateSummary($record);
            }
            
            DB::commit();
            
            Log::info('Traitement MCP réussi', [
                'record_id' => $record->id,
                'features' => $features,
                'results' => array_keys($results)
            ]);
            
            return $results;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Échec traitement MCP', [
                'record_id' => $record->id,
                'features' => $features,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    public function batchProcessRecords(array $recordIds, array $features = ['title', 'thesaurus', 'summary']): array
    {
        $results = [];
        $errors = [];
        
        foreach ($recordIds as $recordId) {
            try {
                $record = Record::findOrFail($recordId);
                $results[$recordId] = $this->processRecord($record, $features);
            } catch (\Exception $e) {
                $errors[$recordId] = $e->getMessage();
            }
        }
        
        return [
            'processed' => count($results),
            'errors' => count($errors),
            'results' => $results,
            'error_details' => $errors
        ];
    }
}
```

### Commandes Artisan

Créons des commandes pour faciliter l'utilisation :

```bash
php artisan make:command McpProcessRecordsCommand
php artisan make:command McpBatchProcessCommand
```

#### Commande de traitement individuel

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Record;
use App\Services\MCP\McpManagerService;

class McpProcessRecordsCommand extends Command
{
    protected $signature = 'mcp:process-record {record_id} {--features=title,thesaurus,summary}';
    protected $description = 'Traite un record avec les fonctionnalités MCP';

    public function handle(McpManagerService $mcpManager)
    {
        $recordId = $this->argument('record_id');
        $features = explode(',', $this->option('features'));
        
        try {
            $record = Record::findOrFail($recordId);
            
            $this->info("Traitement du record ID: {$recordId}");
            $this->info("Fonctionnalités: " . implode(', ', $features));
            
            $results = $mcpManager->processRecord($record, $features);
            
            $this->newLine();
            $this->info('✅ Traitement réussi !');
            
            if (isset($results['title'])) {
                $this->line("📝 Nouveau titre: {$results['title']}");
            }
            
            if (isset($results['thesaurus'])) {
                $this->line("🏷️  Concepts indexés: {$results['thesaurus']['concepts_found']}");
            }
            
            if (isset($results['summary'])) {
                $this->line("📄 Résumé généré: " . substr($results['summary'], 0, 100) . "...");
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
```

#### Commande de traitement par lot

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Record;
use App\Services\MCP\McpManagerService;
use Illuminate\Support\Facades\DB;

class McpBatchProcessCommand extends Command
{
    protected $signature = 'mcp:batch-process 
                           {--organisation_id= : ID de l\'organisation} 
                           {--activity_id= : ID de l\'activité}
                           {--level_id= : ID du niveau}
                           {--features=title,thesaurus,summary : Fonctionnalités à appliquer}
                           {--limit=50 : Nombre maximum de records à traiter}';
    
    protected $description = 'Traite plusieurs records en lot avec les fonctionnalités MCP';

    public function handle(McpManagerService $mcpManager)
    {
        $features = explode(',', $this->option('features'));
        $limit = (int) $this->option('limit');
        
        // Construire la requête
        $query = Record::query();
        
        if ($organisationId = $this->option('organisation_id')) {
            $query->where('organisation_id', $organisationId);
        }
        
        if ($activityId = $this->option('activity_id')) {
            $query->where('activity_id', $activityId);
        }
        
        if ($levelId = $this->option('level_id')) {
            $query->where('level_id', $levelId);
        }
        
        $recordIds = $query->limit($limit)->pluck('id')->toArray();
        
        if (empty($recordIds)) {
            $this->warn('Aucun record trouvé avec ces critères.');
            return Command::SUCCESS;
        }
        
        $this->info("Traitement de " . count($recordIds) . " records...");
        $this->info("Fonctionnalités: " . implode(', ', $features));
        
        $progressBar = $this->output->createProgressBar(count($recordIds));
        $progressBar->start();
        
        $results = [];
        $errors = [];
        
        foreach ($recordIds as $recordId) {
            try {
                $record = Record::find($recordId);
                if ($record) {
                    $results[$recordId] = $mcpManager->processRecord($record, $features);
                }
            } catch (\Exception $e) {
                $errors[$recordId] = $e->getMessage();
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        $this->info("✅ Traitement terminé !");
        $this->info("Records traités avec succès: " . count($results));
        
        if (!empty($errors)) {
            $this->error("Records en erreur: " . count($errors));
            foreach ($errors as $recordId => $error) {
                $this->line("  - Record {$recordId}: {$error}");
            }
        }
        
        return Command::SUCCESS;
    }
}
```

---

## 5. Intégration avec vos Modèles Existants {#integration-modeles}

### Middleware pour l'IA automatique

Créons un middleware qui déclenche automatiquement les traitements MCP :

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\MCP\McpManagerService;
use App\Models\Record;
use Illuminate\Support\Facades\Log;

class AutoMcpProcessingMiddleware
{
    protected McpManagerService $mcpManager;

    public function __construct(McpManagerService $mcpManager)
    {
        $this->mcpManager = $mcpManager;
    }

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Traitement automatique après création/modification d'un record
        if ($request->isMethod('POST') || $request->isMethod('PUT')) {
            $this->handleRecordProcessing($request);
        }
        
        return $response;
    }

    private function handleRecordProcessing(Request $request): void
    {
        // Vérifier si c'est une route de record
        if (!$request->route() || !str_contains($request->route()->getName() ?? '', 'records')) {
            return;
        }
        
        // Récupérer l'ID du record depuis la route ou les paramètres
        $recordId = $request->route('record') ?? $request->get('record_id');
        
        if (!$recordId) return;
        
        // Traitement asynchrone (recommandé)
        dispatch(function () use ($recordId) {
            try {
                $record = Record::find($recordId);
                if ($record) {
                    // Configuration des fonctionnalités à appliquer automatiquement
                    $features = config('ollama.mcp.auto_features', ['thesaurus']);
                    $this->mcpManager->processRecord($record, $features);
                }
            } catch (\Exception $e) {
                Log::warning('Auto MCP processing failed', [
                    'record_id' => $recordId,
                    'error' => $e->getMessage()
                ]);
            }
        })->delay(now()->addSeconds(5)); // Délai pour éviter les conflits
    }
}
```

### Jobs en Queue pour les Traitements Lourds

```bash
php artisan make:job ProcessRecordWithMcp
```

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Record;
use App\Services\MCP\McpManagerService;
use Illuminate\Support\Facades\Log;

class ProcessRecordWithMcp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3;

    public function __construct(
        public Record $record,
        public array $features = ['title', 'thesaurus', 'summary']
    ) {}

    public function handle(McpManagerService $mcpManager): void
    {
        try {
            $results = $mcpManager->processRecord($this->record, $this->features);
            
            Log::info('Job MCP terminé avec succès', [
                'record_id' => $this->record->id,
                'features' => $this->features,
                'results_keys' => array_keys($results)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Échec du job MCP', [
                'record_id' => $this->record->id,
                'features' => $this->features,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);
            
            // Relancer le job si ce n'est pas le dernier essai
            if ($this->attempts() < $this->tries) {
                $this->release(60); // Attendre 1 minute avant de réessayer
            }
            
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Job MCP définitivement échoué', [
            'record_id' => $this->record->id,
            'features' => $this->features,
            'error' => $exception->getMessage()
        ]);
        
        // Envoyer une notification à l'administrateur
        // NotificationService::notifyAdmin("Job MCP échoué pour le record {$this->record->id}");
    }
}
```

### Observer pour les Modèles

Créons un Observer pour déclencher automatiquement les traitements :

```bash
php artisan make:observer RecordObserver --model=Record
```

```php
<?php

namespace App\Observers;

use App\Models\Record;
use App\Jobs\ProcessRecordWithMcp;
use Illuminate\Support\Facades\Config;

class RecordObserver
{
    public function created(Record $record): void
    {
        if (Config::get('ollama.mcp.auto_process_on_create', true)) {
            $features = Config::get('ollama.mcp.auto_features_on_create', ['thesaurus']);
            ProcessRecordWithMcp::dispatch($record, $features);
        }
    }

    public function updated(Record $record): void
    {
        // Traiter seulement si certains champs ont changé
        $watchedFields = ['name', 'content', 'biographical_history'];
        $changedFields = array_keys($record->getChanges());
        
        if (array_intersect($watchedFields, $changedFields) && 
            Config::get('ollama.mcp.auto_process_on_update', false)) {
            
            $features = Config::get('ollama.mcp.auto_features_on_update', ['summary']);
            ProcessRecordWithMcp::dispatch($record, $features);
        }
    }
}
```

### Configuration avancée

Ajoutons ces configurations à `config/ollama.php` :

```php
// Ajout dans le fichier de configuration
'mcp' => [
    // ... configurations existantes ...
    
    // Auto-traitement
    'auto_process_on_create' => env('MCP_AUTO_PROCESS_CREATE', true),
    'auto_process_on_update' => env('MCP_AUTO_PROCESS_UPDATE', false),
    'auto_features_on_create' => ['thesaurus'], // Plus léger à la création
    'auto_features_on_update' => ['summary'], // Mise à jour du résumé seulement
    'auto_features' => ['thesaurus'], // Pour le middleware
    
    // Performance
    'queue_connection' => env('MCP_QUEUE_CONNECTION', 'database'),
    'batch_size' => env('MCP_BATCH_SIZE', 10),
    'delay_between_requests' => env('MCP_DELAY_MS', 100), // ms
    
    // Seuils de qualité
    'min_content_length' => 50, // Longueur minimum pour traiter
    'max_content_length' => 10000, // Limite pour éviter les timeouts
],
```

---

## 6. Exemples Pratiques {#exemples-pratiques}

### Contrôleur d'API pour les Fonctionnalités MCP

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Record;
use App\Services\MCP\McpManagerService;
use App\Jobs\ProcessRecordWithMcp;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class McpController extends Controller
{
    public function __construct(
        protected McpManagerService $mcpManager
    ) {}

    /**
     * Traiter un record avec les fonctionnalités MCP
     */
    public function processRecord(Request $request, Record $record): JsonResponse
    {
        $request->validate([
            'features' => 'array',
            'features.*' => Rule::in(['title', 'thesaurus', 'summary']),
            'async' => 'boolean'
        ]);

        $features = $request->get('features', ['title', 'thesaurus', 'summary']);
        $async = $request->get('async', false);

        try {
            if ($async) {
                ProcessRecordWithMcp::dispatch($record, $features);
                
                return response()->json([
                    'message' => 'Traitement en cours en arrière-plan',
                    'record_id' => $record->id,
                    'features' => $features,
                    'status' => 'queued'
                ]);
            }

            $results = $this->mcpManager->processRecord($record, $features);

            return response()->json([
                'message' => 'Traitement réussi',
                'record_id' => $record->id,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Échec du traitement MCP',
                'message' => $e->getMessage(),
                'record_id' => $record->id
            ], 500);
        }
    }

    /**
     * Traitement par lots
     */
    public function batchProcess(Request $request): JsonResponse
    {
        $request->validate([
            'record_ids' => 'required|array|max:100',
            'record_ids.*' => 'integer|exists:records,id',
            'features' => 'array',
            'features.*' => Rule::in(['title', 'thesaurus', 'summary']),
            'async' => 'boolean'
        ]);

        $recordIds = $request->get('record_ids');
        $features = $request->get('features', ['thesaurus']);
        $async = $request->get('async', true); // Par défaut asynchrone pour les lots

        try {
            if ($async) {
                foreach ($recordIds as $recordId) {
                    $record = Record::find($recordId);
                    if ($record) {
                        ProcessRecordWithMcp::dispatch($record, $features);
                    }
                }

                return response()->json([
                    'message' => 'Traitement par lots lancé',
                    'record_count' => count($recordIds),
                    'features' => $features,
                    'status' => 'queued'
                ]);
            }

            $results = $this->mcpManager->batchProcessRecords($recordIds, $features);

            return response()->json([
                'message' => 'Traitement par lots terminé',
                'summary' => [
                    'total_records' => count($recordIds),
                    'processed' => $results['processed'],
                    'errors' => $results['errors']
                ],
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Échec du traitement par lots',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reformulation de titre en temps réel
     */
    public function reformulateTitle(Request $request, Record $record): JsonResponse
    {
        try {
            $titleService = app(\App\Services\MCP\McpTitleReformulationService::class);
            $newTitle = $titleService->reformulateTitle($record);

            return response()->json([
                'original_title' => $record->getOriginal('name'),
                'new_title' => $newTitle,
                'record_id' => $record->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Échec de la reformulation',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aperçu de reformulation (sans sauvegarder)
     */
    public function previewTitleReformulation(Request $request, Record $record): JsonResponse
    {
        try {
            $titleService = app(\App\Services\MCP\McpTitleReformulationService::class);
            
            // Créer une copie temporaire pour ne pas modifier l'original
            $tempRecord = $record->replicate();
            $newTitle = $titleService->reformulateTitle($tempRecord);
            
            // Restaurer le titre original
            $record->refresh();

            return response()->json([
                'original_title' => $record->name,
                'suggested_title' => $newTitle,
                'record_id' => $record->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Échec de l\'aperçu',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Statut des traitements en cours
     */
    public function getProcessingStatus(Record $record): JsonResponse
    {
        // Vérifier les jobs en queue pour ce record
        $pendingJobs = \DB::table('jobs')
            ->where('payload', 'like', "%{$record->id}%")
            ->count();

        $recentProcessing = \DB::table('job_batches')
            ->where('name', 'like', '%MCP%')
            ->where('created_at', '>=', now()->subHour())
            ->first();

        return response()->json([
            'record_id' => $record->id,
            'pending_jobs' => $pendingJobs,
            'recent_processing' => $recentProcessing ? [
                'batch_id' => $recentProcessing->id,
                'status' => $recentProcessing->finished_at ? 'completed' : 'running',
                'progress' => $recentProcessing->total_jobs > 0 
                    ? round(($recentProcessing->total_jobs - $recentProcessing->pending_jobs) / $recentProcessing->total_jobs * 100)
                    : 0
            ] : null,
            'last_mcp_update' => $record->updated_at,
            'has_thesaurus_concepts' => $record->thesaurusConcepts()->exists()
        ]);
    }
}
```

### Interface Web avec Streaming

Créons une interface qui montre les résultats en temps réel :

```php
<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Services\MCP\McpManagerService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Cloudstudio\Ollama\Facades\Ollama;

class McpStreamingController extends Controller
{
    /**
     * Interface de streaming pour voir le traitement en temps réel
     */
    public function streamProcessing(Request $request, Record $record): Response
    {
        return response()->stream(function () use ($record) {
            // En-têtes SSE
            echo "data: " . json_encode([
                'type' => 'start',
                'message' => 'Début du traitement MCP',
                'record_id' => $record->id
            ]) . "\n\n";
            
            flush();

            try {
                // Étape 1 : Reformulation du titre
                echo "data: " . json_encode([
                    'type' => 'step',
                    'step' => 'title_reformulation',
                    'message' => 'Reformulation du titre en cours...'
                ]) . "\n\n";
                flush();

                $prompt = $this->buildTitlePrompt($record);
                
                $response = Ollama::agent('Vous êtes un archiviste expert.')
                    ->prompt($prompt)
                    ->model('llama3.1:8b')
                    ->stream(true)
                    ->ask();

                // Traiter le stream
                $buffer = '';
                $body = $response->getBody();
                
                while (!$body->eof()) {
                    $chunk = $body->read(1024);
                    $buffer .= $chunk;
                    
                    while (($pos = strpos($buffer, "\n")) !== false) {
                        $line = substr($buffer, 0, $pos);
                        $buffer = substr($buffer, $pos + 1);
                        
                        if (strpos($line, 'data: ') === 0) {
                            $jsonData = substr($line, 6);
                            $data = json_decode($jsonData, true);
                            
                            if (isset($data['response'])) {
                                echo "data: " . json_encode([
                                    'type' => 'stream',
                                    'step' => 'title_reformulation',
                                    'content' => $data['response']
                                ]) . "\n\n";
                                flush();
                            }
                        }
                    }
                }

                echo "data: " . json_encode([
                    'type' => 'complete',
                    'message' => 'Traitement MCP terminé',
                    'record_id' => $record->id
                ]) . "\n\n";
                
            } catch (\Exception $e) {
                echo "data: " . json_encode([
                    'type' => 'error',
                    'message' => $e->getMessage()
                ]) . "\n\n";
            }
            
            flush();
        }, 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no'
        ]);
    }

    private function buildTitlePrompt(Record $record): string
    {
        // Utiliser la même logique que dans le service
        $children = $record->children()->count();
        
        $prompt = "Reformulez ce titre d'archive selon les règles ISAD(G) :\n\n";
        $prompt .= "TITRE ORIGINAL : {$record->name}\n";
        $prompt .= "CONTENU : " . substr($record->content ?? '', 0, 500) . "\n";
        
        // ... reste de la logique
        
        return $prompt;
    }
}
```

### Vue Blade avec JavaScript pour le Streaming

```blade
{{-- resources/views/mcp/streaming.blade.php --}}
<div id="mcp-processing" class="p-6 bg-white rounded-lg shadow-lg">
    <div class="mb-4">
        <h2 class="text-2xl font-bold text-gray-800">Traitement MCP en Temps Réel</h2>
        <p class="text-gray-600">Record: {{ $record->name }}</p>
    </div>

    <div id="progress-container" class="mb-6">
        <div class="w-full bg-gray-200 rounded-full h-2.5">
            <div id="progress-bar" class="bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
        </div>
        <p id="progress-text" class="text-sm text-gray-600 mt-2">En attente...</p>
    </div>

    <div id="results-container" class="space-y-4">
        <!-- Les résultats seront ajoutés ici -->
    </div>

    <div id="streaming-output" class="mt-6 p-4 bg-gray-100 rounded-lg font-mono text-sm max-h-96 overflow-y-auto">
        <!-- Le contenu streamé sera affiché ici -->
    </div>

    <div class="mt-6 flex space-x-4">
        <button id="start-processing" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Démarrer le Traitement
        </button>
        <button id="stop-processing" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700" disabled>
            Arrêter
        </button>
    </div>
</div>

<script>
let eventSource = null;
let isProcessing = false;

document.getElementById('start-processing').addEventListener('click', function() {
    if (isProcessing) return;
    
    startStreaming();
});

document.getElementById('stop-processing').addEventListener('click', function() {
    stopStreaming();
});

function startStreaming() {
    isProcessing = true;
    document.getElementById('start-processing').disabled = true;
    document.getElementById('stop-processing').disabled = false;
    
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const output = document.getElementById('streaming-output');
    const resultsContainer = document.getElementById('results-container');
    
    // Réinitialiser l'affichage
    output.innerHTML = '';
    resultsContainer.innerHTML = '';
    progressBar.style.width = '0%';
    progressText.textContent = 'Connexion...';
    
    eventSource = new EventSource(`/mcp/stream/{{ $record->id }}`);
    
    let currentStep = '';
    let stepProgress = {
        'title_reformulation': 0,
        'thesaurus_indexing': 33,
        'content_summarization': 66,
        'complete': 100
    };
    
    eventSource.onmessage = function(event) {
        const data = JSON.parse(event.data);
        
        switch(data.type) {
            case 'start':
                progressText.textContent = 'Traitement démarré...';
                addToOutput('🚀 ' + data.message, 'text-blue-600');
                break;
                
            case 'step':
                currentStep = data.step;
                const progress = stepProgress[currentStep] || 0;
                progressBar.style.width = progress + '%';
                progressText.textContent = data.message;
                addToOutput('📋 ' + data.message, 'text-yellow-600');
                break;
                
            case 'stream':
                addToOutput(data.content, 'text-gray-800');
                // Faire défiler vers le bas
                output.scrollTop = output.scrollHeight;
                break;
                
            case 'result':
                addResult(data.step, data.result);
                break;
                
            case 'complete':
                progressBar.style.width = '100%';
                progressText.textContent = 'Traitement terminé !';
                addToOutput('✅ ' + data.message, 'text-green-600');
                stopStreaming();
                break;
                
            case 'error':
                addToOutput('❌ Erreur: ' + data.message, 'text-red-600');
                stopStreaming();
                break;
        }
    };
    
    eventSource.onerror = function(event) {
        addToOutput('❌ Erreur de connexion', 'text-red-600');
        stopStreaming();
    };
}

function stopStreaming() {
    if (eventSource) {
        eventSource.close();
        eventSource = null;
    }
    
    isProcessing = false;
    document.getElementById('start-processing').disabled = false;
    document.getElementById('stop-processing').disabled = true;
}

function addToOutput(text, className = '') {
    const output = document.getElementById('streaming-output');
    const div = document.createElement('div');
    div.textContent = text;
    div.className = className + ' mb-1';
    output.appendChild(div);
}

function addResult(step, result) {
    const resultsContainer = document.getElementById('results-container');
    
    let title, content, icon;
    
    switch(step) {
        case 'title_reformulation':
            title = 'Titre Reformulé';
            content = result;
            icon = '📝';
            break;
        case 'thesaurus_indexing':
            title = 'Indexation Thésaurus';
            content = `${result.concepts_found} concepts trouvés`;
            icon = '🏷️';
            break;
        case 'content_summarization':
            title = 'Résumé Généré';
            content = result.substring(0, 200) + '...';
            icon = '📄';
            break;
    }
    
    const resultDiv = document.createElement('div');
    resultDiv.className = 'p-4 border border-gray-200 rounded-lg';
    resultDiv.innerHTML = `
        <h3 class="font-semibold text-gray-800 mb-2">${icon} ${title}</h3>
        <p class="text-gray-600">${content}</p>
    `;
    
    resultsContainer.appendChild(resultDiv);
}

// Nettoyer lors de la fermeture de la page
window.addEventListener('beforeunload', function() {
    if (eventSource) {
        eventSource.close();
    }
});
</script>
```

---

## 7. Meilleures Pratiques et Sécurité {#meilleures-pratiques}

### Configuration de Sécurité

Voici les meilleures pratiques de sécurité pour Laravel avec Ollama :

#### Variables d'Environnement Sécurisées

```bash
# .env
# ⚠️ Ne jamais commiter ce fichier

# Configuration Ollama sécurisée
OLLAMA_URL=http://127.0.0.1:11434
OLLAMA_API_KEY=your-secure-api-key-if-needed

# Chiffrement des jobs
QUEUE_ENCRYPT=true

# Configuration HTTPS forcée
FORCE_HTTPS=true

# Configuration de timeout
OLLAMA_CONNECTION_TIMEOUT=300
OLLAMA_READ_TIMEOUT=600

# Limitation du taux de requêtes
MCP_RATE_LIMIT_REQUESTS=100
MCP_RATE_LIMIT_MINUTES=60
```

#### Middleware de Limitation de Débit

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class McpRateLimitMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'mcp-requests:' . $request->user()->id;
        
        $maxRequests = config('ollama.mcp.rate_limit.requests', 100);
        $decayMinutes = config('ollama.mcp.rate_limit.minutes', 60);
        
        if (RateLimiter::tooManyAttempts($key, $maxRequests)) {
            $retryAfter = RateLimiter::availableIn($key);
            
            return response()->json([
                'error' => 'Trop de requêtes MCP',
                'retry_after' => $retryAfter
            ], 429);
        }
        
        RateLimiter::hit($key, $decayMinutes * 60);
        
        $response = $next($request);
        
        $remaining = $maxRequests - RateLimiter::attempts($key);
        $response->headers->set('X-RateLimit-Remaining', $remaining);
        
        return $response;
    }
}
```

### Validation des Données

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class McpProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('process-mcp', $this->route('record'));
    }

    public function rules(): array
    {
        return [
            'features' => 'required|array|min:1|max:3',
            'features.*' => Rule::in(['title', 'thesaurus', 'summary']),
            'options' => 'array',
            'options.temperature' => 'numeric|min:0|max:1',
            'options.max_tokens' => 'integer|min:100|max:4000',
            'async' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'features.required' => 'Au moins une fonctionnalité MCP doit être sélectionnée',
            'features.*.in' => 'Fonctionnalité non valide. Utilisez: title, thesaurus, ou summary',
            'options.temperature.between' => 'La température doit être entre 0 et 1',
            'options.max_tokens.between' => 'Le nombre de tokens doit être entre 100 et 4000'
        ];
    }

    protected function prepareForValidation()
    {
        // Nettoyer et valider les données avant validation
        if ($this->has('record_content')) {
            $content = strip_tags($this->record_content);
            $content = substr($content, 0, 10000); // Limiter la taille
            $this->merge(['record_content' => $content]);
        }
    }
}
```

### Gestion d'Erreurs Robuste

```php
<?php

namespace App\Services\MCP;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Cloudstudio\Ollama\Facades\Ollama;

class McpErrorHandler
{
    public static function handleOllamaError(\Exception $e, string $context, array $data = []): void
    {
        $errorData = [
            'context' => $context,
            'error_message' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'data' => $data,
            'trace' => $e->getTraceAsString()
        ];

        // Log détaillé pour debug
        Log::error('Erreur Ollama MCP', $errorData);

        // Incrémenter compteur d'erreurs
        $errorKey = "mcp_errors:{$context}:" . date('Y-m-d-H');
        Cache::increment($errorKey, 1);
        Cache::expire($errorKey, 3600); // 1 heure

        // Alerte si trop d'erreurs
        if (Cache::get($errorKey, 0) > 10) {
            static::alertHighErrorRate($context, $errorKey);
        }

        // Nettoyer les données sensibles avant de relancer
        unset($errorData['trace']);
        
        throw new McpProcessingException(
            "Erreur MCP dans {$context}: " . $e->getMessage(),
            $e->getCode(),
            $e
        );
    }

    public static function withRetry(callable $callback, int $maxRetries = 3, int $delayMs = 1000)
    {
        $attempt = 0;
        
        while ($attempt < $maxRetries) {
            try {
                return $callback();
            } catch (\Exception $e) {
                $attempt++;
                
                Log::warning("Tentative MCP #{$attempt} échouée", [
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries,
                    'error' => $e->getMessage()
                ]);
                
                if ($attempt >= $maxRetries) {
                    throw $e;
                }
                
                // Délai exponentiel
                $delay = $delayMs * pow(2, $attempt - 1);
                usleep($delay * 1000);
            }
        }
    }

    private static function alertHighErrorRate(string $context, string $errorKey): void
    {
        $count = Cache::get($errorKey, 0);
        
        Log::critical('Taux d\'erreur MCP élevé', [
            'context' => $context,
            'error_count' => $count,
            'time_window' => '1 hour'
        ]);

        // Envoyer notification (Slack, email, etc.)
        // NotificationService::sendAlert("Taux d'erreur MCP élevé: {$count} dans {$context}");
    }
}

class McpProcessingException extends \Exception
{
    // Exception personnalisée pour les erreurs MCP
}
```

### Monitoring et Métriques

```php
<?php

namespace App\Services\MCP;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class McpMetricsService
{
    public function recordProcessing(string $feature, int $recordId, float $duration, bool $success): void
    {
        $timestamp = now();
        $date = $timestamp->format('Y-m-d');
        $hour = $timestamp->format('H');
        
        // Métriques par heure
        $metricsKey = "mcp_metrics:{$feature}:{$date}:{$hour}";
        
        $metrics = Cache::get($metricsKey, [
            'total_requests' => 0,
            'successful_requests' => 0,
            'failed_requests' => 0,
            'total_duration' => 0,
            'min_duration' => null,
            'max_duration' => null
        ]);
        
        $metrics['total_requests']++;
        $metrics['total_duration'] += $duration;
        
        if ($success) {
            $metrics['successful_requests']++;
        } else {
            $metrics['failed_requests']++;
        }
        
        if (is_null($metrics['min_duration']) || $duration < $metrics['min_duration']) {
            $metrics['min_duration'] = $duration;
        }
        
        if (is_null($metrics['max_duration']) || $duration > $metrics['max_duration']) {
            $metrics['max_duration'] = $duration;
        }
        
        Cache::put($metricsKey, $metrics, 3600 * 24); // 24h
        
        // Log pour les requêtes lentes
        if ($duration > 30) { // Plus de 30 secondes
            Log::warning('Requête MCP lente', [
                'feature' => $feature,
                'record_id' => $recordId,
                'duration' => $duration
            ]);
        }
    }
    
    public function getMetrics(string $feature, Carbon $date): array
    {
        $metrics = [];
        
        for ($hour = 0; $hour < 24; $hour++) {
            $key = "mcp_metrics:{$feature}:{$date->format('Y-m-d')}:{$hour}";
            $hourMetrics = Cache::get($key, null);
            
            if ($hourMetrics) {
                $hourMetrics['hour'] = $hour;
                $hourMetrics['average_duration'] = $hourMetrics['total_requests'] > 0 
                    ? $hourMetrics['total_duration'] / $hourMetrics['total_requests'] 
                    : 0;
                $hourMetrics['success_rate'] = $hourMetrics['total_requests'] > 0 
                    ? ($hourMetrics['successful_requests'] / $hourMetrics['total_requests']) * 100 
                    : 0;
                
                $metrics[] = $hourMetrics;
            }
        }
        
        return $metrics;
    }
    
    public function getDailyReport(): array
    {
        $features = ['title', 'thesaurus', 'summary'];
        $report = [];
        
        foreach ($features as $feature) {
            $todayMetrics = $this->getMetrics($feature, today());
            
            $totalRequests = array_sum(array_column($todayMetrics, 'total_requests'));
            $successfulRequests = array_sum(array_column($todayMetrics, 'successful_requests'));
            $avgDuration = $totalRequests > 0 
                ? array_sum(array_column($todayMetrics, 'total_duration')) / $totalRequests 
                : 0;
            
            $report[$feature] = [
                'total_requests' => $totalRequests,
                'successful_requests' => $successfulRequests,
                'success_rate' => $totalRequests > 0 ? ($successfulRequests / $totalRequests) * 100 : 0,
                'average_duration' => round($avgDuration, 2),
                'hourly_data' => $todayMetrics
            ];
        }
        
        return $report;
    }
}
```

### Test de Performance et Benchmark

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Record;
use App\Services\MCP\McpManagerService;
use App\Services\MCP\McpMetricsService;
use Illuminate\Support\Facades\DB;

class McpBenchmarkCommand extends Command
{
    protected $signature = 'mcp:benchmark 
                           {--records=10 : Nombre de records à tester}
                           {--features=title,thesaurus,summary : Fonctionnalités à benchmarker}
                           {--iterations=3 : Nombre d\'itérations par test}';
    
    protected $description = 'Benchmark des performances MCP';

    public function handle(McpManagerService $mcpManager, McpMetricsService $metricsService)
    {
        $recordCount = (int) $this->option('records');
        $features = explode(',', $this->option('features'));
        $iterations = (int) $this->option('iterations');
        
        $this->info("🚀 Benchmark MCP");
        $this->info("Records: {$recordCount}, Fonctionnalités: " . implode(', ', $features));
        $this->info("Itérations: {$iterations}");
        $this->newLine();
        
        // Sélectionner des records de test
        $testRecords = Record::inRandomOrder()
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->limit($recordCount)
            ->get();
            
        if ($testRecords->isEmpty()) {
            $this->error('Aucun record trouvé pour le test');
            return Command::FAILURE;
        }
        
        $results = [];
        
        foreach ($features as $feature) {
            $this->info("📊 Test de la fonctionnalité: {$feature}");
            $progressBar = $this->output->createProgressBar($recordCount * $iterations);
            
            $featureResults = [];
            
            foreach ($testRecords as $record) {
                for ($i = 0; $i < $iterations; $i++) {
                    $startTime = microtime(true);
                    
                    try {
                        $result = $mcpManager->processRecord($record, [$feature]);
                        $duration = microtime(true) - $startTime;
                        $success = true;
                        
                        $featureResults[] = [
                            'record_id' => $record->id,
                            'iteration' => $i + 1,
                            'duration' => $duration,
                            'success' => $success,
                            'memory_peak' => memory_get_peak_usage(true),
                        ];
                        
                        $metricsService->recordProcessing($feature, $record->id, $duration, $success);
                        
                    } catch (\Exception $e) {
                        $duration = microtime(true) - $startTime;
                        $success = false;
                        
                        $featureResults[] = [
                            'record_id' => $record->id,
                            'iteration' => $i + 1,
                            'duration' => $duration,
                            'success' => $success,
                            'error' => $e->getMessage(),
                        ];
                        
                        $metricsService->recordProcessing($feature, $record->id, $duration, $success);
                    }
                    
                    $progressBar->advance();
                }
            }
            
            $progressBar->finish();
            $this->newLine(2);
            
            // Analyser les résultats
            $successful = array_filter($featureResults, fn($r) => $r['success']);
            $failed = array_filter($featureResults, fn($r) => !$r['success']);
            
            if (!empty($successful)) {
                $durations = array_column($successful, 'duration');
                $avgDuration = array_sum($durations) / count($durations);
                $minDuration = min($durations);
                $maxDuration = max($durations);
                
                $this->info("✅ {$feature} - Résultats:");
                $this->line("   Succès: " . count($successful) . "/" . count($featureResults));
                $this->line("   Durée moyenne: " . round($avgDuration, 2) . "s");
                $this->line("   Durée min/max: " . round($minDuration, 2) . "s / " . round($maxDuration, 2) . "s");
                
                if (!empty($failed)) {
                    $this->line("   Échecs: " . count($failed));
                }
            } else {
                $this->error("❌ {$feature} - Tous les tests ont échoué");
            }
            
            $results[$feature] = $featureResults;
            $this->newLine();
        }
        
        // Rapport final
        $this->info("📈 Rapport de benchmark terminé");
        $this->info("Les métriques détaillées sont disponibles via McpMetricsService");
        
        return Command::SUCCESS;
    }
}
```

---

## 8. Tests et Débogage {#tests-debogage}

### Tests Unitaires

```bash
php artisan make:test McpTitleReformulationServiceTest --unit
php artisan make:test McpThesaurusIndexingServiceTest --unit
php artisan make:test McpContentSummarizationServiceTest --unit
```

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Record;
use App\Models\RecordLevel;
use App\Services\MCP\McpTitleReformulationService;
use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Foundation\Testing\RefreshDatabase;

class McpTitleReformulationServiceTest extends TestCase
{
    use RefreshDatabase;

    private McpTitleReformulationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(McpTitleReformulationService::class);
    }

    /** @test */
    public function it_can_reformulate_single_object_title(): void
    {
        // Mock Ollama response
        Ollama::shouldReceive('agent')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('prompt')
            ->andReturnSelf()
            ->shouldReceive('model')
            ->andReturnSelf()
            ->shouldReceive('options')
            ->andReturnSelf()
            ->shouldReceive('ask')
            ->andReturn([
                'response' => 'Personnel municipal, attribution des médailles du travail : liste des bénéficiaires. 1950-1960'
            ]);

        $record = Record::factory()->create([
            'name' => 'Liste personnel médailles',
            'content' => 'Liste des employés municipaux ayant reçu la médaille du travail',
            'date_start' => '1950',
            'date_end' => '1960'
        ]);

        $result = $this->service->reformulateTitle($record);

        $this->assertStringContains('Personnel municipal', $result);
        $this->assertStringContains('1950-1960', $result);
        
        // Vérifier que le record a été mis à jour
        $record->refresh();
        $this->assertEquals($result, $record->name);
    }

    /** @test */
    public function it_handles_ollama_connection_error(): void
    {
        // Mock Ollama connection error
        Ollama::shouldReceive('agent')
            ->once()
            ->andThrow(new \Exception('Connection refused'));

        $record = Record::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Connection refused');

        $this->service->reformulateTitle($record);
    }

    /** @test */
    public function it_applies_correct_rules_for_multiple_objects(): void
    {
        $parentRecord = Record::factory()->create();
        
        // Créer des records enfants
        Record::factory()->count(3)->create([
            'parent_id' => $parentRecord->id
        ]);

        Ollama::shouldReceive('agent')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('prompt')
            ->with(\Mockery::on(function ($prompt) {
                return str_contains($prompt, 'RÈGLE À APPLIQUER : Intitulé à trois objets ou plus');
            }))
            ->andReturnSelf()
            ->shouldReceive('model')
            ->andReturnSelf()
            ->shouldReceive('options')
            ->andReturnSelf()
            ->shouldReceive('ask')
            ->andReturn([
                'response' => 'Édifices communaux. — Mairie, reconstruction : plans (1880-1900). École, extension : devis (1920-1930). 1880-1930'
            ]);

        $parentRecord->refresh(); // Recharger pour avoir les enfants
        $result = $this->service->reformulateTitle($parentRecord);

        $this->assertStringContains('—', $result); // Point-tiret caractéristique des titres complexes
    }
}
```

### Tests d'Intégration

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Record;
use App\Models\User;
use App\Services\MCP\McpManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

class McpIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_process_record_via_api(): void
    {
        $user = User::factory()->create();
        $record = Record::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/mcp/records/{$record->id}/process", [
                'features' => ['title'],
                'async' => false
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'record_id',
                'results' => [
                    'title'
                ]
            ]);
    }

    /** @test */
    public function it_validates_api_parameters(): void
    {
        $user = User::factory()->create();
        $record = Record::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/mcp/records/{$record->id}/process", [
                'features' => ['invalid_feature']
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['features.0']);
    }

    /** @test */
    public function it_queues_async_processing(): void
    {
        Queue::fake();
        
        $user = User::factory()->create();
        $record = Record::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/mcp/records/{$record->id}/process", [
                'features' => ['title'],
                'async' => true
            ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'queued']);

        Queue::assertPushed(\App\Jobs\ProcessRecordWithMcp::class);
    }

    /** @test */
    public function it_respects_rate_limiting(): void
    {
        $user = User::factory()->create();
        $record = Record::factory()->create();

        // Simuler dépassement de limite
        for ($i = 0; $i < 105; $i++) {
            $this->actingAs($user)
                ->postJson("/api/mcp/records/{$record->id}/process", [
                    'features' => ['title'],
                    'async' => true
                ]);
        }

        // La 106e requête devrait être rejetée
        $response = $this->actingAs($user)
            ->postJson("/api/mcp/records/{$record->id}/process", [
                'features' => ['title']
            ]);

        $response->assertStatus(429);
    }
}
```

### Outils de Debug

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Cloudstudio\Ollama\Facades\Ollama;
use App\Models\Record;

class McpDebugCommand extends Command
{
    protected $signature = 'mcp:debug {record_id?} {--test-connection} {--test-model=llama3.1:8b}';
    protected $description = 'Outils de debug pour MCP';

    public function handle()
    {
        if ($this->option('test-connection')) {
            return $this->testOllamaConnection();
        }

        $recordId = $this->argument('record_id');
        if ($recordId) {
            return $this->debugRecord($recordId);
        }

        $this->info('Options disponibles:');
        $this->line('  --test-connection  : Tester la connexion Ollama');
        $this->line('  {record_id}        : Debug d\'un record spécifique');
    }

    private function testOllamaConnection(): int
    {
        $this->info('🔍 Test de connexion Ollama...');

        try {
            $startTime = microtime(true);
            
            $response = Ollama::prompt('Test de connexion')
                ->model($this->option('test-model'))
                ->options(['max_tokens' => 10])
                ->ask();

            $duration = microtime(true) - $startTime;

            $this->info("✅ Connexion réussie !");
            $this->line("   URL: " . config('ollama.url'));
            $this->line("   Modèle: " . $this->option('test-model'));
            $this->line("   Durée: " . round($duration, 2) . "s");
            $this->line("   Réponse: " . substr($response['response'], 0, 100) . "...");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Échec de connexion:");
            $this->line("   Error: " . $e->getMessage());
            $this->line("   URL configurée: " . config('ollama.url'));
            
            // Tests de diagnostic
            $this->newLine();
            $this->info("🔧 Diagnostic:");
            
            // Test de ping réseau
            $url = parse_url(config('ollama.url'));
            $host = $url['host'] ?? 'localhost';
            $port = $url['port'] ?? 11434;
            
            $this->line("   Testing connectivity to {$host}:{$port}...");
            
            $connection = @fsockopen($host, $port, $errno, $errstr, 5);
            if ($connection) {
                $this->line("   ✅ Port {$port} is reachable");
                fclose($connection);
            } else {
                $this->line("   ❌ Cannot reach {$host}:{$port} - {$errstr}");
            }

            return Command::FAILURE;
        }
    }

    private function debugRecord(int $recordId): int
    {
        $this->info("🔍 Debug Record #{$recordId}");

        $record = Record::with(['children', 'parent', 'level', 'activity', 'organisation'])
            ->find($recordId);

        if (!$record) {
            $this->error("Record #{$recordId} introuvable");
            return Command::FAILURE;
        }

        // Informations du record
        $this->info("📄 Informations du Record:");
        $this->line("   Titre: {$record->name}");
        $this->line("   Niveau: " . ($record->level?->name ?? 'Non défini'));
        $this->line("   Activité: " . ($record->activity?->name ?? 'Non définie'));
        $this->line("   Organisation: " . ($record->organisation?->name ?? 'Non définie'));
        $this->line("   Contenu: " . substr($record->content ?? '', 0, 100) . "...");
        $this->line("   Enfants: " . $record->children->count());
        $this->line("   Parent: " . ($record->parent ? "#{$record->parent->id}" : 'Aucun'));

        // Simulation des prompts MCP
        $this->newLine();
        $this->info("🤖 Simulation des Prompts MCP:");

        // Test prompt titre
        if ($this->confirm('Tester le prompt de reformulation de titre?', true)) {
            $this->testTitlePrompt($record);
        }

        // Test prompt thésaurus
        if ($this->confirm('Tester l\'extraction de mots-clés?', true)) {
            $this->testKeywordExtraction($record);
        }

        return Command::SUCCESS;
    }

    private function testTitlePrompt(Record $record): void
    {
        $this->info("📝 Test Reformulation Titre:");

        $children = $record->children()->count();
        
        $prompt = "Reformulez ce titre d'archive selon les règles ISAD(G) :\n\n";
        $prompt .= "TITRE ORIGINAL : {$record->name}\n";
        $prompt .= "CONTENU : " . substr($record->content ?? '', 0, 200) . "\n";
        $prompt .= "NOMBRE D'OBJETS : " . ($children + 1) . "\n\n";

        $this->line("Prompt généré:");
        $this->line(str_repeat('-', 60));
        $this->line($prompt);
        $this->line(str_repeat('-', 60));

        if ($this->confirm('Envoyer le prompt à Ollama?')) {
            try {
                $startTime = microtime(true);
                
                $response = Ollama::agent('Vous êtes un archiviste expert.')
                    ->prompt($prompt)
                    ->model(config('ollama.mcp.models.title_reformulation'))
                    ->options(['temperature' => 0.2, 'max_tokens' => 200])
                    ->ask();

                $duration = microtime(true) - $startTime;

                $this->info("✅ Réponse reçue en " . round($duration, 2) . "s:");
                $this->line($response['response']);

            } catch (\Exception $e) {
                $this->error("❌ Erreur: " . $e->getMessage());
            }
        }
    }

    private function testKeywordExtraction(Record $record): void
    {
        $this->info("🏷️  Test Extraction Mots-clés:");

        $fullText = "TITRE: {$record->name}\n";
        if ($record->content) $fullText .= "CONTENU: {$record->content}\n";
        if ($record->biographical_history) $fullText .= "HISTORIQUE: {$record->biographical_history}\n";

        $prompt = "Analysez ce texte et extrayez 3 mots-clés principaux:\n\n{$fullText}\n\nRETOURNEZ UNIQUEMENT LES MOTS-CLÉS SÉPARÉS PAR DES VIRGULES.";

        $this->line("Texte à analyser:");
        $this->line(str_repeat('-', 60));
        $this->line(substr($fullText, 0, 300) . "...");
        $this->line(str_repeat('-', 60));

        if ($this->confirm('Extraire les mots-clés?')) {
            try {
                $response = Ollama::agent('Vous êtes un documentaliste expert.')
                    ->prompt($prompt)
                    ->model('mistral:7b')
                    ->options(['temperature' => 0.1])
                    ->ask();

                $this->info("🏷️ Mots-clés extraits:");
                $this->line($response['response']);

            } catch (\Exception $e) {
                $this->error("❌ Erreur: " . $e->getMessage());
            }
        }
    }
}
```

---

## 9. Optimisation des Performances {#optimisation-performances}

### Configuration Optimale

```php
// config/ollama.php - Optimisations
return [
    // ... configuration de base ...
    
    'performance' => [
        // Gestion de la mémoire
        'memory_limit' => env('MCP_MEMORY_LIMIT', '512M'),
        'max_execution_time' => env('MCP_MAX_EXECUTION_TIME', 300),
        
        // Pool de connexions
        'connection_pool_size' => env('MCP_CONNECTION_POOL_SIZE', 5),
        'keep_alive_timeout' => env('MCP_KEEP_ALIVE_TIMEOUT', '10m'),
        
        // Cache
        'cache_responses' => env('MCP_CACHE_RESPONSES', true),
        'cache_ttl' => env('MCP_CACHE_TTL', 3600), // 1 heure
        
        // Optimisations modèles
        'model_preload' => env('MCP_MODEL_PRELOAD', true),
        'gpu_acceleration' => env('MCP_GPU_ACCELERATION', true),
    ],
    
    'models' => [
        'optimized' => [
            // Modèles optimisés pour la vitesse
            'title_reformulation' => env('OLLAMA_FAST_MODEL', 'mistral:7b'),
            'keyword_extraction' => env('OLLAMA_FAST_MODEL', 'mistral:7b'),
            'content_summarization' => env('OLLAMA_QUALITY_MODEL', 'llama3.1:8b'),
        ]
    ]
];
```

### Service de Cache Intelligent

```php
<?php

namespace App\Services\MCP;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use App\Models\Record;

class McpCacheService
{
    private const CACHE_PREFIX = 'mcp_cache:';
    private const DEFAULT_TTL = 3600; // 1 heure
    
    public function getCachedResult(string $feature, Record $record, array $options = []): ?array
    {
        if (!config('ollama.performance.cache_responses', true)) {
            return null;
        }
        
        $cacheKey = $this->generateCacheKey($feature, $record, $options);
        
        return Cache::get($cacheKey);
    }
    
    public function setCachedResult(string $feature, Record $record, array $result, array $options = []): void
    {
        if (!config('ollama.performance.cache_responses', true)) {
            return;
        }
        
        $cacheKey = $this->generateCacheKey($feature, $record, $options);
        $ttl = config('ollama.performance.cache_ttl', self::DEFAULT_TTL);
        
        Cache::put($cacheKey, $result, $ttl);
        
        // Ajouter aux tags pour invalidation groupée
        Cache::tags(['mcp', $feature, "record_{$record->id}"])->put($cacheKey, $result, $ttl);
    }
    
    public function invalidateRecord(Record $record): void
    {
        // Invalider tous les caches pour ce record
        Cache::tags("record_{$record->id}")->flush();
    }
    
    public function invalidateFeature(string $feature): void
    {
        // Invalider tous les caches pour cette fonctionnalité
        Cache::tags($feature)->flush();
    }
    
    public function clearAll(): void
    {
        Cache::tags(['mcp'])->flush();
    }
    
    private function generateCacheKey(string $feature, Record $record, array $options): string
    {
        $baseData = [
            'feature' => $feature,
            'record_id' => $record->id,
            'record_updated_at' => $record->updated_at->timestamp,
            'content_hash' => md5($record->name . $record->content . $record->biographical_history),
            'children_count' => $record->children()->count(),
            'options' => $options
        ];
        
        $hash = md5(json_encode($baseData));
        
        return self::CACHE_PREFIX . "{$feature}:{$record->id}:{$hash}";
    }
    
    public function getStats(): array
    {
        $features = ['title', 'thesaurus', 'summary'];
        $stats = [];
        
        foreach ($features as $feature) {
            $pattern = self::CACHE_PREFIX . $feature . ':*';
            $keys = Cache::getRedis()->keys($pattern);
            
            $stats[$feature] = [
                'cached_entries' => count($keys),
                'memory_usage' => $this->estimateMemoryUsage($keys)
            ];
        }
        
        return $stats;
    }
    
    private function estimateMemoryUsage(array $keys): string
    {
        $totalSize = 0;
        
        foreach (array_slice($keys, 0, 10) as $key) { // Échantillon
            $value = Cache::get($key);
            if ($value) {
                $totalSize += strlen(serialize($value));
            }
        }
        
        $avgSize = count($keys) > 0 ? $totalSize / min(count($keys), 10) : 0;
        $estimatedTotal = $avgSize * count($keys);
        
        return $this->formatBytes($estimatedTotal);
    }
    
    private function formatBytes(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }
}
```

### Optimisation des Services MCP

Mise à jour des services pour intégrer le cache :

```php
<?php

namespace App\Services\MCP;

use Cloudstudio\Ollama\Facades\Ollama;
use App\Models\Record;
use App\Services\MCP\McpCacheService;
use Illuminate\Support\Facades\Log;

class OptimizedMcpTitleReformulationService
{
    public function __construct(
        protected McpCacheService $cacheService
    ) {}

    public function reformulateTitle(Record $record, array $options = []): string
    {
        // Vérifier le cache d'abord
        $cached = $this->cacheService->getCachedResult('title', $record, $options);
        if ($cached) {
            Log::info('Titre reformulé depuis cache', ['record_id' => $record->id]);
            return $cached['title'];
        }

        $startTime = microtime(true);
        
        try {
            $prompt = $this->buildTitlePrompt($record);
            
            // Optimisations des paramètres Ollama
            $optimizedOptions = array_merge([
                'temperature' => 0.1,  // Plus déterministe
                'top_p' => 0.9,
                'max_tokens' => 200,   // Limiter pour les titres
                'stop' => ['\n\n', 'Explication:', 'Note:'] // Arrêter sur ces mots
            ], $options);

            $response = Ollama::agent('Vous êtes un archiviste expert spécialisé dans les règles ISAD(G).')
                ->prompt($prompt)
                ->model(config('ollama.models.optimized.title_reformulation'))
                ->options($optimizedOptions)
                ->ask();

            $reformulatedTitle = $this->extractTitle($response['response']);
            
            // Mettre à jour le record
            $record->update(['name' => $reformulatedTitle]);
            
            $duration = microtime(true) - $startTime;
            
            // Mettre en cache
            $result = ['title' => $reformulatedTitle, 'duration' => $duration];
            $this->cacheService->setCachedResult('title', $record, $result, $options);
            
            Log::info('Titre reformulé avec succès', [
                'record_id' => $record->id,
                'duration' => round($duration, 2)
            ]);
            
            return $reformulatedTitle;
            
        } catch (\Exception $e) {
            Log::error('Erreur reformulation titre optimisée', [
                'record_id' => $record->id,
                'error' => $e->getMessage(),
                'duration' => microtime(true) - $startTime
            ]);
            throw $e;
        }
    }

    private function buildTitlePrompt(Record $record): string
    {
        // Version optimisée du prompt - plus concise
        $children = $record->children()->count();
        
        $prompt = "Reformulez selon ISAD(G):\n";
        $prompt .= "TITRE: {$record->name}\n";
        $prompt .= "DATES: {$record->date_start}-{$record->date_end}\n";
        
        if ($children === 0) {
            $prompt .= "FORMAT: Objet, action : typologie. Dates\n";
        } elseif ($children === 1) {
            $prompt .= "FORMAT: Objet, action (dates) ; autre action (dates). Dates\n";
        } else {
            $prompt .= "FORMAT: Objet principal. — Détails : types (dates). Dates\n";
        }
        
        $prompt .= "RÉPONSE (titre seulement):";
        
        return $prompt;
    }

    private function extractTitle(string $response): string
    {
        // Nettoyage optimisé
        $title = trim($response);
        $title = preg_replace('/^(Titre reformulé|Nouveau titre)\s*:\s*/i', '', $title);
        $title = explode("\n", $title)[0]; // Prendre seulement la première ligne
        
        return $title;
    }
}
```

### Pool de Connexions Ollama

```php
<?php

namespace App\Services\MCP;

use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Support\Facades\Log;

class OllamaConnectionPool
{
    private array $connections = [];
    private int $maxConnections;
    private array $activeConnections = [];

    public function __construct()
    {
        $this->maxConnections = config('ollama.performance.connection_pool_size', 5);
    }

    public function getConnection(string $model = null): \Cloudstudio\Ollama\Ollama
    {
        $connectionId = $this->findAvailableConnection($model);
        
        if ($connectionId === null) {
            $connectionId = $this->createConnection($model);
        }
        
        $this->activeConnections[$connectionId] = time();
        
        return $this->connections[$connectionId];
    }

    public function releaseConnection(string $connectionId): void
    {
        unset($this->activeConnections[$connectionId]);
        
        // Garder la connexion active avec keep-alive
        if (isset($this->connections[$connectionId])) {
            $this->connections[$connectionId]->keepAlive(
                config('ollama.performance.keep_alive_timeout', '10m')
            );
        }
    }

    private function findAvailableConnection(string $model = null): ?string
    {
        foreach ($this->connections as $id => $connection) {
            if (!isset($this->activeConnections[$id])) {
                return $id;
            }
        }
        
        return null;
    }

    private function createConnection(string $model = null): string
    {
        if (count($this->connections) >= $this->maxConnections) {
            // Libérer la plus ancienne connexion
            $oldestId = array_keys($this->activeConnections, min($this->activeConnections))[0];
            $this->closeConnection($oldestId);
        }
        
        $connectionId = uniqid('ollama_', true);
        
        $connection = Ollama::model($model ?? config('ollama.model'))
            ->keepAlive(config('ollama.performance.keep_alive_timeout', '10m'));
            
        $this->connections[$connectionId] = $connection;
        
        Log::debug('Nouvelle connexion Ollama créée', ['connection_id' => $connectionId]);
        
        return $connectionId;
    }

    private function closeConnection(string $connectionId): void
    {
        unset($this->connections[$connectionId]);
        unset($this->activeConnections[$connectionId]);
        
        Log::debug('Connexion Ollama fermée', ['connection_id' => $connectionId]);
    }

    public function closeAllConnections(): void
    {
        foreach (array_keys($this->connections) as $connectionId) {
            $this->closeConnection($connectionId);
        }
    }

    public function getStats(): array
    {
        return [
            'total_connections' => count($this->connections),
            'active_connections' => count($this->activeConnections),
            'max_connections' => $this->maxConnections,
            'connection_ids' => array_keys($this->connections)
        ];
    }
}
```

### Traitement par Lots Optimisé

```php
<?php

namespace App\Services\MCP;

use App\Models\Record;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OptimizedMcpBatchService
{
    public function __construct(
        protected McpCacheService $cacheService,
        protected OllamaConnectionPool $connectionPool
    ) {}

    public function batchProcessRecords(
        Collection $records, 
        array $features, 
        int $batchSize = null
    ): array {
        $batchSize = $batchSize ?? config('ollama.performance.batch_size', 10);
        $results = [];
        $errors = [];
        
        // Préfiltrer les records déjà en cache
        $needsProcessing = $this->filterCachedRecords($records, $features);
        
        Log::info('Traitement par lots MCP', [
            'total_records' => $records->count(),
            'needs_processing' => $needsProcessing->count(),
            'cached' => $records->count() - $needsProcessing->count(),
            'batch_size' => $batchSize
        ]);
        
        // Traiter par petits lots pour éviter les timeouts
        foreach ($needsProcessing->chunk($batchSize) as $batch) {
            $batchResults = $this->processBatch($batch, $features);
            $results = array_merge($results, $batchResults['results']);
            $errors = array_merge($errors, $batchResults['errors']);
            
            // Délai entre les lots pour éviter la surcharge
            $delay = config('ollama.performance.delay_between_requests', 100);
            if ($delay > 0) {
                usleep($delay * 1000); // Convertir ms en µs
            }
        }
        
        // Ajouter les résultats en cache
        foreach ($records as $record) {
            if (!$needsProcessing->contains('id', $record->id)) {
                foreach ($features as $feature) {
                    $cached = $this->cacheService->getCachedResult($feature, $record);
                    if ($cached) {
                        $results[$record->id][$feature] = $cached;
                    }
                }
            }
        }
        
        return [
            'total_processed' => count($results),
            'total_errors' => count($errors),
            'results' => $results,
            'errors' => $errors,
            'performance' => [
                'cache_hits' => $records->count() - $needsProcessing->count(),
                'ollama_requests' => $needsProcessing->count() * count($features),
                'batch_count' => $needsProcessing->chunk($batchSize)->count()
            ]
        ];
    }

    private function filterCachedRecords(Collection $records, array $features): Collection
    {
        return $records->filter(function ($record) use ($features) {
            foreach ($features as $feature) {
                if (!$this->cacheService->getCachedResult($feature, $record)) {
                    return true; // Au moins une fonctionnalité n'est pas en cache
                }
            }
            return false; // Tout est en cache
        });
    }

    private function processBatch(Collection $batch, array $features): array
    {
        $results = [];
        $errors = [];
        
        // Utiliser une transaction pour la cohérence
        DB::beginTransaction();
        
        try {
            foreach ($batch as $record) {
                $recordResults = [];
                
                foreach ($features as $feature) {
                    try {
                        $result = $this->processFeature($record, $feature);
                        $recordResults[$feature] = $result;
                        
                        // Cache immédiatement
                        $this->cacheService->setCachedResult($feature, $record, $result);
                        
                    } catch (\Exception $e) {
                        $errors[$record->id][$feature] = $e->getMessage();
                        Log::warning("Échec {$feature} pour record {$record->id}", [
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                if (!empty($recordResults)) {
                    $results[$record->id] = $recordResults;
                }
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Échec du batch MCP', ['error' => $e->getMessage()]);
            throw $e;
        }
        
        return ['results' => $results, 'errors' => $errors];
    }

    private function processFeature(Record $record, string $feature): array
    {
        $connection = $this->connectionPool->getConnection();
        
        try {
            $startTime = microtime(true);
            
            switch ($feature) {
                case 'title':
                    $service = app(OptimizedMcpTitleReformulationService::class);
                    $result = ['title' => $service->reformulateTitle($record)];
                    break;
                    
                case 'thesaurus':
                    $service = app(McpThesaurusIndexingService::class);
                    $result = $service->indexRecord($record);
                    break;
                    
                case 'summary':
                    $service = app(McpContentSummarizationService::class);
                    $result = ['summary' => $service->generateSummary($record)];
                    break;
                    
                default:
                    throw new \InvalidArgumentException("Fonctionnalité inconnue: {$feature}");
            }
            
            $result['duration'] = microtime(true) - $startTime;
            $result['processed_at'] = now()->toISOString();
            
            return $result;
            
        } finally {
            $this->connectionPool->releaseConnection(spl_object_hash($connection));
        }
    }
}
```

---

## 10. Déploiement en Production {#deploiement-production}

### Configuration Docker pour Production

#### Dockerfile optimisé

```dockerfile
# Dockerfile pour Laravel avec Ollama
FROM php:8.2-fpm-alpine

# Installation des dépendances système
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Création de l'utilisateur pour l'application
RUN adduser -D -s /bin/sh -u 1001 appuser

# Répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application
COPY . .
COPY .env.production .env

# Installation des dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Permissions
RUN chown -R appuser:appuser /var/www/html \
    && chmod -R 755 storage bootstrap/cache

# Configuration Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Configuration Supervisor
COPY docker/supervisord.conf /etc/supervisord.conf

# Scripts de démarrage
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Variables d'environnement
ENV PHP_MEMORY_LIMIT=512M
ENV PHP_MAX_EXECUTION_TIME=300

EXPOSE 80

USER appuser

CMD ["/start.sh"]
```

#### docker-compose.yml pour production

```yaml
version: '3.8'

services:
  app:
    build: 
      context: .
      dockerfile: Dockerfile
    container_name: laravel_mcp_app
    restart: unless-stopped
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - QUEUE_CONNECTION=redis
      - CACHE_DRIVER=redis
      - SESSION_DRIVER=redis
    volumes:
      - ./storage:/var/www/html/storage
      - ./bootstrap/cache:/var/www/html/bootstrap/cache
    networks:
      - app-network
    depends_on:
      - mysql
      - redis
      - ollama

  ollama:
    image: ollama/ollama:latest
    container_name: ollama_service
    restart: unless-stopped
    volumes:
      - ollama_data:/root/.ollama
    environment:
      - OLLAMA_KEEP_ALIVE=24h
      - OLLAMA_HOST=0.0.0.0:11434
    ports:
      - "11434:11434"
    networks:
      - app-network
    deploy:
      resources:
        reservations:
          devices:
            - driver: nvidia
              count: 1
              capabilities: [gpu]

  mysql:
    image: mysql:8.0
    container_name: mysql_db
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - mysql_data:/var/lib/mysql
      - ./docker/mysql/init.sql:/docker-entrypoint-initdb.d/init.sql
    networks:
      - app-network

  redis:
    image: redis:7-alpine
    container_name: redis_cache
    restart: unless-stopped
    command: redis-server --appendonly yes --maxmemory 512mb --maxmemory-policy allkeys-lru
    volumes:
      - redis_data:/data
    networks:
      - app-network

  queue-worker:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: laravel_queue_worker
    restart: unless-stopped
    command: php artisan queue:work --sleep=3 --tries=3 --max-time=3600
    environment:
      - CONTAINER_ROLE=queue
    volumes:
      - ./storage:/var/www/html/storage
    networks:
      - app-network
    depends_on:
      - mysql
      - redis

  scheduler:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: laravel_scheduler
    restart: unless-stopped
    command: sh -c "while true; do php artisan schedule:run; sleep 60; done"
    environment:
      - CONTAINER_ROLE=scheduler
    volumes:
      - ./storage:/var/www/html/storage
    networks:
      - app-network
    depends_on:
      - mysql
      - redis

  nginx:
    image: nginx:alpine
    container_name: nginx_proxy
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf
      - ./docker/nginx/ssl:/etc/nginx/ssl
      - ./public:/var/www/html/public
    networks:
      - app-network
    depends_on:
      - app

networks:
  app-network:
    driver: bridge

volumes:
  mysql_data:
    driver: local
  redis_data:
    driver: local
  ollama_data:
    driver: local
```

### Configuration de Production

#### .env.production

```env
APP_NAME="Archive MCP System"
APP_ENV=production
APP_KEY=base64:VOTRE_CLE_APP_GENEREE_SECURISEE
APP_DEBUG=false
APP_URL=https://votre-domaine.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Base de données
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=archivage_mcp
DB_USERNAME=archive_user
DB_PASSWORD=mot_de_passe_ultra_securise

# Cache et Sessions
BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Ollama Production
OLLAMA_MODEL=llama3.1:8b
OLLAMA_URL=http://ollama:11434
OLLAMA_CONNECTION_TIMEOUT=300

# MCP Production
MCP_AUTO_PROCESS_CREATE=true
MCP_AUTO_PROCESS_UPDATE=false
MCP_CACHE_RESPONSES=true
MCP_CACHE_TTL=7200
MCP_BATCH_SIZE=5
MCP_DELAY_MS=200
MCP_MEMORY_LIMIT=1024M
MCP_MAX_EXECUTION_TIME=600

# Sécurité
FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

# Monitoring
LOG_SLACK_WEBHOOK_URL=your_slack_webhook_url
SENTRY_LARAVEL_DSN=your_sentry_dsn

# Performance
OCTANE_SERVER=swoole
```

### Scripts de Déploiement

#### deploy.sh

```bash
#!/bin/bash

# Script de déploiement automatisé
set -e

echo "🚀 Déploiement Archive MCP System"

# Variables
APP_DIR="/var/www/archive-mcp"
BACKUP_DIR="/backups/archive-mcp"
DATE=$(date +%Y%m%d_%H%M%S)

# Créer sauvegarde
echo "📦 Création de la sauvegarde..."
mkdir -p $BACKUP_DIR
tar -czf $BACKUP_DIR/backup_$DATE.tar.gz -C $APP_DIR .

# Mise à jour du code
echo "📥 Mise à jour du code..."
cd $APP_DIR
git fetch origin
git reset --hard origin/main

# Installation des dépendances
echo "📚 Installation des dépendances..."
composer install --no-dev --optimize-autoloader --no-interaction

# Mise à jour de la base de données
echo "🗄️ Mise à jour base de données..."
php artisan migrate --force

# Optimisations Laravel
echo "⚡ Optimisations Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart

# Vérification des modèles Ollama
echo "🤖 Vérification modèles Ollama..."
php artisan mcp:debug --test-connection

# Test de l'application
echo "🧪 Tests de production..."
php artisan mcp:benchmark --records=3 --features=title --iterations=1

# Redémarrage des services
echo "🔄 Redémarrage des services..."
docker-compose restart queue-worker scheduler

echo "✅ Déploiement terminé avec succès!"

# Nettoyage des anciennes sauvegardes (garder 7 jours)
find $BACKUP_DIR -name "backup_*.tar.gz" -mtime +7 -delete

echo "📈 Vérifiez les logs avec: docker-compose logs -f app"
```

#### setup-production.sh

```bash
#!/bin/bash

# Script d'installation initiale en production
set -e

echo "🔧 Configuration initiale Archive MCP System"

# Vérification des prérequis
echo "✅ Vérification des prérequis..."

# Docker
if ! command -v docker &> /dev/null; then
    echo "❌ Docker n'est pas installé"
    exit 1
fi

# Docker Compose
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose n'est pas installé"
    exit 1
fi

# Nvidia Docker (pour GPU)
if command -v nvidia-smi &> /dev/null; then
    echo "🎮 GPU détecté, installation nvidia-docker..."
    if ! docker run --rm --gpus all nvidia/cuda:11.0-base nvidia-smi; then
        echo "⚠️ Nvidia Docker non configuré, GPU non utilisé"
    fi
fi

# Configuration des répertoires
echo "📁 Configuration des répertoires..."
mkdir -p /var/www/archive-mcp/{storage,bootstrap/cache}
mkdir -p /backups/archive-mcp
mkdir -p /logs/archive-mcp

# Permissions
sudo chown -R www-data:www-data /var/www/archive-mcp
sudo chmod -R 755 /var/www/archive-mcp

# Configuration SSL (Let's Encrypt)
echo "🔒 Configuration SSL..."
if [ "$1" != "--skip-ssl" ]; then
    sudo apt-get update
    sudo apt-get install -y certbot
    
    read -p "Nom de domaine: " DOMAIN
    
    # Obtenir certificat SSL
    sudo certbot certonly --standalone -d $DOMAIN
    
    # Copier les certificats
    sudo cp /etc/letsencrypt/live/$DOMAIN/fullchain.pem ./docker/nginx/ssl/
    sudo cp /etc/letsencrypt/live/$DOMAIN/privkey.pem ./docker/nginx/ssl/
fi

# Génération de clés de chiffrement
echo "🔑 Génération des clés..."
php artisan key:generate --force

# Construction et démarrage des conteneurs
echo "🏗️ Construction des conteneurs..."
docker-compose build --no-cache

echo "🚀 Démarrage des services..."
docker-compose up -d

# Attendre que les services soient prêts
echo "⏳ Attente des services..."
sleep 30

# Installation et téléchargement des modèles Ollama
echo "🤖 Installation des modèles Ollama..."
docker-compose exec ollama ollama pull llama3.1:8b
docker-compose exec ollama ollama pull mistral:7b
docker-compose exec ollama ollama pull nomic-embed-text

# Migration de base de données
echo "🗄️ Migration base de données..."
docker-compose exec app php artisan migrate --force

# Création d'un utilisateur admin
echo "👤 Création utilisateur admin..."
docker-compose exec app php artisan make:user-admin

# Test final
echo "🧪 Tests finaux..."
docker-compose exec app php artisan mcp:debug --test-connection

echo "✅ Installation terminée!"
echo "📊 Interface admin: https://$DOMAIN/admin"
echo "📈 Monitoring: docker-compose logs -f"
echo "🔧 Commandes utiles:"
echo "  - docker-compose logs -f app"
echo "  - docker-compose exec app php artisan queue:work"
echo "  - docker-compose exec app php artisan mcp:benchmark"
```

### Monitoring et Alertes

#### Service de Monitoring

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ProductionMonitoringService
{
    public function checkSystemHealth(): array
    {
        $health = [
            'timestamp' => now()->toISOString(),
            'status' => 'healthy',
            'checks' => []
        ];

        // Vérifier la base de données
        $health['checks']['database'] = $this->checkDatabase();
        
        // Vérifier Redis
        $health['checks']['redis'] = $this->checkRedis();
        
        // Vérifier Ollama
        $health['checks']['ollama'] = $this->checkOllama();
        
        // Vérifier la queue
        $health['checks']['queue'] = $this->checkQueue();
        
        // Vérifier l'espace disque
        $health['checks']['disk'] = $this->checkDiskSpace();
        
        // Vérifier la mémoire
        $health['checks']['memory'] = $this->checkMemory();

        // Déterminer l'état global
        foreach ($health['checks'] as $check) {
            if ($check['status'] !== 'ok') {
                $health['status'] = 'degraded';
                break;
            }
        }

        // Alertes si nécessaire
        if ($health['status'] !== 'healthy') {
            $this->sendAlert($health);
        }

        return $health;
    }

    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $duration = microtime(true) - $start;
            
            return [
                'status' => 'ok',
                'response_time' => round($duration * 1000, 2) . 'ms'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function checkRedis(): array
    {
        try {
            $start = microtime(true);
            Cache::store('redis')->put('health_check', 'ok', 10);
            $result = Cache::store('redis')->get('health_check');
            $duration = microtime(true) - $start;
            
            return [
                'status' => $result === 'ok' ? 'ok' : 'error',
                'response_time' => round($duration * 1000, 2) . 'ms'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function checkOllama(): array
    {
        try {
            $start = microtime(true);
            
            $response = Http::timeout(10)->get(config('ollama.url') . '/api/tags');
            
            $duration = microtime(true) - $start;
            
            if ($response->successful()) {
                $models = $response->json('models', []);
                return [
                    'status' => 'ok',
                    'response_time' => round($duration * 1000, 2) . 'ms',
                    'models_count' => count($models),
                    'models' => array_column($models, 'name')
                ];
            }
            
            return [
                'status' => 'error',
                'message' => 'HTTP ' . $response->status()
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function checkQueue(): array
    {
        try {
            $pendingJobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->where('failed_at', '>=', now()->subHour())->count();
            
            $status = 'ok';
            if ($pendingJobs > 100) {
                $status = 'warning';
            }
            if ($failedJobs > 10) {
                $status = 'error';
            }
            
            return [
                'status' => $status,
                'pending_jobs' => $pendingJobs,
                'failed_jobs_last_hour' => $failedJobs
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function checkDiskSpace(): array
    {
        $path = storage_path();
        $totalSpace = disk_total_space($path);
        $freeSpace = disk_free_space($path);
        $usedPercentage = (($totalSpace - $freeSpace) / $totalSpace) * 100;
        
        $status = 'ok';
        if ($usedPercentage > 85) {
            $status = 'warning';
        }
        if ($usedPercentage > 95) {
            $status = 'error';
        }
        
        return [
            'status' => $status,
            'used_percentage' => round($usedPercentage, 2),
            'free_space' => $this->formatBytes($freeSpace),
            'total_space' => $this->formatBytes($totalSpace)
        ];
    }

    private function checkMemory(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->parseBytes(ini_get('memory_limit'));
        $usedPercentage = ($memoryUsage / $memoryLimit) * 100;
        
        $status = 'ok';
        if ($usedPercentage > 80) {
            $status = 'warning';
        }
        if ($usedPercentage > 90) {
            $status = 'error';
        }
        
        return [
            'status' => $status,
            'used_percentage' => round($usedPercentage, 2),
            'used_memory' => $this->formatBytes($memoryUsage),
            'memory_limit' => $this->formatBytes($memoryLimit)
        ];
    }

    private function sendAlert(array $health): void
    {
        $message = "🚨 Alerte Archive MCP System\n\n";
        $message .= "Status: " . $health['status'] . "\n";
        $message .= "Time: " . $health['timestamp'] . "\n\n";
        
        foreach ($health['checks'] as $checkName => $checkResult) {
            if ($checkResult['status'] !== 'ok') {
                $message .= "❌ {$checkName}: {$checkResult['status']}\n";
                if (isset($checkResult['message'])) {
                    $message .= "   Error: {$checkResult['message']}\n";
                }
            }
        }

        // Envoyer vers Slack
        if ($webhookUrl = config('services.slack.webhook_url')) {
            Http::post($webhookUrl, [
                'text' => $message,
                'channel' => '#alerts',
                'username' => 'Archive MCP Monitor'
            ]);
        }

        // Log l'alerte
        Log::critical('System health alert', $health);
    }

    private function formatBytes(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }

    private function parseBytes(string $val): int
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        $val = (int) $val;
        
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        
        return $val;
    }
}
```

#### Commande de Monitoring

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProductionMonitoringService;

class SystemHealthCheckCommand extends Command
{
    protected $signature = 'system:health-check {--alert : Envoyer alerte si problème}';
    protected $description = 'Vérification de santé du système';

    public function handle(ProductionMonitoringService $monitor)
    {
        $this->info('🔍 Vérification de santé du système...');
        
        $health = $monitor->checkSystemHealth();
        
        $this->newLine();
        $this->info('📊 Résultats:');
        
        // Status général
        $statusIcon = match($health['status']) {
            'healthy' => '✅',
            'degraded' => '⚠️',
            'error' => '❌',
            default => '❓'
        };
        
        $this->line("{$statusIcon} Status global: {$health['status']}");
        $this->newLine();
        
        // Détails par check
        foreach ($health['checks'] as $checkName => $check) {
            $icon = match($check['status']) {
                'ok' => '✅',
                'warning' => '⚠️',
                'error' => '❌',
                default => '❓'
            };
            
            $this->line("{$icon} " . ucfirst($checkName) . ": {$check['status']}");
            
            if (isset($check['response_time'])) {
                $this->line("   Response time: {$check['response_time']}");
            }
            
            if (isset($check['message'])) {
                $this->line("   Message: {$check['message']}");
            }
            
            if (isset($check['models_count'])) {
                $this->line("   Models: {$check['models_count']}");
            }
            
            if (isset($check['pending_jobs'])) {
                $this->line("   Pending jobs: {$check['pending_jobs']}");
            }
            
            if (isset($check['used_percentage'])) {
                $this->line("   Used: {$check['used_percentage']}%");
            }
        }
        
        if ($health['status'] !== 'healthy' && $this->option('alert')) {
            $this->warn('🚨 Des alertes ont été envoyées');
        }
        
        return $health['status'] === 'healthy' ? Command::SUCCESS : Command::FAILURE;
    }
}
```

### Maintenance et Sauvegardes

#### Script de Sauvegarde Automatique

```bash
#!/bin/bash

# backup-system.sh - Sauvegarde complète du système
set -e

BACKUP_DIR="/backups/archive-mcp"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

echo "🔄 Démarrage sauvegarde système - $DATE"

# Créer répertoire de sauvegarde
mkdir -p $BACKUP_DIR/$DATE

# Sauvegarde base de données
echo "💾 Sauvegarde base de données..."
docker-compose exec -T mysql mysqldump \
    -u ${DB_USERNAME} \
    -p${DB_PASSWORD} \
    ${DB_DATABASE} \
    > $BACKUP_DIR/$DATE/database.sql

# Sauvegarde fichiers storage
echo "📁 Sauvegarde fichiers..."
tar -czf $BACKUP_DIR/$DATE/storage.tar.gz \
    -C /var/www/archive-mcp storage/

# Sauvegarde configuration
echo "⚙️ Sauvegarde configuration..."
cp /var/www/archive-mcp/.env $BACKUP_DIR/$DATE/
cp -r /var/www/archive-mcp/docker/ $BACKUP_DIR/$DATE/

# Sauvegarde modèles Ollama
echo "🤖 Sauvegarde modèles Ollama..."
docker run --rm \
    -v archive-mcp_ollama_data:/source \
    -v $BACKUP_DIR/$DATE:/backup \
    alpine \
    tar -czf /backup/ollama_models.tar.gz -C /source .

# Vérification de l'intégrité
echo "🔍 Vérification intégrité..."
if [ -f "$BACKUP_DIR/$DATE/database.sql" ] && 
   [ -f "$BACKUP_DIR/$DATE/storage.tar.gz" ] && 
   [ -f "$BACKUP_DIR/$DATE/ollama_models.tar.gz" ]; then
    echo "✅ Sauvegarde complète réussie"
    
    # Créer fichier de métadonnées
    cat > $BACKUP_DIR/$DATE/backup_info.json << EOF
{
    "date": "$DATE",
    "type": "full_backup",
    "files": {
        "database": "database.sql",
        "storage": "storage.tar.gz",
        "ollama_models": "ollama_models.tar.gz",
        "config": ".env"
    },
    "size": "$(du -sh $BACKUP_DIR/$DATE | cut -f1)"
}
EOF

else
    echo "❌ Erreur: Sauvegarde incomplète"
    exit 1
fi

# Nettoyage anciennes sauvegardes
echo "🧹 Nettoyage anciennes sauvegardes..."
find $BACKUP_DIR -maxdepth 1 -type d -mtime +$RETENTION_DAYS -exec rm -rf {} \;

echo "✅ Sauvegarde terminée: $BACKUP_DIR/$DATE"
```

#### Crontab pour Maintenance

```bash
# /etc/cron.d/archive-mcp-maintenance

# Sauvegarde quotidienne à 2h du matin
0 2 * * * root /opt/archive-mcp/scripts/backup-system.sh >> /var/log/backup.log 2>&1

# Vérification santé système toutes les 5 minutes
*/5 * * * * root cd /var/www/archive-mcp && docker-compose exec -T app php artisan system:health-check --alert >> /var/log/health-check.log 2>&1

# Nettoyage logs hebdomadaire
0 3 * * 0 root find /var/log -name "*.log" -mtime +7 -delete

# Optimisation base de données mensuelle
0 4 1 * * root cd /var/www/archive-mcp && docker-compose exec -T app php artisan db:optimize

# Mise à jour cache métriques
0 */6 * * * root cd /var/www/archive-mcp && docker-compose exec -T app php artisan mcp:update-metrics

# Redémarrage périodique des workers (pour éviter les fuites mémoire)
0 6 * * * root cd /var/www/archive-mcp && docker-compose restart queue-worker
```

---

## Conclusion

Cette documentation complète vous guide dans l'intégration d'Ollama avec Laravel 11 pour implémenter vos trois fonctionnalités MCP :

1. **Reformulation des titres** selon les règles ISAD(G)
2. **Indexation automatique** via thésaurus
3. **Génération de résumés** conformes aux standards archivistiques

### Points Clés à Retenir

- **Sécurité** : Chiffrement des communications, validation des données, limitation des taux
- **Performance** : Cache intelligent, pool de connexions, traitement par lots
- **Monitoring** : Surveillance continue, alertes automatiques, métriques détaillées
- **Scalabilité** : Architecture modulaire, queues asynchrones, configuration flexible

### Prochaines Étapes

1. Installer et configurer Ollama avec les modèles recommandés
2. Intégrer les services MCP dans votre application existante
3. Tester avec un échantillon de vos données d'archives
4. Déployer progressivement en production avec monitoring
5. Affiner les prompts selon vos besoins spécifiques

Cette solution vous permettra de traiter automatiquement vos archives tout en gardant le contrôle total sur vos données et processus.

---

*Documentation créée pour Laravel 11 avec Ollama - Version 1.0*
*Dernière mise à jour : Janvier 2025*
    