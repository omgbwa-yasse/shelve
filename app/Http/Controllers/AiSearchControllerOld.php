<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Record;
use App\Models\Mail;
use App\Models\Communication;
use App\Models\Slip;
use App\Models\Prompt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use AiBridge\Facades\AiBridge;
use App\Services\AI\ProviderRegistry;
use App\Services\AI\SearchActionService;
use App\Services\AI\ActionMixerService;
use App\Services\SettingService;

class AiSearchController extends Controller
{
    public function index()
    {
        return view('ai-search.index');
    }

    public function chat(Request $request)
    {
        $message = $request->input('message');
        $searchType = $request->input('search_type', 'records');

        if (empty($message)) {
            return response()->json([
                'success' => false,
                'error' => 'Message is required'
            ]);
        }

        try {
            // L'IA est au COEUR du système - elle réfléchit et décide
            $response = $this->processWithAI($message, $searchType);

            return response()->json([
                'success' => true,
                'response' => $response['message'],
                'results' => $response['links'] ?? []
            ]);
        } catch (\Exception $e) {
            Log::error('AI Search Chat Error', [
                'message' => $message,
                'search_type' => $searchType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'response' => "Erreur IA: " . $e->getMessage(),
                'results' => []
            ]);
        }
    }

    private function processWithAI($userMessage, $searchType)
    {
        // Donner TOUS les outils à l'IA et la laisser décider
        $context = $this->buildRawDataContext($searchType);
        $systemPrompt = $this->getSimpleAIPrompt($searchType);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => "Recherche dans: $searchType\n\nDemande: $userMessage\n\nDonnées disponibles:\n$context"]
        ];

        $provider = app(SettingService::class)->get('ai_default_provider', 'ollama');
        $model = app(SettingService::class)->get('ai_default_model', 'gemma3:4b');

        app(ProviderRegistry::class)->ensureConfigured($provider);

        // Vérifier si le provider est disponible avant d'appeler
        $providerInstance = AiBridge::provider($provider);
        if (!$providerInstance) {
            throw new \Exception("Provider {$provider} non disponible. Vérifiez la configuration.");
        }

        $response = $providerInstance->chat($messages, [
            'model' => $model,
            'temperature' => 0.3,
            'max_tokens' => 1000,
            'timeout' => 5000, // Réduire le timeout pour échouer plus vite
        ]);

        $aiResponse = $this->extractAIResponse($response);

        // Laisser l'IA construire sa propre réponse avec liens
        return $this->processAIDirectResponse($aiResponse, $searchType);
    }

    private function buildRawDataContext($searchType)
    {
        // Donner les données brutes à l'IA - pas d'algorithmes
        $context = [];

        // Statistiques simples
        $total = $this->getDirectCount($searchType);
        $context[] = "Total {$searchType}: {$total}";

        // Quelques exemples récents avec IDs pour les liens
        $examples = $this->getDirectExamples($searchType, 5);
        if (!empty($examples)) {
            $context[] = "\nExemples récents:";
            foreach ($examples as $item) {
                $context[] = "ID:{$item->id} | {$item->name} | {$item->code}";
            }
        }

        // Routes disponibles pour les liens
        $context[] = "\nRoutes disponibles:";
        $context[] = "- Voir détail: " . $this->getCorrectRoutePattern($searchType);
        $context[] = "- URL complète: " . $this->getExampleUrl($searchType) . " (remplacer l'ID par l'ID réel)";

        return implode("\n", $context);
    }

    private function getCorrectRoutePattern(string $searchType): string
    {
        return match($searchType) {
            'records' => '/repositories/records/{id}',
            'mails' => '/mails/incoming/{id}',
            'communications' => '/communications/transactions/{id}',
            'slips' => '/transferrings/slips/{id}',
            default => "/{$searchType}/{id}"
        };
    }

    private function getExampleUrl(string $searchType): string
    {
        $pattern = $this->getCorrectRoutePattern($searchType);
        $exampleUrl = str_replace('{id}', '123', $pattern);
        return url($exampleUrl);
    }

    private function getSimpleAIPrompt($searchType)
    {
        return "Tu es un assistant de recherche intelligent. Tu peux voir les données et créer des réponses avec des liens directs.

INFORMATIONS IMPORTANTES:
- Tu peux voir tous les {$searchType} disponibles avec leurs IDs
- Tu peux créer des liens directs vers les éléments
- Tu réponds en français naturel
- Tu inclus des liens cliquables dans tes réponses

QUAND L'UTILISATEUR DEMANDE:
- Combien d'éléments ? → Donne le chiffre exact que tu vois
- Une recherche → Trouve les éléments pertinents et donne les liens
- Un lien → Construis l'URL complète avec l'ID approprié

FORMAT DE RÉPONSE:
- Réponds naturellement en français
- Pour les liens, utilise ce format: [Nom du document](URL_complète)
- Sois précis et utile

EXEMPLE:
Si l'utilisateur cherche \"accord Maviance\" et tu vois l'ID 123 avec ce nom, réponds:
\"J'ai trouvé l'accord Maviance : [Accord de Non-Divulgation Maviance](". url('repositories/records') ."/123)\"

Tu as accès aux données ci-dessous. Analyse et réponds intelligemment.";
    }

    private function extractAIResponse($response)
    {
        return \App\Services\AI\ResponseTextExtractor::extract($response);
    }

    private function processAIDirectResponse($aiResponse, $searchType)
    {
        // L'IA construit tout - on parse juste les liens Markdown
        $links = [];

        // Extraire les liens Markdown: [texte](url)
        if (preg_match_all('/\[([^\]]+)\]\(([^)]+)\)/', $aiResponse, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $links[] = [
                    'title' => $match[1],
                    'url' => $match[2],
                    'type' => $searchType
                ];
            }
        }

        return [
            'message' => $aiResponse,
            'links' => $links
        ];
    }

    private function getDirectCount($searchType)
    {
        return match($searchType) {
            'records' => Record::count(),
            'mails' => Mail::count(),
            'communications' => Communication::count(),
            'slips' => Slip::count(),
            default => 0
        };
    }

    private function getDirectExamples($searchType, $limit = 5)
    {
        return match($searchType) {
            'records' => Record::select('id', 'name', 'code')->latest()->limit($limit)->get(),
            'mails' => Mail::select('id', 'name', 'code')->latest()->limit($limit)->get(),
            'communications' => Communication::select('id', 'name', 'code')->latest()->limit($limit)->get(),
            'slips' => Slip::select('id', 'name', 'code')->latest()->limit($limit)->get(),
            default => collect([])
        };
    }

    private function fallbackResponse($message, $searchType)
    {
        // Analyse basique en cas de panne IA avec micro-actions
        $searchService = new SearchActionService();
        $message = strtolower($message);

        // Salutations
        if (preg_match('/(salut|bonjour|hello|hi|hola)/', $message)) {
            return response()->json([
                'success' => true,
                'response' => "Bonjour ! Je peux vous aider à chercher dans les " . $searchService->getTypeConfig($searchType)['name_fr'] . ". Que recherchez-vous ?",
                'results' => []
            ]);
        }

        // Comptage (français et anglais)
        if (preg_match('/(combien|nombre|total|how many|count|number)/', $message)) {
            $total = $searchService->countTotal($searchType);
            $typeName = $searchService->getTypeConfig($searchType)['name_fr'] ?? 'éléments';

            // Recherche d'auteur spécifique
            if (preg_match('/auteur\s+([a-zA-Z]+)/', $message, $matches)) {
                $author = $matches[1];
                $count = $searchService->countByAuthor($searchType, $author);
                return response()->json([
                    'success' => true,
                    'response' => "Il y a {$count} {$typeName} de l'auteur '{$author}'.",
                    'results' => []
                ]);
            }

            return response()->json([
                'success' => true,
                'response' => "Il y a {$total} {$typeName} dans la base de données.",
                'results' => []
            ]);
        }

        // Recherche simple
        if (preg_match('/(cherche|trouve|veux|accord|document)/', $message)) {
            $keywords = $this->extractBasicKeywords($message);
            if (!empty($keywords)) {
                $results = $searchService->searchByKeywords($searchType, $keywords, 5);
                $count = count($results);

                if ($count > 0) {
                    return response()->json([
                        'success' => true,
                        'response' => "J'ai trouvé {$count} résultat(s) pour votre recherche :",
                        'results' => $results
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'response' => "Je rencontre des difficultés avec l'IA en ce moment. Pouvez-vous reformuler votre demande ou être plus précis ?",
            'results' => []
        ]);
    }

    private function extractBasicKeywords($message)
    {
        $stopWords = ['le', 'la', 'les', 'de', 'du', 'des', 'un', 'une', 'et', 'ou', 'avec', 'pour', 'dans', 'sur', 'je', 'veux', 'cherche', 'trouve', 'document'];
        $words = preg_split('/\s+/', strtolower($message));

        return array_filter($words, function($word) use ($stopWords) {
            return !in_array($word, $stopWords) && strlen($word) > 2;
        });
    }

    private function getSearchStats($searchType)
    {
        $typeNames = [
            'records' => 'documents',
            'mails' => 'mails',
            'communications' => 'communications',
            'slips' => 'transferts'
        ];

        $total = match($searchType) {
            'records' => Record::count(),
            'mails' => Mail::count(),
            'communications' => Communication::count(),
            'slips' => Slip::count(),
            default => 0
        };

        return [
            'total' => $total,
            'type_name' => $typeNames[$searchType] ?? 'éléments'
        ];
    }

    private function getRecentExamples($searchType, $limit = 3)
    {
        return match($searchType) {
            'records' => Record::select('id', 'name', 'code')->latest()->limit($limit)->get()->toArray(),
            'mails' => Mail::select('id', 'name', 'code')->latest()->limit($limit)->get()->toArray(),
            'communications' => Communication::select('id', 'name', 'code')->latest()->limit($limit)->get()->toArray(),
            'slips' => Slip::select('id', 'name', 'code')->latest()->limit($limit)->get()->toArray(),
            default => []
        };
    }

    private function searchRecordsIntelligent($terms, $limit = 5)
    {
        $query = Record::query();

        if (!empty($terms)) {
            $query->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $q->orWhere('name', 'LIKE', "%{$term}%")
                      ->orWhere('code', 'LIKE', "%{$term}%")
                      ->orWhere('content', 'LIKE', "%{$term}%")
                      ->orWhere('note', 'LIKE', "%{$term}%")
                      ->orWhereHas('authors', function ($subQuery) use ($term) {
                          $subQuery->where('name', 'LIKE', "%{$term}%");
                      })
                      ->orWhereHas('activity', function ($subQuery) use ($term) {
                          $subQuery->where('name', 'LIKE', "%{$term}%");
                      })
                      ->orWhereHas('keywords', function ($subQuery) use ($term) {
                          $subQuery->where('name', 'LIKE', "%{$term}%");
                      });
                }
            });
        }

        $records = $query->limit($limit)->get();

        return $records->map(function ($record) {
            return [
                'title' => $record->name ?: $record->code ?: 'Document sans titre',
                'url' => route('records.show', $record->id),
                'icon' => 'bi-folder',
                'description' => $record->content ? substr($record->content, 0, 100) . '...' : ''
            ];
        })->toArray();
    }

    private function searchMailsIntelligent($terms, $limit = 5)
    {
        $query = Mail::query();

        if (!empty($terms)) {
            $query->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $q->orWhere('name', 'LIKE', "%{$term}%")
                      ->orWhere('code', 'LIKE', "%{$term}%")
                      ->orWhere('description', 'LIKE', "%{$term}%")
                      ->orWhere('object', 'LIKE', "%{$term}%");
                }
            });
        }

        $mails = $query->limit($limit)->get();

        return $mails->map(function ($mail) {
            return [
                'title' => $mail->name ?: $mail->object ?: $mail->code ?: 'Mail sans titre',
                'url' => route('mails.show', $mail->id),
                'icon' => 'bi-envelope',
                'description' => $mail->description ? substr($mail->description, 0, 100) . '...' : ''
            ];
        })->toArray();
    }

    private function searchCommunicationsIntelligent($terms, $limit = 5)
    {
        $query = Communication::query();

        if (!empty($terms)) {
            $query->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $q->orWhere('name', 'LIKE', "%{$term}%")
                      ->orWhere('code', 'LIKE', "%{$term}%")
                      ->orWhere('content', 'LIKE', "%{$term}%")
                      ->orWhere('description', 'LIKE', "%{$term}%");
                }
            });
        }

        $communications = $query->limit($limit)->get();

        return $communications->map(function ($communication) {
            return [
                'title' => $communication->name ?: $communication->code ?: 'Communication sans titre',
                'url' => route('communications.show', $communication->id),
                'icon' => 'bi-chat-dots',
                'description' => $communication->content ? substr($communication->content, 0, 100) . '...' : ''
            ];
        })->toArray();
    }

    private function searchSlipsIntelligent($terms, $limit = 5)
    {
        $query = Slip::query();

        if (!empty($terms)) {
            $query->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $q->orWhere('name', 'LIKE', "%{$term}%")
                      ->orWhere('code', 'LIKE', "%{$term}%")
                      ->orWhere('description', 'LIKE', "%{$term}%")
                      ->orWhereHas('officer', function ($subQuery) use ($term) {
                          $subQuery->where('name', 'LIKE', "%{$term}%");
                      })
                      ->orWhereHas('user', function ($subQuery) use ($term) {
                          $subQuery->where('name', 'LIKE', "%{$term}%");
                      });
                }
            });
        }

        $slips = $query->limit($limit)->get();

        return $slips->map(function ($slip) {
            return [
                'title' => $slip->name ?: $slip->code ?: 'Transfert sans titre',
                'url' => route('slips.show', $slip->id),
                'icon' => 'bi-arrow-left-right',
                'description' => $slip->description ? substr($slip->description, 0, 100) . '...' : ''
            ];
        })->toArray();
    }

}