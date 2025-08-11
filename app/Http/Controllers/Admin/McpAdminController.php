<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Record;
use App\Models\ThesaurusConcept;
use App\Services\Llm\LlmMetricsService;
use App\Services\MCP\McpManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class McpAdminController extends Controller
{
    private const DEFAULT_PRIMARY_MODEL = 'gemma3:4b';
    private const VALID_STRING_RULE = 'required|string';

    public function __construct(
        protected McpManagerService $mcpManager,
        protected LlmMetricsService $llmMetricsService
    ) {}

    public function dashboard()
    {
        try {
            $llm = $this->llmMetricsService->getDashboardSummary(7);
            $stats = [
                'total_records' => Record::count(),
                'processed_records' => Record::whereNotNull('updated_at')
                    ->where('updated_at', '>', now()->subDays(30))
                    ->count(),
                'thesaurus_concepts' => ThesaurusConcept::count(),
                'pending_jobs' => $this->getPendingJobsCount(),
                'successful_processes' => $this->getSuccessfulProcessesCount(),
                'failed_processes' => $this->getFailedProcessesCount(),
                'llm' => $llm,
            ];
            $health = $this->mcpManager->healthCheck();
            $recentActivity = $this->getRecentActivity();
            $performance = $this->getPerformanceMetrics();
            return view('admin.mcp.dashboard', compact('stats', 'health', 'recentActivity', 'performance'));
        } catch (\Throwable $e) {
            Log::error('Erreur dashboard MCP', ['error' => $e->getMessage()]);
            $fallback = [
                'llm' => [
                    'period_days' => 7,
                    'total_requests' => 0,
                    'success_rate' => 0,
                    'error_rate' => 0,
                    'avg_latency_ms' => 0,
                    'total_tokens' => 0,
                    'total_cost_microusd' => 0,
                    'top_models' => collect(),
                ],
            ];
            $stats = ($stats ?? []) + $fallback;
            $health = $health ?? ['overall_status' => 'error'];
            $recentActivity = $recentActivity ?? ['recent_processes' => []];
            $performance = $performance ?? [
                'avg_response_time' => 0,
                'cache_hit_rate' => 0,
                'success_rate' => 0,
            ];
            return view('admin.mcp.dashboard', compact('stats', 'health', 'recentActivity', 'performance'))
                ->with('error', 'Erreur dashboard: ' . $e->getMessage());
        }
    }

    public function statistics(Request $request)
    {
        $periodKey = $request->get('period', 'month');
        $daysMap = ['day' => 1, 'week' => 7, 'month' => 30, 'year' => 365];
        $days = $daysMap[$periodKey] ?? 30;
        $stats = $this->llmMetricsService->getDetailedStats($days);
        return view('admin.mcp.statistics', [
            'stats' => $stats,
            'period' => $periodKey,
        ]);
    }

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
        } catch (\Throwable $e) {
            return view('admin.mcp.queue-monitor')->with('error', 'Erreur queues: ' . $e->getMessage());
        }
    }

    public function configuration(Request $request)
    {
        if ($request->isMethod('post')) {
            return $this->updateConfiguration($request);
        }
        $config = $this->getCurrentConfiguration();
        $models = $this->getAvailableModels();
        return view('admin.mcp.configuration', compact('config', 'models'));
    }

    public function models()
    {
        try {
            $installedModels = $this->getInstalledModels();
            $availableModels = $this->getAvailableModels();
            $modelStats = $this->getModelStatistics();
            return view('admin.mcp.models', compact('installedModels', 'availableModels', 'modelStats'));
        } catch (\Throwable $e) {
            return view('admin.mcp.models')->with('error', 'Impossible de se connecter à Ollama');
        }
    }

    public function healthCheck()
    {
        $health = $this->mcpManager->healthCheck();
        $systemInfo = $this->getSystemInfo();
        $recommendations = $this->getHealthRecommendations($health);
        return view('admin.mcp.health-check', compact('health', 'systemInfo', 'recommendations'));
    }

    public function documentation()
    {
        $docs = [
            'quick_start' => file_exists(base_path('QUICK_START_MCP.md')) ? file_get_contents(base_path('QUICK_START_MCP.md')) : '',
            'full_guide' => file_exists(base_path('README_MCP.md')) ? file_get_contents(base_path('README_MCP.md')) : '',
            'api_endpoints' => $this->getApiEndpoints(),
        ];
        return view('admin.mcp.documentation', compact('docs'));
    }

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
                            'message' => 'Vérifiez qu\'Ollama est démarré (ollama serve)',
                            'action' => 'Démarrer Ollama'
                        ];
                        break;
                    case 'models':
                        $recommendations[] = [
                            'type' => 'warning',
                            'title' => 'Modèles manquants',
                            'message' => 'Installer avec: ollama pull gemma3:4b',
                            'action' => 'Installer modèles'
                        ];
                        break;
                    default:
                        $recommendations[] = [
                            'type' => 'info',
                            'title' => ucfirst($component),
                            'message' => 'Vérifiez la configuration du composant',
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
                'description' => 'Polyvalent reformulation & résumés',
                'size' => '4.7GB',
                'recommended_for' => ['title', 'summary']
            ],
            'mistral:7b' => [
                'name' => 'Mistral 7B',
                'description' => 'Extraction mots-clés & analyse',
                'size' => '4.1GB',
                'recommended_for' => ['thesaurus', 'keywords']
            ],
            'codellama:7b' => [
                'name' => 'CodeLlama 7B',
                'description' => 'Spécialisé structuration',
                'size' => '3.8GB',
                'recommended_for' => ['structure']
            ]
        ];
    }

    private function getInstalledModels(): array
    {
        return [
            'gemma3:4b' => ['size' => '2.8GB', 'modified' => '2024-01-20'],
        ];
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
                'title_model' => self::VALID_STRING_RULE,
                'thesaurus_model' => self::VALID_STRING_RULE,
                'summary_model' => self::VALID_STRING_RULE,
                'temperature' => 'required|numeric|between:0,2',
                'max_tokens' => 'required|integer|min:100|max:4000',
                'auto_processing' => 'boolean',
                'cache_enabled' => 'boolean',
                'ai_default_provider' => 'nullable|string|in:ollama,mistral,lmstudio,anythingllm,openai',
            ]);
            Cache::put('mcp_custom_config', $config, now()->addDays(30));
            if ($request->filled('ai_default_provider')) {
                try {
                    app(\App\Services\SettingService::class)->set('ai_default_provider', $request->string('ai_default_provider')->toString());
                } catch (\Throwable $e) {
                    return back()->with('success', 'Configuration mise à jour (provider non sauvegardé: ' . $e->getMessage() . ')');
                }
            }
            return back()->with('success', 'Configuration mise à jour avec succès');
        } catch (\Throwable $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }
}
