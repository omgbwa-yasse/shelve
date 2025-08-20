@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">
                <i class="bi bi-list-check me-2"></i>
                {{ __('Ajouter une étape au workflow') }}
            </h1>
            <div class="text-muted">{{ __('Modèle') }}: {{ $template->name }}</div>
        </div>
        <div class="col-auto">
            <a href="{{ route('workflows.templates.show', $template) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('Retour au modèle') }}
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('workflows.templates.steps.store', $template) }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name" class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="type" class="form-label">{{ __('Type d\'étape') }}</label>
                            <select id="type" name="type" class="form-control @error('type') is-invalid @enderror">
                                <option value="">{{ __('Standard') }}</option>
                                @foreach(\App\Enums\WorkflowStepType::cases() as $type)
                                    <option value="{{ $type->value }}" {{ old('type') == $type->value ? 'selected' : '' }}>
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="description" class="form-label">{{ __('Description') }}</label>
                    <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"
                              rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="estimated_duration" class="form-label">{{ __('Durée estimée (heures)') }}</label>
                            <input type="number" id="estimated_duration" name="estimated_duration" min="0" step="1"
                                   class="form-control @error('estimated_duration') is-invalid @enderror"
                                   value="{{ old('estimated_duration') }}">
                            <div class="form-text text-muted small">{{ __('Indiquez la durée en heures. Exemple : 2 pour 2 heures.') }}</div>
                            @error('estimated_duration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="order_index" class="form-label">{{ __('Position') }}</label>
                            <select id="order_index" name="order_index" class="form-control @error('order_index') is-invalid @enderror">
                                @php
                                    $count = $template->steps->count();
                                @endphp
                                @for($i = 0; $i <= $count; $i++)
                                    <option value="{{ $i }}" {{ old('order_index', $count) == $i ? 'selected' : '' }}>
                                        {{ $i + 1 }}{{ $i == $count ? ' (à la fin)' : '' }}
                                    </option>
                                @endfor
                            </select>
                            @error('order_index')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="1"
                                   {{ old('is_required', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_required">{{ __('Étape obligatoire') }}</label>
                            <div class="form-text text-muted small">{{ __('Une étape obligatoire doit être complétée pour avancer dans le workflow') }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="can_be_skipped" name="can_be_skipped" value="1"
                                   {{ old('can_be_skipped', false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="can_be_skipped">{{ __('Peut être ignorée') }}</label>
                            <div class="form-text text-muted small">{{ __('L\'étape peut être ignorée sous certaines conditions') }}</div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="configuration" class="form-label">{{ __('Configuration') }}</label>
                    <textarea id="configuration" name="configuration" class="form-control @error('configuration') is-invalid @enderror"
                              rows="3" placeholder="{ '{"role": "Agent du Courrier"}' }">{{ old('configuration') }}</textarea>
                    @error('configuration')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text text-muted small">{{ __('Format JSON pour les paramètres spécifiques de l\'étape. Exemple: {"role": "Agent du Courrier"}.') }}</div>
                </div>

                <div class="form-group mb-3">
                    <label for="conditions" class="form-label">{{ __('Conditions') }}</label>
                    <textarea id="conditions" name="conditions" class="form-control @error('conditions') is-invalid @enderror"
                              rows="3" placeholder="{ '{"if": "mail.status == \"sensitive\""}' }">{{ old('conditions') }}</textarea>
                    @error('conditions')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text text-muted small">{{ __('Format JSON pour définir les conditions d\'activation de l\'étape. Exemple: {"if": "mail.status == \"sensitive\""}.') }}</div>
                </div>

                <div class="border-top pt-3 mt-4 mb-3">
                    <h4>{{ __('Assignations') }}</h4>
                    <p class="text-muted">{{ __('Qui doit effectuer cette étape ?') }}</p>

                    <div id="assignments-container">
                        <!-- Les assignations seront ajoutées ici dynamiquement -->
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
                                        <label for="organisation_select_0" class="form-label">{{ __('Organisation') }}</label>
                                        <select id="organisation_select_0" name="assignments[0][organisation_id]" class="form-control organisation-select">
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
                                        <label for="organisation_user_select_0" class="form-label">{{ __('Utilisateurs de l\'organisation') }}</label>
                                        <select id="organisation_user_select_0" name="assignments[0][assignee_id]" class="form-control organisation-user-select" disabled>
                                            <option value="">{{ __('Sélectionner une organisation d\'abord') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                            <label for="role_0" class="form-label">{{ __('Rôle / Note') }}</label>
                                            <input id="role_0" type="text" name="assignments[0][role]" class="form-control"
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
                    </div>

                    <button type="button" id="add-assignment" class="btn btn-outline-primary mt-2">
                        <i class="bi bi-plus-circle me-1"></i>{{ __('Ajouter une assignation') }}
                    </button>
                </div>

                <div class="border-top pt-3 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>
                        {{ __('Créer l\'étape') }}
                    </button>
                    <a href="{{ route('workflows.templates.show', $template) }}" class="btn btn-outline-secondary ms-2">
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
        // Simple client-side JSON validation for configuration and conditions
        function validateJSONField(fieldId) {
            const field = document.getElementById(fieldId);
            if (!field) return true;
            const value = field.value.trim();
            if (!value) return true;
            try {
                JSON.parse(value);
                field.classList.remove('is-invalid');
                return true;
            } catch (e) {
                field.classList.add('is-invalid');
                return false;
            }
        }

        document.getElementById('configuration').addEventListener('blur', function() {
            validateJSONField('configuration');
        });
        document.getElementById('conditions').addEventListener('blur', function() {
            validateJSONField('conditions');
        });

        document.querySelector('form').addEventListener('submit', function(e) {
            const validConfig = validateJSONField('configuration');
            const validCond = validateJSONField('conditions');
            let validAssignments = true;
            document.querySelectorAll('.assignment-row').forEach(row => {
                const type = row.querySelector('.assignee-type-select')?.value;
                if (!type) {
                    validAssignments = false;
                    row.querySelector('.assignee-type-select').classList.add('is-invalid');
                }
                if (type === 'user') {
                    const user = row.querySelector('.user-select')?.value;
                    if (!user) {
                        validAssignments = false;
                        row.querySelector('.user-select').classList.add('is-invalid');
                    }
                }
                if (type === 'organisation') {
                    const org = row.querySelector('.organisation-select')?.value;
                    const orgUser = row.querySelector('.organisation-user-select')?.value;
                    if (!org) {
                        validAssignments = false;
                        row.querySelector('.organisation-select').classList.add('is-invalid');
                    }
                    if (!orgUser) {
                        validAssignments = false;
                        row.querySelector('.organisation-user-select').classList.add('is-invalid');
                    }
                }
            });
            if (!validConfig || !validCond || !validAssignments) {
                e.preventDefault();
                alert('Veuillez corriger les champs invalides (JSON ou assignations).');
            }
        });
        // Gestion des assignations
        let assignmentIndex = 0;

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
                        loadAllUsers(userSelect);
                    }
                } else {
                    // Mode organisation : afficher organisation puis utilisateurs de l'organisation
                    userDiv.style.display = 'none';
                    orgDiv.style.display = 'block';
                    if (orgUsersDiv) orgUsersDiv.style.display = 'block';

                    // Réinitialiser le select des utilisateurs d'organisation
                    const orgUserSelect = row.querySelector('.organisation-user-select');
                    if (orgUserSelect) {
                        orgUserSelect.innerHTML = '<option value="">{{ __("Sélectionner une organisation d\'abord") }}</option>';
                        orgUserSelect.disabled = true;
                    }
                }
            });
        }

        function loadAllUsers(userSelect) {
            const loadingOption = '<option value="">{{ __("Chargement...") }}</option>';
            userSelect.innerHTML = loadingOption;
            userSelect.disabled = true;

            fetch('/api/organisations', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(orgs => {
                const allUsersPromises = orgs.map(org =>
                    fetch(`/api/organisations/${org.id}/users`, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin'
                    }).then(r => {
                        if (!r.ok) {
                            throw new Error(`HTTP error! status: ${r.status}`);
                        }
                        return r.json();
                    })
                );
                return Promise.all(allUsersPromises);
            })
            .then(userArrays => {
                const allUsers = userArrays.flat();
                // Supprimer les doublons par ID
                const seen = new Set();
                const uniqueUsers = allUsers.filter(user => {
                    if (seen.has(user.id)) return false;
                    seen.add(user.id);
                    return true;
                });

                userSelect.innerHTML = '<option value="">{{ __("Sélectionner un utilisateur") }}</option>';
                uniqueUsers.sort((a, b) => a.name.localeCompare(b.name)).forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = user.name + (user.email ? ` (${user.email})` : '');
                    userSelect.appendChild(option);
                });
                userSelect.disabled = false;
            })
            .catch(error => {
                console.error('Erreur lors du chargement des utilisateurs:', error);
                userSelect.innerHTML = '<option value="">{{ __("Erreur de chargement") }}</option>';
                userSelect.disabled = true;
                window.alert('Erreur lors du chargement des utilisateurs. Veuillez réessayer ou contacter un administrateur.');
            });
        }
        }

        function loadUsersForOrganisation(organisationId, userSelect) {
            if (!organisationId) {
                userSelect.innerHTML = '<option value="">{{ __("Sélectionner une organisation d\'abord") }}</option>';
                userSelect.disabled = true;
                return;
            }

            const loadingOption = '<option value="">{{ __("Chargement...") }}</option>';
            userSelect.innerHTML = loadingOption;
            userSelect.disabled = true;

            fetch(`/api/organisations/${organisationId}/users`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(users => {
                // Supprimer les doublons par ID
                const seen = new Set();
                const uniqueUsers = users.filter(user => {
                    if (seen.has(user.id)) return false;
                    seen.add(user.id);
                    return true;
                });
                userSelect.innerHTML = '<option value="">{{ __("Sélectionner un utilisateur") }}</option>';
                uniqueUsers.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = user.name + (user.email ? ` (${user.email})` : '');
                    userSelect.appendChild(option);
                });
                userSelect.disabled = false;
            })
            .catch(error => {
                console.error('Erreur lors du chargement des utilisateurs:', error);
                userSelect.innerHTML = '<option value="">{{ __("Erreur de chargement") }}</option>';
                userSelect.disabled = true;
                window.alert('Erreur lors du chargement des utilisateurs pour l\'organisation. Veuillez réessayer ou contacter un administrateur.');
            });
        }
        }

        // Initialiser l'affichage
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

            // Initialiser les selects utilisateurs et organisations
            const userSelect = template.querySelector('.user-select');
            const orgSelect = template.querySelector('.organisation-select');
            const orgUserSelect = template.querySelector('.organisation-user-select');

            // Charger toutes les organisations dans organisation-select
            if (orgSelect) {
                fetch('/api/organisations', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(orgs => {
                    orgSelect.innerHTML = '<option value="">{{ __("Sélectionner une organisation") }}</option>';
                    orgs.forEach(org => {
                        const option = document.createElement('option');
                        option.value = org.id;
                        option.textContent = org.name;
                        orgSelect.appendChild(option);
                    });
                    orgSelect.disabled = false;
                })
                .catch(() => {
                    orgSelect.innerHTML = '<option value="">{{ __("Erreur de chargement") }}</option>';
                    orgSelect.disabled = false;
                });
            }

            // Charger tous les utilisateurs dans user-select
            if (userSelect) {
                loadAllUsers(userSelect);
            }

            // Réinitialiser organisation-user-select
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
