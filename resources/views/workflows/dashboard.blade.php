@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="bi bi-speedometer2"></i> Tableau de bord Workflow</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('workflows.instances.index') }}" class="btn btn-outline-secondary">Instances</a>
            <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">Tâches</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h2 class="mb-0">{{ $totals->total_tasks ?? 0 }}</h2>
                    <span class="text-muted">Tâches de workflow</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h2 class="mb-0 text-warning">{{ $totals->pending ?? 0 }} / {{ $totals->in_progress ?? 0 }}</h2>
                    <span class="text-muted">En attente / En cours</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h2 class="mb-0 text-danger">{{ $totals->overdue ?? 0 }}</h2>
                    <span class="text-muted">En retard</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h2 class="mb-0 text-success">{{ $onTimeRate !== null ? $onTimeRate . '%' : '—' }}</h2>
                    <span class="text-muted">Taux de respect des échéances</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-exclamation-triangle text-danger"></i> Tâches en retard</div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Tâche</th><th>Workflow</th><th>Assigné à</th><th>Échéance</th></tr>
                        </thead>
                        <tbody>
                            @forelse($overdueTasks as $task)
                                <tr>
                                    <td>{{ $task->title }}</td>
                                    <td>{{ $task->workflowInstance?->name ?? '—' }}</td>
                                    <td>{{ $task->assignedUser?->name ?? '—' }}</td>
                                    <td class="text-danger">{{ $task->due_date?->format('d/m/Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Aucune tâche en retard</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-diagram-3"></i> Retards par instance</div>
                <ul class="list-group list-group-flush">
                    @forelse($byWorkflow as $item)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $item['name'] }}</span>
                            <span class="badge bg-danger">{{ $item['overdue'] }} en retard</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Aucun retard par instance</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-sign-turn-right"></i> Retards par étape</div>
                <ul class="list-group list-group-flush">
                    @forelse($byStep as $step)
                        <li class="list-group-item d-flex justify-content-between">
                            <span><code>{{ $step->task_key }}</code></span>
                            <span class="badge bg-danger">{{ $step->overdue }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Aucun retard par étape</li>
                    @endforelse
                </ul>
            </div>

            <div class="card">
                <div class="card-header"><i class="bi bi-person"></i> Retards par utilisateur</div>
                <ul class="list-group list-group-flush">
                    @forelse($byUser as $item)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $item['user'] }}</span>
                            <span class="badge bg-danger">{{ $item['overdue'] }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Aucun retard par utilisateur</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
