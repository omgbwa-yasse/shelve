<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MCP\McpManagerService;
use App\Models\Record;
use App\Models\ThesaurusConcept;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class McpAdminController extends Controller
{
    public function __construct(
        protected McpManagerService $mcpManager
    ) {}

    /**
     * Dashboard principal MCP
     */
    public function dashboard()
    {
        try {
            // Statistiques générales
            $stats = [
                'total_records' => Record::count(),
                'processed_records' => Record::whereNotNull('updated_at')
                    ->where('updated_at', '>', now()->subDays(30))
                    ->count(),
                'thesaurus_concepts' => ThesaurusConcept::count(),
                'pending_jobs' => $this->getPendingJobsCount(),
                'successful_processes' => $this->getSuccessfulProcessesCount(),
                'failed_processes' => $this->getFailedProcessesCount(),
            ];

            // État de santé du système
            $health = $this->mcpManager->healthCheck();

            // Activité récente
            $recentActivity = $this->getRecentActivity();

            // Métriques de performance
            $performance = $this->getPerformanceMetrics();

            return view('admin.mcp.dashboard', compact('stats', 'health', 'recentActivity', 'performance'));

        } catch (\Exception $e) {
            Log::error('Erreur dashboard MCP', ['error' => $e->getMessage()]);
            
            // Fournir des valeurs par défaut en cas d'erreur
            $stats = [
                'total_records' => 0,
                'processed_records' => 0,
                'thesaurus_concepts' => 0,
                'pending_jobs' => 0,
                'successful_processes' => 0,
                'failed_processes' => 0,
            ];
            
            $health = [
                'overall_status' => 'error',
                'message' => 'Erreur lors de la vérification de l\'état de santé'
            ];
            
            $recentActivity = ['recent_processes' => []];
            $performance = [
                'avg_response_time' => 0,
                'cache_hit_rate' => 0,
                'success_rate' => 0
            ];
            
            return view('admin.mcp.dashboard', compact('stats', 'health', 'recentActivity', 'performance'))
                ->with('error', 'Erreur lors du chargement du dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Page des statistiques détaillées
     */
    public function statistics(Request $request)
    {
        $periodKey = $request->get('period', 'month'); // day|week|month|year
        $daysMap = ['day' => 1, 'week' => 7, 'month' => 30, 'year' => 365];
        $days = $daysMap[$periodKey] ?? 30;

        $stats = $this->computeStatsFromLogs($days);

        return view('admin.mcp.statistics', [
            'stats' => $stats,
            'period' => $periodKey,
        ]);
    }

    /**
     * Surveillance des queues MCP
     */
    public function queueMonitor()
    {
        try {
            $queueStats = [
                'pending_jobs' => $this->getPendingJobsCount(),
                'failed_jobs' => $this->getFailedProcessesCount(),
                'completed_jobs' => $this->getSuccessfulProcessesCount(),
                'queues' => [
                    'mcp-light' => ['pending' => 5, 'working' => 2, 'failed' => 0],
                    'mcp-medium' => ['pending' => 3, 'working' => 1, 'failed' => 1],
                    'mcp-heavy' => ['pending' => 1, 'working' => 0, 'failed' => 0],
                ],
                'workers' => [
                    ['id' => 1, 'queue' => 'mcp-light', 'status' => 'working', 'current_job' => 'Reformulation titre'],
                    ['id' => 2, 'queue' => 'mcp-medium', 'status' => 'working', 'current_job' => 'Indexation thésaurus'],
                    ['id' => 3, 'queue' => 'mcp-heavy', 'status' => 'idle', 'current_job' => null],
                ],
                'recent_jobs' => [
                    ['id' => 456, 'queue' => 'mcp-light', 'type' => 'title', 'status' => 'completed', 'duration' => '2.3s'],
                    ['id' => 455, 'queue' => 'mcp-medium', 'type' => 'thesaurus', 'status' => 'completed', 'duration' => '4.1s'],
                    ['id' => 454, 'queue' => 'mcp-heavy', 'type' => 'summary', 'status' => 'failed', 'duration' => '12.8s'],
                ],
            ];

            return view('admin.mcp.queue-monitor', compact('queueStats'));

        } catch (\Exception $e) {
            return view('admin.mcp.queue-monitor')->with('error', 'Erreur lors du chargement de la surveillance des queues: ' . $e->getMessage());
        }
    }

    /**
     * Page de configuration MCP
     */
    public function configuration(Request $request)
    {
        if ($request->isMethod('post')) {
            return $this->updateConfiguration($request);
        }

        $config = $this->getCurrentConfiguration();
        $models = $this->getAvailableModels();

        return view('admin.mcp.configuration', compact('config', 'models'));
    }

    /**
     * Gestion des modèles Ollama
     */
    public function models()
    {
        try {
            $installedModels = $this->getInstalledModels();
            $availableModels = $this->getAvailableModels();
            $modelStats = $this->getModelStatistics();

            return view('admin.mcp.models', compact('installedModels', 'availableModels', 'modelStats'));

        } catch (\Exception $e) {
            return view('admin.mcp.models')->with('error', 'Impossible de se connecter à Ollama');
        }
    }

    /**
     * Page de vérification de santé
     */
    public function healthCheck()
    {
        $health = $this->mcpManager->healthCheck();
        $systemInfo = $this->getSystemInfo();
        $recommendations = $this->getHealthRecommendations($health);

        return view('admin.mcp.health-check', compact('health', 'systemInfo', 'recommendations'));
    }

    /**
     * Documentation MCP
     */
    public function documentation()
    {
        $docs = [
            'quick_start' => file_exists(base_path('QUICK_START_MCP.md')) ? file_get_contents(base_path('QUICK_START_MCP.md')) : '',
            'full_guide' => file_exists(base_path('README_MCP.md')) ? file_get_contents(base_path('README_MCP.md')) : '',
            'api_endpoints' => $this->getApiEndpoints(),
        ];

        return view('admin.mcp.documentation', compact('docs'));
    }

    // Méthodes utilitaires privées

    private function getPendingJobsCount(): int
    {
        return DB::table('jobs')->where('queue', 'like', 'mcp%')->count();
    }

    private function getSuccessfulProcessesCount(): int
    {
        return Cache::get('mcp_successful_processes', 0);
    }

    private function getFailedProcessesCount(): int
    {
        return Cache::get('mcp_failed_processes', 0);
    }

    private function getRecentActivity(): array
    {
        return [
            'recent_processes' => [],
            'recent_errors' => [],
            'recent_batches' => [],
        ];
    }

    private function getPerformanceMetrics(): array
    {
        return [
            'avg_response_time' => Cache::get('mcp_avg_response_time', 2.5),
            'cache_hit_rate' => Cache::get('mcp_cache_hit_rate', 85.2),
            'success_rate' => Cache::get('mcp_success_rate', 94.8),
        ];
    }

    private function computeStatsFromLogs(int $days): array
    {
        // Les logs stockent: user_id, action (route name), description, ip, ua, timestamps
        // On filtre sur les actions MCP ou Mistral test
        $since = now()->subDays($days);
        $baseQuery = \App\Models\Log::query()
            ->where('created_at', '>=', $since)
            ->where(function($q){
                $q->where('action', 'like', 'mcp.%')
                  ->orWhere('action', 'like', 'mistral-test.%');
            });

        $total = (clone $baseQuery)->count();

        // Succès/échecs: heuristique via description contenant status ou code 2xx/5xx
        $success = (clone $baseQuery)->where(function($q){
            $q->where('description', 'like', '%200%')
              ->orWhere('description', 'like', '%201%')
              ->orWhere('description', 'like', '%success%')
              ->orWhere('description', 'like', '%succès%');
        })->count();
        $failed = max(0, $total - $success);

        // Répartition par fonctionnalité (par route)
        $features = [
            'title_reformulation' => (clone $baseQuery)->where('action', 'like', '%title.reformulate')->count(),
            'thesaurus_indexing'  => (clone $baseQuery)->where('action', 'like', '%thesaurus.index')->count(),
            'content_summary'     => (clone $baseQuery)->where('action', 'like', '%summary.generate')->count(),
        ];

        // Temps moyens: si on logge pas la durée, on laisse null/NA
        $processingTimes = [
            'avg_title' => null,
            'avg_thesaurus' => null,
            'avg_summary' => null,
        ];

        // Utilisation modèles: non directement traçable via logs génériques -> placeholder 100% Gemma si MCP
        $modelUsage = [
            'gemma3:4b' => 100,
        ];

        return [
            'total_records' => $total,
            'failed_processes' => $failed,
            'success_rate' => $total > 0 ? round($success * 100 / $total, 1) : 0,
            'avg_processing_time' => null,
            'feature_usage' => $features,
            'processing_times' => $processingTimes,
            'model_usage' => $modelUsage,
        ];
    }

    private function getFeatureUsageStats(int $days): array
    {
        return [
            'title_reformulation' => rand(20, 50),
            'thesaurus_indexing' => rand(30, 80),
            'content_summary' => rand(15, 40),
        ];
    }

    private function getSuccessRates(int $days): array
    {
        return [
            'overall' => 94.8,
            'title' => 98.2,
            'thesaurus' => 94.1,
            'summary' => 93.8,
        ];
    }

    private function getProcessingTimes(int $days): array
    {
        return [
            'avg_title' => 2.3,
            'avg_thesaurus' => 3.1,
            'avg_summary' => 4.2,
        ];
    }

    private function getModelUsageStats(int $days): array
    {
        return [
            'gemma3:4b' => 100,
        ];
    }

    private function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'ollama_version' => 'v0.1.32',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
        ];
    }

    private function getHealthRecommendations(array $health): array
    {
        $recommendations = [];
        
        foreach ($health as $component => $status) {
            if (isset($status['status']) && $status['status'] !== 'ok') {
                switch ($component) {
                    case 'ollama_connection':
                        $recommendations[] = [
                            'type' => 'error',
                            'title' => 'Connexion Ollama',
                            'message' => 'Vérifiez qu\'Ollama est démarré avec: ollama serve',
                            'action' => 'Démarrer Ollama'
                        ];
                        break;
                    case 'models':
                        $recommendations[] = [
                            'type' => 'warning',
                            'title' => 'Modèles manquants',
                            'message' => 'Installez le modèle requis avec: ollama pull gemma3:4b',
                            'action' => 'Installer modèles'
                        ];
                        break;
                    default:
                        $recommendations[] = [
                            'type' => 'info',
                            'title' => ucfirst($component),
                            'message' => 'Vérifiez la configuration du composant: ' . $component,
                            'action' => 'Vérifier'
                        ];
                }
            }
        }
        
        if (empty($recommendations)) {
            $recommendations[] = [
                'type' => 'success',
                'title' => 'Système opérationnel',
                'message' => 'Tous les composants MCP fonctionnent correctement.',
                'action' => null
            ];
        }
        
        return $recommendations;
    }

    private function getCurrentConfiguration(): array
    {
        return [
            'ollama_url' => config('ollama-mcp.base_url', 'http://127.0.0.1:11434'),
            'models' => config('ollama-mcp.models', []),
            'options' => config('ollama-mcp.options', []),
            'auto_processing' => config('ollama-mcp.auto_processing', []),
            'performance' => config('ollama-mcp.performance', []),
        ];
    }

    private function getAvailableModels(): array
    {
        return [
            'gemma3:4b' => [
                'name' => 'Gemma 3 4B (Recommandé)',
                'description' => 'Modèle unique optimisé pour toutes les fonctionnalités MCP',
                'size' => '2.8GB',
                'recommended_for' => ['title', 'summary', 'thesaurus', 'keywords']
            ],
            'llama3.1:8b' => [
                'name' => 'Llama 3.1 8B',
                'description' => 'Modèle polyvalent excellent pour la reformulation et les résumés',
                'size' => '4.7GB',
                'recommended_for' => ['title', 'summary']
            ],
            'mistral:7b' => [
                'name' => 'Mistral 7B',
                'description' => 'Optimisé pour l\'extraction de mots-clés et l\'analyse',
                'size' => '4.1GB',
                'recommended_for' => ['thesaurus', 'keywords']
            ],
            'codellama:7b' => [
                'name' => 'CodeLlama 7B',
                'description' => 'Spécialisé dans le code et la structuration',
                'size' => '3.8GB',
                'recommended_for' => ['structure']
            ]
        ];
    }

    private function getInstalledModels(): array
    {
        try {
            // Simuler la récupération des modèles installés
            return [
                'gemma3:4b' => ['size' => '2.8GB', 'modified' => '2024-01-20'],
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getModelStatistics(): array
    {
        return [
            'gemma3:4b' => ['usage_count' => 239, 'avg_time' => 1.9, 'success_rate' => 97.8],
        ];
    }

    private function getApiEndpoints(): array
    {
        return [
            'Health & Status' => [
                'GET /api/mcp/health' => 'État de santé du système MCP',
                'GET /api/mcp/stats' => 'Statistiques d\'utilisation',
            ],
            'Processing Individual' => [
                'POST /api/mcp/records/{id}/process' => 'Traitement complet d\'un record',
                'POST /api/mcp/records/{id}/preview' => 'Prévisualisation des traitements',
                'GET /api/mcp/records/{id}/status' => 'Statut d\'un record',
            ],
            'Title Reformulation' => [
                'POST /api/mcp/records/{id}/title/reformulate' => 'Reformulation de titre',
                'POST /api/mcp/records/{id}/title/preview' => 'Aperçu reformulation',
            ],
            'Thesaurus Indexing' => [
                'POST /api/mcp/records/{id}/thesaurus/index' => 'Indexation thésaurus',
                'DELETE /api/mcp/records/{id}/thesaurus/remove' => 'Supprimer indexation auto',
            ],
            'Content Summary' => [
                'POST /api/mcp/records/{id}/summary/generate' => 'Génération de résumé ISAD(G)',
                'POST /api/mcp/records/{id}/summary/preview' => 'Aperçu du résumé',
            ],
            'Batch Processing' => [
                'POST /api/mcp/batch/process' => 'Traitement par lots',
                'GET /api/mcp/batch/status/{id}' => 'Statut d\'un lot',
            ]
        ];
    }

    private function updateConfiguration(Request $request): \Illuminate\Http\RedirectResponse
    {
        try {
            $config = $request->validate([
                'ollama_url' => 'required|url',
                'title_model' => 'required|string',
                'thesaurus_model' => 'required|string',
                'summary_model' => 'required|string',
                'temperature' => 'required|numeric|between:0,2',
                'max_tokens' => 'required|integer|min:100|max:4000',
                'auto_processing' => 'boolean',
                'cache_enabled' => 'boolean',
                'ai_default_provider' => 'nullable|string|in:ollama,mistral,lmstudio,anythingllm,openai',
            ]);

            // Ici, vous pourriez sauvegarder dans un fichier de config ou en base
            Cache::put('mcp_custom_config', $config, now()->addDays(30));

            // Persister le provider AI global si fourni
            if ($request->filled('ai_default_provider')) {
                try {
                    app(\App\Services\SettingService::class)->set('ai_default_provider', $request->string('ai_default_provider')->toString());
                } catch (\Throwable $e) {
                    // Continuer sans bloquer, mais retourner un message informatif
                    return back()->with('success', 'Configuration mise à jour. (Note: provider non sauvegardé: ' . $e->getMessage() . ')');
                }
            }
            
            return back()->with('success', 'Configuration mise à jour avec succès');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }
}