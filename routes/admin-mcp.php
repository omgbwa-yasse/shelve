<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\McpAdminController;

/*
|--------------------------------------------------------------------------
| Routes d'Administration MCP
|--------------------------------------------------------------------------
|
| Routes pour l'interface d'administration du module MCP
| (Model Context Protocol). Ces routes nécessitent l'authentification
| et les permissions appropriées.
|
*/

Route::prefix('admin/mcp')->middleware(['auth'])->group(function () {
    
    // Dashboard principal
    Route::get('/', [McpAdminController::class, 'dashboard'])->name('admin.mcp.dashboard');
    Route::get('/dashboard', [McpAdminController::class, 'dashboard'])->name('admin.mcp.dashboard.main');
    
    // Statistiques et monitoring
    Route::get('/statistics', [McpAdminController::class, 'statistics'])->name('admin.mcp.statistics');
    Route::get('/history', [McpAdminController::class, 'history'])->name('admin.mcp.history');
    Route::get('/health-check', [McpAdminController::class, 'healthCheck'])->name('admin.mcp.health-check');
    Route::get('/queue-monitor', [McpAdminController::class, 'queueMonitor'])->name('admin.mcp.queue-monitor');
    Route::get('/logs', [McpAdminController::class, 'logs'])->name('admin.mcp.logs');
    Route::get('/performance', [McpAdminController::class, 'performance'])->name('admin.mcp.performance');
    
    // Fonctionnalités MCP
    Route::match(['GET', 'POST'], '/title-reformulation', [McpAdminController::class, 'titleReformulation'])->name('admin.mcp.title-reformulation');
    Route::match(['GET', 'POST'], '/thesaurus-indexing', [McpAdminController::class, 'thesaurusIndexing'])->name('admin.mcp.thesaurus-indexing');
    Route::match(['GET', 'POST'], '/content-summary', [McpAdminController::class, 'contentSummary'])->name('admin.mcp.content-summary');
    Route::get('/batch-processing', [McpAdminController::class, 'batchProcessing'])->name('admin.mcp.batch-processing');
    
    // Configuration et administration
    Route::match(['GET', 'POST'], '/configuration', [McpAdminController::class, 'configuration'])->name('admin.mcp.configuration');
    Route::get('/models', [McpAdminController::class, 'models'])->name('admin.mcp.models');
    Route::get('/users', [McpAdminController::class, 'users'])->name('admin.mcp.users');
    Route::match(['GET', 'POST'], '/maintenance', [McpAdminController::class, 'maintenance'])->name('admin.mcp.maintenance');
    
    // Documentation
    Route::get('/documentation', [McpAdminController::class, 'documentation'])->name('admin.mcp.documentation');
    
    // Actions AJAX et API internes
    Route::post('/actions/test-connection', function () {
        try {
            $mcpManager = app(\App\Services\MCP\McpManagerService::class);
            $health = $mcpManager->healthCheck();
            return response()->json([
                'success' => $health['overall_status'] === 'ok',
                'status' => $health['overall_status'],
                'message' => $health['overall_status'] === 'ok' ? 'Connexion réussie' : 'Connexion échouée',
                'details' => $health
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    })->name('admin.mcp.actions.test-connection');
    
    Route::post('/actions/clear-cache', function () {
        try {
            \Illuminate\Support\Facades\Cache::flush();
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'Cache vidé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du nettoyage: ' . $e->getMessage()
            ], 500);
        }
    })->name('admin.mcp.actions.clear-cache');
    
    Route::get('/actions/system-info', function () {
        return response()->json([
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_usage' => [
                'current' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true),
                'limit' => ini_get('memory_limit')
            ],
            'disk_usage' => [
                'total' => disk_total_space('.'),
                'free' => disk_free_space('.')
            ],
            'server_time' => now()->format('Y-m-d H:i:s'),
            'timezone' => config('app.timezone')
        ]);
    })->name('admin.mcp.actions.system-info');
    
    Route::get('/actions/queue-stats', function () {
        try {
            $stats = [
                'mcp_light' => \Illuminate\Support\Facades\DB::table('jobs')->where('queue', 'mcp-light')->count(),
                'mcp_medium' => \Illuminate\Support\Facades\DB::table('jobs')->where('queue', 'mcp-medium')->count(),
                'mcp_heavy' => \Illuminate\Support\Facades\DB::table('jobs')->where('queue', 'mcp-heavy')->count(),
                'failed_jobs' => \Illuminate\Support\Facades\DB::table('failed_jobs')->count(),
                'total_pending' => \Illuminate\Support\Facades\DB::table('jobs')->where('queue', 'like', 'mcp%')->count()
            ];
            
            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('admin.mcp.actions.queue-stats');
    
    Route::post('/actions/restart-queues', function () {
        try {
            // Redémarrer les queues (ceci nécessiterait plus de logique en production)
            \Illuminate\Support\Facades\Artisan::call('queue:restart');
            
            return response()->json([
                'success' => true,
                'message' => 'Queues redémarrées avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du redémarrage: ' . $e->getMessage()
            ], 500);
        }
    })->name('admin.mcp.actions.restart-queues');
});

// Routes API pour les statistiques en temps réel (avec protection CSRF)
Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/api/mcp/stats', function () {
        try {
            $mcpManager = app(\App\Services\MCP\McpManagerService::class);
            $health = $mcpManager->healthCheck();
            
            return response()->json([
                'health' => $health,
                'stats' => [
                    'total_records' => \App\Models\Record::count(),
                    'thesaurus_concepts' => \App\Models\ThesaurusConcept::count(),
                    'pending_jobs' => \Illuminate\Support\Facades\DB::table('jobs')->where('queue', 'like', 'mcp%')->count(),
                    'processed_today' => \Illuminate\Support\Facades\Cache::get('mcp_processed_today', 0),
                    'success_rate' => \Illuminate\Support\Facades\Cache::get('mcp_success_rate', 100)
                ],
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('api.mcp.stats');
    
    Route::post('/api/mcp/cache/clear', function () {
        try {
            \Illuminate\Support\Facades\Cache::forget('mcp_*');
            return response()->json(['success' => true, 'message' => 'Cache MCP vidé']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('api.mcp.cache.clear');
});