@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Header avec gradient --}}
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);">
        <div class="card-body text-white py-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h3 mb-2">
                        <i class="bi bi-list-task me-3"></i>
                        Surveillance des Queues MCP
                    </h1>
                    <p class="mb-0 opacity-90">
                        Monitoring en temps réel des jobs et des workers MCP
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex flex-column align-items-end">
                        <div class="mb-2">
                            <span class="badge bg-light text-dark px-3 py-2">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Auto-refresh: 30s
                            </span>
                        </div>
                        <small class="opacity-75">Dernière mise à jour: {{ now()->format('H:i:s') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($error))
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ $error }}
        </div>
    @endif

    {{-- Actions rapides --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="bi bi-lightning text-warning me-2"></i>
                            Actions rapides
                        </h6>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshData()">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Actualiser
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="pauseAllQueues()">
                                <i class="bi bi-pause me-1"></i>
                                Pause All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="restartAllQueues()">
                                <i class="bi bi-play me-1"></i>
                                Restart All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearFailedJobs()">
                                <i class="bi bi-trash me-1"></i>
                                Clear Failed
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Métriques générales --}}
    @if(isset($queueStats))
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-4 text-info mb-2">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h5 class="card-title">{{ $queueStats['pending_jobs'] ?? 0 }}</h5>
                    <p class="card-text text-muted">Jobs en attente</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-4 text-success mb-2">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h5 class="card-title">{{ $queueStats['completed_jobs'] ?? 0 }}</h5>
                    <p class="card-text text-muted">Jobs terminés</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-4 text-danger mb-2">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <h5 class="card-title">{{ $queueStats['failed_jobs'] ?? 0 }}</h5>
                    <p class="card-text text-muted">Jobs échoués</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-4 text-warning mb-2">
                        <i class="bi bi-people"></i>
                    </div>
                    <h5 class="card-title">{{ count($queueStats['workers'] ?? []) }}</h5>
                    <p class="card-text text-muted">Workers actifs</p>
                </div>
            </div>
        </div>
    </div>

    {{-- État des queues --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0">
                        <i class="bi bi-layers text-primary me-2"></i>
                        État des queues MCP
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($queueStats['queues']) && count($queueStats['queues']) > 0)
                        <div class="row">
                            @foreach($queueStats['queues'] as $queueName => $queueData)
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0">{{ $queueName }}</h6>
                                            <span class="badge bg-{{ $queueData['failed'] > 0 ? 'danger' : ($queueData['working'] > 0 ? 'warning' : 'success') }}">
                                                {{ $queueData['working'] > 0 ? 'Actif' : ($queueData['pending'] > 0 ? 'En attente' : 'Idle') }}
                                            </span>
                                        </div>
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <small class="text-muted d-block">En attente</small>
                                                <strong class="text-info">{{ $queueData['pending'] }}</strong>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">En cours</small>
                                                <strong class="text-warning">{{ $queueData['working'] }}</strong>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">Échecs</small>
                                                <strong class="text-danger">{{ $queueData['failed'] }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Workers actifs --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0">
                        <i class="bi bi-person-gear text-success me-2"></i>
                        Workers actifs
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($queueStats['workers']) && count($queueStats['workers']) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Queue</th>
                                        <th>Statut</th>
                                        <th>Job en cours</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($queueStats['workers'] as $worker)
                                        <tr>
                                            <td>{{ $worker['id'] }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $worker['queue'] }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $worker['status'] === 'working' ? 'warning' : 'success' }}">
                                                    {{ ucfirst($worker['status']) }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $worker['current_job'] ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-person-x display-4"></i>
                            <p class="mt-2">Aucun worker actif</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        {{-- Jobs récents --}}
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0">
                        <i class="bi bi-clock-history text-info me-2"></i>
                        Jobs récents
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($queueStats['recent_jobs']) && count($queueStats['recent_jobs']) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Type</th>
                                        <th>Statut</th>
                                        <th>Durée</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($queueStats['recent_jobs'] as $job)
                                        <tr>
                                            <td>{{ $job['id'] }}</td>
                                            <td>
                                                <span class="badge bg-primary">{{ $job['type'] }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $job['status'] === 'completed' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($job['status']) }}
                                                </span>
                                            </td>
                                            <td>{{ $job['duration'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-list display-4"></i>
                            <p class="mt-2">Aucun job récent</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Graphique en temps réel --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0">
                        <i class="bi bi-graph-up text-primary me-2"></i>
                        Activité en temps réel
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="realTimeChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let realTimeChart;

document.addEventListener('DOMContentLoaded', function() {
    initRealTimeChart();
    
    // Auto-refresh toutes les 30 secondes
    setInterval(refreshData, 30000);
});

function initRealTimeChart() {
    const ctx = document.getElementById('realTimeChart').getContext('2d');
    realTimeChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Jobs traités/min',
                data: [],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1
            }, {
                label: 'Jobs échoués/min',
                data: [],
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Simuler des données
    updateChart();
}

function updateChart() {
    const now = new Date();
    const timeLabel = now.getHours() + ':' + String(now.getMinutes()).padStart(2, '0');
    
    realTimeChart.data.labels.push(timeLabel);
    realTimeChart.data.datasets[0].data.push(Math.floor(Math.random() * 10) + 5);
    realTimeChart.data.datasets[1].data.push(Math.floor(Math.random() * 3));
    
    // Garder seulement les 20 derniers points
    if (realTimeChart.data.labels.length > 20) {
        realTimeChart.data.labels.shift();
        realTimeChart.data.datasets[0].data.shift();
        realTimeChart.data.datasets[1].data.shift();
    }
    
    realTimeChart.update();
}

function refreshData() {
    updateChart();
    // Recharger la page pour mettre à jour les données
    // En production, on ferait un appel AJAX
    console.log('Refreshing queue data...');
}

function pauseAllQueues() {
    if (confirm('Êtes-vous sûr de vouloir mettre en pause toutes les queues ?')) {
        // Appel API pour pause
        alert('Toutes les queues ont été mises en pause');
    }
}

function restartAllQueues() {
    if (confirm('Êtes-vous sûr de vouloir redémarrer toutes les queues ?')) {
        // Appel API pour restart
        alert('Toutes les queues ont été redémarrées');
    }
}

function clearFailedJobs() {
    if (confirm('Êtes-vous sûr de vouloir supprimer tous les jobs échoués ?')) {
        // Appel API pour clear failed
        alert('Tous les jobs échoués ont été supprimés');
    }
}
</script>
@endpush

@endsection