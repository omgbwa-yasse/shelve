@extends('layouts.app')

@section('title', 'Configuration MCP')

@push('styles')
<style>
    .config-section {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 1rem;
        overflow: hidden;
    }

    .config-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.75rem 1rem;
        border: none;
    }

    .config-body { padding: 1rem; }

    .form-floating label {
        color: #6c757d;
    }

    .badge-status {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .model-card {
        border: 2px solid #e9ecef;
        border-radius: 6px;
        padding: 0.75rem;
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
    }

    .model-card:hover {
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0,123,255,0.1);
    }

    .model-card.selected {
        border-color: #28a745;
        background-color: #f8fff8;
    }

    .test-connection {
        min-height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border-radius: 6px;
        border: 1px dashed #dee2e6;
    }

    .test-success {
        background: #d4edda !important;
        border-color: #c3e6cb !important;
        color: #155724;
    }

    .test-error {
        background: #f8d7da !important;
        border-color: #f5c6cb !important;
        color: #721c24;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 fw-bold">
                <i class="bi bi-sliders me-3 text-primary"></i>
                Configuration MCP
            </h1>
            <p class="text-muted mb-0">Paramètres et configuration du système Model Context Protocol</p>
        </div>
        <div>
            <a href="{{ route('admin.mcp.dashboard') }}" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-1"></i>Retour Dashboard
            </a>
            <button type="submit" form="configForm" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i>Sauvegarder
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Erreurs de validation :</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form id="configForm" method="POST" action="{{ route('admin.mcp.configuration') }}">
        @csrf
        
        <!-- Provider IA global -->
        <div class="config-section">
            <div class="config-header">
                <h4 class="mb-0">
                    <i class="bi bi-cpu me-2"></i>Provider IA
                </h4>
            </div>
            <div class="config-body">
                <div class="row g-3 align-items-center">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Sélection du provider</label>
                        @php
                            $currentProvider = app(\App\Services\SettingService::class)->get('ai_default_provider', 'ollama');
                        @endphp
                        <div class="btn-group" role="group" aria-label="Choix du provider">
                            <input type="radio" class="btn-check" name="ai_default_provider" id="provider-ollama" value="ollama" {{ $currentProvider === 'ollama' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary" for="provider-ollama"><i class="bi bi-robot"></i> Ollama (MCP)</label>

                            <input type="radio" class="btn-check" name="ai_default_provider" id="provider-mistral" value="mistral" {{ $currentProvider === 'mistral' ? 'checked' : '' }}>
                            <label class="btn btn-outline-warning" for="provider-mistral"><i class="bi bi-stars"></i> Mistral (Test)
                            </label>
                        </div>
                        <div class="form-text">Ce choix s'applique à toute l'application (Records, traitements MCP, etc.).</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Test de connexion -->
        <div class="config-section">
            <div class="config-header">
                <h4 class="mb-0">
                    <i class="bi bi-wifi me-2"></i>Test de Connexion Ollama
                </h4>
            </div>
            <div class="config-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="test-connection" id="connectionTest">
                            <div class="text-center">
                                <i class="bi bi-cloud text-muted" style="font-size: 2rem;"></i>
                                <p class="mb-2 text-muted">Cliquez pour tester la connexion à Ollama</p>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="testOllamaConnection()">
                                    <i class="bi bi-play me-1"></i>Tester la Connexion
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="fw-bold mb-3">État du Système</h6>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-secondary me-2">URL</span>
                            <code>{{ $config['ollama_url'] }}</code>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-info me-2">Status</span>
                            <span id="connectionStatus">Non testé</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-warning me-2">Latence</span>
                            <span id="connectionLatency">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration Ollama -->
        <div class="config-section">
            <div class="config-header">
                <h4 class="mb-0">
                    <i class="bi bi-server me-2"></i>Configuration Ollama
                </h4>
            </div>
            <div class="config-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="url" class="form-control" id="ollama_url" name="ollama_url" 
                                   value="{{ old('ollama_url', $config['ollama_url']) }}" required>
                            <label for="ollama_url">URL du serveur Ollama</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating mb-3">
                            <input type="number" class="form-control" id="temperature" name="temperature" 
                                   value="{{ old('temperature', $config['options']['temperature'] ?? 0.7) }}" 
                                   min="0" max="2" step="0.1">
                            <label for="temperature">Température (créativité)</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating mb-3">
                            <input type="number" class="form-control" id="max_tokens" name="max_tokens" 
                                   value="{{ old('max_tokens', $config['options']['num_predict'] ?? 1000) }}" 
                                   min="100" max="4000">
                            <label for="max_tokens">Tokens maximum</label>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="auto_processing" name="auto_processing" 
                                   {{ old('auto_processing', $config['auto_processing']['enabled'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_processing">
                                <strong>Traitement automatique</strong>
                                <br><small class="text-muted">Traiter automatiquement les nouveaux records</small>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="cache_enabled" name="cache_enabled" 
                                   {{ old('cache_enabled', $config['performance']['cache_enabled'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="cache_enabled">
                                <strong>Cache activé</strong>
                                <br><small class="text-muted">Mettre en cache les résultats pour améliorer les performances</small>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="debug_mode" name="debug_mode" 
                                   {{ old('debug_mode', false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="debug_mode">
                                <strong>Mode debug</strong>
                                <br><small class="text-muted">Logs détaillés pour le débogage</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration des Modèles -->
        <div class="config-section">
            <div class="config-header">
                <h4 class="mb-0">
                    <i class="bi bi-cpu me-2"></i>Modèles par Fonctionnalité
                </h4>
            </div>
            <div class="config-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-magic text-primary me-2"></i>Reformulation de Titre
                        </h6>
                        <select class="form-select mb-3" name="title_model" id="title_model">
                            @foreach($models as $modelId => $modelInfo)
                                <option value="{{ $modelId }}" 
                                        {{ old('title_model', $config['models']['title'] ?? 'gemma3:4b') === $modelId ? 'selected' : '' }}>
                                    {{ $modelInfo['name'] ?? $modelId }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Modèle utilisé pour reformuler les titres selon ISAD(G)</small>
                    </div>
                    
                    <div class="col-md-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-tags text-success me-2"></i>Indexation Thésaurus
                        </h6>
                        <select class="form-select mb-3" name="thesaurus_model" id="thesaurus_model">
                            @foreach($models as $modelId => $modelInfo)
                                <option value="{{ $modelId }}" 
                                        {{ old('thesaurus_model', $config['models']['thesaurus'] ?? 'gemma3:4b') === $modelId ? 'selected' : '' }}>
                                    {{ $modelInfo['name'] ?? $modelId }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Modèle utilisé pour l'extraction de mots-clés</small>
                    </div>
                    
                    <div class="col-md-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-file-text text-info me-2"></i>Résumé ISAD(G)
                        </h6>
                        <select class="form-select mb-3" name="summary_model" id="summary_model">
                            @foreach($models as $modelId => $modelInfo)
                                <option value="{{ $modelId }}" 
                                        {{ old('summary_model', $config['models']['summary'] ?? 'gemma3:4b') === $modelId ? 'selected' : '' }}>
                                    {{ $modelInfo['name'] ?? $modelId }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Modèle utilisé pour générer les résumés</small>
                    </div>
                </div>

                <!-- Informations sur les modèles -->
                <div class="mt-4">
                    <h6 class="fw-bold mb-3">Modèles Disponibles</h6>
                    <div class="row">
                        @foreach($models as $modelId => $modelInfo)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="model-card">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-1">{{ $modelInfo['name'] ?? $modelId }}</h6>
                                        <span class="badge bg-primary">{{ $modelInfo['size'] ?? 'N/A' }}</span>
                                    </div>
                                    <p class="text-muted small mb-2">{{ $modelInfo['description'] ?? 'Modèle de langage' }}</p>
                                    @if(isset($modelInfo['recommended_for']))
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($modelInfo['recommended_for'] as $feature)
                                                <span class="badge bg-light text-dark">{{ $feature }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration Avancée -->
        <div class="config-section">
            <div class="config-header">
                <h4 class="mb-0">
                    <i class="bi bi-gear-wide-connected me-2"></i>Configuration Avancée
                </h4>
            </div>
            <div class="config-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-floating mb-3">
                            <input type="number" class="form-control" id="retry_attempts" name="retry_attempts" 
                                   value="{{ old('retry_attempts', $config['performance']['retry_attempts'] ?? 3) }}" 
                                   min="1" max="10">
                            <label for="retry_attempts">Tentatives de retry</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating mb-3">
                            <input type="number" class="form-control" id="timeout" name="timeout" 
                                   value="{{ old('timeout', $config['performance']['timeout'] ?? 300) }}" 
                                   min="30" max="600">
                            <label for="timeout">Timeout (secondes)</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating mb-3">
                            <input type="number" class="form-control" id="batch_size" name="batch_size" 
                                   value="{{ old('batch_size', $config['performance']['batch_size'] ?? 10) }}" 
                                   min="1" max="100">
                            <label for="batch_size">Taille des lots</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating mb-3">
                            <input type="number" class="form-control" id="cache_ttl" name="cache_ttl" 
                                   value="{{ old('cache_ttl', $config['performance']['cache_ttl'] ?? 3600) }}" 
                                   min="300" max="86400">
                            <label for="cache_ttl">TTL Cache (secondes)</label>
                        </div>
                    </div>
                </div>
                
                <!-- Alertes et recommandations -->
                <div class="alert alert-info">
                    <h6 class="alert-heading">
                        <i class="bi bi-lightbulb me-2"></i>Recommandations
                    </h6>
                    <ul class="mb-0">
                        <li><strong>Température :</strong> 0.3-0.5 pour les reformulations, 0.7-1.0 pour les résumés créatifs</li>
                        <li><strong>Tokens :</strong> 500-1000 pour les titres, 1000-2000 pour les résumés</li>
                        <li><strong>Cache :</strong> Activez le cache pour améliorer les performances sur les gros volumes</li>
                        <li><strong>Traitement automatique :</strong> À utiliser avec précaution sur de gros datasets</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Test automatique de la connexion au chargement
    setTimeout(testOllamaConnection, 1000);
});

function testOllamaConnection() {
    const testDiv = document.getElementById('connectionTest');
    const statusSpan = document.getElementById('connectionStatus');
    const latencySpan = document.getElementById('connectionLatency');
    
    // Reset UI
    testDiv.className = 'test-connection';
    testDiv.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 mb-0">Test de connexion en cours...</p></div>';
    statusSpan.textContent = 'Test en cours...';
    latencySpan.textContent = '-';
    
    const startTime = Date.now();
    
    fetch('/admin/mcp/actions/test-connection', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        const endTime = Date.now();
        const latency = endTime - startTime;
        
        if (data.success) {
            testDiv.className = 'test-connection test-success';
            testDiv.innerHTML = `
                <div class="text-center">
                    <i class="bi bi-check-circle-fill" style="font-size: 2rem;"></i>
                    <p class="mb-2 fw-bold">Connexion réussie !</p>
                    <small>Ollama est accessible et opérationnel</small>
                </div>
            `;
            statusSpan.innerHTML = '<span class="badge bg-success">En ligne</span>';
        } else {
            testDiv.className = 'test-connection test-error';
            testDiv.innerHTML = `
                <div class="text-center">
                    <i class="bi bi-x-circle-fill" style="font-size: 2rem;"></i>
                    <p class="mb-2 fw-bold">Connexion échouée</p>
                    <small>${data.message || 'Impossible de se connecter à Ollama'}</small>
                </div>
            `;
            statusSpan.innerHTML = '<span class="badge bg-danger">Hors ligne</span>';
        }
        
        latencySpan.textContent = latency + 'ms';
    })
    .catch(error => {
        testDiv.className = 'test-connection test-error';
        testDiv.innerHTML = `
            <div class="text-center">
                <i class="bi bi-exclamation-triangle-fill" style="font-size: 2rem;"></i>
                <p class="mb-2 fw-bold">Erreur de connexion</p>
                <small>Vérifiez qu'Ollama est démarré (ollama serve)</small>
            </div>
        `;
        statusSpan.innerHTML = '<span class="badge bg-danger">Erreur</span>';
        latencySpan.textContent = 'N/A';
    });
}

// Validation du formulaire
document.getElementById('configForm').addEventListener('submit', function(e) {
    const ollamaUrl = document.getElementById('ollama_url').value;
    const temperature = parseFloat(document.getElementById('temperature').value);
    const maxTokens = parseInt(document.getElementById('max_tokens').value);
    
    if (!ollamaUrl) {
        e.preventDefault();
        alert('Veuillez spécifier l\'URL d\'Ollama');
        return;
    }
    
    if (temperature < 0 || temperature > 2) {
        e.preventDefault();
        alert('La température doit être entre 0 et 2');
        return;
    }
    
    if (maxTokens < 100 || maxTokens > 4000) {
        e.preventDefault();
        alert('Le nombre de tokens doit être entre 100 et 4000');
        return;
    }
    
    // Confirmation pour les changements sensibles
    if (temperature > 1.5) {
        if (!confirm('Une température élevée (>1.5) peut produire des résultats imprévisibles. Continuer ?')) {
            e.preventDefault();
            return;
        }
    }
});

// Mise à jour dynamique des recommandations
document.getElementById('temperature').addEventListener('input', function() {
    const value = parseFloat(this.value);
    const feedback = document.getElementById('temperature-feedback') || createFeedback(this);
    
    if (value <= 0.3) {
        feedback.textContent = 'Très conservateur - résultats cohérents mais peu créatifs';
        feedback.className = 'form-text text-info';
    } else if (value <= 0.7) {
        feedback.textContent = 'Équilibré - bon compromis créativité/cohérence';
        feedback.className = 'form-text text-success';
    } else if (value <= 1.2) {
        feedback.textContent = 'Créatif - résultats plus variés et originaux';
        feedback.className = 'form-text text-warning';
    } else {
        feedback.textContent = 'Très créatif - peut produire des résultats imprévisibles';
        feedback.className = 'form-text text-danger';
    }
});

function createFeedback(element) {
    const feedback = document.createElement('div');
    feedback.id = element.id + '-feedback';
    element.parentNode.appendChild(feedback);
    return feedback;
}
</script>
@endpush