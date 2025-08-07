<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MistralTestController;

/*
|--------------------------------------------------------------------------
| Routes Mistral Test
|--------------------------------------------------------------------------
|
| Routes de test pour les fonctionnalités MCP avec l'API Mistral
| Ces routes permettent de tester l'intégration Mistral avant
| de remplacer les services MCP Ollama existants.
|
*/

// Préfixe pour toutes les routes de test Mistral
Route::prefix('api/mistral-test')->middleware(['api'])->group(function () {
    
    // Routes pour un record spécifique
    Route::prefix('records/{record}')->group(function () {
        
        // Traitement complet
        Route::post('/process', [MistralTestController::class, 'processRecord'])
             ->name('mistral-test.records.process');
        
        // Prévisualisation
        Route::post('/preview', [MistralTestController::class, 'previewProcessing'])
             ->name('mistral-test.records.preview');
        
        // Fonctionnalités individuelles - Titre
        Route::post('/title/reformulate', [MistralTestController::class, 'reformulateTitle'])
             ->name('mistral-test.records.title.reformulate');
        
        Route::post('/title/preview', [MistralTestController::class, 'previewTitleReformulation'])
             ->name('mistral-test.records.title.preview');
        
        // Fonctionnalités individuelles - Thésaurus
        Route::post('/thesaurus/index', [MistralTestController::class, 'indexWithThesaurus'])
             ->name('mistral-test.records.thesaurus.index');
        
        // Fonctionnalités individuelles - Résumé
        Route::post('/summary/generate', [MistralTestController::class, 'generateSummary'])
             ->name('mistral-test.records.summary.generate');
        
        Route::post('/summary/preview', [MistralTestController::class, 'previewSummary'])
             ->name('mistral-test.records.summary.preview');
    });
    
    // Système et monitoring
    Route::get('/health', [MistralTestController::class, 'healthCheck'])
         ->name('mistral-test.health');
});

// Routes web pour l'interface de test Mistral (optionnel)
Route::prefix('admin/mistral-test')->middleware(['web'])->group(function () {
    
    // Dashboard de test Mistral
    Route::get('/', function () {
        return view('admin.mistral-test.dashboard', [
            'title' => 'Test Mistral API',
            'description' => 'Interface de test pour l\'intégration Mistral avec les fonctionnalités MCP'
        ]);
    })->name('admin.mistral-test.dashboard');
    
    // Interface de comparaison MCP vs Mistral
    Route::get('/compare', function () {
        return view('admin.mistral-test.compare', [
            'title' => 'Comparaison MCP vs Mistral',
            'description' => 'Comparaison des résultats entre les services MCP Ollama et Mistral API'
        ]);
    })->name('admin.mistral-test.compare');
});