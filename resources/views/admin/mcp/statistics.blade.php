@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Header avec gradient --}}
    <div class="card border-0 shadow-sm mb-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="card-body text-white py-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h5 mb-1">
                        <i class="bi bi-graph-up-arrow me-3"></i>
                        Statistiques MCP
                    </h1>
                    <p class="mb-0 opacity-90 small">
                        Analyse des performances et de l'utilisation du Model Context Protocol
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex flex-column align-items-end">
                        <div class="mb-1">
                            <span class="badge bg-light text-dark px-2 py-1">
                                <i class="bi bi-calendar3 me-1"></i>
                                Période: {{ ucfirst($period ?? 'month') }}
                            </span>
                        </div>
                         <small class="opacity-75">Maj: {{ now()->format('H:i') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sélecteur de période --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="bi bi-funnel text-primary me-2"></i>
                            Filtrer par période
                        </h6>
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="?period=day" class="btn {{ $period === 'day' ? 'btn-primary' : 'btn-outline-primary' }}">
                                Aujourd'hui
                            </a>
                            <a href="?period=week" class="btn {{ $period === 'week' ? 'btn-primary' : 'btn-outline-primary' }}">
                                Cette semaine
                            </a>
                            <a href="?period=month" class="btn {{ ($period === 'month' || !$period) ? 'btn-primary' : 'btn-outline-primary' }}">
                                Ce mois
                            </a>
                            <a href="?period=year" class="btn {{ $period === 'year' ? 'btn-primary' : 'btn-outline-primary' }}">
                                Cette année
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Métriques principales --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-4 text-primary mb-2">
                        <i class="bi bi-files"></i>
                    </div>
                    <h5 class="card-title">{{ $stats['total_records'] ?? 0 }}</h5>
                    <p class="card-text text-muted">Records traités</p>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i> +{{ round(($stats['total_records'] ?? 0) * 0.15) }} ce mois
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-4 text-success mb-2">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h5 class="card-title">{{ $stats['success_rate'] ?? 0 }}%</h5>
                    <p class="card-text text-muted">Taux de succès</p>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i> +2.3% vs mois dernier
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-4 text-info mb-2">
                        <i class="bi bi-stopwatch"></i>
                    </div>
                    <h5 class="card-title">{{ $stats['avg_processing_time'] ?? 0 }}s</h5>
                    <p class="card-text text-muted">Temps moyen</p>
                    <small class="text-info">
                        <i class="bi bi-dash"></i> -0.8s vs mois dernier
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-4 text-warning mb-2">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <h5 class="card-title">{{ $stats['failed_processes'] ?? 0 }}</h5>
                    <p class="card-text text-muted">Échecs</p>
                    <small class="text-danger">
                        <i class="bi bi-arrow-down"></i> -{{ round(($stats['failed_processes'] ?? 0) * 0.3) }} ce mois
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Graphiques de performance --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0">
                        <i class="bi bi-graph-up text-primary me-2"></i>
                        Évolution des traitements
                    </h6>
                </div>
                <div class="card-body pt-2">
                    <canvas id="performanceChart" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0">
                        <i class="bi bi-pie-chart text-success me-2"></i>
                        Répartition par fonctionnalité
                    </h6>
                </div>
                <div class="card-body pt-2">
                    <canvas id="featuresChart" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Utilisation des modèles --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0">
                        <i class="bi bi-cpu text-info me-2"></i>
                        Utilisation des modèles
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($stats['model_usage']) && count($stats['model_usage']) > 0)
                        @foreach($stats['model_usage'] as $model => $usage)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-medium">{{ $model }}</span>
                                    <span class="text-muted">{{ $usage }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info" style="width: {{ $usage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-info-circle display-4"></i>
                            <p class="mt-2">Aucune donnée d'utilisation disponible</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0">
                        <i class="bi bi-clock-history text-warning me-2"></i>
                        Temps de traitement par type
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($stats['processing_times']))
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="border rounded p-3">
                                    <h5 class="text-primary">{{ $stats['processing_times']['avg_title'] ?? 'N/A' }}s</h5>
                                    <small class="text-muted">Reformulation titre</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-3">
                                    <h5 class="text-success">{{ $stats['processing_times']['avg_thesaurus'] ?? 'N/A' }}s</h5>
                                    <small class="text-muted">Indexation thésaurus</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-3">
                                    <h5 class="text-info">{{ $stats['processing_times']['avg_summary'] ?? 'N/A' }}s</h5>
                                    <small class="text-muted">Résumé ISAD(G)</small>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-clock display-4"></i>
                            <p class="mt-2">Aucune donnée de temps disponible</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Actions rapides --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning text-warning me-2"></i>
                        Actions rapides
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.mcp.dashboard') }}" class="btn btn-outline-primary">
                            <i class="bi bi-house me-1"></i> Dashboard
                        </a>
                        <a href="{{ route('admin.mcp.queue-monitor') }}" class="btn btn-outline-info">
                            <i class="bi bi-list-task me-1"></i> Surveillance des queues
                        </a>
                        <a href="{{ route('admin.mcp.models') }}" class="btn btn-outline-success">
                            <i class="bi bi-cpu me-1"></i> Gestion des modèles
                        </a>
                        <a href="{{ route('admin.mcp.health-check') }}" class="btn btn-outline-warning">
                            <i class="bi bi-shield-check me-1"></i> Vérification santé
                        </a>
                        <button class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Imprimer le rapport
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique des performances
    const performanceCtx = document.getElementById('performanceChart').getContext('2d');
    new Chart(performanceCtx, {
        type: 'line',
        data: {
            labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            datasets: [{
                label: 'Traitements réussis',
                data: [65, 78, 82, 71, 89, 95, 88],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1
            }, {
                label: 'Échecs',
                data: [8, 5, 3, 7, 2, 1, 4],
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

    // Graphique des fonctionnalités
    const featuresCtx = document.getElementById('featuresChart').getContext('2d');
    new Chart(featuresCtx, {
        type: 'doughnut',
        data: {
            labels: ['Reformulation titre', 'Indexation thésaurus', 'Résumé ISAD(G)'],
            datasets: [{
                data: [45, 30, 25],
                backgroundColor: [
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(255, 99, 132)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>
@endpush

@endsection