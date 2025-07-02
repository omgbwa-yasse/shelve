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

    public function __construct()
    {
        $this->baseUrl = config('ollama.base_url', 'http://localhost:11434');
        $this->timeout = config('ollama.timeout', 120);
    }

    /**
     * Obtenir la liste des modèles disponibles sur Ollama
     */
    public function getAvailableModels(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/api/tags");

            if ($response->successful()) {
                return $response->json()['models'] ?? [];
            }

            throw new Exception("Erreur lors de la récupération des modèles: " . $response->body());
        } catch (Exception $e) {
            Log::error('Ollama getAvailableModels error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Synchroniser les modèles Ollama avec la base de données
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
            Log::error('Ollama syncModels error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Générer une réponse avec Ollama
     */
    public function generate(
        string $model,
        string $prompt,
        array $options = [],
        bool $stream = false
    ): array {
        try {
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

            throw new Exception("Erreur de génération: " . $response->body());
        } catch (Exception $e) {
            Log::error('Ollama generate error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Chat avec contexte (conversation)
     */
    public function chat(string $model, array $messages, array $options = []): array
    {
        try {
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

            throw new Exception("Erreur de chat: " . $response->body());
        } catch (Exception $e) {
            Log::error('Ollama chat error: ' . $e->getMessage());
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
            $parameters = json_decode($interaction->parameters, true) ?? [];

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
            Log::error('Process interaction error: ' . $e->getMessage());
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
            $parameters = json_decode($job->parameters, true) ?? [];
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
            Log::error('Process batch job error: ' . $e->getMessage());
            $job->update([
                'status' => 'failed',
                'error' => $e->getMessage()
            ]);
            return $job->fresh();
        }
    }

    /**
     * Vérifier l'état de santé d'Ollama
     */
    public function healthCheck(): array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/");

            return [
                'status' => $response->successful() ? 'healthy' : 'unhealthy',
                'response_time' => $response->transferStats?->getTransferTime() ?? 0,
                'message' => $response->successful() ? 'Ollama is running' : 'Ollama is not responding'
            ];
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'response_time' => 0,
                'message' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Streaming pour les réponses en temps réel
     */
    public function generateStream(string $model, string $prompt, array $options = []): \Generator
    {
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
}
