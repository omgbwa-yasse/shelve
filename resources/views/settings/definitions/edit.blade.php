@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Modifier le paramètre</h5>
                    <a href="{{ route('settings.definitions.show', $setting) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.definitions.update', $setting) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Catégorie *</label>
                                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                        <option value="">Sélectionner une catégorie</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ (old('category_id', $setting->category_id) == $category->id) ? 'selected' : '' }}>
                                                {{ str_repeat('— ', $category->depth ?? 0) }}{{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom du paramètre *</label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name', $setting->name) }}"
                                           required
                                           placeholder="ex: max_upload_size">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Identifiant unique du paramètre (snake_case recommandé)
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description *</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                              id="description"
                                              name="description"
                                              rows="3"
                                              required
                                              placeholder="Description détaillée du paramètre...">{{ old('description', $setting->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Utilisateur spécifique</label>
                                    <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                                        <option value="">Paramètre global (tous les utilisateurs)</option>
                                        @if(isset($users))
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}"
                                                    {{ (old('user_id', $setting->user_id) == $user->id) ? 'selected' : '' }}>
                                                    {{ $user->name }} ({{ $user->email }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Laisser vide pour un paramètre global
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="organisation_id" class="form-label">Organisation spécifique</label>
                                    <select class="form-select @error('organisation_id') is-invalid @enderror" id="organisation_id" name="organisation_id">
                                        <option value="">Paramètre global (toutes les organisations)</option>
                                        @if(isset($organisations))
                                            @foreach($organisations as $organisation)
                                                <option value="{{ $organisation->id }}"
                                                    {{ (old('organisation_id', $setting->organisation_id) == $organisation->id) ? 'selected' : '' }}>
                                                    {{ $organisation->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('organisation_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Laisser vide pour un paramètre global
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Type *</label>
                                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                        <option value="">Sélectionner un type</option>
                                        <option value="string" {{ old('type', $setting->type) == 'string' ? 'selected' : '' }}>Chaîne de caractères</option>
                                        <option value="integer" {{ old('type', $setting->type) == 'integer' ? 'selected' : '' }}>Nombre entier</option>
                                        <option value="boolean" {{ old('type', $setting->type) == 'boolean' ? 'selected' : '' }}>Booléen</option>
                                        <option value="json" {{ old('type', $setting->type) == 'json' ? 'selected' : '' }}>JSON</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                              id="description"
                                              name="description"
                                              rows="3"
                                              placeholder="Description détaillée du paramètre...">{{ old('description', $setting->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="default_value" class="form-label">Valeur par défaut</label>
                                    <div id="defaultValueContainer">
                                        <!-- Le contenu sera généré dynamiquement par JavaScript -->
                                        <textarea class="form-control @error('default_value') is-invalid @enderror"
                                                  id="default_value"
                                                  name="default_value"
                                                  rows="3"
                                                  placeholder="Valeur par défaut...">{{ old('default_value', json_encode($setting->default_value, JSON_PRETTY_PRINT)) }}</textarea>
                                    </div>
                                    @error('default_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted" id="defaultValueHelp">
                                        Format selon le type sélectionné
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="constraints" class="form-label">Contraintes</label>
                                    <textarea class="form-control @error('constraints') is-invalid @enderror"
                                              id="constraints"
                                              name="constraints"
                                              rows="3"
                                              placeholder="Contraintes (JSON)...">{{ old('constraints', json_encode($setting->constraints, JSON_PRETTY_PRINT)) }}</textarea>
                                    @error('constraints')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Format JSON : {"min": 1, "max": 100} ou {"min_length": 5, "max_length": 255}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="value" class="form-label">Valeur actuelle</label>
                                    <div id="valueContainer">
                                        <!-- Le contenu sera généré dynamiquement par JavaScript -->
                                        <textarea class="form-control @error('value') is-invalid @enderror"
                                                  id="value"
                                                  name="value"
                                                  rows="3"
                                                  placeholder="Valeur actuelle...">{{ old('value', json_encode($setting->value, JSON_PRETTY_PRINT)) }}</textarea>
                                    </div>
                                    @error('value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted" id="valueHelp">
                                        Valeur actuelle du paramètre (vide = utilise la valeur par défaut)
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="is_system"
                                               name="is_system"
                                               {{ old('is_system', $setting->is_system) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_system">
                                            Paramètre système
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Les paramètres système ne peuvent pas être modifiés par les utilisateurs
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Space for future fields -->
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="is_required"
                                               name="is_required"
                                               {{ old('is_required', $setting->is_required ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_required">
                                            Paramètre obligatoire
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="is_active"
                                               name="is_active"
                                               {{ old('is_active', $setting->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Paramètre actif
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">Annuler</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const defaultValueContainer = document.getElementById('defaultValueContainer');
    const defaultValueHelp = document.getElementById('defaultValueHelp');
    const valueContainer = document.getElementById('valueContainer');
    const valueHelp = document.getElementById('valueHelp');
    const constraintsField = document.getElementById('constraints');

    // Récupérer les valeurs actuelles depuis PHP
    const currentDefaultValue = @json($setting->default_value);
    const currentValue = @json($setting->value);
    const currentType = '{{ $setting->type }}';

    // Initialiser avec le type actuel et les valeurs actuelles
    updateDefaultValueInput(currentType, currentDefaultValue);
    updateValueInput(currentType, currentValue);

    // Écouter les changements de type
    typeSelect.addEventListener('change', function() {
        updateDefaultValueInput(this.value, null);
        updateValueInput(this.value, null);
        updateConstraintsPlaceholder(this.value);
    });

    function updateDefaultValueInput(type, currentValue = null) {
        // Si pas de valeur fournie, essayer de récupérer depuis le champ actuel
        if (currentValue === null) {
            currentValue = getCurrentFieldValue('default_value');
        }

        let inputHtml = '';

        switch (type) {
            case 'string':
                inputHtml = `<input type="text" class="form-control @error('default_value') is-invalid @enderror"
                               id="default_value" name="default_value" value="${escapeHtml(String(currentValue || ''))}"
                               placeholder="Texte par défaut">`;
                defaultValueHelp.textContent = 'Texte libre';
                break;

            case 'integer':
                inputHtml = `<input type="number" class="form-control @error('default_value') is-invalid @enderror"
                               id="default_value" name="default_value" value="${escapeHtml(String(currentValue || ''))}"
                               placeholder="0" step="1">`;
                defaultValueHelp.textContent = 'Nombre entier (ex: 10, -5, 100)';
                break;

            case 'boolean':
                const isChecked = currentValue === true || currentValue === 'true' || currentValue === 1 || currentValue === '1';
                inputHtml = `<div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="default_value" name="default_value" value="1" ${isChecked ? 'checked' : ''}>
                    <label class="form-check-label" for="default_value">
                        ${isChecked ? 'Activé (true)' : 'Désactivé (false)'}
                    </label>
                </div>`;
                defaultValueHelp.textContent = 'État par défaut (coché = true, décoché = false)';
                break;

            case 'json':
                const jsonValue = formatJsonValue(currentValue);
                inputHtml = `<textarea class="form-control @error('default_value') is-invalid @enderror"
                               id="default_value" name="default_value" rows="3"
                               placeholder='{"clé": "valeur"} ou ["item1", "item2"]'>${escapeHtml(jsonValue)}</textarea>`;
                defaultValueHelp.textContent = 'Format JSON (ex: {"clé": "valeur"} ou ["item1", "item2"])';
                break;

            default:
                inputHtml = `<textarea class="form-control @error('default_value') is-invalid @enderror"
                               id="default_value" name="default_value" rows="3"
                               placeholder="Valeur par défaut...">${escapeHtml(String(currentValue || ''))}</textarea>`;
                defaultValueHelp.textContent = 'Valeur selon le type sélectionné';
        }

        defaultValueContainer.innerHTML = inputHtml;

        // Ré-attacher l'écouteur pour le checkbox boolean
        if (type === 'boolean') {
            const checkbox = document.getElementById('default_value');
            if (checkbox) {
                checkbox.addEventListener('change', function() {
                    const label = this.parentNode.querySelector('.form-check-label');
                    if (label) {
                        label.textContent = this.checked ? 'Activé (true)' : 'Désactivé (false)';
                    }
                });
            }
        }
    }

    function updateValueInput(type, currentValue = null) {
        // Si pas de valeur fournie, essayer de récupérer depuis le champ actuel
        if (currentValue === null) {
            currentValue = getCurrentFieldValue('value');
        }

        let inputHtml = '';

        switch (type) {
            case 'string':
                inputHtml = `<input type="text" class="form-control @error('value') is-invalid @enderror"
                               id="value" name="value" value="${escapeHtml(String(currentValue || ''))}"
                               placeholder="Valeur actuelle">`;
                valueHelp.textContent = 'Valeur actuelle du paramètre (vide = utilise la valeur par défaut)';
                break;

            case 'integer':
                inputHtml = `<input type="number" class="form-control @error('value') is-invalid @enderror"
                               id="value" name="value" value="${escapeHtml(String(currentValue || ''))}"
                               placeholder="Nombre" step="1">`;
                valueHelp.textContent = 'Valeur actuelle du paramètre (vide = utilise la valeur par défaut)';
                break;

            case 'boolean':
                const isChecked = currentValue === true || currentValue === 'true' || currentValue === 1 || currentValue === '1';
                inputHtml = `<div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="value" name="value" value="1" ${isChecked ? 'checked' : ''}>
                    <label class="form-check-label" for="value">
                        ${isChecked ? 'Activé (true)' : 'Désactivé (false)'}
                    </label>
                </div>`;
                valueHelp.textContent = 'Valeur actuelle du paramètre (vide = utilise la valeur par défaut)';
                break;

            case 'json':
                const jsonValue = formatJsonValue(currentValue);
                inputHtml = `<textarea class="form-control @error('value') is-invalid @enderror"
                               id="value" name="value" rows="3"
                               placeholder='{"clé": "valeur"} ou ["item1", "item2"] (vide = utilise valeur par défaut)'>${escapeHtml(jsonValue)}</textarea>`;
                valueHelp.textContent = 'Valeur actuelle du paramètre (vide = utilise la valeur par défaut)';
                break;

            default:
                inputHtml = `<textarea class="form-control @error('value') is-invalid @enderror"
                               id="value" name="value" rows="3"
                               placeholder="Valeur actuelle (vide = utilise valeur par défaut)...">${escapeHtml(String(currentValue || ''))}</textarea>`;
                valueHelp.textContent = 'Valeur actuelle du paramètre (vide = utilise la valeur par défaut)';
        }

        valueContainer.innerHTML = inputHtml;

        // Ré-attacher l'écouteur pour le checkbox boolean
        if (type === 'boolean') {
            const checkbox = document.getElementById('value');
            if (checkbox) {
                checkbox.addEventListener('change', function() {
                    const label = this.parentNode.querySelector('.form-check-label');
                    if (label) {
                        label.textContent = this.checked ? 'Activé (true)' : 'Désactivé (false)';
                    }
                });
            }
        }
    }

    function updateConstraintsPlaceholder(type) {
        const placeholders = {
            'string': '{"min_length": 1, "max_length": 255, "pattern": "^[A-Za-z]+$"}',
            'integer': '{"min": 0, "max": 100}',
            'boolean': '{}',
            'json': '{}'
        };

        constraintsField.placeholder = placeholders[type] || 'Contraintes spécifiques au type...';
    }

    function getCurrentFieldValue(fieldName) {
        const field = document.getElementById(fieldName);
        if (!field) return null;

        if (field.type === 'checkbox') {
            return field.checked;
        }
        return field.value || null;
    }

    function formatJsonValue(value) {
        if (value === null || value === undefined) return '';

        if (typeof value === 'object') {
            return JSON.stringify(value, null, 2);
        }
        return String(value);
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';

        const div = document.createElement('div');
        div.textContent = String(text);
        return div.innerHTML;
    }
});
</script>
@endsection
