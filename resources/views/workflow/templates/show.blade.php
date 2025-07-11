
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">
                <i class="bi bi-file-earmark-text me-2"></i>
                {{ $template->name }}
            </h1>
            <div class="text-muted">{{ __('Modèle de workflow') }} #{{ $template->id }}</div>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                @can('update', $template)
                <a href="{{ route('workflows.templates.edit', $template) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>
                    {{ __('Modifier') }}
                </a>
                @endcan

                <a href="{{ route('workflows.templates.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    {{ __('Retour à la liste') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">{{ __('Informations') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">{{ __('Statut') }}</label>
                        <div>
                            @if($template->is_active)
                                <span class="badge bg-success">{{ __('Actif') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('Inactif') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">{{ __('Catégorie') }}</label>
                        <div>{{ $template->category }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">{{ __('Description') }}</label>
                        <div>{{ $template->description ?: __('Aucune description') }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">{{ __('Créé par') }}</label>
                        <div>{{ $template->creator->name ?? 'N/A' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">{{ __('Date de création') }}</label>
                        <div>{{ $template->created_at->format('d/m/Y H:i') }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">{{ __('Dernière modification') }}</label>
                        <div>{{ $template->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between">
                        @can('toggleActive', $template)
                        <form action="{{ route('workflows.templates.toggle-active', $template) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-{{ $template->is_active ? 'warning' : 'success' }}">
                                <i class="bi bi-{{ $template->is_active ? 'toggle-off' : 'toggle-on' }} me-1"></i>
                                {{ $template->is_active ? __('Désactiver') : __('Activer') }}
                            </button>
                        </form>
                        @endcan

                        <div>
                            @can('duplicate', $template)
                            <form action="{{ route('workflows.templates.duplicate', $template) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-info">
                                    <i class="bi bi-copy me-1"></i>
                                    {{ __('Dupliquer') }}
                                </button>
                            </form>
                            @endcan

                            @can('delete', $template)
                            <form action="{{ route('workflows.templates.destroy', $template) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash me-1"></i>
                                    {{ __('Supprimer') }}
                                </button>
                            </form>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">{{ __('Statistiques') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3 text-center">
                                <div class="h4">{{ $template->steps->count() }}</div>
                                <div class="text-muted small">{{ __('Étapes DB') }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3 text-center">
                                <div class="h4">{{ $template->instances->count() }}</div>
                                <div class="text-muted small">{{ __('Instances') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3 text-center">
                                @if(!empty($template->configuration))
                                    <div class="h5 text-success">{{ count($template->configuration) }}</div>
                                    <div class="text-muted small">{{ __('Étapes JSON') }}</div>
                                @else
                                    <div class="h5 text-muted">0</div>
                                    <div class="text-muted small">{{ __('Aucune config JSON') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Navigation par onglets -->
            <ul class="nav nav-tabs" id="templateTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="steps-tab" data-bs-toggle="tab" data-bs-target="#steps" type="button" role="tab">
                        <i class="bi bi-list-task me-1"></i>
                        {{ __('Étapes du workflow') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="configuration-tab" data-bs-toggle="tab" data-bs-target="#configuration" type="button" role="tab">
                        <i class="bi bi-code-square me-1"></i>
                        {{ __('Configuration JSON') }}
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="templateTabsContent">
                <!-- Onglet Étapes -->
                <div class="tab-pane fade show active" id="steps" role="tabpanel">
                    <div class="card shadow-sm border-top-0">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">{{ __('Étapes du workflow') }}</h5>
                            @can('create', [\App\Models\WorkflowStep::class, $template])
                            <a href="{{ route('workflows.templates.steps.create', $template) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>
                                {{ __('Ajouter une étape') }}
                            </a>
                            @endcan
                        </div>
                <div class="card-body">
                    @if($template->steps->isEmpty())
                        <div class="alert alert-info">
                            {{ __('Aucune étape définie pour ce modèle de workflow.') }}
                        </div>
                    @else
                        <div class="workflow-steps">
                            @foreach($template->steps->sortBy('order_index') as $step)
                                <div class="workflow-step-card mb-3 p-3 border rounded-3 {{ $step->type ? 'bg-light' : '' }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="mb-1">
                                                <span class="badge bg-secondary me-2">{{ $step->order_index + 1 }}</span>
                                                {{ $step->name }}
                                            </h5>
                                            <div class="small text-muted mb-2">{{ $step->description }}</div>

                                            <div class="mb-2">
                                                <span class="badge bg-info">{{ $step->type ? $step->type->label() : 'Standard' }}</span>

                                                @if($step->is_required)
                                                    <span class="badge bg-danger">{{ __('Obligatoire') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('Optionnelle') }}</span>
                                                @endif

                                                @if($step->estimated_duration)
                                                    <span class="badge bg-warning">
                                                        <i class="bi bi-clock me-1"></i>
                                                        {{ $step->estimated_duration }} {{ __('jours') }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if($step->assignments->isNotEmpty())
                                                <div class="mt-2">
                                                    <strong class="small">{{ __('Assigné à:') }}</strong>
                                                    <div class="mt-1">
                                                        @foreach($step->assignments as $assignment)
                                                            <span class="badge bg-light text-dark me-1 mb-1 py-1 px-2">
                                                                @if($assignment->assignee_type == 'user')
                                                                    <i class="bi bi-person me-1"></i>
                                                                    {{ $assignment->assigneeUser->name ?? 'N/A' }}
                                                                @elseif($assignment->assignee_type == 'organisation')
                                                                    <i class="bi bi-building me-1"></i>
                                                                    {{ $assignment->assigneeOrganisation->name ?? 'N/A' }}
                                                                @else
                                                                    <i class="bi bi-question-circle me-1"></i>
                                                                    {{ __('Inconnu') }}
                                                                @endif
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('workflows.steps.edit', $step) }}" class="btn btn-outline-secondary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('workflows.steps.destroy', $step) }}" method="POST" class="delete-step-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @can('reorder', [$template])
                        <div class="text-center mt-4">
                            <button id="reorderStepsBtn" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-down-up me-1"></i>
                                {{ __('Réorganiser les étapes') }}
                            </button>
                        </div>
                        @endcan
                    @endif
                </div>
            </div>
        </div>

        <!-- Onglet Configuration JSON -->
        <div class="tab-pane fade" id="configuration" role="tabpanel">
            <div class="card shadow-sm border-top-0">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ __('Configuration JSON') }}</h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-primary" onclick="addConfigurationStep()">
                            <i class="bi bi-plus-lg me-1"></i>
                            {{ __('Ajouter une étape') }}
                        </button>
                        <button type="button" class="btn btn-success" onclick="validateConfiguration()">
                            <i class="bi bi-check-circle me-1"></i>
                            {{ __('Valider') }}
                        </button>
                        <button type="button" class="btn btn-info" onclick="saveConfiguration()">
                            <i class="bi bi-save me-1"></i>
                            {{ __('Sauvegarder') }}
                        </button>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-gear me-1"></i>
                                {{ __('Actions') }}
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="importFromSteps()">
                                    <i class="bi bi-arrow-down-circle me-2"></i>{{ __('Importer depuis les étapes') }}
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportConfiguration()">
                                    <i class="bi bi-arrow-up-circle me-2"></i>{{ __('Exporter JSON') }}
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-warning" href="#" onclick="resetConfiguration()">
                                    <i class="bi bi-arrow-clockwise me-2"></i>{{ __('Réinitialiser') }}
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Alertes de validation -->
                    <div id="configuration-alerts"></div>

                    <!-- Configuration JSON existante -->
                    @if(!empty($template->configuration))
                        <div class="mb-4">
                            <h6>{{ __('Configuration actuelle:') }}</h6>
                            <div id="current-configuration" class="bg-light p-3 rounded">
                                <pre class="mb-0"><code id="current-config-json">{{ json_encode($template->configuration, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                            </div>
                        </div>
                    @endif

                    <!-- Éditeur de configuration -->
                    <div class="configuration-editor">
                        <h6>{{ __('Éditeur de configuration:') }}</h6>

                        <!-- Liste des étapes configurées -->
                        <div id="configuration-steps" class="mb-3">
                            <!-- Les étapes seront ajoutées dynamiquement ici -->
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex gap-2 mb-3">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="importFromSteps()">
                                <i class="bi bi-arrow-down-circle me-1"></i>
                                {{ __('Importer depuis les étapes') }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetConfiguration()">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                {{ __('Réinitialiser') }}
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="previewJSON()">
                                <i class="bi bi-eye me-1"></i>
                                {{ __('Aperçu JSON') }}
                            </button>
                        </div>

                        <!-- Aperçu JSON -->
                        <div id="json-preview" class="d-none">
                            <h6>{{ __('Aperçu JSON:') }}</h6>
                            <div class="bg-dark text-light p-3 rounded">
                                <pre class="mb-0"><code id="preview-json-content"></code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let currentConfiguration = @json($template->configuration ?? []);
    let templateId = {{ $template->id }};
    let csrfToken = '{{ csrf_token() }}';

    // Confirmation pour la suppression du template
    const deleteForm = document.querySelector('.delete-form');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(event) {
            event.preventDefault();
            if (confirm("{{ __('Êtes-vous sûr de vouloir supprimer ce modèle de workflow ? Cette action est irréversible.') }}")) {
                this.submit();
            }
        });
    }

    // Confirmation pour la suppression des étapes
    document.querySelectorAll('.delete-step-form').forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            if (confirm("{{ __('Êtes-vous sûr de vouloir supprimer cette étape ?') }}")) {
                this.submit();
            }
        });
    });

    // Initialiser la configuration lors du changement d'onglet
    document.getElementById('configuration-tab').addEventListener('shown.bs.tab', function () {
        loadConfigurationEditor();
    });

    // Charger l'éditeur de configuration
    function loadConfigurationEditor() {
        const container = document.getElementById('configuration-steps');
        container.innerHTML = '';

        if (currentConfiguration.length === 0) {
            container.innerHTML = `
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    {{ __('Aucune configuration JSON définie. Cliquez sur "Ajouter une étape" pour commencer.') }}
                </div>
            `;
        } else {
            currentConfiguration.forEach((step, index) => {
                addConfigurationStepToEditor(step, index);
            });
        }
    }

    // Ajouter une étape à l'éditeur
    function addConfigurationStepToEditor(step, index) {
        const container = document.getElementById('configuration-steps');
        const stepDiv = document.createElement('div');
        stepDiv.className = 'configuration-step card mb-3';
        stepDiv.setAttribute('data-index', index);

        stepDiv.innerHTML = `
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <span class="badge bg-primary me-2">${step.ordre || index + 1}</span>
                    ${step.name || 'Nouvelle étape'}
                </h6>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-primary" onclick="editConfigurationStep(${index})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="removeConfigurationStep(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">ID:</small> <code>${step.id || ''}</code><br>
                        <small class="text-muted">Action ID:</small> <code>${step.action_id || ''}</code><br>
                        <small class="text-muted">Organisation ID:</small> <code>${step.organisation_id || 'N/A'}</code>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Auto-assign:</small> ${step.auto_assign ? 'Oui' : 'Non'}<br>
                        <small class="text-muted">Timeout:</small> ${step.timeout_hours || 'N/A'} heures<br>
                        <small class="text-muted">Conditions:</small> ${step.conditions ? Object.keys(step.conditions).length + ' condition(s)' : 'Aucune'}
                    </div>
                </div>
            </div>
        `;

        container.appendChild(stepDiv);
    }

    // Fonctions globales pour les boutons
    window.addConfigurationStep = function() {
        const newStep = {
            id: `step_${Date.now()}`,
            name: 'Nouvelle étape',
            organisation_id: null,
            action_id: 1,
            ordre: currentConfiguration.length + 1,
            conditions: {},
            auto_assign: false,
            timeout_hours: 24,
            metadata: {}
        };

        currentConfiguration.push(newStep);
        loadConfigurationEditor();
        editConfigurationStep(currentConfiguration.length - 1);
    };

    window.editConfigurationStep = function(index) {
        const step = currentConfiguration[index];

        // Créer un modal d'édition
        const modalHtml = `
            <div class="modal fade" id="editStepModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('Modifier l\'étape') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="stepEditForm">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">ID de l'étape</label>
                                        <input type="text" class="form-control" name="id" value="${step.id}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nom de l'étape</label>
                                        <input type="text" class="form-control" name="name" value="${step.name}" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Organisation ID</label>
                                        <input type="number" class="form-control" name="organisation_id" value="${step.organisation_id || ''}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Action ID</label>
                                        <input type="number" class="form-control" name="action_id" value="${step.action_id}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Ordre</label>
                                        <input type="number" class="form-control" name="ordre" value="${step.ordre}" min="1" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Timeout (heures)</label>
                                        <input type="number" class="form-control" name="timeout_hours" value="${step.timeout_hours || ''}" min="1">
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="auto_assign" ${step.auto_assign ? 'checked' : ''}>
                                            <label class="form-check-label">Auto-assignation</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Conditions (JSON)</label>
                                    <textarea class="form-control" name="conditions" rows="3">${JSON.stringify(step.conditions || {}, null, 2)}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Métadonnées (JSON)</label>
                                    <textarea class="form-control" name="metadata" rows="3">${JSON.stringify(step.metadata || {}, null, 2)}</textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="button" class="btn btn-primary" onclick="saveStepEdit(${index})">Sauvegarder</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Supprimer le modal existant et ajouter le nouveau
        const existingModal = document.getElementById('editStepModal');
        if (existingModal) existingModal.remove();

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('editStepModal'));
        modal.show();
    };

    window.saveStepEdit = function(index) {
        const form = document.getElementById('stepEditForm');
        const formData = new FormData(form);

        try {
            const updatedStep = {
                id: formData.get('id'),
                name: formData.get('name'),
                organisation_id: formData.get('organisation_id') ? parseInt(formData.get('organisation_id')) : null,
                action_id: parseInt(formData.get('action_id')),
                ordre: parseInt(formData.get('ordre')),
                auto_assign: formData.has('auto_assign'),
                timeout_hours: formData.get('timeout_hours') ? parseInt(formData.get('timeout_hours')) : null,
                conditions: formData.get('conditions') ? JSON.parse(formData.get('conditions')) : {},
                metadata: formData.get('metadata') ? JSON.parse(formData.get('metadata')) : {}
            };

            currentConfiguration[index] = updatedStep;
            loadConfigurationEditor();

            const modal = bootstrap.Modal.getInstance(document.getElementById('editStepModal'));
            modal.hide();

            showAlert('success', 'Étape mise à jour avec succès');
        } catch (error) {
            showAlert('danger', 'Erreur dans les données JSON: ' + error.message);
        }
    };

    window.removeConfigurationStep = function(index) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette étape ?')) {
            currentConfiguration.splice(index, 1);
            loadConfigurationEditor();
            showAlert('info', 'Étape supprimée');
        }
    };

    window.validateConfiguration = function() {
        fetch(`/api/workflows/templates/${templateId}/configuration/validate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.data.is_valid) {
                    showAlert('success', `Configuration valide! ${data.data.steps_count} étape(s) trouvée(s).`);
                } else {
                    let message = 'Configuration invalide:\\n';
                    data.data.errors.forEach(error => message += `- ${error}\\n`);
                    if (data.data.warnings.length > 0) {
                        message += '\\nAvertissements:\\n';
                        data.data.warnings.forEach(warning => message += `- ${warning}\\n`);
                    }
                    showAlert('warning', message);
                }
            }
        })
        .catch(error => {
            showAlert('danger', 'Erreur lors de la validation: ' + error.message);
        });
    };

    window.saveConfiguration = function() {
        fetch(`/api/workflows/templates/${templateId}/configuration`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                configuration: currentConfiguration
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Configuration sauvegardée avec succès!');
                // Mettre à jour l'affichage de la configuration actuelle
                document.getElementById('current-config-json').textContent =
                    JSON.stringify(data.data.configuration, null, 2);
            } else {
                showAlert('danger', 'Erreur: ' + data.message);
            }
        })
        .catch(error => {
            showAlert('danger', 'Erreur lors de la sauvegarde: ' + error.message);
        });
    };

    window.importFromSteps = function() {
        @php
            $stepsData = $template->steps->map(function($step) {
                return [
                    'id' => 'step_' . $step->id,
                    'name' => $step->name,
                    'ordre' => $step->order_index + 1,
                    'action_id' => 1,
                    'organisation_id' => null,
                    'auto_assign' => false,
                    'timeout_hours' => $step->estimated_duration ? $step->estimated_duration * 24 : 24,
                    'conditions' => [],
                    'metadata' => []
                ];
            })->toArray();
        @endphp

        const steps = @json($stepsData);

        if (steps.length === 0) {
            showAlert('warning', 'Aucune étape trouvée à importer');
            return;
        }

        if (confirm(`Importer ${steps.length} étape(s) depuis la base de données ? Cela remplacera la configuration actuelle.`)) {
            currentConfiguration = steps;
            loadConfigurationEditor();
            showAlert('success', `${steps.length} étape(s) importée(s) avec succès`);
        }
    };

    window.resetConfiguration = function() {
        if (confirm('Réinitialiser la configuration ? Toutes les modifications non sauvegardées seront perdues.')) {
            currentConfiguration = @json($template->configuration ?? []);
            loadConfigurationEditor();
            showAlert('info', 'Configuration réinitialisée');
        }
    };

    window.previewJSON = function() {
        const preview = document.getElementById('json-preview');
        const content = document.getElementById('preview-json-content');

        content.textContent = JSON.stringify(currentConfiguration, null, 2);
        preview.classList.toggle('d-none');
    };

    window.exportConfiguration = function() {
        const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(currentConfiguration, null, 2));
        const downloadAnchorNode = document.createElement('a');
        downloadAnchorNode.setAttribute("href", dataStr);
        downloadAnchorNode.setAttribute("download", `workflow_template_${templateId}_config.json`);
        document.body.appendChild(downloadAnchorNode);
        downloadAnchorNode.click();
        downloadAnchorNode.remove();
        showAlert('success', 'Configuration exportée avec succès');
    };

    function showAlert(type, message) {
        const alertsContainer = document.getElementById('configuration-alerts');
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message.replace(/\\n/g, '<br>')}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        alertsContainer.innerHTML = alertHtml;

        // Auto-hide after 5 seconds for success messages
        if (type === 'success') {
            setTimeout(() => {
                const alert = alertsContainer.querySelector('.alert');
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        }
    }
});
</script>
@endsection
