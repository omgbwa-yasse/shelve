<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class MistralTestController extends Controller
{
    protected $mockMode;

    public function __construct()
    {
        // Vérifier si le package Mistral est disponible
        $this->mockMode = !class_exists('HelgeSverre\Mistral\Mistral') || !config('mistral.api_key');
        
        if ($this->mockMode) {
            Log::info('MistralTestController: Mode simulation activé (package ou clé API manquant)');
        }
    }

    /**
     * Reformuler le titre d'un record avec Mistral
     */
    public function reformulateTitle(Request $request, Record $record): JsonResponse
    {
        try {
            if ($this->mockMode) {
                $suggestedTitle = $this->generateMockTitle($record);
                $tokensUsed = rand(50, 150);
            } else {
                $response = $this->callMistralAPI('title', $record);
                $suggestedTitle = $response['content'];
                $tokensUsed = $response['tokens'];
            }

            // Sauvegarder le nouveau titre
            $originalTitle = $record->name;
            $record->update(['name' => $suggestedTitle]);

            return response()->json([
                'message' => 'Titre reformulé avec succès' . ($this->mockMode ? ' (simulation)' : ''),
                'record_id' => $record->id,
                'original_title' => $originalTitle,
                'new_title' => $suggestedTitle,
                'tokens_used' => $tokensUsed,
                'mock_mode' => $this->mockMode
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur reformulation titre Mistral', [
                'record_id' => $record->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Erreur lors de la reformulation du titre',
                'message' => $e->getMessage(),
                'record_id' => $record->id
            ], 500);
        }
    }

    /**
     * Prévisualiser la reformulation du titre
     */
    public function previewTitleReformulation(Request $request, Record $record): JsonResponse
    {
        try {
            if ($this->mockMode) {
                $suggestedTitle = $this->generateMockTitle($record);
                $tokensUsed = rand(50, 150);
            } else {
                $response = $this->callMistralAPI('title', $record);
                $suggestedTitle = $response['content'];
                $tokensUsed = $response['tokens'];
            }

            return response()->json([
                'message' => 'Prévisualisation générée' . ($this->mockMode ? ' (simulation)' : ''),
                'record_id' => $record->id,
                'preview' => [
                    'original_title' => $record->name,
                    'suggested_title' => $suggestedTitle,
                    'confidence' => rand(75, 95) / 100
                ],
                'tokens_used' => $tokensUsed,
                'mock_mode' => $this->mockMode
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la prévisualisation',
                'message' => $e->getMessage(),
                'record_id' => $record->id
            ], 500);
        }
    }

    /**
     * Indexation thésaurus avec Mistral
     */
    public function indexWithThesaurus(Request $request, Record $record): JsonResponse
    {
        try {
            if ($this->mockMode) {
                $concepts = $this->generateMockConcepts($record);
                $tokensUsed = rand(100, 300);
                $suggestions = "Concepts suggérés (simulation): " . implode(', ', array_column($concepts, 'preferred_label'));
            } else {
                $response = $this->callMistralAPI('thesaurus', $record);
                $suggestions = $response['content'];
                $concepts = $this->extractConceptsFromResponse($suggestions);
                $tokensUsed = $response['tokens'];
            }

            return response()->json([
                'message' => 'Indexation thésaurus réussie' . ($this->mockMode ? ' (simulation)' : ''),
                'record_id' => $record->id,
                'concepts_found' => count($concepts),
                'concepts' => $concepts,
                'raw_response' => $suggestions,
                'tokens_used' => $tokensUsed,
                'mock_mode' => $this->mockMode
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur indexation thésaurus Mistral', [
                'record_id' => $record->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Erreur lors de l\'indexation thésaurus',
                'message' => $e->getMessage(),
                'record_id' => $record->id
            ], 500);
        }
    }

    /**
     * Générer un résumé ISAD(G) avec Mistral
     */
    public function generateSummary(Request $request, Record $record): JsonResponse
    {
        try {
            if ($this->mockMode) {
                $summary = $this->generateMockSummary($record);
                $tokensUsed = rand(200, 500);
            } else {
                $response = $this->callMistralAPI('summary', $record);
                $summary = $response['content'];
                $tokensUsed = $response['tokens'];
            }

            // Sauvegarder le résumé dans le champ approprié
            $record->update(['summary' => $summary]);

            return response()->json([
                'message' => 'Résumé généré avec succès' . ($this->mockMode ? ' (simulation)' : ''),
                'record_id' => $record->id,
                'summary' => $summary,
                'tokens_used' => $tokensUsed,
                'mock_mode' => $this->mockMode
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur génération résumé Mistral', [
                'record_id' => $record->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Erreur lors de la génération du résumé',
                'message' => $e->getMessage(),
                'record_id' => $record->id
            ], 500);
        }
    }

    /**
     * Prévisualiser le résumé ISAD(G)
     */
    public function previewSummary(Request $request, Record $record): JsonResponse
    {
        try {
            if ($this->mockMode) {
                $summary = $this->generateMockSummary($record);
                $tokensUsed = rand(200, 500);
            } else {
                $response = $this->callMistralAPI('summary', $record);
                $summary = $response['content'];
                $tokensUsed = $response['tokens'];
            }

            return response()->json([
                'message' => 'Prévisualisation du résumé générée' . ($this->mockMode ? ' (simulation)' : ''),
                'record_id' => $record->id,
                'preview' => [
                    'current_summary' => $record->summary,
                    'suggested_summary' => $summary
                ],
                'tokens_used' => $tokensUsed,
                'mock_mode' => $this->mockMode
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la prévisualisation du résumé',
                'message' => $e->getMessage(),
                'record_id' => $record->id
            ], 500);
        }
    }

    /**
     * Traitement complet avec prévisualisation
     */
    public function previewProcessing(Request $request, Record $record): JsonResponse
    {
        $request->validate([
            'features' => 'array',
            'features.*' => Rule::in(['title', 'thesaurus', 'summary'])
        ]);

        $features = $request->get('features', ['title', 'thesaurus', 'summary']);
        $previews = [];

        try {
            foreach ($features as $feature) {
                switch ($feature) {
                    case 'title':
                        $titleResponse = $this->previewTitleReformulation($request, $record);
                        $titleData = json_decode($titleResponse->getContent(), true);
                        if (!isset($titleData['error'])) {
                            $previews['title'] = $titleData['preview'];
                        }
                        break;

                    case 'summary':
                        $summaryResponse = $this->previewSummary($request, $record);
                        $summaryData = json_decode($summaryResponse->getContent(), true);
                        if (!isset($summaryData['error'])) {
                            $previews['summary'] = $summaryData['preview'];
                        }
                        break;

                    case 'thesaurus':
                        $thesaurusResponse = $this->indexWithThesaurus($request, $record);
                        $thesaurusData = json_decode($thesaurusResponse->getContent(), true);
                        if (!isset($thesaurusData['error'])) {
                            $previews['thesaurus'] = [
                                'concepts_found' => $thesaurusData['concepts_found'],
                                'concepts' => $thesaurusData['concepts']
                            ];
                        }
                        break;
                }
            }

            return response()->json([
                'message' => 'Prévisualisation complète générée' . ($this->mockMode ? ' (simulation)' : ''),
                'record_id' => $record->id,
                'previews' => $previews,
                'mock_mode' => $this->mockMode
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la prévisualisation complète',
                'message' => $e->getMessage(),
                'record_id' => $record->id
            ], 500);
        }
    }

    /**
     * Traitement complet du record
     */
    public function processRecord(Request $request, Record $record): JsonResponse
    {
        $request->validate([
            'features' => 'array',
            'features.*' => Rule::in(['title', 'thesaurus', 'summary'])
        ]);

        $features = $request->get('features', ['title', 'thesaurus', 'summary']);
        $results = [];

        try {
            foreach ($features as $feature) {
                switch ($feature) {
                    case 'title':
                        $titleResponse = $this->reformulateTitle($request, $record);
                        $titleData = json_decode($titleResponse->getContent(), true);
                        $results['title'] = $titleData;
                        break;

                    case 'summary':
                        $summaryResponse = $this->generateSummary($request, $record);
                        $summaryData = json_decode($summaryResponse->getContent(), true);
                        $results['summary'] = $summaryData;
                        break;

                    case 'thesaurus':
                        $thesaurusResponse = $this->indexWithThesaurus($request, $record);
                        $thesaurusData = json_decode($thesaurusResponse->getContent(), true);
                        $results['thesaurus'] = $thesaurusData;
                        break;
                }
            }

            return response()->json([
                'message' => 'Traitement complet réussi' . ($this->mockMode ? ' (simulation)' : ''),
                'record_id' => $record->id,
                'results' => $results,
                'mock_mode' => $this->mockMode
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du traitement complet',
                'message' => $e->getMessage(),
                'record_id' => $record->id
            ], 500);
        }
    }

    /**
     * Statut du service Mistral
     */
    public function healthCheck(): JsonResponse
    {
        try {
            if ($this->mockMode) {
                return response()->json([
                    'status' => 'healthy',
                    'service' => 'Mistral API Test (Mode Simulation)',
                    'mock_mode' => true,
                    'timestamp' => now()->toISOString()
                ]);
            }

            // Test réel avec Mistral si disponible
            $response = $this->callMistralAPI('health', null, 'Test de connexion');

            return response()->json([
                'status' => 'healthy',
                'service' => 'Mistral API Test',
                'mock_mode' => false,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'mock_mode' => $this->mockMode,
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    /**
     * Appel à l'API Mistral réelle (si disponible)
     */
    private function callMistralAPI(string $type, ?Record $record, string $customPrompt = null): array
    {
        if ($this->mockMode) {
            throw new \Exception('Mode simulation activé');
        }

        // Import dynamique du package Mistral
        $mistralClass = 'HelgeSverre\Mistral\Mistral';
        $roleClass = 'HelgeSverre\Mistral\Enums\Role';
        
        $mistral = new $mistralClass(apiKey: config('mistral.api_key'));

        $messages = [
            [
                'role' => $roleClass::system->value,
                'content' => $this->getSystemPrompt($type)
            ]
        ];

        if ($customPrompt) {
            $messages[] = [
                'role' => $roleClass::user->value,
                'content' => $customPrompt
            ];
        } else {
            $recordContext = $this->buildRecordContext($record);
            $messages[] = [
                'role' => $roleClass::user->value,
                'content' => $this->buildPrompt($type, $record, $recordContext)
            ];
        }

        $config = config("mistral.tasks.{$type}", config('mistral.defaults'));

        $response = $mistral->chat()->create(
            messages: $messages,
            model: config("mistral.models.{$type}", 'mistral-medium-latest'),
            temperature: $config['temperature'] ?? 0.3,
            maxTokens: $config['max_tokens'] ?? 500,
            safeMode: true
        );

        $dto = $response->dto();

        return [
            'content' => trim($dto->choices[0]->message->content),
            'tokens' => $dto->usage->totalTokens ?? 0
        ];
    }

    /**
     * Générer un titre simulé
     */
    private function generateMockTitle(Record $record): string
    {
        $templates = [
            "Correspondance relative à {topic}",
            "Rapport d'activité - {topic}",
            "Procès-verbal de réunion concernant {topic}",
            "Document administratif - {topic}",
            "Dossier de {topic} - Période {period}",
        ];

        $topics = ['la gestion des archives', 'l\'administration', 'les ressources humaines', 'la comptabilité', 'les projets'];
        $template = $templates[array_rand($templates)];
        $topic = $topics[array_rand($topics)];
        $period = date('Y', strtotime($record->date_start ?? now()));

        return str_replace(['{topic}', '{period}'], [$topic, $period], $template);
    }

    /**
     * Générer des concepts simulés
     */
    private function generateMockConcepts(Record $record): array
    {
        $concepts = [
            ['preferred_label' => 'Administration générale', 'weight' => 0.85, 'type' => 'concept'],
            ['preferred_label' => 'Gestion documentaire', 'weight' => 0.92, 'type' => 'concept'],
            ['preferred_label' => 'Archives historiques', 'weight' => 0.78, 'type' => 'concept'],
            ['preferred_label' => 'Patrimoine culturel', 'weight' => 0.71, 'type' => 'concept'],
            ['preferred_label' => 'Correspondance officielle', 'weight' => 0.89, 'type' => 'concept'],
        ];

        // Retourner 3-5 concepts aléatoires
        $selected = array_rand($concepts, rand(3, 5));
        if (!is_array($selected)) {
            $selected = [$selected];
        }

        return array_map(fn($index) => $concepts[$index], $selected);
    }

    /**
     * Générer un résumé simulé
     */
    private function generateMockSummary(Record $record): string
    {
        $templates = [
            "Ce document contient des informations relatives à {topic}. Il documente les activités et décisions prises durant la période {period}. Les principaux sujets abordés concernent la gestion administrative et les procédures mises en place.",
            "Archive documentant {topic} pour la période {period}. Le dossier comprend la correspondance, les rapports d'activité et les documents de travail relatifs aux procédures administratives et aux décisions prises.",
            "Ensemble documentaire relatif à {topic}. Les documents couvrent la période {period} et témoignent des activités menées et des décisions administratives prises dans ce domaine."
        ];

        $topics = ['la gestion des archives', 'l\'administration municipale', 'les services publics', 'les affaires courantes'];
        $template = $templates[array_rand($templates)];
        $topic = $topics[array_rand($topics)];
        $period = ($record->date_start ?? date('Y')) . '-' . ($record->date_end ?? date('Y'));

        return str_replace(['{topic}', '{period}'], [$topic, $period], $template);
    }

    /**
     * Construire le contexte d'un record
     */
    private function buildRecordContext(Record $record): string
    {
        $context = "CODE: {$record->code}\n";
        $context .= "TITRE: {$record->name}\n";
        
        if ($record->description) {
            $context .= "DESCRIPTION: {$record->description}\n";
        }
        
        if ($record->level) {
            $context .= "NIVEAU: {$record->level->name}\n";
        }
        
        if ($record->authors->count() > 0) {
            $context .= "PRODUCTEURS: " . $record->authors->pluck('name')->implode(', ') . "\n";
        }
        
        $context .= "DATES: ";
        if ($record->date_exact) {
            $context .= $record->date_exact;
        } else {
            $context .= ($record->date_start ?? 'N/A') . ' - ' . ($record->date_end ?? 'N/A');
        }
        $context .= "\n";
        
        if ($record->parent) {
            $context .= "PARENT: [{$record->parent->level->name}] {$record->parent->name}\n";
        }

        return $context;
    }

    /**
     * Construire le prompt selon le type de tâche
     */
    private function buildPrompt(string $type, Record $record, string $context): string
    {
        switch ($type) {
            case 'title':
                return "Reformule le titre suivant selon les règles ISAD(G):\n\nTitre actuel: {$record->name}\n\nContexte du record:\n{$context}";
            case 'thesaurus':
                return "Analyse ce record et propose des concepts du thésaurus:\n\n{$context}";
            case 'summary':
                return "Génère un résumé ISAD(G) (élément 3.3.1) pour ce record:\n\n{$context}";
            default:
                return $context;
        }
    }

    /**
     * Obtenir le prompt système selon le type de tâche
     */
    private function getSystemPrompt(string $type): string
    {
        $prompts = [
            'title' => $this->getTitleReformulationPrompt(),
            'thesaurus' => $this->getThesaurusIndexingPrompt(),
            'summary' => $this->getSummaryGenerationPrompt(),
            'health' => 'Tu es un assistant de test. Réponds simplement "OK" pour confirmer la connexion.'
        ];

        return $prompts[$type] ?? $prompts['health'];
    }

    /**
     * Prompt pour la reformulation de titre
     */
    private function getTitleReformulationPrompt(): string
    {
        return <<<EOT
Tu es un expert en archivistique et en normes ISAD(G). Ton rôle est de reformuler les titres de documents d'archives selon les principes ISAD(G).

RÈGLES POUR LA REFORMULATION :
1. Le titre doit être informatif et précis
2. Éviter les mots vagues ou génériques
3. Indiquer la nature du document (correspondance, rapport, procès-verbal...)
4. Mentionner les personnes/organismes principaux si pertinent
5. Garder une longueur raisonnable (50-100 caractères idéalement)
6. Utiliser un langage normalisé et professionnel
7. Éviter les acronymes non expliqués

RÉPONSE ATTENDUE :
Réponds UNIQUEMENT par le titre reformulé, sans explication ni commentaire.
EOT;
    }

    /**
     * Prompt pour l'indexation thésaurus
     */
    private function getThesaurusIndexingPrompt(): string
    {
        return <<<EOT
Tu es un expert en indexation documentaire et en thésaurus d'archives. Ton rôle est d'analyser le contenu d'un record et d'identifier les concepts pertinents.

MISSION :
1. Analyser le titre, la description et le contexte du record
2. Identifier les concepts principaux (sujets, personnes, lieux, périodes)
3. Proposer des termes d'indexation normalisés
4. Éviter les concepts trop généraux ou trop spécifiques

RÉPONSE ATTENDUE :
Propose une liste de 3-7 concepts sous forme de liste à puces :
- Concept 1
- Concept 2
- etc.
EOT;
    }

    /**
     * Prompt pour la génération de résumé
     */
    private function getSummaryGenerationPrompt(): string
    {
        return <<<EOT
Tu es un archiviste expert en description selon la norme ISAD(G). Ton rôle est de rédiger l'élément 3.3.1 "Présentation du contenu" selon ISAD(G).

CRITÈRES POUR LE RÉSUMÉ ISAD(G) :
1. Décrire le contenu informationnel du document
2. Mentionner les thèmes principaux traités
3. Indiquer les personnes/organismes importants
4. Préciser la forme documentaire si pertinent
5. Garder un style objectif et factuel
6. Longueur : 2-4 phrases (150-300 mots max)
7. Éviter les jugements de valeur

RÉPONSE ATTENDUE :
Rédige directement le résumé ISAD(G), sans titre ni introduction.
EOT;
    }

    /**
     * Extraire les concepts de la réponse Mistral (simulation)
     */
    private function extractConceptsFromResponse(string $response): array
    {
        $concepts = [];
        $lines = explode("\n", $response);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, '- ') || str_starts_with($line, '* ')) {
                $concept = trim(substr($line, 2));
                if (!empty($concept)) {
                    $concepts[] = [
                        'preferred_label' => $concept,
                        'weight' => rand(60, 95) / 100, // Simulation de poids
                        'type' => 'concept'
                    ];
                }
            }
        }
        
        return array_slice($concepts, 0, 7); // Max 7 concepts
    }
}