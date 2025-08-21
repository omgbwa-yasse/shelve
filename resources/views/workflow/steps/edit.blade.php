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
                <a href="{{ route('workflows.steps.show', [$step->template, $step]) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    {{ __('Annuler et retourner') }}
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('workflows.steps.update', [$step->template, $step]) }}" method="POST">
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
                            <label for="step_type" class="form-label">{{ __('Type d\'étape') }} <span class="text-danger">*</span></label>
                            <select class="form-control @error('step_type') is-invalid @enderror" id="step_type" name="step_type" required>
                                @foreach(\App\Enums\WorkflowStepType::cases() as $type)
                                    <option value="{{ $type->value }}" {{ old('step_type', $step->step_type->value) == $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                                @endforeach
                            </select>
                            @error('step_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="estimated_hours" class="form-label">{{ __('Durée estimée (heures)') }}</label>
                            <input type="number" class="form-control @error('estimated_hours') is-invalid @enderror" id="estimated_hours" name="estimated_hours" value="{{ old('estimated_hours', $step->estimated_hours) }}" min="0" step="1">
                            @error('estimated_hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('Durée estimée en heures pour compléter cette étape.') }}</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="order_index" class="form-label">{{ __('Position') }}</label>
                            <input type="number" class="form-control @error('order_index') is-invalid @enderror" id="order_index" name="order_index" value="{{ old('order_index', $step->order_index) }}" min="0" required>
                            @error('order_index')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="1" {{ old('is_required', $step->is_required) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_required">{{ __('Étape obligatoire') }}</label>
                        </div>
                    </div>
                </div>

                <div class="border-top pt-3 mt-4 mb-3">
                    <h4>{{ __('Assignations') }}</h4>
                    <p class="text-muted">{{ __('Qui doit effectuer cette étape ?') }}</p>

                    <div id="assignments-container">
                        @forelse($step->assignments as $index => $assignment)
                        <div class="assignment-row mb-3 border p-3 rounded-3">
                            <input type="hidden" name="assignments[{{ $index }}][assignment_id]" value="{{ $assignment->id }}">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="user_select_{{ $index }}" class="form-label">{{ __('Utilisateur') }}</label>
                                        <select id="user_select_{{ $index }}" name="assignments[{{ $index }}][assignee_user_id]" class="form-control user-select">
                                            <option value="">{{ __('Sélectionner un utilisateur (optionnel)') }}</option>
                                            @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                                <option value="{{ $user->id }}" {{ $assignment->assignee_user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="organisation_select_{{ $index }}" class="form-label">{{ __('Organisation') }}</label>
                                        <select id="organisation_select_{{ $index }}" name="assignments[{{ $index }}][assignee_organisation_id]" class="form-control organisation-select">
                                            <option value="">{{ __('Sélectionner une organisation (optionnel)') }}</option>
                                            @foreach(\App\Models\Organisation::orderBy('name')->get() as $org)
                                                <option value="{{ $org->id }}" {{ $assignment->assignee_organisation_id == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>



                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-danger remove-assignment" style="{{ count($step->assignments) > 1 ? 'display: inline-block;' : 'display: none;' }}">
                                    <i class="bi bi-trash me-1"></i>{{ __('Retirer cette assignation') }}
                                </button>
                            </div>
                        </div>
                        @empty
                        <div class="assignment-row mb-3 border p-3 rounded-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="user_select_0" class="form-label">{{ __('Utilisateur') }}</label>
                                        <select id="user_select_0" name="assignments[0][assignee_user_id]" class="form-control user-select">
                                            <option value="">{{ __('Sélectionner un utilisateur (optionnel)') }}</option>
                                            @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="organisation_select_0" class="form-label">{{ __('Organisation') }}</label>
                                        <select id="organisation_select_0" name="assignments[0][assignee_organisation_id]" class="form-control organisation-select">
                                            <option value="">{{ __('Sélectionner une organisation (optionnel)') }}</option>
                                            @foreach(\App\Models\Organisation::orderBy('name')->get() as $org)
                                                <option value="{{ $org->id }}">{{ $org->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-danger remove-assignment" style="display: none;">
                                    <i class="bi bi-trash me-1"></i>{{ __('Retirer cette assignation') }}
                                </button>
                            </div>
                        </div>
                        @endforelse
                    </div>

                    <button type="button" id="add-assignment" class="btn btn-outline-primary mt-2">
                        <i class="bi bi-plus-circle me-1"></i>{{ __('Ajouter une assignation') }}
                    </button>
                </div>

                <div class="border-top pt-3 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>
                        {{ __('Mettre à jour l\'étape') }}
                    </button>
                    <a href="{{ route('workflows.steps.show', [$step->template, $step]) }}" class="btn btn-outline-secondary ms-2">
                        {{ __('Annuler') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion des assignations
        let assignmentIndex = {{ $step->assignments->count() }};

        function loadUsers(userSelect, selectedUserId = null) {
            const loadingOption = '<option value="">{{ __("Chargement...") }}</option>';
            userSelect.innerHTML = loadingOption;
            userSelect.disabled = true;

            // Charger tous les utilisateurs
            fetch('/api/users', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(users => {
                users.sort((a, b) => a.name.localeCompare(b.name));
                userSelect.innerHTML = '<option value="">{{ __("Sélectionner un utilisateur (optionnel)") }}</option>';

                users.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = user.name + (user.email ? ` (${user.email})` : '');
                    if (selectedUserId && user.id == selectedUserId) {
                        option.selected = true;
                    }
                    userSelect.appendChild(option);
                });
                userSelect.disabled = false;
            })
            .catch(error => {
                console.error('Erreur lors du chargement des utilisateurs:', error);
                userSelect.innerHTML = '<option value="">{{ __("Erreur de chargement") }}</option>';
                userSelect.disabled = false;
            });
        }
            });
        }

        // Initialiser le chargement des utilisateurs pour chaque assignation
        document.querySelectorAll('.user-select').forEach(userSelect => {
            loadUsers(userSelect, userSelect.value);
        });
        }

                // Ajouter un écouteur pour le bouton d'ajout d'assignation
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-assignment') || e.target.parentElement.classList.contains('remove-assignment')) {
                const row = e.target.closest('.assignment-row');

                // Ne pas supprimer s'il n'y a qu'une seule assignation
                if (document.querySelectorAll('.assignment-row').length > 1) {
                    row.remove();
                }

                // Masquer le bouton de suppression s'il ne reste qu'une seule assignation
                if (document.querySelectorAll('.assignment-row').length === 1) {
                    document.querySelector('.assignment-row .remove-assignment').style.display = 'none';
                }
            }
        });

        // Ajouter une nouvelle assignation
        document.getElementById('add-assignment').addEventListener('click', function() {
            assignmentIndex++;

            const template = document.querySelector('.assignment-row').cloneNode(true);

            // Supprimer l'input hidden d'ID pour les nouvelles assignations
            const hiddenInput = template.querySelector('input[type="hidden"]');
            if (hiddenInput) {
                hiddenInput.remove();
            }

            // Mettre à jour les noms des champs avec le nouvel index
            template.querySelectorAll('[name]').forEach(input => {
                const name = input.getAttribute('name');
                input.setAttribute('name', name.replace(/assignments\[\d+\]/, `assignments[${assignmentIndex}]`));

                // Mettre à jour les IDs aussi
                if (input.id) {
                    input.id = input.id.replace(/\d+$/, assignmentIndex);
                }

                // Réinitialiser les valeurs
                if (input.tagName === 'SELECT') {
                    input.selectedIndex = 0;
                } else if (input.tagName === 'INPUT') {
                    input.value = '';
                }
            });

            // Mettre à jour les labels qui référencent les IDs
            template.querySelectorAll('label').forEach(label => {
                if (label.getAttribute('for')) {
                    label.setAttribute('for', label.getAttribute('for').replace(/\d+$/, assignmentIndex));
                }
            });

            // Réinitialiser et charger les utilisateurs pour le select
            const userSelect = template.querySelector('.user-select');
            if (userSelect) {
                loadUsers(userSelect);
            }

            // Afficher le bouton de suppression
            template.querySelector('.remove-assignment').style.display = 'inline-block';

            document.getElementById('assignments-container').appendChild(template);

            // Activer le bouton de suppression pour toutes les assignations si > 1
            if (document.querySelectorAll('.assignment-row').length > 1) {
                document.querySelectorAll('.assignment-row .remove-assignment').forEach(btn => {
                    btn.style.display = 'inline-block';
                });
            }
        });
    });
</script>
@endsection
