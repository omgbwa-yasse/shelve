@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/workflow-templates.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">
                <i class="bi bi-file-earmark-plus me-2"></i>
                {{ __('Créer un modèle de workflow') }}
            </h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('workflows.templates.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('Retour à la liste') }}
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('workflows.templates.store') }}" method="POST">
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
                            <label for="category" class="form-label">{{ __('Catégorie') }} <span class="text-danger">*</span></label>
                            <input type="text" id="category" name="category" class="form-control @error('category') is-invalid @enderror"
                                   value="{{ old('category') }}" required>
                            @error('category')
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

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">{{ __('Activer ce modèle') }}</label>
                </div>

                <!-- Section Configuration JSON -->
                <div class="card border-secondary mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bi bi-code-square me-2"></i>
                            {{ __('Configuration JSON (Optionnel)') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="enable_json_config"
                                   onchange="toggleJSONConfig()">
                            <label class="form-check-label" for="enable_json_config">
                                {{ __('Définir une configuration JSON personnalisée') }}
                            </label>
                        </div>

                        <div id="json-config-section" class="d-none">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                {{ __('Vous pouvez définir une configuration pour ce template. Ajoutez des étapes une par une ou utilisez l\'éditeur JSON avancé.') }}
                            </div>

                            <!-- Navigation par onglets -->
                            <ul class="nav nav-pills mb-3" id="configTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="form-tab" data-bs-toggle="pill" data-bs-target="#form-config" type="button" role="tab">
                                        <i class="bi bi-list-ul me-1"></i>
                                        {{ __('Formulaire') }}
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="json-tab" data-bs-toggle="pill" data-bs-target="#json-config" type="button" role="tab">
                                        <i class="bi bi-code me-1"></i>
                                        {{ __('JSON Avancé') }}
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="configTabsContent">
                                <!-- Onglet Formulaire -->
                                <div class="tab-pane fade show active" id="form-config" role="tabpanel">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-bold">{{ __('Étapes de configuration') }}</label>
                                            <button type="button" class="btn btn-sm btn-primary" onclick="addStepRow()">
                                                <i class="bi bi-plus-lg me-1"></i>
                                                {{ __('Ajouter une étape') }}
                                            </button>
                                        </div>

                                        <!-- En-têtes des colonnes -->
                                        <div class="row bg-light p-2 rounded mb-2 small fw-bold">
                                            <div class="col-2">{{ __('ID Étape') }}</div>
                                            <div class="col-3">{{ __('Nom') }}</div>
                                            <div class="col-1">{{ __('Ordre') }}</div>
                                            <div class="col-2">{{ __('Action ID') }}</div>
                                            <div class="col-2">{{ __('Org. ID') }}</div>
                                            <div class="col-1">{{ __('Auto') }}</div>
                                            <div class="col-1">{{ __('Actions') }}</div>
                                        </div>

                                        <!-- Container pour les lignes d'étapes -->
                                        <div id="steps-container">
                                            <!-- Les lignes seront ajoutées dynamiquement ici -->
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="validateFormConfig()">
                                            <i class="bi bi-check-circle me-1"></i>
                                            {{ __('Valider Configuration') }}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearAllSteps()">
                                            <i class="bi bi-trash me-1"></i>
                                            {{ __('Vider tout') }}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="previewFormAsJSON()">
                                            <i class="bi bi-eye me-1"></i>
                                            {{ __('Aperçu JSON') }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Onglet JSON Avancé -->
                                <div class="tab-pane fade" id="json-config" role="tabpanel">
                                    <div class="mb-3">
                                        <label for="configuration_json" class="form-label">{{ __('Configuration JSON') }}</label>
                                        <textarea id="configuration_json" name="configuration_json"
                                                 class="form-control font-monospace @error('configuration') is-invalid @enderror"
                                                 rows="10" placeholder='[
  {
    "id": "step_1",
    "name": "Première étape",
    "organisation_id": 1,
    "action_id": 1,
    "ordre": 1,
    "auto_assign": false,
    "timeout_hours": 24,
    "conditions": {},
    "metadata": {}
  }
]'>{{ old('configuration_json') }}</textarea>
                                        @error('configuration')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="validateJSONConfig()">
                                            <i class="bi bi-check-circle me-1"></i>
                                            {{ __('Valider JSON') }}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatJSONConfig()">
                                            <i class="bi bi-code me-1"></i>
                                            {{ __('Formater') }}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="addJSONExample()">
                                            <i class="bi bi-plus-circle me-1"></i>
                                            {{ __('Ajouter un exemple') }}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="syncFromForm()">
                                            <i class="bi bi-arrow-left-right me-1"></i>
                                            {{ __('Sync depuis formulaire') }}
                                        </button>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <input type="file" id="import-file" accept=".json" style="display: none;" onchange="importFromFile(event)">
                                            <button type="button" class="btn btn-outline-success" onclick="document.getElementById('import-file').click()">
                                                <i class="bi bi-upload me-1"></i>
                                                {{ __('Importer') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="json-validation-result" class="mt-3"></div>
                        </div>
                    </div>
                </div>

                <div class="border-top pt-3 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>
                        {{ __('Créer le modèle') }}
                    </button>
                    <a href="{{ route('workflows.templates.index') }}" class="btn btn-outline-secondary ms-2">
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
    let stepCounter = 0;

    // Gestionnaire pour le formulaire avec conversion JSON
    document.querySelector('form').addEventListener('submit', function(event) {
        const jsonConfigEnabled = document.getElementById('enable_json_config').checked;

        if (jsonConfigEnabled) {
            // Convertir les données du formulaire ou du JSON selon l'onglet actif
            const activeTab = document.querySelector('#configTabs .nav-link.active').id;

            if (activeTab === 'form-tab') {
                // Convertir depuis le formulaire
                convertFormToLaravel(this);
            } else {
                // Convertir depuis le JSON
                convertJSONToLaravel(this);
            }
        }
    });

    // Fonctions globales
    window.addStepRow = function() {
        stepCounter++;
        const container = document.getElementById('steps-container');
        const row = createStepRow(stepCounter);
        container.appendChild(row);
    };

    window.removeStepRow = function(rowId) {
        const row = document.getElementById(rowId);
        if (row) {
            row.remove();
        }
    };

    window.validateFormConfig = function() {
        const steps = getFormSteps();
        const validation = validateSteps(steps);
        showValidationResult('form-validation-result', validation);
    };

    window.clearAllSteps = function() {
        if (confirm('{{ __("Êtes-vous sûr de vouloir supprimer toutes les étapes ?") }}')) {
            document.getElementById('steps-container').innerHTML = '';
            stepCounter = 0;
        }
    };

    window.previewFormAsJSON = function() {
        const steps = getFormSteps();
        const textarea = document.getElementById('configuration_json');
        textarea.value = JSON.stringify(steps, null, 2);

        // Basculer vers l'onglet JSON
        const jsonTab = document.getElementById('json-tab');
        const tab = new bootstrap.Tab(jsonTab);
        tab.show();
    };

    window.syncFromForm = function() {
        const steps = getFormSteps();
        const textarea = document.getElementById('configuration_json');
        textarea.value = JSON.stringify(steps, null, 2);
        showAlert('success', 'Configuration synchronisée depuis le formulaire');
    };

    function createStepRow(id) {
        const div = document.createElement('div');
        div.className = 'row mb-2 step-row';
        div.id = `step-row-${id}`;

        div.innerHTML = `
            <div class="col-2">
                <input type="text" class="form-control form-control-sm" name="step_id_${id}"
                       placeholder="step_${id}" value="step_${id}" required>
            </div>
            <div class="col-3">
                <input type="text" class="form-control form-control-sm" name="step_name_${id}"
                       placeholder="{{ __('Nom de l\'étape') }}" required>
            </div>
            <div class="col-1">
                <input type="number" class="form-control form-control-sm" name="step_ordre_${id}"
                       value="${getNextOrder()}" min="1" required>
            </div>
            <div class="col-2">
                <input type="number" class="form-control form-control-sm" name="step_action_id_${id}"
                       placeholder="1" min="1" required>
            </div>
            <div class="col-2">
                <input type="number" class="form-control form-control-sm" name="step_organisation_id_${id}"
                       placeholder="{{ __('Optionnel') }}" min="1">
            </div>
            <div class="col-1">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="step_auto_assign_${id}">
                </div>
            </div>
            <div class="col-1">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStepRow('step-row-${id}')">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;

        return div;
    }

    function getNextOrder() {
        const existingOrders = Array.from(document.querySelectorAll('[name^="step_ordre_"]'))
            .map(input => parseInt(input.value) || 0);
        return existingOrders.length > 0 ? Math.max(...existingOrders) + 1 : 1;
    }

    function getFormSteps() {
        const steps = [];
        const stepRows = document.querySelectorAll('.step-row');

        stepRows.forEach(row => {
            const getId = (name) => row.querySelector(`[name*="${name}"]`)?.value || '';
            const getChecked = (name) => row.querySelector(`[name*="${name}"]`)?.checked || false;

            const step = {
                id: getId('step_id_'),
                name: getId('step_name_'),
                ordre: parseInt(getId('step_ordre_')) || 1,
                action_id: parseInt(getId('step_action_id_')) || 1,
                organisation_id: getId('step_organisation_id_') ? parseInt(getId('step_organisation_id_')) : null,
                auto_assign: getChecked('step_auto_assign_'),
                timeout_hours: 24,
                conditions: {},
                metadata: {}
            };

            if (step.id && step.name) {
                steps.push(step);
            }
        });

        return steps;
    }

    function validateSteps(steps) {
        const errors = [];
        const warnings = [];

        if (steps.length === 0) {
            errors.push('{{ __("Aucune étape définie") }}');
            return { errors, warnings };
        }

        // Vérifier l'unicité des IDs
        const ids = steps.map(s => s.id);
        const uniqueIds = [...new Set(ids)];
        if (ids.length !== uniqueIds.length) {
            errors.push('{{ __("Les IDs des étapes doivent être uniques") }}');
        }

        // Vérifier l'unicité des ordres
        const ordres = steps.map(s => s.ordre);
        const uniqueOrdres = [...new Set(ordres)];
        if (ordres.length !== uniqueOrdres.length) {
            errors.push('{{ __("Les ordres des étapes doivent être uniques") }}');
        }

        // Vérifier la continuité des ordres
        const sortedOrdres = ordres.sort((a, b) => a - b);
        for (let i = 0; i < sortedOrdres.length; i++) {
            if (sortedOrdres[i] !== i + 1) {
                warnings.push('{{ __("Les ordres des étapes ne sont pas continus") }}');
                break;
            }
        }

        return { errors, warnings };
    }

    function convertFormToLaravel(form) {
        const steps = getFormSteps();

        if (steps.length > 0) {
            try {
                // Créer des champs cachés pour chaque étape
                steps.forEach((step, index) => {
                    Object.keys(step).forEach(key => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `configuration[${index}][${key}]`;
                        input.value = typeof step[key] === 'object' ? JSON.stringify(step[key]) : step[key];
                        form.appendChild(input);
                    });
                });
            } catch (error) {
                event.preventDefault();
                showAlert('danger', '{{ __("Erreur lors de la conversion des données") }}: ' + error.message);
            }
        }
    }

    function convertJSONToLaravel(form) {
        const jsonTextarea = document.getElementById('configuration_json');

        if (jsonTextarea.value.trim()) {
            try {
                const configData = JSON.parse(jsonTextarea.value);

                // Créer des champs cachés pour chaque élément de configuration
                configData.forEach((step, index) => {
                    Object.keys(step).forEach(key => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `configuration[${index}][${key}]`;
                        input.value = typeof step[key] === 'object' ? JSON.stringify(step[key]) : step[key];
                        form.appendChild(input);
                    });
                });

                // Vider le textarea pour éviter les conflits
                jsonTextarea.value = '';
            } catch (error) {
                event.preventDefault();
                showAlert('danger', '{{ __("Configuration JSON invalide") }}: ' + error.message);
            }
        }
    }

    function showValidationResult(containerId, validation) {
        const container = document.getElementById(containerId) || createValidationContainer();

        let html = '';
        if (validation.errors.length > 0) {
            html += `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>`;
            html += validation.errors.join('<br>') + '</div>';
        }

        if (validation.warnings.length > 0) {
            html += `<div class="alert alert-warning"><i class="bi bi-exclamation-circle me-2"></i>`;
            html += validation.warnings.join('<br>') + '</div>';
        }

        if (validation.errors.length === 0 && validation.warnings.length === 0) {
            html = `<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>{{ __("Configuration valide !") }}</div>`;
        }

        container.innerHTML = html;
    }

    function createValidationContainer() {
        const container = document.createElement('div');
        container.id = 'form-validation-result';
        container.className = 'mt-3';
        document.getElementById('form-config').appendChild(container);
        return container;
    }

    // Ajouter une ligne par défaut
    addStepRow();
});

function toggleJSONConfig() {
    const checkbox = document.getElementById('enable_json_config');
    const section = document.getElementById('json-config-section');

    if (checkbox.checked) {
        section.classList.remove('d-none');
    } else {
        section.classList.add('d-none');
        document.getElementById('configuration_json').value = '';
        document.getElementById('steps-container').innerHTML = '';
    }
}

function validateJSONConfig() {
    // ...existing code...
}

function formatJSONConfig() {
    // ...existing code...
}

function addJSONExample() {
    // ...existing code...
}

function showAlert(type, message) {
    // ...existing code...
}

function importFromFile(event) {
    // ...existing code...
}
</script>
@endsection

function validateJSONConfig() {
    const textarea = document.getElementById('configuration_json');
    const resultDiv = document.getElementById('json-validation-result');

    try {
        const config = JSON.parse(textarea.value);

        // Validation basique
        if (!Array.isArray(config)) {
            throw new Error('La configuration doit être un tableau');
        }

        config.forEach((step, index) => {
            if (!step.id) throw new Error(`Étape ${index + 1}: ID manquant`);
            if (!step.name) throw new Error(`Étape ${index + 1}: Nom manquant`);
            if (!step.action_id) throw new Error(`Étape ${index + 1}: action_id manquant`);
            if (!step.ordre) throw new Error(`Étape ${index + 1}: ordre manquant`);
        });

        // Vérifier l'unicité des IDs et ordres
        const ids = config.map(step => step.id);
        const ordres = config.map(step => step.ordre);

        if (new Set(ids).size !== ids.length) {
            throw new Error('Les IDs des étapes doivent être uniques');
        }

        if (new Set(ordres).size !== ordres.length) {
            throw new Error('Les ordres des étapes doivent être uniques');
        }

        resultDiv.innerHTML = `
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                Configuration valide! ${config.length} étape(s) trouvée(s).
            </div>
        `;
    } catch (error) {
        resultDiv.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Erreur: ${error.message}
            </div>
        `;
    }
}

function formatJSONConfig() {
    const textarea = document.getElementById('configuration_json');
    try {
        const config = JSON.parse(textarea.value);
        textarea.value = JSON.stringify(config, null, 2);
        showAlert('success', 'JSON formaté avec succès');
    } catch (error) {
        showAlert('danger', 'Impossible de formater: JSON invalide');
    }
}

function addJSONExample() {
    const textarea = document.getElementById('configuration_json');
    const example = [
        {
            "id": "reception",
            "name": "Réception du courrier",
            "organisation_id": 1,
            "action_id": 1,
            "ordre": 1,
            "auto_assign": true,
            "timeout_hours": 24,
            "conditions": {
                "require_scan": true
            },
            "metadata": {
                "priority": "normal"
            }
        },
        {
            "id": "classification",
            "name": "Classification et tri",
            "organisation_id": 1,
            "action_id": 2,
            "ordre": 2,
            "auto_assign": false,
            "timeout_hours": 48,
            "conditions": {},
            "metadata": {}
        }
    ];

    textarea.value = JSON.stringify(example, null, 2);
    validateJSONConfig();
}

function showAlert(type, message) {
    const resultDiv = document.getElementById('json-validation-result');
    resultDiv.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show">
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}

function importFromFile(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const config = JSON.parse(e.target.result);
            document.getElementById('configuration_json').value = JSON.stringify(config, null, 2);
            validateJSONConfig();
            showAlert('success', 'Configuration importée avec succès');
        } catch (error) {
            showAlert('danger', 'Erreur lors de l\'import: ' + error.message);
        }
    };
    reader.readAsText(file);
}
</script>
@endsection
