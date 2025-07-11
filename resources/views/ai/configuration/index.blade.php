@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">
                        <i class="fas fa-cogs"></i> Configuration de l'Intelligence Artificielle
                    </h2>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-info" onclick="syncOllamaModels()">
                            <i class="fas fa-sync"></i> Synchroniser Ollama
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Messages système -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Onglets de configuration -->
                    <ul class="nav nav-tabs" id="configTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                                <i class="fas fa-sliders-h"></i> Configuration Générale
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="local-models-tab" data-bs-toggle="tab" data-bs-target="#local-models" type="button" role="tab">
                                <i class="fas fa-server"></i> Modèles Locaux ({{ $localModels->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="api-models-tab" data-bs-toggle="tab" data-bs-target="#api-models" type="button" role="tab">
                                <i class="fas fa-cloud"></i> Modèles API ({{ $apiModels->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="add-api-tab" data-bs-toggle="tab" data-bs-target="#add-api" type="button" role="tab">
                                <i class="fas fa-plus"></i> Ajouter Modèle API
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="configTabsContent">
                        <!-- Configuration Générale -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel">
                            <div class="row mt-4">
                                <div class="col-md-8">
                                    <form action="{{ route('ai.configuration.settings') }}" method="POST">
                                        @csrf

                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="mb-0">Paramètres par défaut</h5>
                                            </div>
                                            <div class="card-body">
                                                <!-- Modèle par défaut -->
                                                <div class="mb-3">
                                                    <label for="default_model_id" class="form-label">
                                                        <i class="fas fa-star"></i> Modèle par défaut
                                                    </label>
                                                    <select name="default_model_id" id="default_model_id" class="form-select">
                                                        <option value="">Aucun modèle sélectionné</option>
                                                        <optgroup label="Modèles Locaux (Ollama)">
                                                            @foreach($localModels as $model)
                                                                <option value="{{ $model->id }}"
                                                                    {{ $settings['default_model_id'] == $model->id ? 'selected' : '' }}>
                                                                    {{ $model->name }}
                                                                    @if($model->formatted_size)
                                                                        ({{ $model->formatted_size }})
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                        <optgroup label="Modèles API">
                                                            @foreach($apiModels as $model)
                                                                <option value="{{ $model->id }}"
                                                                    {{ $settings['default_model_id'] == $model->id ? 'selected' : '' }}>
                                                                    {{ $model->name }} ({{ ucfirst($model->provider) }})
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                    </select>
                                                    <div class="form-text">
                                                        Ce modèle sera utilisé par défaut pour toutes les nouvelles conversations.
                                                    </div>
                                                </div>

                                                <!-- Provider par défaut -->
                                                <div class="mb-3">
                                                    <label for="default_provider" class="form-label">
                                                        <i class="fas fa-building"></i> Fournisseur par défaut
                                                    </label>
                                                    <select name="default_provider" id="default_provider" class="form-select" required>
                                                        <option value="ollama" {{ $settings['default_provider'] == 'ollama' ? 'selected' : '' }}>
                                                            Ollama (Local)
                                                        </option>
                                                        <option value="openai" {{ $settings['default_provider'] == 'openai' ? 'selected' : '' }}>
                                                            OpenAI
                                                        </option>
                                                        <option value="anthropic" {{ $settings['default_provider'] == 'anthropic' ? 'selected' : '' }}>
                                                            Anthropic (Claude)
                                                        </option>
                                                        <option value="grok" {{ $settings['default_provider'] == 'grok' ? 'selected' : '' }}>
                                                            Grok (xAI)
                                                        </option>
                                                    </select>
                                                </div>

                                                <!-- Modèle de fallback -->
                                                <div class="mb-3">
                                                    <label for="fallback_model_id" class="form-label">
                                                        <i class="fas fa-life-ring"></i> Modèle de secours
                                                    </label>
                                                    <select name="fallback_model_id" id="fallback_model_id" class="form-select">
                                                        <option value="">Aucun</option>
                                                        @foreach($localModels as $model)
                                                            <option value="{{ $model->id }}"
                                                                {{ $settings['fallback_model_id'] == $model->id ? 'selected' : '' }}>
                                                                {{ $model->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="form-text">
                                                        Modèle à utiliser si le modèle par défaut est indisponible.
                                                    </div>
                                                </div>

                                                <!-- Nombre de tentatives -->
                                                <div class="mb-3">
                                                    <label for="max_retries" class="form-label">
                                                        <i class="fas fa-redo"></i> Nombre maximum de tentatives
                                                    </label>
                                                    <input type="number" class="form-control" id="max_retries" name="max_retries"
                                                           value="{{ $settings['max_retries'] }}" min="1" max="10" required>
                                                    <div class="form-text">
                                                        Nombre de tentatives en cas d'échec de génération.
                                                    </div>
                                                </div>

                                                <!-- Auto-sync Ollama -->
                                                <div class="mb-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="auto_sync_ollama"
                                                               name="auto_sync_ollama" value="1"
                                                               {{ $settings['auto_sync_ollama'] ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="auto_sync_ollama">
                                                            <i class="fas fa-sync-alt"></i> Synchronisation automatique des modèles Ollama
                                                        </label>
                                                    </div>
                                                    <div class="form-text">
                                                        Synchronise automatiquement les nouveaux modèles depuis Ollama.
                                                    </div>
                                                </div>

                                                <div class="d-grid">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-save"></i> Sauvegarder la configuration
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="col-md-4">
                                    <!-- Informations sur le modèle actuel -->
                                    @if($defaultModel)
                                        <div class="card">
                                            <div class="card-header bg-success text-white">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-star"></i> Modèle actuel par défaut
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <h6>{{ $defaultModel->name }}</h6>
                                                <p class="mb-1"><strong>Provider:</strong> {{ ucfirst($defaultModel->provider) }}</p>
                                                <p class="mb-1"><strong>Type:</strong> {{ ucfirst($defaultModel->model_type) }}</p>
                                                @if($defaultModel->formatted_size)
                                                    <p class="mb-1"><strong>Taille:</strong> {{ $defaultModel->formatted_size }}</p>
                                                @endif
                                                @if($defaultModel->parameter_size_formatted)
                                                    <p class="mb-1"><strong>Paramètres:</strong> {{ $defaultModel->parameter_size_formatted }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Aucun modèle par défaut configuré</strong><br>
                                            Veuillez sélectionner un modèle par défaut dans la configuration.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Modèles Locaux -->
                        <div class="tab-pane fade" id="local-models" role="tabpanel">
                            <div class="mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5>Modèles Ollama Disponibles</h5>
                                    <button type="button" class="btn btn-outline-primary" onclick="syncOllamaModels()">
                                        <i class="fas fa-sync"></i> Synchroniser
                                    </button>
                                </div>

                                @if($localModels->isEmpty())
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        Aucun modèle Ollama trouvé.
                                        <a href="#" onclick="syncOllamaModels()" class="alert-link">Cliquez ici pour synchroniser</a>.
                                    </div>
                                @else
                                    <div class="row">
                                        @foreach($localModels as $model)
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="card h-100 {{ $model->is_default ? 'border-success' : '' }}">
                                                    <div class="card-body">
                                                        <h6 class="card-title d-flex justify-content-between">
                                                            {{ $model->name }}
                                                            @if($model->is_default)
                                                                <span class="badge bg-success">Défaut</span>
                                                            @endif
                                                        </h6>

                                                        <div class="small text-muted">
                                                            <div><strong>Taille:</strong> {{ $model->formatted_size ?? 'N/A' }}</div>
                                                            <div><strong>Paramètres:</strong> {{ $model->parameter_size_formatted ?? 'N/A' }}</div>
                                                            @if($model->model_family)
                                                                <div><strong>Famille:</strong> {{ $model->model_family }}</div>
                                                            @endif
                                                            <div><strong>Mis à jour:</strong> {{ $model->model_modified_at ? $model->model_modified_at->format('d/m/Y') : 'N/A' }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer bg-transparent">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <small class="text-muted">
                                                                <i class="fas fa-{{ $model->is_active ? 'check text-success' : 'times text-danger' }}"></i>
                                                                {{ $model->is_active ? 'Actif' : 'Inactif' }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Modèles API -->
                        <div class="tab-pane fade" id="api-models" role="tabpanel">
                            <div class="mt-4">
                                <h5>Modèles API Configurés</h5>

                                @if($apiModels->isEmpty())
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        Aucun modèle API configuré.
                                        <a href="#add-api-tab" onclick="$('#add-api-tab').tab('show')" class="alert-link">Cliquez ici pour en ajouter un</a>.
                                    </div>
                                @else
                                    <div class="row">
                                        @foreach($apiModels as $model)
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="card h-100 {{ $model->is_default ? 'border-warning' : '' }}">
                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                        <h6 class="mb-0">{{ $model->name }}</h6>
                                                        @if($model->is_default)
                                                            <span class="badge bg-warning">Défaut</span>
                                                        @endif
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="small">
                                                            <div><strong>Provider:</strong> {{ ucfirst($model->provider) }}</div>
                                                            <div><strong>Modèle:</strong> {{ $model->external_model_id }}</div>
                                                            @if($model->cost_per_token_input)
                                                                <div><strong>Coût input:</strong> ${{ number_format($model->cost_per_token_input, 8) }}/token</div>
                                                            @endif
                                                            @if($model->cost_per_token_output)
                                                                <div><strong>Coût output:</strong> ${{ number_format($model->cost_per_token_output, 8) }}/token</div>
                                                            @endif
                                                            @if($model->max_context_length)
                                                                <div><strong>Contexte max:</strong> {{ number_format($model->max_context_length) }} tokens</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <div class="btn-group w-100">
                                                            <button type="button" class="btn btn-sm btn-outline-info"
                                                                    onclick="testApiModel({{ $model->id }})">
                                                                <i class="fas fa-vial"></i> Test
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                    onclick="deleteApiModel({{ $model->id }}, '{{ $model->name }}')">
                                                                <i class="fas fa-trash"></i> Supprimer
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Ajouter Modèle API -->
                        <div class="tab-pane fade" id="add-api" role="tabpanel">
                            <div class="mt-4">
                                <div class="row justify-content-center">
                                    <div class="col-md-8">
                                        <form action="{{ route('ai.configuration.api-model') }}" method="POST" id="apiModelForm">
                                            @csrf

                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="mb-0">Ajouter un modèle API</h5>
                                                </div>
                                                <div class="card-body">
                                                    <!-- Provider -->
                                                    <div class="mb-3">
                                                        <label for="api_provider" class="form-label">Fournisseur</label>
                                                        <select name="provider" id="api_provider" class="form-select" required onchange="updateProviderDefaults()">
                                                            <option value="">Sélectionnez un fournisseur</option>
                                                            <option value="openai">OpenAI (ChatGPT)</option>
                                                            <option value="anthropic">Anthropic (Claude)</option>
                                                            <option value="grok">Grok (xAI)</option>
                                                        </select>
                                                    </div>

                                                    <!-- Configuration commune -->
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="api_name" class="form-label">Nom du modèle</label>
                                                                <input type="text" class="form-control" id="api_name" name="name" required
                                                                       placeholder="ex: GPT-4 Turbo">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="external_model_id" class="form-label">ID du modèle</label>
                                                                <input type="text" class="form-control" id="external_model_id" name="external_model_id" required
                                                                       placeholder="ex: gpt-4-turbo">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- URL et Clé API -->
                                                    <div class="mb-3">
                                                        <label for="api_endpoint" class="form-label">URL de l'API</label>
                                                        <input type="url" class="form-control" id="api_endpoint" name="api_endpoint" required
                                                               placeholder="https://api.openai.com/v1/chat/completions">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="api_key" class="form-label">Clé API</label>
                                                        <div class="input-group">
                                                            <input type="password" class="form-control" id="api_key" name="api_key" required
                                                                   placeholder="Votre clé API secrète">
                                                            <button class="btn btn-outline-secondary" type="button" onclick="toggleApiKeyVisibility()">
                                                                <i class="fas fa-eye" id="api-key-icon"></i>
                                                            </button>
                                                        </div>
                                                        <div class="form-text">La clé sera chiffrée et stockée en sécurité.</div>
                                                    </div>

                                                    <!-- Configuration avancée -->
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="cost_per_token_input" class="form-label">Coût par token (entrée)</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text">$</span>
                                                                    <input type="number" class="form-control" id="cost_per_token_input" name="cost_per_token_input"
                                                                           step="0.00000001" placeholder="0.00000001">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="cost_per_token_output" class="form-label">Coût par token (sortie)</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text">$</span>
                                                                    <input type="number" class="form-control" id="cost_per_token_output" name="cost_per_token_output"
                                                                           step="0.00000001" placeholder="0.00000003">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="max_context_length" class="form-label">Contexte maximum (tokens)</label>
                                                                <input type="number" class="form-control" id="max_context_length" name="max_context_length"
                                                                       placeholder="128000">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="default_temperature" class="form-label">Température par défaut</label>
                                                                <input type="number" class="form-control" id="default_temperature" name="default_temperature"
                                                                       step="0.1" min="0" max="2" value="0.7">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="d-grid">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-plus"></i> Ajouter le modèle
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
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

@push('styles')
<style>
    .nav-tabs .nav-link {
        border-radius: 0.5rem 0.5rem 0 0;
    }

    .card.border-success {
        border-width: 2px;
    }

    .card.border-warning {
        border-width: 2px;
    }

    .btn-group > .btn {
        flex: 1;
    }

    .form-text {
        font-size: 0.875em;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Synchroniser les modèles Ollama
function syncOllamaModels() {
    if (confirm('Voulez-vous synchroniser les modèles Ollama ? Cela peut prendre quelques secondes.')) {
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spin fa-spinner"></i> Synchronisation...';
        btn.disabled = true;

        fetch('{{ route("ai.configuration.sync-ollama") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur: ' + (data.message || 'Synchronisation échouée'));
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur de synchronisation: ' + error.message);
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
}

// Basculer la visibilité de la clé API
function toggleApiKeyVisibility() {
    const input = document.getElementById('api_key');
    const icon = document.getElementById('api-key-icon');

    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Mettre à jour les valeurs par défaut selon le provider
function updateProviderDefaults() {
    const provider = document.getElementById('api_provider').value;
    const endpointInput = document.getElementById('api_endpoint');
    const nameInput = document.getElementById('api_name');
    const modelIdInput = document.getElementById('external_model_id');
    const contextInput = document.getElementById('max_context_length');
    const inputCostInput = document.getElementById('cost_per_token_input');
    const outputCostInput = document.getElementById('cost_per_token_output');

    // Valeurs par défaut selon le provider
    const defaults = {
        openai: {
            endpoint: 'https://api.openai.com/v1/chat/completions',
            name: 'GPT-4 Turbo',
            model_id: 'gpt-4-turbo',
            context: '128000',
            input_cost: '0.00001',
            output_cost: '0.00003'
        },
        anthropic: {
            endpoint: 'https://api.anthropic.com/v1/messages',
            name: 'Claude 3.5 Sonnet',
            model_id: 'claude-3-5-sonnet-20241022',
            context: '200000',
            input_cost: '0.000003',
            output_cost: '0.000015'
        },
        grok: {
            endpoint: 'https://api.x.ai/v1/chat/completions',
            name: 'Grok Beta',
            model_id: 'grok-beta',
            context: '131072',
            input_cost: '0.000005',
            output_cost: '0.000015'
        }
    };

    if (defaults[provider]) {
        const config = defaults[provider];
        endpointInput.value = config.endpoint;
        nameInput.value = config.name;
        modelIdInput.value = config.model_id;
        contextInput.value = config.context;
        inputCostInput.value = config.input_cost;
        outputCostInput.value = config.output_cost;
    }
}

// Tester un modèle API
function testApiModel(modelId) {
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spin fa-spinner"></i> Test...';
    btn.disabled = true;

    fetch(`/ai/configuration/api-models/${modelId}/test`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Test réussi!\nTemps de réponse: ${data.response_time}s\n${data.message}`);
        } else {
            alert('Test échoué: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur de test: ' + error.message);
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Supprimer un modèle API
function deleteApiModel(modelId, modelName) {
    if (confirm(`Voulez-vous vraiment supprimer le modèle "${modelName}" ?\nCette action est irréversible.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/ai/configuration/api-models/${modelId}`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';

        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection
