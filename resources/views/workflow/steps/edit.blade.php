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
                            <label for="estimated_duration" class="form-label">{{ __('Durée estimée (minutes)') }}</label>
                            <input type="number" class="form-control @error('estimated_duration') is-invalid @enderror" id="estimated_duration" name="estimated_duration" value="{{ old('estimated_duration', $step->estimated_duration) }}" min="0" step="1">
                            @error('estimated_duration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('Durée estimée en minutes pour compléter cette étape.') }}</small>
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

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="can_be_skipped" name="can_be_skipped" value="1" {{ old('can_be_skipped', $step->can_be_skipped) ? 'checked' : '' }}>
                            <label class="form-check-label" for="can_be_skipped">{{ __('Peut être ignorée') }}</label>
                            <small class="form-text d-block text-muted">{{ __('L\'étape peut être ignorée sous certaines conditions.') }}</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="configuration" class="form-label">{{ __('Configuration') }}</label>
                            <textarea class="form-control @error('configuration') is-invalid @enderror" id="configuration" name="configuration" rows="3">{{ old('configuration', json_encode($step->configuration, JSON_PRETTY_PRINT)) }}</textarea>
                            @error('configuration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('Configuration JSON pour cette étape (optionnel).') }}</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="conditions" class="form-label">{{ __('Conditions') }}</label>
                            <textarea class="form-control @error('conditions') is-invalid @enderror" id="conditions" name="conditions" rows="3">{{ old('conditions', json_encode($step->conditions, JSON_PRETTY_PRINT)) }}</textarea>
                            @error('conditions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('Conditions d\'exécution de l\'étape (optionnel).') }}</small>
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
                                        <label class="form-label">{{ __('Type d\'assigné') }}</label>
                                        <select name="assignments[{{ $index }}][assignee_type]" class="form-control assignee-type-select">
                                            <option value="user" {{ $assignment->assignee_type == 'user' ? 'selected' : '' }}>{{ __('Utilisateur') }}</option>
                                            <option value="organisation" {{ $assignment->assignee_type == 'organisation' ? 'selected' : '' }}>{{ __('Organisation') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group assignee-user" style="{{ $assignment->assignee_type == 'user' ? 'display: block;' : 'display: none;' }}">
                                        <label class="form-label">{{ __('Utilisateur') }}</label>
                                        <select name="assignments[{{ $index }}][assignee_id]" class="form-control user-select">
                                            <option value="">{{ __('Sélectionner un utilisateur') }}</option>
                                            @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                                <option value="{{ $user->id }}" {{ $assignment->assignee_user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group assignee-organisation" style="{{ $assignment->assignee_type == 'organisation' ? 'display: block;' : 'display: none;' }}">
                                        <label class="form-label">{{ __('Organisation') }}</label>
                                        <select name="assignments[{{ $index }}][organisation_id]" class="form-control organisation-select">
                                            <option value="">{{ __('Sélectionner une organisation') }}</option>
                                            @foreach(\App\Models\Organisation::orderBy('name')->get() as $org)
                                                <option value="{{ $org->id }}" {{ $assignment->assignee_organisation_id == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-2 organisation-users" style="{{ $assignment->assignee_type == 'organisation' ? 'display: block;' : 'display: none;' }}">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">{{ __('Utilisateurs de l\'organisation') }}</label>
                                        <select name="assignments[{{ $index }}][assignee_id]" class="form-control organisation-user-select">
                                            <option value="">{{ __('Sélectionner un utilisateur') }}</option>
                                            @if($assignment->assignee_type == 'organisation' && $assignment->assignee_organisation_id)
                                                @foreach(\App\Models\Organisation::find($assignment->assignee_organisation_id)?->users ?? [] as $user)
                                                    <option value="{{ $user->id }}" {{ $assignment->assignee_user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">{{ __('Rôle / Note') }}</label>
                                        <input type="text" name="assignments[{{ $index }}][role]" class="form-control"
                                               value="{{ $assignment->assignment_rules['role'] ?? '' }}"
                                               placeholder="{{ __('Ex: Approbateur, Vérificateur, etc.') }}">
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
                                        <label class="form-label">{{ __('Type d\'assigné') }}</label>
                                        <select name="assignments[0][assignee_type]" class="form-control assignee-type-select">
                                            <option value="user">{{ __('Utilisateur') }}</option>
                                            <option value="organisation">{{ __('Organisation') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group assignee-user">
                                        <label class="form-label">{{ __('Utilisateur') }}</label>
                                        <select name="assignments[0][assignee_id]" class="form-control user-select" disabled>
                                            <option value="">{{ __('Chargement des utilisateurs...') }}</option>
                                        </select>
                                    </div>

                                    <div class="form-group assignee-organisation" style="display: none;">
                                        <label class="form-label">{{ __('Organisation') }}</label>
                                        <select name="assignments[0][organisation_id]" class="form-control organisation-select">
                                            <option value="">{{ __('Sélectionner une organisation') }}</option>
                                            @foreach(\App\Models\Organisation::orderBy('name')->get() as $org)
                                                <option value="{{ $org->id }}">{{ $org->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-2 organisation-users" style="display: none;">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">{{ __('Utilisateurs de l\'organisation') }}</label>
                                        <select name="assignments[0][assignee_id]" class="form-control organisation-user-select" disabled>
                                            <option value="">{{ __('Sélectionner une organisation d\'abord') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">{{ __('Rôle / Note') }}</label>
                                        <input type="text" name="assignments[0][role]" class="form-control"
                                               placeholder="{{ __('Ex: Approbateur, Vérificateur, etc.') }}">
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

        function updateAssigneeTypeVisibility() {
            document.querySelectorAll('.assignee-type-select').forEach(select => {
                const row = select.closest('.assignment-row');
                const userDiv = row.querySelector('.assignee-user');
                const orgDiv = row.querySelector('.assignee-organisation');
                const orgUsersDiv = row.querySelector('.organisation-users');

                if (select.value === 'user') {
                    // Mode utilisateur : afficher tous les utilisateurs
                    userDiv.style.display = 'block';
                    orgDiv.style.display = 'none';
                    if (orgUsersDiv) orgUsersDiv.style.display = 'none';

                    // Charger tous les utilisateurs
                    const userSelect = row.querySelector('.user-select');
                    if (userSelect) {
                        loadUsersForOrganisation(null, userSelect, true, userSelect.value);
                    }
                } else {
                    // Mode organisation : afficher organisation puis utilisateurs de l'organisation
                    userDiv.style.display = 'none';
                    orgDiv.style.display = 'block';
                    if (orgUsersDiv) orgUsersDiv.style.display = 'block';

                    // Charger les utilisateurs de l'organisation si une organisation est sélectionnée
                    const orgSelect = row.querySelector('.organisation-select');
                    const orgUserSelect = row.querySelector('.organisation-user-select');
                    if (orgSelect && orgUserSelect && orgSelect.value) {
                        loadUsersForOrganisation(orgSelect.value, orgUserSelect, false, orgUserSelect.value);
                    }
                }
            });
        }

        function loadUsersForOrganisation(organisationId, userSelect, showAllUsers = false, selectedUserId = null) {
            const loadingOption = '<option value="">{{ __("Chargement...") }}</option>';
            userSelect.innerHTML = loadingOption;
            userSelect.disabled = true;

            let url;
            if (showAllUsers || !organisationId) {
                url = '/api/organisations';
            } else {
                url = `/api/organisations/${organisationId}/users`;
            }

            const fetchOptions = {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            };

            fetch(url, fetchOptions)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    userSelect.innerHTML = '<option value="">{{ __("Sélectionner un utilisateur") }}</option>';

                    if (showAllUsers || !organisationId) {
                        // Si on affiche tous les utilisateurs, on fait un appel pour récupérer tous les utilisateurs
                        return fetch('/api/organisations', fetchOptions)
                            .then(r => {
                                if (!r.ok) {
                                    throw new Error(`HTTP error! status: ${r.status}`);
                                }
                                return r.json();
                            })
                            .then(orgs => {
                                const allUsersPromises = orgs.map(org =>
                                    fetch(`/api/organisations/${org.id}/users`, fetchOptions)
                                        .then(r => {
                                            if (!r.ok) {
                                                throw new Error(`HTTP error! status: ${r.status}`);
                                            }
                                            return r.json();
                                        })
                                );
                                return Promise.all(allUsersPromises).then(userArrays => {
                                    const allUsers = userArrays.flat();
                                    // Supprimer les doublons par ID
                                    const uniqueUsers = allUsers.filter((user, index, self) =>
                                        index === self.findIndex(u => u.id === user.id)
                                    );
                                    return uniqueUsers.sort((a, b) => a.name.localeCompare(b.name));
                                });
                            });
                    } else {
                        return data;
                    }
                })
                .then(users => {
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
                    alert('Erreur lors du chargement des utilisateurs. Vérifiez la console pour plus de détails.');
                });
        }

        // Initialiser l'affichage et charger les utilisateurs pour les assignations existantes
        updateAssigneeTypeVisibility();

        // Écouter les changements de type d'assigné
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('assignee-type-select')) {
                updateAssigneeTypeVisibility();
            }

            // Gestion du changement d'organisation pour charger ses utilisateurs
            if (e.target.classList.contains('organisation-select')) {
                const row = e.target.closest('.assignment-row');
                const orgUserSelect = row.querySelector('.organisation-user-select');
                const organisationId = e.target.value;

                if (orgUserSelect) {
                    loadUsersForOrganisation(organisationId, orgUserSelect);
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

                // Réinitialiser les valeurs
                if (input.tagName === 'SELECT') {
                    input.selectedIndex = 0;
                } else if (input.tagName === 'INPUT') {
                    input.value = '';
                }
            });

            // Réinitialiser l'état des selects utilisateurs
            const userSelect = template.querySelector('.user-select');
            const orgUserSelect = template.querySelector('.organisation-user-select');

            if (userSelect) {
                userSelect.innerHTML = '<option value="">{{ __("Chargement des utilisateurs...") }}</option>';
                userSelect.disabled = true;
            }

            if (orgUserSelect) {
                orgUserSelect.innerHTML = '<option value="">{{ __("Sélectionner une organisation d\'abord") }}</option>';
                orgUserSelect.disabled = true;
            }

            // Afficher le bouton de suppression
            template.querySelector('.remove-assignment').style.display = 'inline-block';

            document.getElementById('assignments-container').appendChild(template);

            // Mettre à jour l'affichage en fonction du type d'assigné sélectionné
            updateAssigneeTypeVisibility();

            // Activer le bouton de suppression pour toutes les assignations si > 1
            if (document.querySelectorAll('.assignment-row').length > 1) {
                document.querySelectorAll('.assignment-row .remove-assignment').forEach(btn => {
                    btn.style.display = 'inline-block';
                });
            }
        });

        // Supprimer une assignation
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
    });
</script>
@endsection
