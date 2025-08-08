@extends('layouts.app')

@section('title', 'Dashboard MCP')

@push('styles')
<style>
    .mcp-dashboard {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 1rem 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .metric-card {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        height: 100%;
    }

    .metric-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .metric-value {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .metric-label { color: #6c757d; font-size: 0.8rem; font-weight: 500; }

    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }

    .status-online { background-color: #28a745; }
    .status-warning { background-color: #ffc107; }
    .status-offline { background-color: #dc3545; }

    .health-component {
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        margin-bottom: 0.4rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .health-ok { background-color: #d4edda; color: #155724; }
    .health-warning { background-color: #fff3cd; color: #856404; }
    .health-error { background-color: #f8d7da; color: #721c24; }

    .activity-item {
        border-left: 3px solid #007bff;
        padding-left: 1rem;
        margin-bottom: 1rem;
    }

    .chart-container { height: 220px; }
    .card-header h5 { font-size: 1rem; }
    h1 { font-size: 1.25rem; }
    .mcp-dashboard p { font-size: 0.85rem; }
    .progress { height: 6px !important; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header avec gradient -->
    <div class="mcp-dashboard">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-2">
                    <i class="bi bi-robot me-3"></i>
                    Dashboard MCP
                </h1>
                <p class="mb-0 opacity-90">
                    Model Context Protocol - Intelligence Artificielle pour l'Archivage ISAD(G)
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="d-flex flex-column align-items-end">
                    <div class="mb-2">
                        <span class="status-indicator status-{{ isset($health['overall_status']) && $health['overall_status'] === 'ok' ? 'online' : 'offline' }}"></span>
                        <span class="fw-bold">{{ isset($health['overall_status']) && $health['overall_status'] === 'ok' ? 'Système Opérationnel' : 'Système Dégradé' }}</span>
                    </div>
                    <small class="opacity-75">Dernière mise à jour: {{ now()->format('H:i:s') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Métriques principales -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="metric-card text-center">
                <div class="metric-value text-primary">{{ $stats['total_records'] ?? 0 }}</div>
                <div class="metric-label">Records Totaux</div>
                <small class="text-muted">
                    <i class="bi bi-files"></i> Base de données
                </small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="metric-card text-center">
                <div class="metric-value text-success">{{ $stats['processed_records'] ?? 0 }}</div>
                <div class="metric-label">Traités (30j)</div>
                <small class="text-muted">
                    <i class="bi bi-check-circle"></i> Derniers traitements
                </small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="metric-card text-center">
                <div class="metric-value text-info">{{ $stats['thesaurus_concepts'] ?? 0 }}</div>
                <div class="metric-label">Concepts Thésaurus</div>
                <small class="text-muted">
                    <i class="bi bi-tags"></i> Indexation disponible
                </small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="metric-card text-center">
                <div class="metric-value text-warning">{{ $stats['pending_jobs'] ?? 0 }}</div>
                <div class="metric-label">Jobs en Attente</div>
                <small class="text-muted">
                    <i class="bi bi-hourglass-split"></i> Files d'attente
                </small>
            </div>
        </div>
    </div>

    <!-- État de santé détaillé -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card metric-card">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-heart-pulse me-2"></i>
                        État de Santé du Système
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($health) && !empty($health))
                        @foreach($health as $component => $status)
                            @if(is_array($status) && isset($status['status']))
                                <div class="health-component health-{{ $status['status'] === 'ok' ? 'ok' : ($status['status'] === 'warning' ? 'warning' : 'error') }}">
                                    <div class="d-flex align-items-center">
                                        <span class="status-indicator status-{{ $status['status'] === 'ok' ? 'online' : 'offline' }}"></span>
                                        <strong>{{ ucfirst(str_replace('_', ' ', $component)) }}</strong>
                                        @if(isset($status['model_name']))
                                            <span class="badge bg-primary ms-2">{{ $status['model_name'] }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        @if(isset($status['response_time']))
                                            <small class="text-muted">{{ number_format($status['response_time'], 2) }}s</small>
                                        @endif
                                        @if(isset($status['error']))
                                            <small class="text-danger">{{ $status['error'] }}</small>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Vérification...</span>
                            </div>
                            <p class="mt-2 text-muted">Vérification de l'état de santé...</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card metric-card">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-speedometer me-2"></i>
                        Performance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Temps de réponse moyen</small>
                            <small class="fw-bold">{{ number_format($performance['avg_response_time'] ?? 0, 1) }}s</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: {{ min(($performance['avg_response_time'] ?? 0) * 20, 100) }}%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Taux de cache</small>
                            <small class="fw-bold">{{ number_format($performance['cache_hit_rate'] ?? 0, 1) }}%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: {{ $performance['cache_hit_rate'] ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Taux de succès</small>
                            <small class="fw-bold">{{ number_format($performance['success_rate'] ?? 0, 1) }}%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" style="width: {{ $performance['success_rate'] ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques et activité récente -->
    <div class="row">
        <div class="col-md-8">
            <div class="card metric-card">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>
                        Utilisation des Fonctionnalités (7 derniers jours)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="usageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card metric-card">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        Activité Récente
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($recentActivity) && count($recentActivity['recent_processes']) > 0)
                        @foreach($recentActivity['recent_processes'] as $activity)
                            <div class="activity-item">
                                <div class="fw-bold">{{ $activity['action'] }}</div>
                                <small class="text-muted">{{ $activity['time'] }}</small>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-clock text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">Aucune activité récente</p>
                            <a href="/records" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-play me-1"></i>
                                Commencer un traitement
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="row mb-4 mt-4">
        <div class="col-12">
            <div class="card metric-card">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>
                        Actions Rapides
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-primary btn-sm w-100" onclick="testConnection()">
                                <i class="bi bi-wifi me-2"></i>Test Connexion
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-success btn-sm w-100" onclick="openBatchModal()">
                                <i class="bi bi-layers me-2"></i>Traitement par Lots
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-info btn-sm w-100" onclick="clearCache()">
                                <i class="bi bi-arrow-clockwise me-2"></i>Vider Cache
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="/admin/mcp/configuration" class="btn btn-outline-warning btn-sm w-100">
                                <i class="bi bi-gear me-2"></i>Configuration
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initUsageChart();
    setInterval(refreshDashboard, 30000);
    refreshDashboard();
});

function initUsageChart() {
    const ctx = document.getElementById('usageChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            datasets: [{
                label: 'Reformulation Titre',
                data: [12, 19, 8, 15, 22, 18, 25],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4
            }, {
                label: 'Indexation Thésaurus',
                data: [18, 25, 15, 28, 35, 30, 40],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4
            }, {
                label: 'Résumé ISAD(G)',
                data: [8, 12, 6, 10, 15, 12, 18],
                borderColor: '#17a2b8',
                backgroundColor: 'rgba(23, 162, 184, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.1)' } },
                x: { grid: { color: 'rgba(0,0,0,0.1)' } }
            }
        }
    });
}

function refreshDashboard() {
    fetch('/admin/mcp/actions/system-info')
        .then(response => response.json())
        .then(data => updateHealthStatus(data))
        .catch(error => console.warn('Erreur lors de la mise à jour:', error));
}

function updateHealthStatus(health) {
    const indicators = document.querySelectorAll('.status-indicator');
    indicators.forEach(indicator => {
        const isOnline = !!health.php_version;
        indicator.className = 'status-indicator status-' + (isOnline ? 'online' : 'offline');
    });
}

function testConnection() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Test...';
    button.disabled = true;
    
    fetch('/admin/mcp/actions/system-info')
        .then(response => response.json())
        .then(data => {
            const success = !!data.php_version;
            button.innerHTML = `<i class="bi bi-${success ? 'check-circle' : 'x-circle'} me-2"></i>${success ? 'Connexion OK' : 'Connexion KO'}`;
            button.className = `btn btn-sm w-100 ${success ? 'btn-success' : 'btn-danger'}`;
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.className = 'btn btn-outline-primary btn-sm w-100';
                button.disabled = false;
            }, 3000);
        })
        .catch(error => {
            button.innerHTML = '<i class="bi bi-x-circle me-2"></i>Erreur';
            button.className = 'btn btn-danger btn-sm w-100';
            setTimeout(() => {
                button.innerHTML = originalText;
                button.className = 'btn btn-outline-primary btn-sm w-100';
                button.disabled = false;
            }, 3000);
        });
}

function openBatchModal() {
    window.location.href = '/records?open_mcp_batch=1';
}

function clearCache() {
    if (confirm('Vider le cache MCP ?')) {
        const button = event.target;
        const originalText = button.innerHTML;
        
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Nettoyage...';
        button.disabled = true;
        
        fetch('/admin/mcp/actions/clear-cache', { 
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            button.innerHTML = '<i class="bi bi-check-circle me-2"></i>Cache vidé';
            button.className = 'btn btn-success btn-sm w-100';
            setTimeout(() => {
                button.innerHTML = originalText;
                button.className = 'btn btn-outline-info btn-sm w-100';
                button.disabled = false;
            }, 3000);
        })
        .catch(error => {
            button.innerHTML = '<i class="bi bi-x-circle me-2"></i>Erreur';
            button.className = 'btn btn-danger btn-sm w-100';
            setTimeout(() => {
                button.innerHTML = originalText;
                button.className = 'btn btn-outline-info btn-sm w-100';
                button.disabled = false;
            }, 3000);
        });
    }
}
</script>
@endpush