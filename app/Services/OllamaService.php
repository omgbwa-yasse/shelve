<?php

namespace App\Services;

use App\Models\AiModel;
use App\Models\AiInteraction;
use App\Models\AiJob;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class OllamaService
{
    protected string $baseUrl;
    protected int $timeout;
    protected int $retryCount;
    protected int $retryDelay;

    public function __construct()
    {
        $this->baseUrl = config('ollama.base_url', 'http://localhost:11434');
        $this->timeout = config('ollama.timeout', 120);
        $this->retryCount = config('ollama.retry_count', 2);
        $this->retryDelay = config('ollama.retry_delay', 1000); // ms
    }

    /**
     * Obtenir la liste des modèles disponibles sur Ollama
     *
     * @throws Exception Si la connexion échoue ou si la réponse n'est pas valide
     * @return array
     */
    public function getAvailableModels(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retryCount, $this->retryDelay)
                ->get("{$this->baseUrl}/api/tags");

            if ($response->successful()) {
                return $response->json()['models'] ?? [];
            }

            throw new Exception("Erreur lors de la récupération des modèles: " . $response->body());
        } catch (Exception $e) {
            Log::error('Ollama getAvailableModels error: ' . $e->getMessage(), [
                'base_url' => $this->baseUrl,
                'exception' => $e
            ]);
            throw $e;
        }
    }

    /**
     * Synchroniser les modèles Ollama avec la base de données
     *
     * @throws Exception Si la récupération des modèles échoue
     * @return int Nombre de modèles synchronisés
     */
    public function syncModels(): int
    {
        $synced = 0;

        try {
            $ollamaModels = $this->getAvailableModels();

            foreach ($ollamaModels as $model) {
                $existing = AiModel::where('name', $model['name'])
                    ->where('provider', 'ollama')
                    ->first();

                if (!$existing) {
                    AiModel::create([
                        'name' => $model['name'],
                        'provider' => 'ollama',
                        'version' => $model['details']['parameter_size'] ?? 'unknown',
                        'api_type' => 'local',
                        'capabilities' => json_encode([
                            'size' => $model['size'] ?? 0,
                            'modified_at' => $model['modified_at'] ?? null,
                            'digest' => $model['digest'] ?? null,
                            'details' => $model['details'] ?? []
                        ]),
                        'is_active' => true
                    ]);
                    $synced++;
                }
            }

            return $synced;
        } catch (Exception $e) {
            Log::error('Ollama syncModels error: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;
        }
    }

    /**
     * Générer une réponse avec Ollama
     *
     * @param string $model Le nom du modèle à utiliser
     * @param string $prompt Le prompt/message à envoyer
     * @param array $options Options additionnelles pour la génération
     * @param bool $stream Utiliser le streaming pour la réponse
     * @return array
     */
    public function generate(
        string $model,
        string $prompt,
        array $options = [],
        bool $stream = false
    ): array {
        try {
            // Vérifier si le modèle existe avant de faire l'appel
            if (!$this->modelExists($model)) {
                return [
                    'success' => false,
                    'error' => "Le modèle '$model' n'existe pas sur le serveur Ollama."
                ];
            }

            $payload = [
                'model' => $model,
                'prompt' => $prompt,
                'stream' => $stream,
                'options' => array_merge([
                    'temperature' => 0.7,
                    'top_p' => 0.9,
                    'top_k' => 40,
                ], $options)
            ];

            $response = Http::timeout($this->timeout)
                ->retry($this->retryCount, $this->retryDelay)
                ->post("{$this->baseUrl}/api/generate", $payload);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'response' => $data['response'] ?? '',
                    'model' => $data['model'] ?? $model,
                    'created_at' => $data['created_at'] ?? now(),
                    'done' => $data['done'] ?? false,
                    'context' => $data['context'] ?? null,
                    'total_duration' => $data['total_duration'] ?? 0,
                    'load_duration' => $data['load_duration'] ?? 0,
                    'prompt_eval_count' => $data['prompt_eval_count'] ?? 0,
                    'prompt_eval_duration' => $data['prompt_eval_duration'] ?? 0,
                    'eval_count' => $data['eval_count'] ?? 0,
                    'eval_duration' => $data['eval_duration'] ?? 0,
                ];
            }

            return [
                'success' => false,
                'error' => "Erreur de génération: " . $response->body(),
                'status_code' => $response->status()
            ];
        } catch (Exception $e) {
            Log::error('Ollama generate error: ' . $e->getMessage(), [
                'model' => $model,
                'exception' => $e
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Chat avec contexte (conversation)
     *
     * @param string $model Le nom du modèle à utiliser
     * @param array $messages Les messages précédents de la conversation
     * @param array $options Options additionnelles pour la génération
     * @return array
     */
    public function chat(string $model, array $messages, array $options = []): array
    {
        try {
            // Vérifier si le modèle existe avant de faire l'appel
            if (!$this->modelExists($model)) {
                return [
                    'success' => false,
                    'error' => "Le modèle '$model' n'existe pas sur le serveur Ollama."
                ];
            }

            $payload = [
                'model' => $model,
                'messages' => $messages,
                'stream' => false,
                'options' => array_merge([
                    'temperature' => 0.7,
                    'top_p' => 0.9,
                ], $options)
            ];

            $response = Http::timeout($this->timeout)
                ->retry($this->retryCount, $this->retryDelay)
                ->post("{$this->baseUrl}/api/chat", $payload);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'message' => $data['message'] ?? null,
                    'model' => $data['model'] ?? $model,
                    'created_at' => $data['created_at'] ?? now(),
                    'done' => $data['done'] ?? false,
                    'total_duration' => $data['total_duration'] ?? 0,
                    'load_duration' => $data['load_duration'] ?? 0,
                    'prompt_eval_count' => $data['prompt_eval_count'] ?? 0,
                    'prompt_eval_duration' => $data['prompt_eval_duration'] ?? 0,
                    'eval_count' => $data['eval_count'] ?? 0,
                    'eval_duration' => $data['eval_duration'] ?? 0,
                ];
            }

            return [
                'success' => false,
                'error' => "Erreur de chat: " . $response->body(),
                'status_code' => $response->status()
            ];
        } catch (Exception $e) {
            Log::error('Ollama chat error: ' . $e->getMessage(), [
                'model' => $model,
                'exception' => $e
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Créer une interaction avec suivi
     */
    public function createInteraction(
        int $userId,
        int $aiModelId,
        string $input,
        array $parameters = [],
        ?string $moduleType = null,
        ?int $moduleId = null,
        ?string $sessionId = null
    ): AiInteraction {
        return AiInteraction::create([
            'user_id' => $userId,
            'ai_model_id' => $aiModelId,
            'input' => $input,
            'parameters' => json_encode($parameters),
            'module_type' => $moduleType,
            'module_id' => $moduleId,
            'session_id' => $sessionId,
            'status' => 'pending'
        ]);
    }

    /**
     * Traiter une interaction avec Ollama
     */
    public function processInteraction(AiInteraction $interaction): AiInteraction
    {
        try {
            $interaction->update(['status' => 'processing']);

            $aiModel = $interaction->aiModel;
            $parameters = [];
            if (isset($interaction->parameters)) {
                $parameters = $interaction->parameters; // It's already decoded due to the json cast in the model
            }

            $result = $this->generate(
                $aiModel->name,
                $interaction->input,
                $parameters['options'] ?? []
            );

            if ($result['success']) {
                $interaction->update([
                    'output' => $result['response'],
                    'tokens_used' => ($result['prompt_eval_count'] ?? 0) + ($result['eval_count'] ?? 0),
                    'status' => 'completed',
                    'parameters' => json_encode(array_merge($parameters, [
                        'ollama_stats' => [
                            'total_duration' => $result['total_duration'],
                            'load_duration' => $result['load_duration'],
                            'prompt_eval_duration' => $result['prompt_eval_duration'],
                            'eval_duration' => $result['eval_duration'],
                        ]
                    ]))
                ]);
            } else {
                $interaction->update([
                    'status' => 'failed',
                    'output' => 'Erreur: ' . $result['error']
                ]);
            }

            return $interaction->fresh();
        } catch (Exception $e) {
            Log::error('Process interaction error: ' . $e->getMessage(), [
                'interaction_id' => $interaction->id,
                'exception' => $e
            ]);
            $interaction->update([
                'status' => 'failed',
                'output' => 'Erreur système: ' . $e->getMessage()
            ]);
            return $interaction->fresh();
        }
    }

    /**
     * Traitement par lot (jobs)
     */
    public function createBatchJob(
        string $jobType,
        int $aiModelId,
        array $inputs,
        array $parameters = []
    ): AiJob {
        return AiJob::create([
            'job_type' => $jobType,
            'ai_model_id' => $aiModelId,
            'status' => 'pending',
            'parameters' => json_encode($parameters),
            'input' => json_encode($inputs)
        ]);
    }

    /**
     * Exécuter un job par lot
     */
    public function processBatchJob(AiJob $job): AiJob
    {
        try {
            $job->update(['status' => 'processing']);

            $inputs = json_decode($job->input, true);
            $parameters = [];
            if (isset($job->parameters)) {
                $parameters = $job->parameters; // It's already decoded due to the json cast in the model
            }
            $results = [];

            foreach ($inputs as $input) {
                $result = $this->generate(
                    $job->aiModel->name,
                    $input,
                    $parameters['options'] ?? []
                );
                $results[] = $result;
            }

            $job->update([
                'status' => 'completed',
                'result' => json_encode($results)
            ]);

            return $job->fresh();
        } catch (Exception $e) {
            Log::error('Process batch job error: ' . $e->getMessage(), [
                'job_id' => $job->id,
                'exception' => $e
            ]);
            $job->update([
                'status' => 'failed',
                'error' => $e->getMessage()
            ]);
            return $job->fresh();
        }
    }

    /**
     * Vérifier l'état de santé d'Ollama
     *
     * @return array ['status' => 'healthy'|'unhealthy', 'response_time' => float, 'message' => string]
     */
    public function healthCheck(): array
    {
        try {
            // Utiliser un timeout court pour la vérification de santé
            $response = Http::timeout(5)->get("{$this->baseUrl}/");

            // Vérifier si des modèles sont disponibles
            $modelsAvailable = false;
            try {
                $models = $this->getAvailableModels();
                $modelsAvailable = !empty($models);
            } catch (Exception $e) {
                Log::warning('Ollama health check - models check failed: ' . $e->getMessage());
            }

            return [
                'status' => $response->successful() ? 'healthy' : 'unhealthy',
                'response_time' => $response->transferStats?->getTransferTime() ?? 0,
                'models_available' => $modelsAvailable,
                'message' => $response->successful()
                    ? 'Ollama est en ligne' . ($modelsAvailable ? ' avec des modèles disponibles' : ' mais aucun modèle n\'est disponible')
                    : 'Ollama ne répond pas'
            ];
        } catch (Exception $e) {
            Log::warning('Ollama health check failed: ' . $e->getMessage());
            return [
                'status' => 'unhealthy',
                'response_time' => 0,
                'models_available' => false,
                'message' => 'Connexion échouée: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Streaming pour les réponses en temps réel
     */
    public function generateStream(string $model, string $prompt, array $options = []): \Generator
    {
        try {
            // Vérifier si le modèle existe
            if (!$this->modelExists($model)) {
                yield [
                    'error' => "Le modèle '$model' n'existe pas sur le serveur Ollama."
                ];
                return;
            }

            $payload = [
                'model' => $model,
                'prompt' => $prompt,
                'stream' => true,
                'options' => array_merge([
                    'temperature' => 0.7,
                ], $options)
            ];

            $response = Http::timeout($this->timeout)
                ->withOptions(['stream' => true])
                ->post("{$this->baseUrl}/api/generate", $payload);

            $body = $response->getBody();

            while (!$body->eof()) {
                $line = trim($body->read(1024));
                if (!empty($line)) {
                    $data = json_decode($line, true);
                    if ($data) {
                        yield $data;
                    }
                }
            }
        } catch (Exception $e) {
            Log::error('Ollama generateStream error: ' . $e->getMessage());
            yield [
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifie si un modèle existe sur Ollama par son nom
     *
     * @param string $name Nom du modèle
     * @return bool
     */
    public function modelExists(string $name): bool
    {
        try {
            $models = $this->getAvailableModels();
            foreach ($models as $model) {
                if ($model['name'] === $name) {
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            Log::error('Ollama modelExists error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les détails d'un modèle Ollama par son nom
     *
     * @param string $name Nom du modèle
     * @return array
     * @throws Exception Si le modèle n'est pas trouvé
     */
    public function getModelDetails(string $name): array
    {
        try {
            $models = $this->getAvailableModels();
            foreach ($models as $model) {
                if ($model['name'] === $name) {
                    return [
                        'name' => $model['name'],
                        'type' => $this->inferModelType($model['name']),
                        'version' => $model['modified_at'] ?? null,
                        'size' => $model['size'] ?? 0,
                        'digest' => $model['digest'] ?? null,
                    ];
                }
            }
            throw new Exception("Modèle non trouvé: {$name}");
        } catch (Exception $e) {
            Log::error('Ollama getModelDetails error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Infère le type de modèle en fonction de son nom
     */
    protected function inferModelType(string $modelName): string
    {
        $modelName = strtolower($modelName);

        // Inférer le type de modèle à partir du nom
        if (str_contains($modelName, 'image') || str_contains($modelName, 'dall-e') ||
            str_contains($modelName, 'stable') || str_contains($modelName, 'sd')) {
            return 'image';
        } elseif (str_contains($modelName, 'code') || str_contains($modelName, 'starcoder') ||
                str_contains($modelName, 'codellama') || str_contains($modelName, 'wizard-coder')) {
            return 'code';
        } elseif (str_contains($modelName, 'embed') || str_contains($modelName, 'embedding')) {
            return 'embedding';
        }

        // Par défaut, c'est un modèle de texte
        return 'text';
    }

    /**
     * Récupère l'historique des messages d'un chat et le formate pour Ollama
     *
     * @param  \App\Models\AiChat  $chat
     * @param  int  $limit  Nombre maximum de messages à récupérer
     * @return array
     */
    public function getChatHistory(\App\Models\AiChat $chat, int $limit = 10): array
    {
        // Récupérer les messages par ordre chronologique
        $messages = $chat->messages()
            ->whereNotIn('metadata->type', ['thinking', 'error'])  // Exclure les messages temporaires/erreur
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->sortBy('created_at')
            ->values();

        // Formatter les messages pour l'API Ollama
        $history = [];
        foreach ($messages as $message) {
            // Mapper 'user' -> 'user', 'assistant' -> 'assistant', 'system' -> 'system'
            $role = in_array($message->role, ['user', 'assistant', 'system'])
                ? $message->role
                : 'user'; // par défaut

            $history[] = [
                'role' => $role,
                'content' => $message->content
            ];
        }

        return $history;
    }

    /**
     * Annule une génération en cours en supprimant le modèle de la mémoire Ollama
     *
     * @return bool Succès de l'opération
     */
    public function cancelGeneration(): bool
    {
        try {
            $response = Http::timeout(5)
                ->delete("{$this->baseUrl}/api/generate");

            return $response->successful();
        } catch (Exception $e) {
            Log::error('Ollama cancelGeneration error: ' . $e->getMessage());
            return false;
        }
    }
}
