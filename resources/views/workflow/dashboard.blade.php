@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">
                <i class="bi bi-grid me-2"></i>
                {{ __('Tableau de bord des workflows') }}
            </h1>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                @can('workflow_template_viewAny')
                <a href="{{ route('workflow.templates.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-file-earmark-text me-1"></i>
                    {{ __('Modèles') }}
                </a>
                @endcan

                @can('workflow_instance_viewAny')
                <a href="{{ route('workflow.instances.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-diagram-3 me-1"></i>
                    {{ __('Instances') }}
                </a>
                @endcan

                @can('task_viewAny')
                <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-list-task me-1"></i>
                    {{ __('Tâches') }}
                </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Statistiques globales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">{{ __('Workflows en cours') }}</h6>
                            <h2 class="mb-0">{{ $stats['active_workflows'] }}</h2>
                        </div>
                        <div class="bg-primary rounded-circle p-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-play-circle text-white fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">{{ __('Workflows complétés') }}</h6>
                            <h2 class="mb-0">{{ $stats['completed_workflows'] }}</h2>
                        </div>
                        <div class="bg-success rounded-circle p-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-check-circle text-white fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">{{ __('Tâches actives') }}</h6>
                            <h2 class="mb-0">{{ $stats['active_tasks'] }}</h2>
                        </div>
                        <div class="bg-info rounded-circle p-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-list-task text-white fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">{{ __('Échéances dépassées') }}</h6>
                            <h2 class="mb-0">{{ $stats['overdue_items'] }}</h2>
                        </div>
                        <div class="bg-danger rounded-circle p-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-exclamation-triangle text-white fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mes workflows -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Mes workflows actifs') }}</h5>
                    <a href="{{ route('workflow.instances.index', ['assigned' => 'me']) }}" class="btn btn-sm btn-link">
                        {{ __('Voir tout') }}
                    </a>
                </div>
                <div class="card-body">
                    @if($myWorkflows->isEmpty())
                        <div class="alert alert-info">
                            {{ __('Aucun workflow actif vous est assigné actuellement.') }}
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($myWorkflows as $workflow)
                                <a href="{{ route('workflow.instances.show', $workflow) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $workflow->name }}</h6>
                                        <small class="text-muted">{{ $workflow->template->name }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge {{ $workflow->status->badgeClass() }}">{{ $workflow->status->label() }}</span>
                                        @if($workflow->currentStep)
                                            <div class="small text-muted mt-1">{{ __('Étape') }}: {{ $workflow->currentStep->step->name }}</div>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Mes tâches à faire') }}</h5>
                    <a href="{{ route('tasks.my') }}" class="btn btn-sm btn-link">
                        {{ __('Voir tout') }}
                    </a>
                </div>
                <div class="card-body">
                    @if($myTasks->isEmpty())
                        <div class="alert alert-info">
                            {{ __('Aucune tâche en attente vous est assignée actuellement.') }}
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($myTasks as $task)
                                <a href="{{ route('tasks.show', $task) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $task->title }}</h6>
                                        <small class="text-muted">{{ $task->category->name ?? '' }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge {{ $task->priority->badgeClass() }}">{{ $task->priority->label() }}</span>
                                        @if($task->due_date)
                                            <div class="small {{ $task->is_overdue ? 'text-danger' : 'text-muted' }} mt-1">
                                                {{ __('Échéance') }}: {{ $task->due_date->format('d/m/Y') }}
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Dernières activités -->
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Activités récentes') }}</h5>
                </div>
                <div class="card-body">
                    @if($recentActivities->isEmpty())
                        <div class="alert alert-info">
                            {{ __('Aucune activité récente.') }}
                        </div>
                    @else
                        <div class="timeline">
                            @foreach($recentActivities as $activity)
                                <div class="timeline-item">
                                    <div class="timeline-badge bg-{{ $activity->type->badgeClass() }}">
                                        <i class="bi {{ $activity->type->icon() }}"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between">
                                            <div class="time">{{ $activity->created_at->format('d/m/Y H:i') }}</div>
                                            <div class="small text-muted">{{ $activity->user ? $activity->user->name : __('Système') }}</div>
                                        </div>
                                        <h6 class="mb-1">{{ $activity->title }}</h6>
                                        <p class="mb-0">{{ $activity->description }}</p>

                                        @if($activity->linkable)
                                            <div class="mt-2">
                                                <a href="{{ $activity->link_url }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-arrow-right me-1"></i>
                                                    {{ __('Voir les détails') }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Workflows par modèle -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Workflows par modèle') }}</h5>
                </div>
                <div class="card-body">
                    @if($workflowsByTemplate->isEmpty())
                        <div class="alert alert-info">
                            {{ __('Aucune donnée disponible.') }}
                        </div>
                    @else
                        <div class="chart-container" style="position: relative; height:300px;">
                            <canvas id="workflowsByTemplateChart"></canvas>
                        </div>

                        <div class="mt-4">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('Modèle') }}</th>
                                        <th class="text-center">{{ __('En cours') }}</th>
                                        <th class="text-center">{{ __('Complétés') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($workflowsByTemplate as $item)
                                        <tr>
                                            <td>{{ $item->name }}</td>
                                            <td class="text-center">{{ $item->active_count }}</td>
                                            <td class="text-center">{{ $item->completed_count }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .timeline {
        position: relative;
        padding: 20px 0;
    }

    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 15px;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 30px;
    }

    .timeline-badge {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        left: 0;
        top: 0;
    }

    .timeline-content {
        margin-left: 50px;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 4px;
        position: relative;
    }

    .timeline-content:before {
        content: '';
        position: absolute;
        top: 10px;
        left: -10px;
        width: 0;
        height: 0;
        border-top: 10px solid transparent;
        border-bottom: 10px solid transparent;
        border-right: 10px solid #f8f9fa;
    }

    .time {
        color: #6c757d;
        font-size: 0.85rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Graphique des workflows par modèle
        const workflowCtx = document.getElementById('workflowsByTemplateChart');

        if (workflowCtx) {
            const data = @json($workflowsByTemplate);

            if (data.length > 0) {
                new Chart(workflowCtx, {
                    type: 'bar',
                    data: {
                        labels: data.map(item => item.name),
                        datasets: [
                            {
                                label: '{{ __('En cours') }}',
                                data: data.map(item => item.active_count),
                                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                borderColor: 'rgb(54, 162, 235)',
                                borderWidth: 1
                            },
                            {
                                label: '{{ __('Complétés') }}',
                                data: data.map(item => item.completed_count),
                                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                                borderColor: 'rgb(75, 192, 192)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                precision: 0
                            }
                        }
                    }
                });
            }
        }
    });
</script>
@endpush
@endsection
