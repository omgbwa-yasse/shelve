@extends('layouts.app')

@section('title', 'Dashboard MCP')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">
                <i class="fas fa-robot"></i> 
                Dashboard MCP (Model Context Protocol)
            </h2>
        </div>
    </div>

    <!-- Statut du Système -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-heartbeat"></i> État de Santé du Système</h5>
                </div>
                <div class="card-body">
                    <div id="health-status">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="sr-only">Vérification...</span>
                            </div>
                            <p class="mt-2">Vérification de l'état de santé...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Métriques Rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Records Traités</h6>
                            <h3 id="processed-count">-</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Jobs en Queue</h6>
                            <h3 id="queue-count">-</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-tasks fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Erreurs (24h)</h6>
                            <h3 id="error-count">-</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Concepts Indexés</h6>
                            <h3 id="concepts-count">-</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-tags fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-play"></i> Actions Rapides</h5>
                </div>
                <div class="card-body">
                    <form id="quick-process-form">
                        <div class="form-group">
                            <label for="record-id">Record ID:</label>
                            <input type="number" class="form-control" id="record-id" placeholder="ex: 123" required>
                        </div>
                        <div class="form-group">
                            <label>Fonctionnalités:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="title" id="feat-title">
                                <label class="form-check-label" for="feat-title">
                                    Reformulation Titre
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="thesaurus" id="feat-thesaurus" checked>
                                <label class="form-check-label" for="feat-thesaurus">
                                    Indexation Thésaurus
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="summary" id="feat-summary">
                                <label class="form-check-label" for="feat-summary">
                                    Résumé ISAD(G)
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="preview-mode" checked>
                                <label class="form-check-label" for="preview-mode">
                                    Mode prévisualisation (sans sauvegarder)
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-cog"></i> Traiter
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-layer-group"></i> Traitement par Lots</h5>
                </div>
                <div class="card-body">
                    <form id="batch-process-form">
                        <div class="form-group">
                            <label for="batch-limit">Nombre de records:</label>
                            <input type="number" class="form-control" id="batch-limit" value="10" min="1" max="100">
                        </div>
                        <div class="form-group">
                            <label for="batch-feature">Fonctionnalité:</label>
                            <select class="form-control" id="batch-feature">
                                <option value="thesaurus">Indexation Thésaurus</option>
                                <option value="title">Reformulation Titre</option>
                                <option value="summary">Résumé ISAD(G)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="batch-async" checked>
                                <label class="form-check-label" for="batch-async">
                                    Traitement asynchrone (recommandé)
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-layers"></i> Lancer le Lot
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Résultats -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list"></i> Résultats</h5>
                </div>
                <div class="card-body">
                    <div id="results-container">
                        <p class="text-muted text-center">Aucun résultat à afficher pour le moment.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Charger l'état de santé au démarrage
    loadHealthStatus();
    
    // Actualiser toutes les 30 secondes
    setInterval(loadHealthStatus, 30000);
    
    // Gestionnaires de formulaires
    document.getElementById('quick-process-form').addEventListener('submit', handleQuickProcess);
    document.getElementById('batch-process-form').addEventListener('submit', handleBatchProcess);
});

function loadHealthStatus() {
    fetch('/api/mcp/health')
        .then(response => response.json())
        .then(data => displayHealthStatus(data))
        .catch(error => displayHealthError(error));
}

function displayHealthStatus(health) {
    const container = document.getElementById('health-status');
    const overallStatus = health.overall_status;
    
    let html = `
        <div class="row">
            <div class="col-md-4">
                <div class="alert ${overallStatus === 'ok' ? 'alert-success' : 'alert-danger'}">
                    <h6><i class="fas ${overallStatus === 'ok' ? 'fa-check-circle' : 'fa-times-circle'}"></i> 
                        Statut Global: ${overallStatus.toUpperCase()}</h6>
                </div>
            </div>
            <div class="col-md-8">
                <div class="row">
    `;
    
    Object.entries(health.components || {}).forEach(([component, status]) => {
        const statusClass = status.status === 'ok' ? 'success' : 'danger';
        const icon = status.status === 'ok' ? 'check' : 'times';
        
        html += `
            <div class="col-md-6 mb-2">
                <span class="badge badge-${statusClass}">
                    <i class="fas fa-${icon}"></i> ${component}
                </span>
                ${status.response_time ? `<small class="ml-2">${status.response_time}s</small>` : ''}
            </div>
        `;
    });
    
    html += '</div></div></div>';
    container.innerHTML = html;
}

