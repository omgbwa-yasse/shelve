@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Détails du paramètre</h5>
                    <div>
                        <a href="{{ route('settings.definitions.edit', $setting) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <a href="{{ route('settings.definitions.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Informations générales</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 30%">ID :</th>
                                    <td><code>{{ $setting->id }}</code></td>
                                </tr>
                                <tr>
                                    <th>Catégorie :</th>
                                    <td>
                                        @if($setting->category)
                                            <a href="{{ route('settings.categories.show', $setting->category) }}" class="badge bg-secondary text-decoration-none">
                                                {{ $setting->category->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">Sans catégorie</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Nom :</th>
                                    <td><code>{{ $setting->name }}</code></td>
                                </tr>
                                <tr>
                                    <th>Clé :</th>
                                    <td><code>{{ $setting->name }}</code></td>
                                </tr>
                                <tr>
                                    <th>Description :</th>
                                    <td>{{ $setting->description ?? 'Aucune description' }}</td>
                                </tr>
                                <tr>
                                    <th>Type :</th>
                                    <td>
                                        <span class="badge bg-info">{{ $setting->type }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Obligatoire :</th>
                                    <td>
                                        @if(isset($setting->is_required) && $setting->is_required)
                                            <span class="badge bg-danger">Oui</span>
                                        @else
                                            <span class="badge bg-secondary">Non</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Actif :</th>
                                    <td>
                                        @if(isset($setting->is_active) && $setting->is_active)
                                            <span class="badge bg-success">Oui</span>
                                        @else
                                            <span class="badge bg-secondary">Non</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Statut :</th>
                                    <td>
                                        @if($setting->is_system)
                                            <span class="badge bg-warning">Système</span>
                                        @else
                                            <span class="badge bg-success">Utilisateur</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Utilisateur spécifique :</th>
                                    <td>
                                        @if($setting->user)
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user text-primary me-2"></i>
                                                <div>
                                                    <strong>{{ $setting->user->name }}</strong>
                                                    <small class="text-muted d-block">{{ $setting->user->email }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Paramètre global (tous les utilisateurs)</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Organisation spécifique :</th>
                                    <td>
                                        @if($setting->organisation)
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-building text-primary me-2"></i>
                                                <strong>{{ $setting->organisation->name }}</strong>
                                            </div>
                                        @else
                                            <span class="text-muted">Paramètre global (toutes les organisations)</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Créé le :</th>
                                    <td>{{ $setting->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Modifié le :</th>
                                    <td>{{ $setting->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted">Valeurs</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 30%">Valeur par défaut :</th>
                                    <td>
                                        @if($setting->default_value !== null)
                                            <div class="border rounded p-2 bg-light">
                                                @if(is_bool($setting->default_value))
                                                    <span class="badge {{ $setting->default_value ? 'bg-success' : 'bg-secondary' }}">
                                                        {{ $setting->default_value ? 'true' : 'false' }}
                                                    </span>
                                                @elseif(is_array($setting->default_value) || is_object($setting->default_value))
                                                    <pre class="mb-0 small"><code>{{ json_encode($setting->default_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                @else
                                                    <code>{{ $setting->default_value }}</code>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">Aucune valeur par défaut</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Valeur actuelle :</th>
                                    <td>
                                        @if($setting->hasCustomValue())
                                            <div class="border rounded p-2 bg-success-subtle">
                                                <small class="text-success d-block mb-1">Valeur personnalisée :</small>
                                                @if(is_bool($setting->value))
                                                    <span class="badge {{ $setting->value ? 'bg-success' : 'bg-secondary' }}">
                                                        {{ $setting->value ? 'true' : 'false' }}
                                                    </span>
                                                @elseif(is_array($setting->value) || is_object($setting->value))
                                                    <pre class="mb-0 small"><code>{{ json_encode($setting->value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                @else
                                                    <code class="text-success">{{ $setting->value }}</code>
                                                @endif
                                            </div>
                                        @else
                                            <div class="text-muted">
                                                <em>Utilise la valeur par défaut</em>
                                                @if($setting->default_value !== null)
                                                    <div class="mt-1">
                                                        @if(is_bool($setting->default_value))
                                                            <span class="badge {{ $setting->default_value ? 'bg-success' : 'bg-secondary' }}">
                                                                {{ $setting->default_value ? 'true' : 'false' }}
                                                            </span>
                                                        @elseif(is_array($setting->default_value) || is_object($setting->default_value))
                                                            <pre class="mb-0 small text-muted"><code>{{ json_encode($setting->default_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                        @else
                                                            <code class="text-muted">{{ $setting->default_value }}</code>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Valeur effective :</th>
                                    <td>
                                        @php
                                            $effectiveValue = $setting->getEffectiveValue();
                                        @endphp
                                        <div class="border rounded p-2 bg-primary-subtle">
                                            <small class="text-primary d-block mb-1">Valeur utilisée par l'application :</small>
                                            @if(is_bool($effectiveValue))
                                                <span class="badge {{ $effectiveValue ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $effectiveValue ? 'true' : 'false' }}
                                                </span>
                                            @elseif(is_array($effectiveValue) || is_object($effectiveValue))
                                                <pre class="mb-0 small"><code>{{ json_encode($effectiveValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                            @else
                                                <code class="text-primary">{{ $effectiveValue }}</code>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($setting->description)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-muted">Description</h6>
                            <div class="bg-light p-3 rounded">
                                {{ $setting->description }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($setting->constraints)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-muted">Contraintes</h6>
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <pre class="mb-0">{{ json_encode($setting->constraints, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    </div>
                    @endif
                        </div>
                    </div>
                    @endif

                    <!-- Actions pour personnaliser le paramètre -->
                    @if(!$setting->hasCustomValue())
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Personnaliser ce paramètre</h6>
                                    <p class="card-text text-muted">Ce paramètre utilise actuellement sa valeur par défaut. Vous pouvez le personnaliser pour votre compte.</p>
                                    <button type="button" class="btn btn-primary" onclick="showCustomizeModal()">
                                        <i class="fas fa-cog"></i> Personnaliser
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card bg-success bg-opacity-10">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-success">Paramètre personnalisé</h6>
                                    <p class="card-text">Ce paramètre a été personnalisé pour votre compte.</p>
                                    <button type="button" class="btn btn-warning" onclick="resetSetting()">
                                        <i class="fas fa-undo"></i> Réinitialiser
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end">
                                <form method="POST" action="{{ route('settings.definitions.destroy', $setting) }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce paramètre ? Toutes les valeurs associées seront également supprimées.')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour personnaliser un paramètre -->
<div class="modal fade" id="customizeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Personnaliser le paramètre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="customizeForm">
                    @csrf
                    <div class="mb-3">
                        <label for="customValue" class="form-label">Nouvelle valeur</label>
                        <input type="text" class="form-control" id="customValue" name="value" required>
                        <div class="form-text" id="typeHelp">Type: {{ $setting->type }}</div>
                        <div class="form-text text-muted" id="exampleHelp"></div>
                    </div>
                    <div id="errorAlert" class="alert alert-danger" style="display: none;"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="saveBtn">
                    <span class="spinner-border spinner-border-sm" aria-hidden="true" style="display: none;"></span>
                    <span class="visually-hidden" id="loadingText" style="display: none;">Chargement...</span>
                    <span id="buttonText">Enregistrer</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showCustomizeModal() {
    const settingType = '{{ $setting->type }}';
    const currentValue = {{ $setting->hasCustomValue() ? json_encode($setting->value) : json_encode($setting->default_value) }};
    const input = document.getElementById('customValue');
    const errorAlert = document.getElementById('errorAlert');
    const exampleHelp = document.getElementById('exampleHelp');

    // Réinitialiser le modal
    errorAlert.style.display = 'none';
    input.value = '';

    // Configurer le type d'input selon le type du paramètre
    configureInputForType(input, settingType);

    // Pré-remplir avec la valeur actuelle
    if (currentValue !== null && currentValue !== undefined) {
        if (settingType === 'boolean') {
            input.value = currentValue ? 'true' : 'false';
        } else if (settingType === 'array' || settingType === 'json') {
            input.value = JSON.stringify(currentValue, null, 2);
        } else {
            input.value = String(currentValue);
        }
    }

    // Afficher des exemples selon le type
    showTypeExamples(exampleHelp, settingType);

    new bootstrap.Modal(document.getElementById('customizeModal')).show();
}

function configureInputForType(input, type) {
    // Supprimer les anciens attributs
    input.removeAttribute('type');
    input.removeAttribute('step');
    input.removeAttribute('min');
    input.removeAttribute('max');

    switch (type) {
        case 'integer':
            input.type = 'number';
            input.step = '1';
            break;
        case 'float':
            input.type = 'number';
            input.step = '0.01';
            break;
        case 'boolean':
            input.type = 'text';
            // Pour les booleans, on accepte true/false, 1/0, yes/no, on/off
            break;
        case 'array':
        case 'json':
            input.type = 'text';
            // Utiliser une textarea pour les structures complexes
            input.style.minHeight = '100px';
            break;
        case 'string':
        default:
            input.type = 'text';
            break;
    }
}

function showTypeExamples(helpElement, type) {
    let examples = '';

    switch (type) {
        case 'boolean':
            examples = 'Exemples: true, false, 1, 0, yes, no, on, off';
            break;
        case 'integer':
            examples = 'Exemple: 42';
            break;
        case 'float':
            examples = 'Exemple: 3.14';
            break;
        case 'array':
            examples = 'Exemple: ["valeur1", "valeur2"] ou [1, 2, 3]';
            break;
        case 'json':
            examples = 'Exemple: {"clé": "valeur"} ou ["item1", "item2"]';
            break;
        case 'string':
        default:
            examples = 'Texte libre';
            break;
    }

    helpElement.textContent = examples;
}

function saveCustomValue() {
    const value = document.getElementById('customValue').value.trim();
    const settingId = {{ $setting->id }};
    const settingType = '{{ $setting->type }}';
    const saveBtn = document.getElementById('saveBtn');
    const buttonText = document.getElementById('buttonText');
    const loadingText = document.getElementById('loadingText');
    const spinner = saveBtn.querySelector('.spinner-border');
    const errorAlert = document.getElementById('errorAlert');

    // Validation côté client basique
    if (!value) {
        showError('La valeur ne peut pas être vide.');
        return;
    }

    // Validation spécifique selon le type
    if (!validateValueForType(value, settingType)) {
        return;
    }

    // Afficher le loading
    saveBtn.disabled = true;
    spinner.style.display = 'inline-block';
    loadingText.style.display = 'inline';
    buttonText.style.display = 'none';
    errorAlert.style.display = 'none';

    fetch(`/settings/definitions/${settingId}/set-value`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ value: value })
    })
    .then(response => response.json())
    .then(data => {
        if (data.errors && data.errors.value) {
            showError('Erreur de validation: ' + data.errors.value[0]);
        } else if (data.error) {
            showError('Erreur: ' + (data.errors?.value?.[0] || data.error));
        } else {
            // Succès - recharger la page
            location.reload();
        }
    })
    .catch(error => {
        console.error('Erreur lors de la sauvegarde:', error);
        showError('Erreur lors de la sauvegarde. Veuillez réessayer.');
    })
    .finally(() => {
        // Masquer le loading
        saveBtn.disabled = false;
        spinner.style.display = 'none';
        loadingText.style.display = 'none';
        buttonText.style.display = 'inline';
    });
}

function validateValueForType(value, type) {
    switch (type) {
        case 'integer':
            if (!/^-?\d+$/.test(value)) {
                showError('Veuillez saisir un nombre entier valide.');
                return false;
            }
            break;
        case 'float':
            if (!/^-?\d*\.?\d+$/.test(value)) {
                showError('Veuillez saisir un nombre décimal valide.');
                return false;
            }
            break;
        case 'boolean':
            const validBooleans = ['true', 'false', '1', '0', 'yes', 'no', 'on', 'off'];
            if (!validBooleans.includes(value.toLowerCase())) {
                showError('Veuillez saisir une valeur booléenne valide (true/false, 1/0, yes/no, on/off).');
                return false;
            }
            break;
        case 'array':
        case 'json':
            try {
                JSON.parse(value);
            } catch (e) {
                showError('Veuillez saisir un JSON valide.');
                return false;
            }
            break;
    }
    return true;
}

function showError(message) {
    const errorAlert = document.getElementById('errorAlert');
    errorAlert.textContent = message;
    errorAlert.style.display = 'block';
}

function resetSetting() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser ce paramètre à sa valeur par défaut ?')) {
        const settingId = {{ $setting->id }};

        fetch(`/settings/definitions/${settingId}/reset-value`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                location.reload();
            } else {
                alert('Erreur lors de la réinitialisation');
            }
        })
        .catch(error => {
            alert('Erreur lors de la réinitialisation');
            console.error(error);
        });
    }
}
</script>
@endsection
