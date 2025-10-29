@extends('layouts.app')

@section('title', 'Configuration OPAC')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Configuration OPAC</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Administration</a></li>
                        <li class="breadcrumb-item active">Configuration OPAC</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages de statut -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-cogs text-primary me-2"></i>
                        Configuration du catalogue public (OPAC)
                    </h4>
                </div>
                <div class="card-body">

                    <!-- Sélection d'organisation -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form method="GET" id="organisationForm">
                                <div class="mb-3">
                                    <label for="organisation_id" class="form-label">
                                        <i class="fas fa-building me-1"></i>
                                        Organisation
                                    </label>
                                    <select name="organisation_id" id="organisation_id" class="form-select" onchange="this.form.submit()">
                                        <option value="">Sélectionnez une organisation</option>
                                        @foreach($organisations as $org)
                                            <option value="{{ $org->id }}"
                                                {{ $selectedOrganisationId == $org->id ? 'selected' : '' }}>
                                                {{ $org->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            @if($selectedOrganisationId)
                                <!-- Boutons d'actions -->
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-primary" onclick="exportConfiguration()">
                                        <i class="fas fa-download me-1"></i>
                                        Exporter
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
                                        <i class="fas fa-upload me-1"></i>
                                        Importer
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($selectedOrganisationId && $categories->isNotEmpty())
                        <!-- Formulaire de configuration -->
                        <form method="POST" action="{{ route('admin.opac.configurations.update') }}" id="configurationForm">
                            @csrf
                            <input type="hidden" name="organisation_id" value="{{ $selectedOrganisationId }}">

                            <!-- Navigation par onglets -->
                            <ul class="nav nav-tabs nav-tabs-custom" id="configTabs" role="tablist">
                                @foreach($categories as $index => $category)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                                id="tab-{{ $category->name }}"
                                                data-bs-toggle="tab"
                                                data-bs-target="#content-{{ $category->name }}"
                                                type="button" role="tab">
                                            <i class="{{ $category->icon }} me-1"></i>
                                            {{ $category->label }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>

                            <!-- Contenu des onglets -->
                            <div class="tab-content" id="configTabContent">
                                @foreach($categories as $index => $category)
                                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                                         id="content-{{ $category->name }}"
                                         role="tabpanel">

                                        <div class="p-4">
                                            <h5 class="mb-3">
                                                <i class="{{ $category->icon }} text-muted me-2"></i>
                                                {{ $category->label }}
                                            </h5>

                                            @if($category->description)
                                                <p class="text-muted mb-4">{{ $category->description }}</p>
                                            @endif

                                            <div class="row">
                                                @foreach($category->configurations as $config)
                                                    <div class="col-md-6 mb-4">
                                                        <div class="configuration-item p-3 border rounded">
                                                            @php
                                                                $currentValue = $configurationValues[$config->key] ?? $config->default_value;
                                                            @endphp

                                                            <label class="form-label fw-bold">
                                                                {{ $config->label }}
                                                                @if($config->is_required)
                                                                    <span class="text-danger">*</span>
                                                                @endif
                                                            </label>

                                                            @if($config->description)
                                                                <small class="form-text text-muted d-block mb-2">
                                                                    {{ $config->description }}
                                                                </small>
                                                            @endif

                                                            @switch($config->type)
                                                                @case('boolean')
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input"
                                                                               type="checkbox"
                                                                               name="configurations[{{ $config->id }}]"
                                                                               id="config_{{ $config->id }}"
                                                                               value="1"
                                                                               {{ $currentValue ? 'checked' : '' }}>
                                                                        <label class="form-check-label" for="config_{{ $config->id }}">
                                                                            {{ $currentValue ? 'Activé' : 'Désactivé' }}
                                                                        </label>
                                                                    </div>
                                                                    @break

                                                                @case('select')
                                                                    <select name="configurations[{{ $config->id }}]"
                                                                            class="form-select"
                                                                            id="config_{{ $config->id }}"
                                                                            {{ $config->is_required ? 'required' : '' }}>
                                                                        @if($config->options)
                                                                            @foreach($config->options as $value => $label)
                                                                                <option value="{{ $value }}"
                                                                                        {{ $currentValue == $value ? 'selected' : '' }}>
                                                                                    {{ $label }}
                                                                                </option>
                                                                            @endforeach
                                                                        @endif
                                                                    </select>
                                                                    @break

                                                                @case('multiselect')
                                                                    @if($config->options)
                                                                        @php
                                                                            $selectedValues = is_array($currentValue) ? $currentValue : json_decode($currentValue, true) ?? [];
                                                                        @endphp
                                                                        <div class="multiselect-container border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                                                            @foreach($config->options as $value => $label)
                                                                                <div class="form-check">
                                                                                    <input class="form-check-input"
                                                                                           type="checkbox"
                                                                                           name="configurations[{{ $config->id }}][]"
                                                                                           value="{{ $value }}"
                                                                                           id="config_{{ $config->id }}_{{ $value }}"
                                                                                           {{ in_array($value, $selectedValues) ? 'checked' : '' }}>
                                                                                    <label class="form-check-label" for="config_{{ $config->id }}_{{ $value }}">
                                                                                        {{ $label }}
                                                                                    </label>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                    @break

                                                                @case('text')
                                                                    <textarea name="configurations[{{ $config->id }}]"
                                                                              class="form-control"
                                                                              rows="3"
                                                                              id="config_{{ $config->id }}"
                                                                              {{ $config->is_required ? 'required' : '' }}>{{ $currentValue }}</textarea>
                                                                    @break

                                                                @case('integer')
                                                                    <input type="number"
                                                                           name="configurations[{{ $config->id }}]"
                                                                           class="form-control"
                                                                           value="{{ $currentValue }}"
                                                                           id="config_{{ $config->id }}"
                                                                           {{ $config->is_required ? 'required' : '' }}>
                                                                    @break

                                                                @case('email')
                                                                    <input type="email"
                                                                           name="configurations[{{ $config->id }}]"
                                                                           class="form-control"
                                                                           value="{{ $currentValue }}"
                                                                           id="config_{{ $config->id }}"
                                                                           {{ $config->is_required ? 'required' : '' }}>
                                                                    @break

                                                                @default
                                                                    <input type="text"
                                                                           name="configurations[{{ $config->id }}]"
                                                                           class="form-control"
                                                                           value="{{ $currentValue }}"
                                                                           id="config_{{ $config->id }}"
                                                                           {{ $config->is_required ? 'required' : '' }}>
                                                            @endswitch

                                                            <!-- Actions pour chaque configuration -->
                                                            <div class="mt-2">
                                                                @if($currentValue != $config->default_value)
                                                                    <button type="button" class="btn btn-sm btn-outline-warning"
                                                                            onclick="resetConfiguration({{ $config->id }}, '{{ $config->label }}')">
                                                                        <i class="fas fa-undo me-1"></i>
                                                                        Réinitialiser
                                                                    </button>
                                                                @endif
                                                                <small class="text-muted ms-2">
                                                                    Défaut: {{ is_array($config->default_value) ? json_encode($config->default_value) : $config->default_value }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Boutons d'action -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left me-1"></i>
                                            Retour
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>
                                            Enregistrer les modifications
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                    @elseif($selectedOrganisationId && $categories->isEmpty())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Aucune configuration disponible. Veuillez exécuter les migrations et seeders pour initialiser les configurations OPAC.
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Veuillez sélectionner une organisation pour configurer l'OPAC.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'importation -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.opac.configurations.import') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="organisation_id" value="{{ $selectedOrganisationId }}">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-upload me-2"></i>
                        Importer une configuration
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="config_file" class="form-label">Fichier de configuration (JSON)</label>
                        <input type="file" name="config_file" id="config_file" class="form-control" accept=".json" required>
                        <div class="form-text">
                            Sélectionnez un fichier de configuration OPAC au format JSON exporté précédemment.
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>
                        Importer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Formulaire caché pour les actions -->
<form id="actionForm" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="organisation_id" value="{{ $selectedOrganisationId }}">
</form>
@endsection

@push('scripts')
<script>
function exportConfiguration() {
    if (!{{ $selectedOrganisationId ?? 0 }}) {
        alert('Veuillez sélectionner une organisation.');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.opac.configurations.export") }}';

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';

    const orgId = document.createElement('input');
    orgId.type = 'hidden';
    orgId.name = 'organisation_id';
    orgId.value = '{{ $selectedOrganisationId }}';

    form.appendChild(csrf);
    form.appendChild(orgId);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function resetConfiguration(configId, configLabel) {
    if (confirm(`Êtes-vous sûr de vouloir réinitialiser la configuration "${configLabel}" à sa valeur par défaut ?`)) {
        const form = document.getElementById('actionForm');
        form.action = '{{ route("admin.opac.configurations.reset", ":id") }}'.replace(':id', configId);
        form.submit();
    }
}

// Mise à jour du texte des boutons switch
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[type="checkbox"].form-check-input').forEach(function(checkbox) {
        const label = checkbox.nextElementSibling;
        if (label && label.classList.contains('form-check-label')) {
            checkbox.addEventListener('change', function() {
                label.textContent = this.checked ? 'Activé' : 'Désactivé';
            });
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.configuration-item {
    transition: all 0.2s ease;
}

.configuration-item:hover {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.nav-tabs-custom .nav-link {
    border-bottom: 2px solid transparent;
}

.nav-tabs-custom .nav-link.active {
    border-bottom-color: #007bff;
}

.multiselect-container {
    background-color: #fff;
}

.form-check {
    margin-bottom: 0.5rem;
}
</style>
@endpush
