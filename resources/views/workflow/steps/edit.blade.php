@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">
                <i class="bi bi-pencil-square me-2"></i>
                {{ __('Modifier l\'étape') }}: {{ $step->name }}
            </h1>
            <p class="text-muted mt-2">
                {{ __('Modèle') }}: {{ $step->template->name }}
            </p>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('workflows.steps.show', $step) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    {{ __('Annuler et retourner') }}
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('workflows.steps.update', $step) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="name" class="form-label">{{ __('Nom de l\'étape') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $step->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="description" class="form-label">{{ __('Description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $step->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="type" class="form-label">{{ __('Type d\'étape') }} <span class="text-danger">*</span></label>
                            <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                @foreach(\App\Enums\WorkflowStepType::cases() as $type)
                                    <option value="{{ $type->value }}" {{ old('type', $step->type->value) == $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="deadline_days" class="form-label">{{ __('Délai d\'exécution (jours)') }}</label>
                            <input type="number" class="form-control @error('deadline_days') is-invalid @enderror" id="deadline_days" name="deadline_days" value="{{ old('deadline_days', $step->deadline_days) }}" min="0">
                            @error('deadline_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('Laissez vide pour ne pas définir de délai.') }}</small>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="action_class" class="form-label">{{ __('Classe d\'action automatique') }}</label>
                            <input type="text" class="form-control @error('action_class') is-invalid @enderror" id="action_class" name="action_class" value="{{ old('action_class', $step->action_class) }}">
                            @error('action_class')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('Classe PHP exécutée automatiquement pour cette étape (optionnel).') }}</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" id="requires_approval" name="requires_approval" value="1" {{ old('requires_approval', $step->requires_approval) ? 'checked' : '' }}>
                            <label class="form-check-label" for="requires_approval">{{ __('Nécessite une approbation') }}</label>
                        </div>

                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" id="is_blocker" name="is_blocker" value="1" {{ old('is_blocker', $step->is_blocker) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_blocker">{{ __('Étape bloquante') }}</label>
                            <small class="form-text d-block text-muted">{{ __('Si activé, le workflow ne peut pas continuer tant que cette étape n\'est pas complétée.') }}</small>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('Options avancées') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="failure_condition" class="form-label">{{ __('Condition d\'échec') }}</label>
                                    <input type="text" class="form-control @error('failure_condition') is-invalid @enderror" id="failure_condition" name="failure_condition" value="{{ old('failure_condition', $step->failure_condition) }}">
                                    @error('failure_condition')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('Expression conditionnelle pour déterminer un échec automatique.') }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="success_condition" class="form-label">{{ __('Condition de succès') }}</label>
                                    <input type="text" class="form-control @error('success_condition') is-invalid @enderror" id="success_condition" name="success_condition" value="{{ old('success_condition', $step->success_condition) }}">
                                    @error('success_condition')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('Expression conditionnelle pour déterminer un succès automatique.') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="next_step_on_failure" class="form-label">{{ __('Étape suivante en cas d\'échec') }}</label>
                                    <select class="form-control @error('next_step_on_failure') is-invalid @enderror" id="next_step_on_failure" name="next_step_on_failure">
                                        <option value="">{{ __('Étape suivante par défaut') }}</option>
                                        @foreach($step->template->steps as $nextStep)
                                            @if($nextStep->id !== $step->id)
                                                <option value="{{ $nextStep->id }}" {{ old('next_step_on_failure', $step->next_step_on_failure) == $nextStep->id ? 'selected' : '' }}>{{ $nextStep->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('next_step_on_failure')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="next_step_on_success" class="form-label">{{ __('Étape suivante en cas de succès') }}</label>
                                    <select class="form-control @error('next_step_on_success') is-invalid @enderror" id="next_step_on_success" name="next_step_on_success">
                                        <option value="">{{ __('Étape suivante par défaut') }}</option>
                                        @foreach($step->template->steps as $nextStep)
                                            @if($nextStep->id !== $step->id)
                                                <option value="{{ $nextStep->id }}" {{ old('next_step_on_success', $step->next_step_on_success) == $nextStep->id ? 'selected' : '' }}>{{ $nextStep->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('next_step_on_success')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> {{ __('Enregistrer les modifications') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Section Assignations -->
    @can('workflow.step.manageAssignments', $step)
    <div class="card shadow-sm mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('Assignations') }}</h5>
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignModal">
                <i class="bi bi-person-plus me-1"></i> {{ __('Ajouter une assignation') }}
            </button>
        </div>
        <div class="card-body">
            @if($step->assignments->isEmpty())
                <div class="alert alert-info">{{ __('Aucune assignation pour cette étape.') }}</div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Assigné à') }}</th>
                                <th>{{ __('Rôle') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($step->assignments as $assignment)
                                <tr>
                                    <td>{{ str_replace('App\\Models\\', '', $assignment->assignee_type) }}</td>
                                    <td>
                                        @if($assignment->assignee_type === 'App\\Models\\User')
                                            {{ $assignment->assignee->name ?? 'N/A' }}
                                        @elseif($assignment->assignee_type === 'App\\Models\\Role')
                                            {{ $assignment->assignee->name ?? 'N/A' }}
                                        @elseif($assignment->assignee_type === 'App\\Models\\Department')
                                            {{ $assignment->assignee->name ?? 'N/A' }}
                                        @else
                                            {{ $assignment->assignee_id }}
                                        @endif
                                    </td>
                                    <td>{{ $assignment->role }}</td>
                                    <td class="text-end">
                                        <form action="{{ route('workflows.steps.assignments.destroy', ['step' => $step, 'assignment' => $assignment]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cette assignation?') }}')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal d'assignation -->
    <div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignModalLabel">{{ __('Ajouter une assignation') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('workflows.steps.assignments.store', $step) }}" method="POST">
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
                                @foreach(\Spatie\Permission\Models\Role::orderBy('name')->get() as $role)
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
</div>

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
