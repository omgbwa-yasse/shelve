<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\McpController;

/*
|--------------------------------------------------------------------------
| Routes MCP (Model Context Protocol)
|--------------------------------------------------------------------------
|
| Routes dédiées aux fonctionnalités MCP avec Ollama
| pour le traitement automatique des archives selon ISAD(G)
|
*/

// Préfixe pour toutes les routes MCP
Route::prefix('api/mcp')->middleware(['api'])->group(function () {
    
    // Routes pour un record spécifique
    Route::prefix('records/{record}')->group(function () {
        
        // Traitement complet
        Route::post('/process', [McpController::class, 'processRecord'])
             ->name('mcp.records.process');
        
        // Prévisualisation
        Route::post('/preview', [McpController::class, 'previewProcessing'])
             ->name('mcp.records.preview');
        
        // Fonctionnalités individuelles
        Route::post('/title/reformulate', [McpController::class, 'reformulateTitle'])
             ->name('mcp.records.title.reformulate');
        
        Route::post('/title/preview', [McpController::class, 'previewTitleReformulation'])
             ->name('mcp.records.title.preview');
        
        Route::post('/thesaurus/index', [McpController::class, 'indexWithThesaurus'])
             ->name('mcp.records.thesaurus.index');
        
        Route::delete('/thesaurus/remove', [McpController::class, 'removeAutoIndexing'])
              ->name('mcp.records.thesaurus.remove');
        
        Route::post('/summary/generate', [McpController::class, 'generateSummary'])
             ->name('mcp.records.summary.generate');
        
        Route::post('/summary/preview', [McpController::class, 'previewSummary'])
             ->name('mcp.records.summary.preview');
        
        // Statut et informations
        Route::get('/status', [McpController::class, 'getProcessingStatus'])
             ->name('mcp.records.status');
    });
    
    // Traitement par lots
    Route::post('/batch/process', [McpController::class, 'batchProcess'])
         ->name('mcp.batch.process');
    
    // Système et monitoring
    Route::get('/health', [McpController::class, 'healthCheck'])
         ->name('mcp.health');
    
    Route::get('/stats', [McpController::class, 'getUsageStats'])
         ->name('mcp.stats');
});

// Routes web pour l'interface d'administration (optionnel)
Route::prefix('admin/mcp')->middleware(['web'])->group(function () {
    
    // Dashboard MCP
    Route::get('/', function () {
        return view('admin.mcp.dashboard');
    })->name('admin.mcp.dashboard');
    
    // Interface de traitement par lots
    Route::get('/batch', function () {
        return view('admin.mcp.batch');
    })->name('admin.mcp.batch');
    
    // Monitoring et logs
    Route::get('/monitoring', function () {
        return view('admin.mcp.monitoring');
    })->name('admin.mcp.monitoring');
    
    // Configuration
    Route::get('/config', function () {
        return view('admin.mcp.config');
    })->name('admin.mcp.config');
});