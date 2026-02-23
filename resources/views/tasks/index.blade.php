@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="bi bi-list-task me-2"></i>{{ __('Gestion des Tâches') }}</h5>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>{{ __('Nouvelle tâche') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Statistiques compactes -->
    <div class="d-flex flex-wrap gap-4 mb-3">
        <span class="d-flex align-items-center gap-2" title="En attente">
            <i class="bi bi-hourglass-split text-secondary fs-5"></i>
            <strong class="fs-5">{{ $tasks->where('status', 'pending')->count() }}</strong>
            <span class="text-muted">{{ __('En attente') }}</span>
        </span>
        <span class="d-flex align-items-center gap-2" title="En cours">
            <i class="bi bi-play-circle text-primary fs-5"></i>
            <strong class="fs-5">{{ $tasks->where('status', 'in_progress')->count() }}</strong>
            <span class="text-muted">{{ __('En cours') }}</span>
        </span>
        <span class="d-flex align-items-center gap-2" title="Terminées">
            <i class="bi bi-check-circle text-success fs-5"></i>
            <strong class="fs-5">{{ $tasks->where('status', 'completed')->count() }}</strong>
            <span class="text-muted">{{ __('Terminées') }}</span>
        </span>
        <span class="d-flex align-items-center gap-2" title="Urgentes">
            <i class="bi bi-exclamation-triangle text-warning fs-5"></i>
            <strong class="fs-5">{{ $tasks->where('priority', 'urgent')->count() }}</strong>
            <span class="text-muted">{{ __('Urgentes') }}</span>
        </span>
    </div>

    <!-- Filtres -->
    <form method="GET" action="{{ route('tasks.index') }}" class="d-flex flex-wrap gap-2 align-items-end mb-3">
        <div>
            <label class="form-label small mb-0">{{ __('Statut') }}</label>
            <select name="status" class="form-select form-select-sm" style="min-width:130px">
                <option value="">{{ __('Tous') }}</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('En attente') }}</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>{{ __('En cours') }}</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('Terminées') }}</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('Annulées') }}</option>
            </select>
        </div>
        <div>
            <label class="form-label small mb-0">{{ __('Priorité') }}</label>
            <select name="priority" class="form-select form-select-sm" style="min-width:120px">
                <option value="">{{ __('Toutes') }}</option>
                <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>{{ __('Urgente') }}</option>
                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>{{ __('Haute') }}</option>
                <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>{{ __('Normale') }}</option>
                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>{{ __('Basse') }}</option>
            </select>
        </div>
        <div>
            <label class="form-label small mb-0">{{ __('Assigné à') }}</label>
            <select name="assigned_to" class="form-select form-select-sm" style="min-width:120px">
                <option value="">{{ __('Tous') }}</option>
                <option value="me" {{ request('assigned_to') == 'me' ? 'selected' : '' }}>{{ __('Mes tâches') }}</option>
            </select>
        </div>
        <div class="d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>{{ __('Filtrer') }}</button>
            <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i></a>
        </div>
    </form>

    <!-- Liste des tâches -->
    <div class="card">
        <div class="card-body">
            @if($tasks->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Titre') }}</th>
                                <th>{{ __('Statut') }}</th>
                                <th>{{ __('Priorité') }}</th>
                                <th>{{ __('Assigné à') }}</th>
                                <th>{{ __('Date limite') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tasks as $task)
                                <tr class="{{ $task->isOverdue ? 'table-danger' : '' }}">
                                    <td>
                                        <strong>{{ $task->title }}</strong>
                                        @if($task->isOverdue)
                                            <span class="badge bg-danger ms-2">{{ __('En retard') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @switch($task->status)
                                            @case('pending')
                                                <span class="badge bg-secondary">{{ __('En attente') }}</span>
                                                @break
                                            @case('in_progress')
                                                <span class="badge bg-primary">{{ __('En cours') }}</span>
                                                @break
                                            @case('completed')
                                                <span class="badge bg-success">{{ __('Terminée') }}</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-danger">{{ __('Annulée') }}</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        @switch($task->priority)
                                            @case('urgent')
                                                <span class="badge bg-danger">{{ __('Urgente') }}</span>
                                                @break
                                            @case('high')
                                                <span class="badge bg-warning">{{ __('Haute') }}</span>
                                                @break
                                            @case('normal')
                                                <span class="badge bg-info">{{ __('Normale') }}</span>
                                                @break
                                            @case('low')
                                                <span class="badge bg-secondary">{{ __('Basse') }}</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ $task->assignedUser->name ?? __('Non assignée') }}</td>
                                    <td>{{ $task->due_date ? $task->due_date->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        @if($task->isWorkflowTask)
                                            <span class="badge bg-info">
                                                <i class="bi bi-diagram-3"></i> {{ __('Workflow') }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-list-task"></i> {{ __('Générale') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($task->status != 'completed')
                                            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer cette tâche ?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $tasks->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-list-task text-muted" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-3">{{ __('Aucune tâche trouvée.') }}</p>
                    <a href="{{ route('tasks.create') }}" class="btn btn-primary mt-2">
                        <i class="bi bi-plus-circle"></i> {{ __('Créer la première tâche') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
