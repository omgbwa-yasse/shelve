@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header avec navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('settings.definitions.index') }}" class="text-decoration-none">
                            <i class="bi bi-gear-wide-connected me-1"></i>{{ __('Parameters') }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Parameter Details') }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 mt-2 text-primary">
                <i class="bi bi-gear me-2"></i>{{ $setting->name }}
            </h1>
            <p class="text-muted mb-0">{{ Str::limit($setting->description, 100) }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('settings.definitions.edit', $setting) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-2"></i>{{ __('Modify') }}
            </a>
            <a href="{{ route('settings.definitions.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Informations générales -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>{{ __('General Information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label class="form-label text-muted small">{{ __('ID') }}</label>
                            <div class="fw-bold">
                                <code class="bg-light px-2 py-1 rounded">{{ $setting->id }}</code>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="form-label text-muted small">{{ __('Status') }}</label>
                            <div>
                                @if($setting->is_system)
                                    <span class="badge bg-warning">
                                        <i class="bi bi-cpu me-1"></i>{{ __('System') }}
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="bi bi-person me-1"></i>{{ __('User Parameter') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">{{ __('Category') }}</label>
                        <div>
                            @if($setting->category)
                                <a href="{{ route('settings.categories.show', $setting->category) }}" 
                                   class="badge bg-secondary text-decoration-none">
                                    <i class="bi bi-folder me-1"></i>{{ $setting->category->name }}
                                </a>
                            @else
                                <span class="text-muted">
                                    <i class="bi bi-folder-x me-1"></i>{{ __('No Category') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">{{ __('Name') }}</label>
                        <div class="fw-bold">
                            <code class="bg-light px-2 py-1 rounded">{{ $setting->name }}</code>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">{{ __('Type') }}</label>
                        <div>
                            <span class="badge bg-info">
                                <i class="bi bi-code me-1"></i>{{ $setting->type }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">{{ __('Required') }}</label>
                        <div>
                            @if(isset($setting->is_required) && $setting->is_required)
                                <span class="badge bg-danger">
                                    <i class="bi bi-exclamation-triangle me-1"></i>{{ __('Yes') }}
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('No') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">{{ __('Active') }}</label>
                        <div>
                            @if(isset($setting->is_active) && $setting->is_active)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('Yes') }}
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-x-circle me-1"></i>{{ __('No') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label class="form-label text-muted small">{{ __('Created') }}</label>
                            <div class="small">
                                <i class="bi bi-calendar-plus me-1"></i>{{ $setting->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="form-label text-muted small">{{ __('Modified') }}</label>
                            <div class="small">
                                <i class="bi bi-calendar-check me-1"></i>{{ $setting->updated_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Valeurs -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-database me-2"></i>{{ __('Values') }}
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Valeur par défaut -->
                    <div class="mb-4">
                        <label class="form-label text-muted small">{{ __('Default Value') }}</label>
                        <div class="border rounded p-3 bg-light">
                            @if($setting->default_value !== null)
                                @if(is_bool($setting->default_value))
                                    <span class="badge {{ $setting->default_value ? 'bg-success' : 'bg-secondary' }}">
                                        <i class="bi bi-{{ $setting->default_value ? 'check' : 'x' }}-circle me-1"></i>
                                        {{ $setting->default_value ? __('true') : __('false') }}
                                    </span>
                                @elseif(is_array($setting->default_value) || is_object($setting->default_value))
                                    <pre class="mb-0 small"><code>{{ json_encode($setting->default_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                @else
                                    <code class="text-dark">{{ $setting->default_value }}</code>
                                @endif
                            @else
                                <span class="text-muted">
                                    <i class="bi bi-dash-circle me-1"></i>{{ __('No Default Value') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Valeur actuelle -->
                    <div class="mb-4">
                        <label class="form-label text-muted small">{{ __('Current Value') }}</label>
                        @if($setting->hasCustomValue())
                            <div class="border rounded p-3 bg-success-subtle">
                                <small class="text-success d-block mb-2">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('Custom Value') }}
                                </small>
                                @if(is_bool($setting->value))
                                    <span class="badge {{ $setting->value ? 'bg-success' : 'bg-secondary' }}">
                                        <i class="bi bi-{{ $setting->value ? 'check' : 'x' }}-circle me-1"></i>
                                        {{ $setting->value ? __('true') : __('false') }}
                                    </span>
                                @elseif(is_array($setting->value) || is_object($setting->value))
                                    <pre class="mb-0 small"><code>{{ json_encode($setting->value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                @else
                                    <code class="text-success">{{ $setting->value }}</code>
                                @endif
                            </div>
                        @else
                            <div class="text-muted">
                                <em>
                                    <i class="bi bi-arrow-right me-1"></i>{{ __('Uses Default Value') }}
                                </em>
                                @if($setting->default_value !== null)
                                    <div class="mt-2">
                                        @if(is_bool($setting->default_value))
                                            <span class="badge {{ $setting->default_value ? 'bg-success' : 'bg-secondary' }}">
                                                <i class="bi bi-{{ $setting->default_value ? 'check' : 'x' }}-circle me-1"></i>
                                                {{ $setting->default_value ? __('true') : __('false') }}
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
                    </div>

                    <!-- Valeur effective -->
                    <div class="mb-3">
                        <label class="form-label text-muted small">{{ __('Effective Value') }}</label>
                        @php
                            $effectiveValue = $setting->getEffectiveValue();
                        @endphp
                        <div class="border rounded p-3 bg-primary-subtle">
                            <small class="text-primary d-block mb-2">
                                <i class="bi bi-lightning me-1"></i>{{ __('Value Used by Application') }}
                            </small>
                            @if(is_bool($effectiveValue))
                                <span class="badge {{ $effectiveValue ? 'bg-success' : 'bg-secondary' }}">
                                    <i class="bi bi-{{ $effectiveValue ? 'check' : 'x' }}-circle me-1"></i>
                                    {{ $effectiveValue ? __('true') : __('false') }}
                                </span>
                            @elseif(is_array($effectiveValue) || is_object($effectiveValue))
                                <pre class="mb-0 small"><code>{{ json_encode($effectiveValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                            @else
                                <code class="text-primary">{{ $effectiveValue }}</code>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations supplémentaires -->
    <div class="row">
        @if($setting->description)
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-text me-2"></i>{{ __('Description') }}
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $setting->description }}</p>
                </div>
            </div>
        </div>
        @endif

        @if($setting->constraints)
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-exclamation me-2"></i>{{ __('Constraints') }}
                    </h5>
                </div>
                <div class="card-body">
                    <pre class="mb-0 bg-light p-3 rounded"><code>{{ json_encode($setting->constraints, JSON_PRETTY_PRINT) }}</code></pre>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Actions -->
    <div class="row">
        <div class="col-12">
            @if(!$setting->hasCustomValue())
            <div class="card bg-light border-0">
                <div class="card-body text-center">
                    <div class="row align-items-center">
                        <div class="col-md-8 text-md-start">
                            <h6 class="card-title mb-1">{{ __('Customize This Parameter') }}</h6>
                            <p class="card-text text-muted mb-0">{{ __('This parameter currently uses its default value. You can customize it for your account.') }}</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <button type="button" class="btn btn-primary" onclick="showCustomizeModal()">
                                <i class="bi bi-gear me-2"></i>{{ __('Customize') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="card bg-success-subtle border-success">
                <div class="card-body text-center">
                    <div class="row align-items-center">
                        <div class="col-md-8 text-md-start">
                            <h6 class="card-title text-success mb-1">{{ __('Parameter Customized') }}</h6>
                            <p class="card-text text-success mb-0">{{ __('This parameter has been customized for your account.') }}</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <button type="button" class="btn btn-warning" onclick="resetSetting()">
                                <i class="bi bi-arrow-clockwise me-2"></i>{{ __('Reset') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Actions de suppression -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-end">
                <form method="POST" action="{{ route('settings.definitions.destroy', $setting) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" 
                            onclick="return confirm('{{ __('Are you sure you want to delete this parameter? All associated values will also be deleted.') }}')">
                        <i class="bi bi-trash me-2"></i>{{ __('Delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour personnaliser un paramètre -->
<div class="modal fade" id="customizeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-gear me-2"></i>{{ __('Customize Parameter') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="customizeForm">
                    @csrf
                    <div class="mb-3">
                        <label for="customValue" class="form-label">{{ __('New Value') }}</label>
                        <input type="text" class="form-control" id="customValue" name="value" required>
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>{{ __('Type') }}: <strong>{{ $setting->type }}</strong>
                        </div>
                        <div class="form-text text-muted" id="exampleHelp"></div>
                    </div>
                    <div id="errorAlert" class="alert alert-danger" style="display: none;"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>{{ __('Cancel') }}
                </button>
                <button type="button" class="btn btn-primary" id="saveBtn">
                    <span class="spinner-border spinner-border-sm" aria-hidden="true" style="display: none;"></span>
                    <span class="visually-hidden" id="loadingText" style="display: none;">{{ __('Loading...') }}</span>
                    <span id="buttonText">
                        <i class="bi bi-check-circle me-2"></i>{{ __('Save') }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    border-bottom: none;
}

.badge {
    font-size: 0.75rem;
    padding: 0.5em 0.75em;
}

code {
    font-size: 0.875rem;
    background-color: rgba(0, 0, 0, 0.05);
    padding: 0.25em 0.5em;
    border-radius: 4px;
}

pre {
    background-color: rgba(0, 0, 0, 0.05);
    border-radius: 8px;
    font-size: 0.875rem;
}

.breadcrumb-item a {
    color: #6c757d;
}

.breadcrumb-item a:hover {
    color: #0d6efd;
}

.modal-content {
    border-radius: 12px;
    border: none;
}

.modal-header {
    border-radius: 12px 12px 0 0;
}

@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}
</style>

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
            break;
        case 'array':
        case 'json':
            input.type = 'text';
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
            examples = '{{ __("Boolean Examples") }}: true, false, 1, 0, yes, no, on, off';
            break;
        case 'integer':
            examples = '{{ __("Integer Example") }}: 42';
            break;
        case 'float':
            examples = '{{ __("Float Example") }}: 3.14';
            break;
        case 'array':
            examples = '{{ __("Array Example") }}: ["valeur1", "valeur2"] ou [1, 2, 3]';
            break;
        case 'json':
            examples = '{{ __("JSON Example") }}: {"clé": "valeur"} ou ["item1", "item2"]';
            break;
        case 'string':
        default:
            examples = '{{ __("String Example") }}: {{ __("Free Text") }}';
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
        showError('{{ __("The value cannot be empty.") }}');
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
            showError('{{ __("Validation Error") }}: ' + data.errors.value[0]);
        } else if (data.error) {
            showError('{{ __("Validation Error") }}: ' + (data.errors?.value?.[0] || data.error));
        } else {
            // Succès - recharger la page
            location.reload();
        }
    })
    .catch(error => {
        console.error('Erreur lors de la sauvegarde:', error);
        showError('{{ __("Error during save. Please try again.") }}');
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
                showError('{{ __("Please enter a valid integer.") }}');
                return false;
            }
            break;
        case 'float':
            if (!/^-?\d*\.?\d+$/.test(value)) {
                showError('{{ __("Please enter a valid decimal number.") }}');
                return false;
            }
            break;
        case 'boolean':
            const validBooleans = ['true', 'false', '1', '0', 'yes', 'no', 'on', 'off'];
            if (!validBooleans.includes(value.toLowerCase())) {
                showError('{{ __("Please enter a valid boolean value (true/false, 1/0, yes/no, on/off).") }}');
                return false;
            }
            break;
        case 'array':
        case 'json':
            try {
                JSON.parse(value);
            } catch (e) {
                showError('{{ __("Please enter valid JSON.") }}');
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
    if (confirm('{{ __("Are you sure you want to reset this parameter to its default value?") }}')) {
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
                alert('{{ __("Error during reset") }}');
            }
        })
        .catch(error => {
            alert('{{ __("Error during reset") }}');
            console.error(error);
        });
    }
}

// Event listener pour le bouton de sauvegarde
document.getElementById('saveBtn').addEventListener('click', saveCustomValue);
</script>
@endsection