function displayHealthError(error) {
    document.getElementById('health-status').innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> 
            Impossible de vérifier l'état de santé: ${error.message}
        </div>
    `;
}

function handleQuickProcess(event) {
    event.preventDefault();
    
    const recordId = document.getElementById('record-id').value;
    const features = Array.from(document.querySelectorAll('input[type="checkbox"][value]:checked'))
        .map(cb => cb.value);
    const isPreview = document.getElementById('preview-mode').checked;
    
    if (features.length === 0) {
        alert('Veuillez sélectionner au moins une fonctionnalité.');
        return;
    }
    
    const url = isPreview 
        ? `/api/mcp/records/${recordId}/preview`
        : `/api/mcp/records/${recordId}/process`;
    
    const data = { features: features };
    
    showLoading('Traitement en cours...');
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => displayResults(data, isPreview))
    .catch(error => displayError(error));
}

function handleBatchProcess(event) {
    event.preventDefault();
    
    const limit = document.getElementById('batch-limit').value;
    const feature = document.getElementById('batch-feature').value;
    const async = document.getElementById('batch-async').checked;
    
    // Pour cet exemple, on traite les premiers records disponibles
    // En production, vous pourriez avoir des filtres plus avancés
    
    showLoading('Lancement du traitement par lots...');
    
    // Simuler l'appel API - en réalité, vous devriez récupérer les IDs des records
    alert(`Fonctionnalité à implémenter: traitement par lots de ${limit} records avec la fonctionnalité "${feature}" en mode ${async ? 'asynchrone' : 'synchrone'}`);
}

function showLoading(message) {
    document.getElementById('results-container').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="sr-only">Chargement...</span>
            </div>
            <p class="mt-2">${message}</p>
        </div>
    `;
}

function displayResults(data, isPreview) {
    const container = document.getElementById('results-container');
    
    if (data.error) {
        container.innerHTML = `
            <div class="alert alert-danger">
                <h6><i class="fas fa-exclamation-triangle"></i> Erreur</h6>
                <p>${data.message}</p>
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="alert alert-success">
            <h6><i class="fas fa-check-circle"></i> ${data.message}</h6>
        </div>
    `;
    
    if (isPreview && data.previews) {
        html += '<h6>Aperçus:</h6>';
        Object.entries(data.previews).forEach(([feature, preview]) => {
            html += `
                <div class="card mb-2">
                    <div class="card-header">
                        <strong>${feature.charAt(0).toUpperCase() + feature.slice(1)}</strong>
                    </div>
                    <div class="card-body">
                        ${formatPreview(feature, preview)}
                    </div>
                </div>
            `;
        });
    } else if (data.results) {
        html += '<h6>Résultats:</h6>';
        Object.entries(data.results).forEach(([feature, result]) => {
            html += `
                <div class="card mb-2">
                    <div class="card-header">
                        <strong>${feature.charAt(0).toUpperCase() + feature.slice(1)}</strong>
                    </div>
                    <div class="card-body">
                        ${formatResult(feature, result)}
                    </div>
                </div>
            `;
        });
    }
    
    container.innerHTML = html;
}

function formatPreview(feature, preview) {
    switch(feature) {
        case 'title':
            return `
                <p><strong>Original:</strong> ${preview.original_title || 'N/A'}</p>
                <p><strong>Suggéré:</strong> <em>${preview.suggested_title || 'N/A'}</em></p>
            `;
        case 'summary':
            return `
                <p><strong>Original:</strong> ${(preview.original_content || 'N/A').substring(0, 100)}...</p>
                <p><strong>Suggéré:</strong> <em>${(preview.suggested_summary || 'N/A').substring(0, 100)}...</em></p>
            `;
        default:
            return '<pre>' + JSON.stringify(preview, null, 2) + '</pre>';
    }
}

function formatResult(feature, result) {
    switch(feature) {
        case 'title':
            return `<p><strong>Nouveau titre:</strong> ${result}</p>`;
        case 'thesaurus':
            return `
                <p><strong>Mots-clés extraits:</strong> ${result.keywords_extracted?.length || 0}</p>
                <p><strong>Concepts trouvés:</strong> ${result.concepts_found || 0}</p>
            `;
        case 'summary':
            return `<p><strong>Résumé généré:</strong> ${result.substring(0, 200)}...</p>`;
        default:
            return '<pre>' + JSON.stringify(result, null, 2) + '</pre>';
    }
}

function displayError(error) {
    document.getElementById('results-container').innerHTML = `
        <div class="alert alert-danger">
            <h6><i class="fas fa-exclamation-triangle"></i> Erreur</h6>
            <p>${error.message || 'Une erreur est survenue'}</p>
        </div>
    `;
}
</script>
@endsection