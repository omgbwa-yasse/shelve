@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">
                <i class="bi bi-diagram-3 me-2"></i>
                {{ $instance->name }}
            </h1>
            <p class="text-muted mt-2">
                {{ __('Instance du modèle') }}: <a href="{{ route('workflows.templates.show', $instance->template) }}">{{ $instance->template->name }}</a>
                @if($instance->reference)
                <span class="ms-3"><strong>{{ __('Référence') }}:</strong> {{ $instance->reference }}</span>
                @endif
            </p>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('workflows.instances.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    {{ __('Retour à la liste') }}
                </a>

                @if($instance->status->value === 'draft')
                    @can('workflow.instance.start', $instance)
                    <button type="button" class="btn btn-success" onclick="confirmAction('{{ route('workflows.instances.start', $instance) }}', '{{ __('Êtes-vous sûr de vouloir démarrer ce workflow ?') }}')">
                        <i class="bi bi-play me-1"></i>
                        {{ __('Démarrer') }}
                    </button>
                    @endcan
                @elseif($instance->status->value === 'in_progress')
                    @can('workflow.instance.pause', $instance)
                    <button type="button" class="btn btn-warning" onclick="confirmAction('{{ route('workflows.instances.pause', $instance) }}', '{{ __('Êtes-vous sûr de vouloir mettre en pause ce workflow ?') }}')">
                        <i class="bi bi-pause me-1"></i>
                        {{ __('Mettre en pause') }}
                    </button>
                    @endcan
                @elseif($instance->status->value === 'paused')
                    @can('workflow.instance.resume', $instance)
                    <button type="button" class="btn btn-success" onclick="confirmAction('{{ route('workflows.instances.resume', $instance) }}', '{{ __('Êtes-vous sûr de vouloir reprendre ce workflow ?') }}')">
                        <i class="bi bi-play me-1"></i>
                        {{ __('Reprendre') }}
                    </button>
                    @endcan
                @endif

                @if(!in_array($instance->status->value, ['completed', 'cancelled']))
                    @can('workflow.instance.cancel', $instance)
                    <button type="button" class="btn btn-danger" onclick="confirmAction('{{ route('workflows.instances.cancel', $instance) }}', '{{ __('Êtes-vous sûr de vouloir annuler ce workflow ?') }}')">
                        <i class="bi bi-x-lg me-1"></i>
                        {{ __('Annuler') }}
                    </button>
                    @endcan
                @endif
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">{{ __('Statut') }}</h6>
                    <div class="d-flex align-items-center">
                        <span class="badge {{ $instance->status->badgeClass() }} fs-5 me-2">{{ $instance->status->label() }}</span>
                        @if($instance->currentStep)
                        <span class="ms-2">{{ __('Étape') }} {{ $instance->currentStepNumber ?? '-' }}/{{ $instance->totalSteps }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">{{ __('Progression') }}</h6>
                    <div class="d-flex align-items-center">
                        <div class="progress flex-grow-1" style="height: 10px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $instance->progress }}%;" aria-valuenow="{{ $instance->progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span class="ms-2 fw-bold">{{ $instance->progress }}%</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">{{ __('Dates') }}</h6>
                    <div>
                        <div><strong>{{ __('Créée le') }}:</strong> {{ $instance->created_at->format('d/m/Y H:i') }}</div>
                        <div><strong>{{ __('Démarrée le') }}:</strong> {{ $instance->started_at ? $instance->started_at->format('d/m/Y H:i') : '-' }}</div>
                        <div><strong>{{ __('Terminée le') }}:</strong> {{ $instance->completed_at ? $instance->completed_at->format('d/m/Y H:i') : '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">{{ __('Informations') }}</h6>
                    <div>
                        <div><strong>{{ __('Créée par') }}:</strong> {{ $instance->creator->name ?? '-' }}</div>
                        @if($instance->entity_type)
                        <div>
                            <strong>{{ __('Entité liée') }}:</strong>
                            {{ class_basename($instance->entity_type) }} #{{ $instance->entity_id }}
                        </div>
                        @endif
                        <div><strong>{{ __('Étapes complétées') }}:</strong> {{ $instance->completedStepsCount ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Visualisation du workflow -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Progression du workflow') }}</h5>
        </div>
        <div class="card-body">
            <div class="workflow-diagram">
                @php
                    $stepCount = count($instance->stepInstances);
                    $currentStep = $instance->currentStep;
                    $currentStepId = $currentStep ? $currentStep->id : null;
                @endphp

                <div class="workflow-steps">
                    @foreach($instance->stepInstances as $index => $stepInstance)
                        @php
                            $stepStatus = $stepInstance->status->value;
                            $statusClass = match($stepStatus) {
                                'pending' => 'secondary',
                                'in_progress' => 'primary',
                                'completed' => 'success',
                                'rejected' => 'danger',
                                'skipped' => 'info',
                                default => 'light'
                            };
                            $isCurrentStep = $stepInstance->id === $currentStepId;
                        @endphp

                        <div class="workflow-step {{ $isCurrentStep ? 'current-step' : '' }}">
                            <div class="step-number bg-{{ $statusClass }} {{ $isCurrentStep ? 'pulsating' : '' }}">
                                {{ $index + 1 }}
                            </div>
                            <div class="step-connector {{ $index < $stepCount - 1 ? '' : 'd-none' }}"></div>
                            <div class="step-details">
                                <h6>
                                    <a href="{{ route('workflows.step-instances.show', $stepInstance) }}" class="text-decoration-none">
                                        {{ $stepInstance->step->name }}
                                    </a>
                                </h6>
                                <span class="badge bg-{{ $statusClass }}">{{ $stepInstance->status->label() }}</span>
                                <div class="small text-muted mt-1">
                                    @if($stepInstance->started_at)
                                        {{ __('Démarrée le') }}: {{ $stepInstance->started_at->format('d/m/Y') }}
                                    @endif
                                    @if($stepInstance->completed_at)
                                        <br>{{ __('Terminée le') }}: {{ $stepInstance->completed_at->format('d/m/Y') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Étape actuelle -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Étape actuelle') }}</h5>
                    @if($currentStep)
                    <a href="{{ route('workflows.step-instances.show', $currentStep) }}" class="btn btn-sm btn-outline-primary">
                        {{ __('Voir les détails') }}
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    @if($currentStep)
                        <h5>{{ $currentStep->step->name }}</h5>
                        <p>{{ $currentStep->step->description }}</p>

                        <div class="row mb-3">
                            <div class="col-md-3 fw-bold">{{ __('Statut') }}</div>
                            <div class="col-md-9">
                                <span class="badge {{ $currentStep->status->badgeClass() }}">{{ $currentStep->status->label() }}</span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3 fw-bold">{{ __('Démarrée le') }}</div>
                            <div class="col-md-9">{{ $currentStep->started_at ? $currentStep->started_at->format('d/m/Y H:i') : '-' }}</div>
                        </div>

                        @if($currentStep->deadline_at)
                        <div class="row mb-3">
                            <div class="col-md-3 fw-bold">{{ __('Échéance') }}</div>
                            <div class="col-md-9">
                                @php
                                    $daysRemaining = now()->diffInDays($currentStep->deadline_at, false);
                                    $badgeClass = $daysRemaining < 0 ? 'bg-danger' : ($daysRemaining < 2 ? 'bg-warning' : 'bg-info');
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    {{ $currentStep->deadline_at->format('d/m/Y') }}
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
                            <div class="col-md-3 fw-bold">{{ __('Assignés') }}</div>
                            <div class="col-md-9">
                                @if($currentStep->assignments->isEmpty())
                                    <span class="text-muted">{{ __('Aucune assignation') }}</span>
                                @else
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($currentStep->assignments as $assignment)
                                            <span class="badge bg-info">
                                                @if($assignment->assignee_type === 'App\\Models\\User')
                                                    {{ $assignment->assignee->name ?? 'N/A' }}
                                                @elseif($assignment->assignee_type === 'App\\Models\\Role')
                                                    {{ __('Rôle') }}: {{ $assignment->assignee->name ?? 'N/A' }}
                                                @elseif($assignment->assignee_type === 'App\\Models\\Department')
                                                    {{ __('Dépt') }}: {{ $assignment->assignee->name ?? 'N/A' }}
                                                @else
                                                    {{ $assignment->assignee_type }}: {{ $assignment->assignee_id }}
                                                @endif
                                                @if($assignment->role)
                                                    ({{ $assignment->role }})
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($currentStep->status->value === 'in_progress' && $currentStep->can_be_completed_by_user)
                            <div class="mt-4">
                                <hr>
                                <h5>{{ __('Actions disponibles') }}</h5>
                                <div class="btn-group mt-2">
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#completeStepModal">
                                        <i class="bi bi-check-lg me-1"></i>
                                        {{ __('Compléter cette étape') }}
                                    </button>

                                    @if($currentStep->can_be_rejected)
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectStepModal">
                                        <i class="bi bi-x-lg me-1"></i>
                                        {{ __('Rejeter cette étape') }}
                                    </button>
                                    @endif

                                    @can('workflow.stepInstance.reassign', $currentStep)
                                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#reassignStepModal">
                                        <i class="bi bi-people me-1"></i>
                                        {{ __('Réassigner') }}
                                    </button>
                                    @endcan
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            @if($instance->status->value === 'draft')
                                {{ __('Le workflow n\'a pas encore été démarré.') }}
                            @elseif($instance->status->value === 'completed')
                                {{ __('Le workflow est terminé.') }}
                            @elseif($instance->status->value === 'cancelled')
                                {{ __('Le workflow a été annulé.') }}
                            @else
                                {{ __('Aucune étape active actuellement.') }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Informations complémentaires -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Informations complémentaires') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="border-bottom pb-2">{{ __('Description') }}</h6>
                        <p>{{ $instance->description ?: __('Aucune description') }}</p>
                    </div>

                    @if($instance->entity_type && $instance->entity_id)
                        <div class="mb-3">
                            <h6 class="border-bottom pb-2">{{ __('Entité associée') }}</h6>
                            <p>
                                <strong>{{ __('Type') }}:</strong> {{ class_basename($instance->entity_type) }}<br>
                                <strong>{{ __('ID') }}:</strong> {{ $instance->entity_id }}
                            </p>
                        </div>
                    @endif

                    <div class="mb-3">
                        <h6 class="border-bottom pb-2">{{ __('Tâches associées') }}</h6>
                        @if($instance->tasks->isEmpty())
                            <p class="text-muted">{{ __('Aucune tâche associée') }}</p>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach($instance->tasks as $task)
                                    <li class="list-group-item d-flex justify-content-between align-items-center ps-0 pe-0">
                                        <div>
                                            <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none">
                                                {{ $task->title }}
                                            </a>
                                            <span class="badge {{ $task->status->badgeClass() }} ms-2">{{ $task->status->label() }}</span>
                                        </div>
                                        <div class="small text-muted">{{ $task->due_date ? $task->due_date->format('d/m/Y') : '-' }}</div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historique -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Historique des étapes') }}</h5>
        </div>
        <div class="card-body">
            <div class="timeline">
                @forelse($instance->history as $historyItem)
                    <div class="timeline-item">
                        <div class="timeline-badge bg-{{ $historyItem->type->badgeClass() }}">
                            <i class="bi {{ $historyItem->type->icon() }}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="time">{{ $historyItem->created_at->format('d/m/Y H:i') }}</div>
                            <h6 class="mb-1">{{ $historyItem->title }}</h6>
                            <p class="mb-0">{{ $historyItem->description }}</p>
                            @if($historyItem->user)
                                <div class="small text-muted mt-1">{{ __('Par') }}: {{ $historyItem->user->name }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="alert alert-info">
                        {{ __('Aucun événement dans l\'historique.') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Modals pour les actions sur l'étape courante -->
@if($currentStep && $currentStep->status->value === 'in_progress')
    <!-- Modal pour compléter une étape -->
    <div class="modal fade" id="completeStepModal" tabindex="-1" aria-labelledby="completeStepModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="completeStepModalLabel">{{ __('Compléter l\'étape') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('workflows.step-instances.complete', $currentStep) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>{{ __('Vous êtes sur le point de marquer cette étape comme complétée.') }}</p>

                        <div class="mb-3">
                            <label for="comment" class="form-label">{{ __('Commentaire (optionnel)') }}</label>
                            <textarea class="form-control" id="comment" name="comment" rows="3"></textarea>
                        </div>

                        @if($currentStep->step->requires_approval)
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
                <form action="{{ route('workflows.step-instances.reject', $currentStep) }}" method="POST">
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
                <form action="{{ route('workflows.step-instances.reassign', $currentStep) }}" method="POST">
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
@endif

@push('styles')
<style>
    .workflow-diagram {
        padding: 20px 0;
        overflow-x: auto;
    }

    .workflow-steps {
        display: flex;
        align-items: flex-start;
        min-width: fit-content;
    }

    .workflow-step {
        display: flex;
        align-items: center;
        margin-right: 10px;
        position: relative;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        z-index: 1;
    }

    .step-connector {
        height: 2px;
        background-color: #dee2e6;
        width: 50px;
        margin: 0 5px;
    }

    .step-details {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 10px;
        margin-left: 10px;
        min-width: 200px;
    }

    .current-step .step-details {
        border: 2px solid #007bff;
        box-shadow: 0 0 10px rgba(0, 123, 255, 0.3);
    }

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

    .pulsating {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(0, 123, 255, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function confirmAction(url, message) {
        if (confirm(message)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.innerHTML = `@csrf`;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endpush
@endsection
