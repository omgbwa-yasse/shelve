@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">
                <i class="bi bi-list-ol me-2"></i>
                {{ $step->name }}
            </h1>
            <p class="text-muted mt-2">
                {{ __('Étape du modèle') }}: <a href="{{ route('workflow.templates.show', $step->template) }}">{{ $step->template->name }}</a>
            </p>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('workflow.templates.show', $step->template) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    {{ __('Retour au modèle') }}
                </a>

                @can('workflow.step.update', $step)
                <a href="{{ route('workflow.steps.edit', $step) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i>
                    {{ __('Modifier') }}
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Colonne principale -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Détails de l\'étape') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Description') }}</div>
                        <div class="col-md-9">{{ $step->description ?: __('Aucune description') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Type d\'étape') }}</div>
                        <div class="col-md-9">{{ $step->type->label() }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Ordre') }}</div>
                        <div class="col-md-9">{{ $step->order }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Délai d\'exécution') }}</div>
                        <div class="col-md-9">
                            @if($step->deadline_days)
                                {{ $step->deadline_days }} {{ __('jours') }}
                            @else
                                {{ __('Aucun délai spécifié') }}
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Exige une approbation') }}</div>
                        <div class="col-md-9">
                            @if($step->requires_approval)
                                <span class="badge bg-success">{{ __('Oui') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('Non') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Étape bloquante') }}</div>
                        <div class="col-md-9">
                            @if($step->is_blocker)
                                <span class="badge bg-danger">{{ __('Oui') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('Non') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Action automatique') }}</div>
                        <div class="col-md-9">
                            @if($step->action_class)
                                <code>{{ $step->action_class }}</code>
                            @else
                                {{ __('Aucune action automatique définie') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Options avancées') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('Condition de succès') }}</div>
                        <div class="col-md-8">
                            @if($step->success_condition)
                                <code>{{ $step->success_condition }}</code>
                            @else
                                {{ __('Aucune condition définie') }}
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('Condition d\'échec') }}</div>
                        <div class="col-md-8">
                            @if($step->failure_condition)
                                <code>{{ $step->failure_condition }}</code>
                            @else
                                {{ __('Aucune condition définie') }}
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('Prochaine étape (succès)') }}</div>
                        <div class="col-md-8">
                            @if($step->nextStepOnSuccess)
                                <a href="{{ route('workflow.steps.show', $step->nextStepOnSuccess) }}">
                                    {{ $step->nextStepOnSuccess->name }}
                                </a>
                            @else
                                {{ __('Étape suivante séquentielle') }}
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('Prochaine étape (échec)') }}</div>
                        <div class="col-md-8">
                            @if($step->nextStepOnFailure)
                                <a href="{{ route('workflow.steps.show', $step->nextStepOnFailure) }}">
                                    {{ $step->nextStepOnFailure->name }}
                                </a>
                            @else
                                {{ __('Étape suivante séquentielle') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne latérale -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Assignations') }}</h5>
                </div>
                <div class="card-body">
                    @if($step->assignments->isEmpty())
                        <div class="alert alert-info">
                            {{ __('Aucune assignation pour cette étape.') }}
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($step->assignments as $assignment)
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
                                    @can('workflow.step.manageAssignments', $step)
                                    <form action="{{ route('workflow.steps.assignments.destroy', ['step' => $step, 'assignment' => $assignment]) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cette assignation?') }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @can('workflow.step.manageAssignments', $step)
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#assignModal">
                            <i class="bi bi-person-plus me-1"></i> {{ __('Ajouter une assignation') }}
                        </button>
                    </div>
                    @endcan
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Historique d\'utilisation') }}</h5>
                </div>
                <div class="card-body">
                    @if($step->instances_count > 0)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>{{ __('Instances en cours') }}</div>
                            <span class="badge bg-primary">{{ $step->active_instances_count }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>{{ __('Instances complétées') }}</div>
                            <span class="badge bg-success">{{ $step->completed_instances_count }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>{{ __('Temps moyen d\'exécution') }}</div>
                            <span class="badge bg-info">{{ $step->average_completion_time ?? '-' }} {{ __('jours') }}</span>
                        </div>
                    @else
                        <div class="alert alert-info">
                            {{ __('Cette étape n\'a jamais été utilisée dans des instances de workflow.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@can('workflow.step.manageAssignments', $step)
<!-- Modal d'assignation -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignModalLabel">{{ __('Ajouter une assignation') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('workflow.steps.assignments.store', $step) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="assignee_type" class="form-label">{{ __('Type d\'assignation') }} <span class="text-danger">*</span></label>
                        <select class="form-control" id="assignee_type" name="assignee_type" required>
                            <option value="App\Models\User">{{ __('Utilisateur') }}</option>
                            <option value="App\Models\Role">{{ __('Rôle') }}</option>
                            <option value="App\Models\Department">{{ __('Département') }}</option>
                        </select>
                    </div>

                    <div class="form-group mb-3" id="userSelect">
                        <label for="user_id" class="form-label">{{ __('Utilisateur') }} <span class="text-danger">*</span></label>
                        <select class="form-control" id="user_id" name="user_id">
                            <option value="">{{ __('Sélectionner un utilisateur') }}</option>
                            @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3 d-none" id="roleSelect">
                        <label for="role_id" class="form-label">{{ __('Rôle') }} <span class="text-danger">*</span></label>
                        <select class="form-control" id="role_id" name="role_id">
                            <option value="">{{ __('Sélectionner un rôle') }}</option>
                            @foreach(\App\Models\Role::orderBy('name')->get() as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3 d-none" id="departmentSelect">
                        <label for="department_id" class="form-label">{{ __('Département') }} <span class="text-danger">*</span></label>
                        <select class="form-control" id="department_id" name="department_id">
                            <option value="">{{ __('Sélectionner un département') }}</option>
                            @foreach(\App\Models\Department::orderBy('name')->get() as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="role" class="form-label">{{ __('Rôle dans l\'étape') }}</label>
                        <input type="text" class="form-control" id="role" name="role" placeholder="{{ __('Par exemple: Approbateur, Vérificateur...') }}">
                        <small class="form-text text-muted">{{ __('Champ optionnel pour spécifier le rôle de cet assigné dans l\'étape') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Ajouter') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Gestion des sélecteurs d'assignation dynamiques
        const assigneeType = document.getElementById('assignee_type');
        const userSelect = document.getElementById('userSelect');
        const roleSelect = document.getElementById('roleSelect');
        const departmentSelect = document.getElementById('departmentSelect');

        if (assigneeType) {
            assigneeType.addEventListener('change', function() {
                userSelect.classList.add('d-none');
                roleSelect.classList.add('d-none');
                departmentSelect.classList.add('d-none');

                if (this.value === 'App\\Models\\User') {
                    userSelect.classList.remove('d-none');
                } else if (this.value === 'App\\Models\\Role') {
                    roleSelect.classList.remove('d-none');
                } else if (this.value === 'App\\Models\\Department') {
                    departmentSelect.classList.remove('d-none');
                }
            });
        }
    });
</script>
@endpush
@endsection
