@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 fw-bold">
            <i class="bi bi-stars text-warning me-3"></i>
            Dashboard Test Mistral
        </h1>
        <div class="btn-group">
            <a href="/admin/mcp" class="btn btn-outline-primary">
                <i class="bi bi-robot me-2"></i>Dashboard MCP
            </a>
            <a href="/admin/mistral-test/compare" class="btn btn-outline-secondary">
                <i class="bi bi-bar-chart me-2"></i>Comparer
            </a>
        </div>
    </div>

    {{-- Statut de l'API Mistral --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning bg-opacity-10">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle text-warning me-2"></i>
                        Statut de l'API Mistral
                    </h5>
                </div>
                <div class="card-body">
                    <div id="mistral-status" class="d-flex align-items-center">
                        <div class="spinner-border spinner-border-sm text-warning me-2" role="status"></div>
                        <span>Vérification du statut...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Instructions d'utilisation --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-book text-primary me-2"></i>
                        Comment utiliser le test Mistral
                    </h5>
                </div>
                <div class="card-body">
                    <ol class="list-unstyled">
                        <li class="mb-3">
                            <span class="badge bg-primary me-2">1</span>
                            <strong>Accédez aux vues records</strong><br>
                            <small class="text-muted">Allez sur la liste des records ou la vue détaillée d'un record</small>
                        </li>
                        <li class="mb-3">
                            <span class="badge bg-primary me-2">2</span>
                            <strong>Sélectionnez le mode Mistral</strong><br>
                            <small class="text-muted">Utilisez le sélecteur "Mode IA" et choisissez "Mistral"</small>
                        </li>
                        <li class="mb-3">
                            <span class="badge bg-primary me-2">3</span>
                            <strong>Testez les fonctionnalités</strong><br>
                            <small class="text-muted">Cliquez sur les boutons de reformulation, indexation ou résumé</small>
                        </li>
                        <li class="mb-0">
                            <span class="badge bg-primary me-2">4</span>
                            <strong>Comparez les résultats</strong><br>
                            <small class="text-muted">Utilisez la page de comparaison pour analyser les différences</small>
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-gear text-secondary me-2"></i>
                        Configuration
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Configuration Mistral :</label>
                        <div class="small text-muted">
                            <div><strong>Mode simulation :</strong> <span id="mock-mode">Vérification...</span></div>
                            <div><strong>Clé API :</strong> <span id="api-key-status">Vérification...</span></div>
                            <div><strong>Package installé :</strong> <span id="package-status">Vérification...</span></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Modèles configurés :</label>
                        <div class="small text-muted">
                            <div>• Titre : <code>{{ config('mistral.models.title_reformulation', 'mistral-medium-latest') }}</code></div>
                            <div>• Thésaurus : <code>{{ config('mistral.models.thesaurus_indexing', 'mistral-large-latest') }}</code></div>
                            <div>• Résumé : <code>{{ config('mistral.models.summary_generation', 'mistral-large-latest') }}</code></div>
                        </div>
                    </div>

                    @if(config('mistral.test_mode.enabled'))
                        <div class="alert alert-warning alert-sm">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Mode test activé
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Liens rapides --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-link text-info me-2"></i>
                        Liens rapides
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('records.index') }}?mode=mistral" class="btn btn-outline-warning w-100">
                                <i class="bi bi-list-ul me-2"></i>
                                Liste records (Mistral)
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('records.index') }}?mode=mcp" class="btn btn-outline-primary w-100">
                                <i class="bi bi-list-ul me-2"></i>
                                Liste records (MCP)
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-info w-100" onclick="testMistralConnection()">
                                <i class="bi bi-wifi me-2"></i>
                                Test connexion
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="/admin/mistral-test/compare" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-bar-chart me-2"></i>
                                Comparaison
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Scripts pour les tests --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    checkMistralStatus();
});

function checkMistralStatus() {
    fetch('/api/mistral-test/health')
        .then(response => response.json())
        .then(data => {
            const statusElement = document.getElementById('mistral-status');
            const mockModeElement = document.getElementById('mock-mode');
            const apiKeyElement = document.getElementById('api-key-status');
            const packageElement = document.getElementById('package-status');
            
            if (data.status === 'healthy') {
                statusElement.innerHTML = `
                    <i class="bi bi-check-circle text-success me-2"></i>
                    <span class="text-success">API Mistral opérationnelle</span>
                `;
                
                mockModeElement.innerHTML = data.mock_mode ? 
                    '<span class="badge bg-warning">Simulation</span>' : 
                    '<span class="badge bg-success">API réelle</span>';
                
                apiKeyElement.innerHTML = data.mock_mode ? 
                    '<span class="text-warning">Non configurée</span>' : 
                    '<span class="text-success">Configurée</span>';
                
                packageElement.innerHTML = data.mock_mode ? 
                    '<span class="text-warning">Non installé</span>' : 
                    '<span class="text-success">Installé</span>';
                    
            } else {
                statusElement.innerHTML = `
                    <i class="bi bi-x-circle text-danger me-2"></i>
                    <span class="text-danger">Erreur : ${data.error}</span>
                `;
                
                mockModeElement.innerHTML = '<span class="badge bg-danger">Erreur</span>';
                apiKeyElement.innerHTML = '<span class="text-danger">Erreur</span>';
                packageElement.innerHTML = '<span class="text-danger">Erreur</span>';
            }
        })
        .catch(error => {
            document.getElementById('mistral-status').innerHTML = `
                <i class="bi bi-x-circle text-danger me-2"></i>
                <span class="text-danger">Impossible de contacter l'API</span>
            `;
            
            document.getElementById('mock-mode').innerHTML = '<span class="badge bg-danger">Erreur</span>';
            document.getElementById('api-key-status').innerHTML = '<span class="text-danger">Erreur</span>';
            document.getElementById('package-status').innerHTML = '<span class="text-danger">Erreur</span>';
        });
}

function testMistralConnection() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Test en cours...';
    button.disabled = true;
    
    fetch('/api/mistral-test/health')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'healthy') {
                showToast('Test réussi', 'Connexion Mistral fonctionnelle', 'success');
            } else {
                showToast('Test échoué', data.error, 'error');
            }
        })
        .catch(error => {
            showToast('Erreur', 'Impossible de tester la connexion', 'error');
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
}

function showToast(title, message, type = 'info') {
    // Créer une notification toast simple
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';
    
    const toastHtml = `
        <div id="${toastId}" class="toast ${bgClass} text-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header ${bgClass} text-white border-0">
                <i class="bi bi-stars me-2"></i>
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    const toast = new bootstrap.Toast(document.getElementById(toastId));
    toast.show();
    
    // Supprimer le toast après fermeture
    document.getElementById(toastId).addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '1055';
    document.body.appendChild(container);
    return container;
}
</script>
@endsection