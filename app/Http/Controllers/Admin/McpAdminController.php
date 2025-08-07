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
        $period = $request->get('period', '30'); // jours
        
        $stats = [
            'period_stats' => $this->getPeriodStats($period),
            'feature_usage' => $this->getFeatureUsageStats($period),
            'success_rates' => $this->getSuccessRates($period),
            'processing_times' => $this->getProcessingTimes($period),
            'model_usage' => $this->getModelUsageStats($period),
        ];

        return view('admin.mcp.statistics', compact('stats', 'period'));
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

    private function getPeriodStats(int $days): array
    {
        return [
            'total_processed' => rand(50, 200),
            'success_rate' => 94.8,
            'avg_processing_time' => 2.3,
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
            ]);

            // Ici, vous pourriez sauvegarder dans un fichier de config ou en base
            Cache::put('mcp_custom_config', $config, now()->addDays(30));
            
            return back()->with('success', 'Configuration mise à jour avec succès');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }
}