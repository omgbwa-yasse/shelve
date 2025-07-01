@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">
                <i class="bi bi-list-check me-2"></i>
                {{ __('Étape') }}: {{ $stepInstance->step->name }}
            </h1>
            <p class="text-muted mt-2">
                {{ __('Instance') }}: <a href="{{ route('workflow.instances.show', $stepInstance->instance) }}">{{ $stepInstance->instance->name }}</a> |
                {{ __('Modèle') }}: <a href="{{ route('workflow.templates.show', $stepInstance->instance->template) }}">{{ $stepInstance->instance->template->name }}</a>
            </p>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('workflow.instances.show', $stepInstance->instance) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    {{ __('Retour à l\'instance') }}
                </a>

                @if($stepInstance->status->value === 'in_progress' && $stepInstance->can_be_completed_by_user)
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#completeStepModal">
                        <i class="bi bi-check-lg me-1"></i>
                        {{ __('Compléter') }}
                    </button>

                    @if($stepInstance->can_be_rejected)
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectStepModal">
                        <i class="bi bi-x-lg me-1"></i>
                        {{ __('Rejeter') }}
                    </button>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Colonne principale -->
        <div class="col-md-8">
            <!-- Détails de l'étape -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Détails de l\'étape') }}</h5>
                    <span class="badge {{ $stepInstance->status->badgeClass() }}">{{ $stepInstance->status->label() }}</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Description') }}</div>
                        <div class="col-md-9">{{ $stepInstance->step->description ?: __('Aucune description') }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Type d\'étape') }}</div>
                        <div class="col-md-9">{{ $stepInstance->step->type->label() }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Ordre') }}</div>
                        <div class="col-md-9">{{ $stepInstance->step->order }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Démarrée le') }}</div>
                        <div class="col-md-9">{{ $stepInstance->started_at ? $stepInstance->started_at->format('d/m/Y H:i') : '-' }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Terminée le') }}</div>
                        <div class="col-md-9">{{ $stepInstance->completed_at ? $stepInstance->completed_at->format('d/m/Y H:i') : '-' }}</div>
                    </div>

                    @if($stepInstance->deadline_at)
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Échéance') }}</div>
                        <div class="col-md-9">
                            @php
                                $daysRemaining = now()->diffInDays($stepInstance->deadline_at, false);
                                $badgeClass = $daysRemaining < 0 ? 'bg-danger' : ($daysRemaining < 2 ? 'bg-warning' : 'bg-info');
                            @endphp
                            <span class="badge {{ $badgeClass }}">
                                {{ $stepInstance->deadline_at->format('d/m/Y') }}
                                @if($daysRemaining < 0)
                                    ({{ abs($daysRemaining) }} {{ __('jours de retard') }})
                                @else
                                    ({{ $daysRemaining }} {{ __('jours restants') }})
                                @endif
                            </span>
                        </div>
                    </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Durée') }}</div>
                        <div class="col-md-9">
                            @if($stepInstance->started_at)
                                @if($stepInstance->completed_at)
                                    {{ $stepInstance->started_at->diffInDays($stepInstance->completed_at) }} {{ __('jours') }}
                                    ({{ $stepInstance->started_at->format('d/m/Y') }} - {{ $stepInstance->completed_at->format('d/m/Y') }})
                                @else
                                    {{ $stepInstance->started_at->diffInDays(now()) }} {{ __('jours et en cours') }}
                                    ({{ __('depuis le') }} {{ $stepInstance->started_at->format('d/m/Y') }})
                                @endif
                            @else
                                {{ __('Non démarrée') }}
                            @endif
                        </div>
                    </div>

                    @if($stepInstance->result_data)
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Résultat') }}</div>
                        <div class="col-md-9">
                            <pre class="bg-light p-2 rounded"><code>{{ json_encode($stepInstance->result_data, JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                    @endif

                    @if($stepInstance->rejection_reason)
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Motif du rejet') }}</div>
                        <div class="col-md-9">
                            <div class="alert alert-danger">
                                {{ $stepInstance->rejection_reason }}
                            </div>
                            <div class="small text-muted">
                                {{ __('Rejeté par') }}: {{ $stepInstance->rejected_by_user->name ?? __('Système') }}
                                {{ __('le') }} {{ $stepInstance->rejected_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Commentaires et activité -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Commentaires et activité') }}</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @forelse($stepInstance->comments_and_activity as $item)
                            <div class="timeline-item">
                                <div class="timeline-badge bg-{{ $item->type->badgeClass() }}">
                                    <i class="bi {{ $item->type->icon() }}"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="time">{{ $item->created_at->format('d/m/Y H:i') }}</div>
                                    <h6 class="mb-1">{{ $item->title }}</h6>
                                    <p class="mb-0">{{ $item->content }}</p>
                                    @if($item->user)
                                        <div class="small text-muted mt-1">{{ __('Par') }}: {{ $item->user->name }}</div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info">
                                {{ __('Aucun commentaire ou activité pour cette étape.') }}
                            </div>
                        @endforelse
                    </div>

                    @if($stepInstance->status->value === 'in_progress')
                    <div class="mt-4">
                        <form action="{{ route('workflow.step-instances.comment', $stepInstance) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="comment" class="form-label">{{ __('Ajouter un commentaire') }}</label>
                                <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-chat-text me-1"></i>
                                    {{ __('Envoyer') }}
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Tâches associées -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Tâches associées') }}</h5>
                    @if($stepInstance->status->value === 'in_progress')
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                        <i class="bi bi-plus-lg me-1"></i>
                        {{ __('Ajouter une tâche') }}
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    @if($stepInstance->tasks->isEmpty())
                        <div class="alert alert-info">
                            {{ __('Aucune tâche associée à cette étape.') }}
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>{{ __('Titre') }}</th>
                                        <th>{{ __('Assigné à') }}</th>
                                        <th>{{ __('Échéance') }}</th>
                                        <th>{{ __('Statut') }}</th>
                                        <th class="text-end">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stepInstance->tasks as $task)
                                        <tr>
                                            <td>
                                                <a href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a>
                                            </td>
                                            <td>
                                                @forelse($task->assignments as $assignment)
                                                    <span class="badge bg-info">{{ $assignment->assignee->name ?? $assignment->assignee_id }}</span>
                                                @empty
                                                    <span class="text-muted">{{ __('Non assignée') }}</span>
                                                @endforelse
                                            </td>
                                            <td>
                                                @if($task->due_date)
                                                    @php
                                                        $daysRemaining = now()->diffInDays($task->due_date, false);
                                                        $badgeClass = $daysRemaining < 0 ? 'bg-danger' : ($daysRemaining < 2 ? 'bg-warning' : 'bg-info');
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">{{ $task->due_date->format('d/m/Y') }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $task->status->badgeClass() }}">{{ $task->status->label() }}</span>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-secondary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>

                                                    @if($task->status->value === 'pending')
                                                        @can('task.start', $task)
                                                        <a href="{{ route('tasks.start', $task) }}" class="btn btn-outline-primary">
                                                            <i class="bi bi-play"></i>
                                                        </a>
                                                        @endcan
                                                    @elseif($task->status->value === 'in_progress')
                                                        @can('task.complete', $task)
                                                        <a href="{{ route('tasks.complete', $task) }}" class="btn btn-outline-success">
                                                            <i class="bi bi-check-lg"></i>
                                                        </a>
                                                        @endcan
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Colonne latérale -->
        <div class="col-md-4">
            <!-- Assignations -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Assignations') }}</h5>
                    @if($stepInstance->status->value === 'in_progress')
                    @can('workflow.stepInstance.reassign', $stepInstance)
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reassignStepModal">
                        <i class="bi bi-people me-1"></i>
                        {{ __('Réassigner') }}
                    </button>
                    @endcan
                    @endif
                </div>
                <div class="card-body">
                    @if($stepInstance->assignments->isEmpty())
                        <div class="alert alert-info">
                            {{ __('Aucune assignation pour cette étape.') }}
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($stepInstance->assignments as $assignment)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ str_replace('App\\Models\\', '', $assignment->assignee_type) }}</strong><br>
                                        <span class="text-muted">
                                            @if($assignment->assignee_type === 'App\\Models\\User')
                                                {{ $assignment->assignee->name ?? $assignment->assignee_id }}
                                            @elseif($assignment->assignee_type === 'App\\Models\\Role')
                                                {{ $assignment->assignee->name ?? $assignment->assignee_id }}
                                            @elseif($assignment->assignee_type === 'App\\Models\\Department')
                                                {{ $assignment->assignee->name ?? $assignment->assignee_id }}
                                            @else
                                                {{ $assignment->assignee_id }}
                                            @endif
                                        </span>
                                        @if($assignment->role)
                                            <br><span class="badge bg-info">{{ $assignment->role }}</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted">
                                        {{ $assignment->created_at->format('d/m/Y') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Données de contexte -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Données de contexte') }}</h5>
                </div>
                <div class="card-body">
                    @if($stepInstance->context_data)
                        <pre class="bg-light p-2 rounded" style="max-height: 300px; overflow: auto;"><code>{{ json_encode($stepInstance->context_data, JSON_PRETTY_PRINT) }}</code></pre>
                    @else
                        <div class="alert alert-info">
                            {{ __('Aucune donnée de contexte disponible.') }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Entité liée -->
            @if($stepInstance->instance->entity_type && $stepInstance->instance->entity_id)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('Entité liée') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>{{ __('Type') }}:</strong> {{ class_basename($stepInstance->instance->entity_type) }}
                            </div>
                            <div>
                                <strong>{{ __('ID') }}:</strong> {{ $stepInstance->instance->entity_id }}
                            </div>
                        </div>
                        @if(isset($entity))
                            <hr>
                            <div class="d-grid gap-2">
                                <a href="{{ $entityUrl ?? '#' }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-link-45deg me-1"></i>
                                    {{ __('Voir l\'entité liée') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modals -->
@if($stepInstance->status->value === 'in_progress')
    <!-- Modal pour compléter une étape -->
    <div class="modal fade" id="completeStepModal" tabindex="-1" aria-labelledby="completeStepModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="completeStepModalLabel">{{ __('Compléter l\'étape') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('workflow.step-instances.complete', $stepInstance) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>{{ __('Vous êtes sur le point de marquer cette étape comme complétée.') }}</p>

                        <div class="mb-3">
                            <label for="comment" class="form-label">{{ __('Commentaire (optionnel)') }}</label>
                            <textarea class="form-control" id="comment" name="comment" rows="3"></textarea>
                        </div>

                        @if($stepInstance->step->requires_approval)
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                {{ __('Cette étape nécessite une approbation. Veuillez confirmer que vous approuvez la progression du workflow.') }}
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="approve" name="approve" value="1" required>
                                <label class="form-check-label" for="approve">
                                    {{ __('J\'approuve cette étape et la progression du workflow') }}
                                </label>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i>
                            {{ __('Compléter l\'étape') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal pour rejeter une étape -->
    <div class="modal fade" id="rejectStepModal" tabindex="-1" aria-labelledby="rejectStepModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectStepModalLabel">{{ __('Rejeter l\'étape') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('workflow.step-instances.reject', $stepInstance) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            {{ __('Vous êtes sur le point de rejeter cette étape. Cette action peut affecter la progression du workflow.') }}
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label">{{ __('Motif de rejet') }} <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-lg me-1"></i>
                            {{ __('Rejeter l\'étape') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal pour réassigner une étape -->
    <div class="modal fade" id="reassignStepModal" tabindex="-1" aria-labelledby="reassignStepModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reassignStepModalLabel">{{ __('Réassigner l\'étape') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('workflow.step-instances.reassign', $stepInstance) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>{{ __('Sélectionnez les nouveaux assignés pour cette étape.') }}</p>

                        <div class="form-group mb-3">
                            <label for="users" class="form-label">{{ __('Utilisateurs') }}</label>
                            <select class="form-control" id="users" name="users[]" multiple>
                                @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">{{ __('Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs utilisateurs') }}</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="comment" class="form-label">{{ __('Commentaire (optionnel)') }}</label>
                            <textarea class="form-control" id="comment" name="comment" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-people me-1"></i>
                            {{ __('Réassigner') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal pour créer une tâche -->
    <div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTaskModalLabel">{{ __('Créer une tâche') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('tasks.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="workflow_step_instance_id" value="{{ $stepInstance->id }}">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group mb-3">
                                    <label for="title" class="form-label">{{ __('Titre') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="description" class="form-label">{{ __('Description') }}</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="category_id" class="form-label">{{ __('Catégorie') }}</label>
                                    <select class="form-control" id="category_id" name="category_id">
                                        <option value="">{{ __('Sélectionner une catégorie') }}</option>
                                        @foreach(\App\Models\TaskCategory::orderBy('name')->get() as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="priority" class="form-label">{{ __('Priorité') }}</label>
                                    <select class="form-control" id="priority" name="priority">
                                        @foreach(\App\Enums\TaskPriority::cases() as $priority)
                                            <option value="{{ $priority->value }}">{{ $priority->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="due_date" class="form-label">{{ __('Date d\'échéance') }}</label>
                                    <input type="date" class="form-control" id="due_date" name="due_date">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="assignees" class="form-label">{{ __('Assigné à') }}</label>
                            <select class="form-control" id="assignees" name="assignees[]" multiple>
                                @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">{{ __('Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs utilisateurs') }}</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>
                            {{ __('Créer la tâche') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Code pour initialiser des sélecteurs avancés ou d'autres fonctionnalités
    });
</script>
@endpush
@endsection
