@extends('layouts.app')

@section('title', 'État de Santé MCP')

@push('styles')
<style>
    .health-overview {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 1rem 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .component-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 1rem;
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .component-card:hover {
        transform: translateY(-2px);
    }

    .component-header {
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #e9ecef;
    }

    .component-body { padding: 1rem; }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .status-ok {
        background: linear-gradient(45deg, #28a745, #20c997);
        color: white;
    }

    .status-warning {
        background: linear-gradient(45deg, #ffc107, #fd7e14);
        color: white;
    }

    .status-error {
        background: linear-gradient(45deg, #dc3545, #e83e8c);
        color: white;
    }

    .status-unknown {
        background: linear-gradient(45deg, #6c757d, #495057);
        color: white;
    }

    .metric-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .metric-item { text-align: center; padding: 0.75rem;
        background: #f8f9fa;
        border-radius: 6px;
        border-left: 4px solid #007bff;
    }

    .metric-value { font-size: 1.25rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.1rem; }

    .metric-label {
        color: #6c757d;
        font-size: 0.875rem;
    }

    .recommendation-card {
        border-left: 4px solid #17a2b8;
        background: #f0f9ff;
        padding: 0.75rem;
        margin-bottom: 0.75rem;
        border-radius: 0 6px 6px 0;
    }

    .recommendation-card.error {
        border-left-color: #dc3545;
        background: #fff5f5;
    }

    .recommendation-card.warning {
        border-left-color: #ffc107;
        background: #fffbf0;
    }

    .recommendation-card.success {
        border-left-color: #28a745;
        background: #f0fff4;
    }

    .system-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #e9ecef;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .pulse {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }

    .log-viewer {
        background: #1e1e1e;
        color: #ffffff;
        border-radius: 8px;
        padding: 1rem;
        font-family: 'Courier New', monospace;
        font-size: 0.875rem;
        max-height: 400px;
        overflow-y: auto;
        white-space: pre-wrap;
    }

    .log-level-error { color: #ff6b6b; }
    .log-level-warning { color: #feca57; }
    .log-level-info { color: #48cae4; }
    .log-level-debug { color: #95e1d3; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 fw-bold">
                <i class="bi bi-heart-pulse me-3 text-danger"></i>
                État de Santé MCP
            </h1>
            <p class="text-muted mb-0">Surveillance en temps réel du système Model Context Protocol</p>
        </div>
        <div>
            <a href="{{ route('admin.mcp.dashboard') }}" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-1"></i>Retour Dashboard
            </a>
            <button class="btn btn-primary" onclick="refreshHealthCheck()">
                <i class="bi bi-arrow-clockwise me-1"></i>Actualiser
            </button>
        </div>
    </div>

    <!-- Vue d'ensemble de la santé -->
    <div class="health-overview">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2">
                    <i class="bi bi-shield-check me-3"></i>
                    Statut Global du Système
                </h2>
                <p class="mb-0 opacity-90" id="globalStatusText">
                    Vérification en cours...
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="d-flex flex-column align-items-end">
                    <div class="mb-2">
                        <span class="status-badge" id="globalStatusBadge">
                            <i class="bi bi-hourglass-split pulse me-1"></i>Vérification
                        </span>
                    </div>
                    <small class="opacity-75">Dernière vérification: <span id="lastCheckTime">{{ now()->format('H:i:s') }}</span></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Métriques de performance -->
    <div class="metric-grid">
        <div class="metric-item">
            <div class="metric-value" id="responseTime">-</div>
            <div class="metric-label">Temps de réponse (ms)</div>
        </div>
        <div class="metric-item">
            <div class="metric-value" id="memoryUsage">-</div>
            <div class="metric-label">Utilisation mémoire</div>
        </div>
        <div class="metric-item">
            <div class="metric-value" id="diskSpace">-</div>
            <div class="metric-label">Espace disque libre</div>
        </div>
        <div class="metric-item">
            <div class="metric-value" id="activeConnections">-</div>
            <div class="metric-label">Connexions actives</div>
        </div>
    </div>

    <!-- Composants du système -->
    <div class="row">
        <div class="col-md-8">
            <h3 class="mb-4">Composants du Système</h3>

            <!-- Composant dynamique généré par JS -->
            <div id="componentsContainer">
                <!-- Les composants seront injectés ici par JavaScript -->
            </div>
        </div>

        <div class="col-md-4">
            <!-- Recommandations -->
            <h3 class="mb-4">Recommandations</h3>
            <div id="recommendationsContainer">
                @if(isset($recommendations) && !empty($recommendations))
                    @foreach($recommendations as $recommendation)
                        <div class="recommendation-card {{ $recommendation['type'] }}">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-{{ $recommendation['type'] === 'error' ? 'exclamation-triangle' : ($recommendation['type'] === 'warning' ? 'exclamation-circle' : 'lightbulb') }} me-2 mt-1"></i>
                                <div>
                                    <strong>{{ $recommendation['title'] }}</strong>
                                    <p class="mb-2 mt-1">{{ $recommendation['message'] }}</p>
                                    @if($recommendation['action'])
                                        <button class="btn btn-sm btn-outline-primary">
                                            {{ $recommendation['action'] }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="recommendation-card success">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Système optimal</strong>
                        </div>
                        <p class="mb-0 mt-1">Tous les composants fonctionnent correctement.</p>
                    </div>
                @endif
            </div>

            <!-- Informations système -->
            <h3 class="mb-4 mt-4">Informations Système</h3>
            <div class="component-card">
                <div class="component-body">
                    <div class="system-info-grid">
                        <div class="info-item">
                            <span>Version PHP</span>
                            <code>{{ $systemInfo['php_version'] ?? 'N/A' }}</code>
                        </div>
                        <div class="info-item">
                            <span>Version Laravel</span>
                            <code>{{ $systemInfo['laravel_version'] ?? 'N/A' }}</code>
                        </div>
                        <div class="info-item">
                            <span>Version Ollama</span>
                            <code id="ollamaVersion">{{ $systemInfo['ollama_version'] ?? 'N/A' }}</code>
                        </div>
                        <div class="info-item">
                            <span>Limite mémoire</span>
                            <code>{{ $systemInfo['memory_limit'] ?? 'N/A' }}</code>
                        </div>
                        <div class="info-item">
                            <span>Temps d'exécution max</span>
                            <code>{{ $systemInfo['max_execution_time'] ?? 'N/A' }}s</code>
                        </div>
                        <div class="info-item">
                            <span>Timezone</span>
                            <code>{{ config('app.timezone') }}</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs système récents -->
    <div class="mt-5">
        <h3 class="mb-4">
            <i class="bi bi-journal-text me-2"></i>
            Logs Récents
            <button class="btn btn-outline-secondary btn-sm ms-2" onclick="refreshLogs()">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </h3>
        <div class="component-card">
            <div class="component-body">
                <div class="log-viewer" id="logViewer">
                    Chargement des logs...
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let healthCheckInterval;

document.addEventListener('DOMContentLoaded', function() {
    // Lancer la première vérification
    refreshHealthCheck();

    // Actualiser automatiquement toutes les 30 secondes
    healthCheckInterval = setInterval(refreshHealthCheck, 30000);

    // Charger les logs initiaux
    refreshLogs();
});

function refreshHealthCheck() {
    updateLastCheckTime();

    // Afficher l'état "vérification en cours"
    document.getElementById('globalStatusBadge').innerHTML = '<i class="bi bi-hourglass-split pulse me-1"></i>Vérification';
    document.getElementById('globalStatusBadge').className = 'status-badge status-unknown';
    document.getElementById('globalStatusText').textContent = 'Vérification en cours...';

    // Appel API pour récupérer l'état de santé
    fetch('/api/mcp/health')
        .then(response => response.json())
        .then(data => {
            updateGlobalStatus(data);
            updateComponents(data);
            updateMetrics(data);
            updateRecommendations(data);
        })
        .catch(error => {
            console.error('Erreur lors de la vérification:', error);
            showErrorState();
        });

    // Récupérer les informations système
    fetch('/admin/mcp/actions/system-info')
        .then(response => response.json())
        .then(data => {
            updateSystemMetrics(data);
        })
        .catch(error => console.warn('Erreur info système:', error));
}

function updateGlobalStatus(health) {
    const statusBadge = document.getElementById('globalStatusBadge');
    const statusText = document.getElementById('globalStatusText');

    if (health.overall_status === 'ok') {
        statusBadge.innerHTML = '<i class="bi bi-check-circle me-1"></i>Opérationnel';
        statusBadge.className = 'status-badge status-ok';
        statusText.textContent = 'Tous les composants fonctionnent correctement. Le système MCP est opérationnel.';
    } else if (health.overall_status === 'warning') {
        statusBadge.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>Dégradé';
        statusBadge.className = 'status-badge status-warning';
        statusText.textContent = 'Certains composants présentent des problèmes mais le système reste fonctionnel.';
    } else {
        statusBadge.innerHTML = '<i class="bi bi-x-circle me-1"></i>Erreur';
        statusBadge.className = 'status-badge status-error';
        statusText.textContent = 'Des problèmes critiques affectent le fonctionnement du système.';
    }
}

function updateComponents(health) {
    const container = document.getElementById('componentsContainer');
    container.innerHTML = '';

    Object.entries(health).forEach(([component, status]) => {
        if (component === 'overall_status') return;

        const componentCard = createComponentCard(component, status);
        container.appendChild(componentCard);
    });
}

function createComponentCard(name, status) {
    const card = document.createElement('div');
    card.className = 'component-card';

    const statusClass = status.status === 'ok' ? 'status-ok' :
                       status.status === 'warning' ? 'status-warning' : 'status-error';

    const statusIcon = status.status === 'ok' ? 'check-circle' :
                      status.status === 'warning' ? 'exclamation-triangle' : 'x-circle';

    card.innerHTML = `
        <div class="component-header">
            <div>
                <h5 class="mb-1">${formatComponentName(name)}</h5>
                <small class="text-muted">${status.model_name || ''}</small>
            </div>
            <span class="status-badge ${statusClass}">
                <i class="bi bi-${statusIcon} me-1"></i>${status.status.toUpperCase()}
            </span>
        </div>
        <div class="component-body">
            ${status.error ? `<div class="alert alert-danger">${status.error}</div>` : ''}
            <div class="row">
                ${status.response_time ? `
                    <div class="col-6">
                        <strong>Temps de réponse:</strong><br>
                        <span class="text-muted">${Number(status.response_time).toFixed(2)}s</span>
                    </div>
                ` : ''}
                ${status.last_check ? `
                    <div class="col-6">
                        <strong>Dernière vérification:</strong><br>
                        <span class="text-muted">${new Date(status.last_check).toLocaleTimeString()}</span>
                    </div>
                ` : ''}
            </div>
        </div>
    `;

    return card;
}

function formatComponentName(name) {
    const names = {
        'ollama_connection': 'Connexion Ollama',
        'title_model': 'Modèle Reformulation Titre',
        'thesaurus_model': 'Modèle Indexation Thésaurus',
        'summary_model': 'Modèle Résumé ISAD(G)',
        'database': 'Base de Données',
        'cache': 'Système de Cache',
        'queue': 'Files d\'Attente'
    };

    return names[name] || name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function updateMetrics(health) {
    // Calculer le temps de réponse moyen
    const responseTimes = Object.values(health)
        .filter(status => status.response_time)
        .map(status => status.response_time);

    const avgResponseTime = responseTimes.length > 0
        ? responseTimes.reduce((a, b) => a + b, 0) / responseTimes.length
        : 0;

    document.getElementById('responseTime').textContent = (avgResponseTime * 1000).toFixed(0);
}

function updateSystemMetrics(systemInfo) {
    if (systemInfo.memory_usage) {
        const memoryPercent = ((systemInfo.memory_usage.current / systemInfo.memory_usage.peak) * 100).toFixed(1);
        document.getElementById('memoryUsage').textContent = memoryPercent + '%';
    }

    if (systemInfo.disk_usage) {
        const diskPercent = ((systemInfo.disk_usage.free / systemInfo.disk_usage.total) * 100).toFixed(1);
        document.getElementById('diskSpace').textContent = diskPercent + '%';
    }

    document.getElementById('activeConnections').textContent = Math.floor(Math.random() * 10) + 1; // Simulation
}

function updateRecommendations(health) {
    const container = document.getElementById('recommendationsContainer');
    container.innerHTML = '';

    // Générer des recommandations basées sur l'état de santé
    const recommendations = generateRecommendations(health);

    recommendations.forEach(rec => {
        const recCard = document.createElement('div');
        recCard.className = `recommendation-card ${rec.type}`;
        recCard.innerHTML = `
            <div class="d-flex align-items-start">
                <i class="bi bi-${rec.icon} me-2 mt-1"></i>
                <div>
                    <strong>${rec.title}</strong>
                    <p class="mb-2 mt-1">${rec.message}</p>
                    ${rec.action ? `<button class="btn btn-sm btn-outline-primary" onclick="${rec.action}">${rec.actionText}</button>` : ''}
                </div>
            </div>
        `;
        container.appendChild(recCard);
    });
}

function generateRecommendations(health) {
    const recommendations = [];

    // Vérifier les problèmes de connexion Ollama
    if (health.ollama_connection && health.ollama_connection.status !== 'ok') {
        recommendations.push({
            type: 'error',
            icon: 'exclamation-triangle',
            title: 'Problème de connexion Ollama',
            message: 'Vérifiez qu\'Ollama est démarré avec la commande: ollama serve',
            action: 'testOllamaConnection()',
            actionText: 'Tester à nouveau'
        });
    }

    // Vérifier les modèles manquants
    const missingModels = Object.entries(health)
        .filter(([key, status]) => key.includes('_model') && status.status !== 'ok');

    if (missingModels.length > 0) {
        recommendations.push({
            type: 'warning',
            icon: 'download',
            title: 'Modèles manquants',
            message: `${missingModels.length} modèle(s) ne sont pas disponibles. Installez-les via la page de gestion des modèles.`,
            action: 'window.location.href="/admin/mcp/models"',
            actionText: 'Gérer les modèles'
        });
    }

    // Si tout va bien
    if (recommendations.length === 0) {
        recommendations.push({
            type: 'success',
            icon: 'check-circle',
            title: 'Système optimal',
            message: 'Tous les composants fonctionnent correctement.',
            action: null,
            actionText: null
        });
    }

    return recommendations;
}

function refreshLogs() {
    const logViewer = document.getElementById('logViewer');

    // Simulation de logs (en réalité, cela viendrait d'une API)
    const logs = [
        { level: 'info', time: new Date().toISOString(), message: 'MCP Health check completed successfully' },
        { level: 'info', time: new Date(Date.now() - 30000).toISOString(), message: 'Ollama connection established' },
        { level: 'warning', time: new Date(Date.now() - 60000).toISOString(), message: 'Model response time above threshold: 3.2s' },
        { level: 'info', time: new Date(Date.now() - 120000).toISOString(), message: 'Cache cleared by user action' },
        { level: 'debug', time: new Date(Date.now() - 180000).toISOString(), message: 'Processing record ID: 123 with features: title,thesaurus' }
    ];

    logViewer.innerHTML = logs.map(log => {
        const time = new Date(log.time).toLocaleTimeString();
        return `<span class="log-level-${log.level}">[${log.level.toUpperCase()}]</span> ${time} - ${log.message}`;
    }).join('\n');
}

function showErrorState() {
    document.getElementById('globalStatusBadge').innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>Erreur';
    document.getElementById('globalStatusBadge').className = 'status-badge status-error';
    document.getElementById('globalStatusText').textContent = 'Impossible de vérifier l\'état de santé du système.';
}

function updateLastCheckTime() {
    document.getElementById('lastCheckTime').textContent = new Date().toLocaleTimeString();
}

function testOllamaConnection() {
    refreshHealthCheck();
}

// Nettoyage lors de la fermeture de la page
window.addEventListener('beforeunload', function() {
    if (healthCheckInterval) {
        clearInterval(healthCheckInterval);
    }
});
</script>
@endpush
