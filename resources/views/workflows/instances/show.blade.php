@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-play-circle"></i> {{ $instance->name }}</h1>
        <a href="{{ route('workflows.instances.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Informations de l'instance -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Informations de l\'instance') }}</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">{{ __('Nom') }}</dt>
                        <dd class="col-sm-9">{{ $instance->name }}</dd>

                        <dt class="col-sm-3">{{ __('Définition') }}</dt>
                        <dd class="col-sm-9">
                            <a href="{{ route('workflows.definitions.show', $instance->definition) }}">
                                {{ $instance->definition->name }} (v{{ $instance->definition->version }})
                            </a>
                        </dd>

                        <dt class="col-sm-3">{{ __('Statut') }}</dt>
                        <dd class="col-sm-9">
                            @switch($instance->status)
                                @case('running')
                                    <span class="badge bg-primary">
                                        <i class="bi bi-arrow-repeat"></i> {{ __('En cours') }}
                                    </span>
                                    @break
                                @case('completed')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> {{ __('Terminé') }}
                                    </span>
                                    @break
                                @case('paused')
                                    <span class="badge bg-warning">
                                        <i class="bi bi-pause-circle"></i> {{ __('En pause') }}
                                    </span>
                                    @break
                                @case('cancelled')
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle"></i> {{ __('Annulé') }}
                                    </span>
                                    @break
                            @endswitch
                        </dd>

                        <dt class="col-sm-3">{{ __('Démarré par') }}</dt>
                        <dd class="col-sm-9">{{ $instance->starter->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">{{ __('Date démarrage') }}</dt>
                        <dd class="col-sm-9">{{ $instance->started_at->format('d/m/Y H:i') }}</dd>

                        @if($instance->completed_at)
                            <dt class="col-sm-3">{{ __('Date fin') }}</dt>
                            <dd class="col-sm-9">{{ $instance->completed_at->format('d/m/Y H:i') }}</dd>

                            <dt class="col-sm-3">{{ __('Durée') }}</dt>
                            <dd class="col-sm-9">{{ $instance->started_at->diffForHumans($instance->completed_at, true) }}</dd>
                        @else
                            <dt class="col-sm-3">{{ __('Durée actuelle') }}</dt>
                            <dd class="col-sm-9">{{ $instance->started_at->diffForHumans(null, true) }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- État actuel -->
            @if($instance->current_state && count($instance->current_state) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('État actuel') }}</h5>
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-3 rounded"><code>{{ json_encode($instance->current_state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                    </div>
                </div>
            @endif

            <!-- Tâches du workflow -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Tâches du workflow') }}</h5>
                </div>
                <div class="card-body">
                    @if($instance->tasks->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Séquence') }}</th>
                                        <th>{{ __('Titre') }}</th>
                                        <th>{{ __('Statut') }}</th>
                                        <th>{{ __('Assigné à') }}</th>
                                        <th>{{ __('Priorité') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($instance->tasks->sortBy('sequence_order') as $task)
                                        <tr>
                                            <td>{{ $task->sequence_order }}</td>
                                            <td>{{ $task->title }}</td>
                                            <td>
                                                @switch($task->status)
                                                    @case('pending')
                                                        <span class="badge bg-secondary">{{ __('En attente') }}</span>
                                                        @break
                                                    @case('in_progress')
                                                        <span class="badge bg-primary">{{ __('En cours') }}</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge bg-success">{{ __('Terminé') }}</span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="badge bg-danger">{{ __('Annulé') }}</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>{{ $task->assignedUser->name ?? '-' }}</td>
                                            <td>
                                                @switch($task->priority)
                                                    @case('urgent')
                                                        <span class="badge bg-danger">{{ __('Urgent') }}</span>
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
                                            <td>
                                                <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">{{ __('Aucune tâche créée pour cette instance.') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions et statistiques -->
        <div class="col-md-4">
            <!-- Statistiques -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Statistiques des tâches') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>{{ __('Total tâches') }}</strong>
                        <div class="display-6">{{ $instance->tasks->count() }}</div>
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('En attente') }}</strong>
                        <div class="display-6">{{ $instance->tasks->where('status', 'pending')->count() }}</div>
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('En cours') }}</strong>
                        <div class="display-6">{{ $instance->tasks->where('status', 'in_progress')->count() }}</div>
                    </div>
                    <div>
                        <strong>{{ __('Terminées') }}</strong>
                        <div class="display-6">{{ $instance->tasks->where('status', 'completed')->count() }}</div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            @if($instance->status == 'running')
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('Actions') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('workflows.instances.destroy', $instance) }}" method="POST" onsubmit="return confirm('{{ __('Annuler ce workflow ? Cette action est irréversible.') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-x-circle"></i> {{ __('Annuler le workflow') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
