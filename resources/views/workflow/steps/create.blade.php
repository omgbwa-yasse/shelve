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
                            <label for="estimated_duration" class="form-label">{{ __('Durée estimée (jours)') }}</label>
                            <input type="number" id="estimated_duration" name="estimated_duration" min="0" step="0.5"
                                   class="form-control @error('estimated_duration') is-invalid @enderror"
                                   value="{{ old('estimated_duration') }}">
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
                            <input class="form-check-input" type="checkbox" id="allow_comments" name="allow_comments" value="1"
                                   {{ old('allow_comments', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_comments">{{ __('Autoriser les commentaires') }}</label>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="completion_rules" class="form-label">{{ __('Règles de complétion') }}</label>
                    <textarea id="completion_rules" name="completion_rules" class="form-control @error('completion_rules') is-invalid @enderror"
                              rows="3" placeholder="{{ __('Ex: Critères pour marquer cette étape comme terminée') }}">{{ old('completion_rules') }}</textarea>
                    @error('completion_rules')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
                                        <select name="assignments[0][assignee_id]" class="form-control">
                                            <option value="">{{ __('Sélectionner un utilisateur') }}</option>
                                            @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group assignee-organisation" style="display: none;">
                                        <label class="form-label">{{ __('Organisation') }}</label>
                                        <select name="assignments[0][organisation_id]" class="form-control">
                                            <option value="">{{ __('Sélectionner une organisation') }}</option>
                                            @foreach(\App\Models\Organisation::orderBy('name')->get() as $org)
                                                <option value="{{ $org->id }}">{{ $org->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-12">
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
        // Gestion des assignations
        let assignmentIndex = 0;

        function updateAssigneeTypeVisibility() {
            document.querySelectorAll('.assignee-type-select').forEach(select => {
                const row = select.closest('.assignment-row');
                const userDiv = row.querySelector('.assignee-user');
                const orgDiv = row.querySelector('.assignee-organisation');

                if (select.value === 'user') {
                    userDiv.style.display = 'block';
                    orgDiv.style.display = 'none';
                } else {
                    userDiv.style.display = 'none';
                    orgDiv.style.display = 'block';
                }
            });
        }

        // Initialiser l'affichage
        updateAssigneeTypeVisibility();

        // Écouter les changements de type d'assigné
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('assignee-type-select')) {
                updateAssigneeTypeVisibility();
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

            // Afficher le bouton de suppression
            template.querySelector('.remove-assignment').style.display = 'inline-block';

            document.getElementById('assignments-container').appendChild(template);

            // Mettre à jour l'affichage en fonction du type d'assigné sélectionné
            updateAssigneeTypeVisibility();

            // Activer le bouton de suppression pour la première assignation
            if (document.querySelectorAll('.assignment-row').length > 1) {
                document.querySelector('.assignment-row .remove-assignment').style.display = 'inline-block';
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
