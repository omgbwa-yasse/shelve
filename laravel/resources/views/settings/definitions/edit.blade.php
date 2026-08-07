@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Personnaliser le paramètre</h5>
                    <a href="{{ route('settings.definitions.show', $setting) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.definitions.update', $setting) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h6 class="mb-0 text-muted">Informations du paramètre (lecture seule)</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Nom :</strong> <code>{{ $setting->name }}</code>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Type :</strong> <span class="badge bg-info">{{ $setting->type }}</span>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-12">
                                                <strong>Description :</strong> {{ $setting->description ?? 'Aucune description' }}
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-12">
                                                <strong>Valeur par défaut :</strong>
                                                @if($setting->default_value !== null)
                                                    @if(is_bool($setting->default_value))
                                                        <span class="badge {{ $setting->default_value ? 'bg-success' : 'bg-secondary' }}">
                                                            {{ $setting->default_value ? 'true' : 'false' }}
                                                        </span>
                                                    @elseif(is_array($setting->default_value) || is_object($setting->default_value))
                                                        <pre class="mb-0 small bg-white p-2 rounded border"><code>{{ json_encode($setting->default_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                    @else
                                                        <code>{{ $setting->default_value }}</code>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Aucune valeur par défaut</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h6 class="mb-0 text-muted">Informations du paramètre (lecture seule)</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Nom :</strong> <code>{{ $setting->name }}</code>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Type :</strong> <span class="badge bg-info">{{ $setting->type }}</span>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-12">
                                                <strong>Description :</strong> {{ $setting->description ?? 'Aucune description' }}
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-12">
                                                <strong>Valeur par défaut :</strong>
                                                @if($setting->default_value !== null)
                                                    @if(is_bool($setting->default_value))
                                                        <span class="badge {{ $setting->default_value ? 'bg-success' : 'bg-secondary' }}">
                                                            {{ $setting->default_value ? 'true' : 'false' }}
                                                        </span>
                                                    @elseif(is_array($setting->default_value) || is_object($setting->default_value))
                                                        <pre class="mb-0 small bg-white p-2 rounded border"><code>{{ json_encode($setting->default_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                    @else
                                                        <code>{{ $setting->default_value }}</code>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Aucune valeur par défaut</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Personnaliser la valeur</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="value" class="form-label">Votre valeur personnalisée</label>
                                            <div id="valueContainer">
                                                <!-- Le contenu sera généré dynamiquement par JavaScript -->
                                                <textarea class="form-control @error('value') is-invalid @enderror"
                                                          id="value"
                                                          name="value"
                                                          rows="3"
                                                          placeholder="Votre valeur personnalisée (laissez vide pour utiliser la valeur par défaut)...">{{ old('value', json_encode($setting->value, JSON_PRETTY_PRINT)) }}</textarea>
                                            </div>
                                            @error('value')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted" id="valueHelp">
                                                Laissez ce champ vide pour utiliser la valeur par défaut
                                            </small>
                                        </div>

                                        @if($setting->user_id || $setting->organisation_id)
                                        <div class="alert alert-info">
                                            <strong>Portée du paramètre :</strong>
                                            @if($setting->user_id)
                                                Spécifique à l'utilisateur : {{ $setting->user->name ?? 'N/A' }}
                                            @endif
                                            @if($setting->organisation_id)
                                                Spécifique à l'organisation : {{ $setting->organisation->name ?? 'N/A' }}
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">Annuler</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Sauvegarder ma valeur
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
    const valueContainer = document.getElementById('valueContainer');
    const valueHelp = document.getElementById('valueHelp');

    // Récupérer les valeurs actuelles depuis PHP
    const currentValue = @json($setting->value);
    const currentType = '{{ $setting->type }}';

    // Initialiser avec le type actuel et la valeur actuelle
    updateValueInput(currentType, currentValue);

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
                               placeholder="Votre valeur personnalisée (vide = valeur par défaut)">`;
                valueHelp.textContent = 'Texte libre - Laissez vide pour utiliser la valeur par défaut';
                break;

            case 'integer':
                inputHtml = `<input type="number" class="form-control @error('value') is-invalid @enderror"
                               id="value" name="value" value="${escapeHtml(String(currentValue || ''))}"
                               placeholder="Nombre entier" step="1">`;
                valueHelp.textContent = 'Nombre entier - Laissez vide pour utiliser la valeur par défaut';
                break;

            case 'boolean':
                const isChecked = currentValue === true || currentValue === 'true' || currentValue === 1 || currentValue === '1';
                inputHtml = `<div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="value" name="value" value="1" ${isChecked ? 'checked' : ''}>
                    <label class="form-check-label" for="value">
                        ${isChecked ? 'Activé (true)' : 'Désactivé (false)'}
                    </label>
                </div>
                <small class="text-muted d-block mt-1">
                    <input type="checkbox" id="use_default" onchange="toggleDefaultValue()"> Utiliser la valeur par défaut
                </small>`;
                valueHelp.textContent = 'Cochez la case pour définir votre préférence';
                break;

            case 'json':
                const jsonValue = formatJsonValue(currentValue);
                inputHtml = `<textarea class="form-control @error('value') is-invalid @enderror"
                               id="value" name="value" rows="4"
                               placeholder='{"clé": "valeur"} ou ["item1", "item2"] - Laissez vide pour valeur par défaut'>${escapeHtml(jsonValue)}</textarea>`;
                valueHelp.textContent = 'Format JSON - Laissez vide pour utiliser la valeur par défaut';
                break;

            default:
                inputHtml = `<textarea class="form-control @error('value') is-invalid @enderror"
                               id="value" name="value" rows="3"
                               placeholder="Votre valeur personnalisée (vide = valeur par défaut)...">${escapeHtml(String(currentValue || ''))}</textarea>`;
                valueHelp.textContent = 'Laissez vide pour utiliser la valeur par défaut';
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

    // Fonction pour gérer l'utilisation de la valeur par défaut pour les booléens
    window.toggleDefaultValue = function() {
        const useDefault = document.getElementById('use_default');
        const valueField = document.getElementById('value');

        if (useDefault && valueField) {
            if (useDefault.checked) {
                valueField.disabled = true;
                valueField.checked = false;
            } else {
                valueField.disabled = false;
            }
        }
    }
});
</script>
@endsection
