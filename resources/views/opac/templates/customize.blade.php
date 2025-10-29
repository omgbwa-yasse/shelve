@extends('layouts.opac')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Personnaliser le thème</h1>
                    <p class="text-muted">{{ $template->name }} - {{ $template->description }}</p>
                </div>
                <a href="{{ route('opac.templates.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form id="customization-form" method="POST" action="{{ route('opac.templates.save-customization') }}">
                @csrf
                <input type="hidden" name="template_id" value="{{ $template->id }}">

                <!-- Couleurs principales -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-palette me-2"></i>Couleurs du thème
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="primary_color" class="form-label">Couleur principale</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color"
                                           id="primary_color" name="variables[primary_color]"
                                           value="{{ old('variables.primary_color', $customizations['primary_color'] ?? $template->variables['primary_color'] ?? '#007bff') }}">
                                    <input type="text" class="form-control color-text"
                                           data-color-input="primary_color"
                                           value="{{ old('variables.primary_color', $customizations['primary_color'] ?? $template->variables['primary_color'] ?? '#007bff') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="secondary_color" class="form-label">Couleur secondaire</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color"
                                           id="secondary_color" name="variables[secondary_color]"
                                           value="{{ old('variables.secondary_color', $customizations['secondary_color'] ?? $template->variables['secondary_color'] ?? '#6c757d') }}">
                                    <input type="text" class="form-control color-text"
                                           data-color-input="secondary_color"
                                           value="{{ old('variables.secondary_color', $customizations['secondary_color'] ?? $template->variables['secondary_color'] ?? '#6c757d') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="accent_color" class="form-label">Couleur d'accent</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color"
                                           id="accent_color" name="variables[accent_color]"
                                           value="{{ old('variables.accent_color', $customizations['accent_color'] ?? $template->variables['accent_color'] ?? '#28a745') }}">
                                    <input type="text" class="form-control color-text"
                                           data-color-input="accent_color"
                                           value="{{ old('variables.accent_color', $customizations['accent_color'] ?? $template->variables['accent_color'] ?? '#28a745') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="background_color" class="form-label">Arrière-plan</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color"
                                           id="background_color" name="variables[background_color]"
                                           value="{{ old('variables.background_color', $customizations['background_color'] ?? $template->variables['background_color'] ?? '#ffffff') }}">
                                    <input type="text" class="form-control color-text"
                                           data-color-input="background_color"
                                           value="{{ old('variables.background_color', $customizations['background_color'] ?? $template->variables['background_color'] ?? '#ffffff') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Presets de couleurs -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Palettes prédéfinies</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <button type="button" class="btn btn-outline-secondary w-100 color-preset"
                                        data-preset='{"primary_color":"#007bff","secondary_color":"#6c757d","accent_color":"#28a745","background_color":"#ffffff"}'>
                                    <div class="d-flex align-items-center">
                                        <div class="preset-colors me-2">
                                            <span style="background:#007bff" class="color-sample"></span>
                                            <span style="background:#6c757d" class="color-sample"></span>
                                            <span style="background:#28a745" class="color-sample"></span>
                                        </div>
                                        Défaut
                                    </div>
                                </button>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button type="button" class="btn btn-outline-secondary w-100 color-preset"
                                        data-preset='{"primary_color":"#dc3545","secondary_color":"#fd7e14","accent_color":"#ffc107","background_color":"#fff5f5"}'>
                                    <div class="d-flex align-items-center">
                                        <div class="preset-colors me-2">
                                            <span style="background:#dc3545" class="color-sample"></span>
                                            <span style="background:#fd7e14" class="color-sample"></span>
                                            <span style="background:#ffc107" class="color-sample"></span>
                                        </div>
                                        Chaud
                                    </div>
                                </button>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button type="button" class="btn btn-outline-secondary w-100 color-preset"
                                        data-preset='{"primary_color":"#198754","secondary_color":"#20c997","accent_color":"#0dcaf0","background_color":"#f0fff4"}'>
                                    <div class="d-flex align-items-center">
                                        <div class="preset-colors me-2">
                                            <span style="background:#198754" class="color-sample"></span>
                                            <span style="background:#20c997" class="color-sample"></span>
                                            <span style="background:#0dcaf0" class="color-sample"></span>
                                        </div>
                                        Nature
                                    </div>
                                </button>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button type="button" class="btn btn-outline-secondary w-100 color-preset"
                                        data-preset='{"primary_color":"#6f42c1","secondary_color":"#e83e8c","accent_color":"#fd7e14","background_color":"#f8f5ff"}'>
                                    <div class="d-flex align-items-center">
                                        <div class="preset-colors me-2">
                                            <span style="background:#6f42c1" class="color-sample"></span>
                                            <span style="background:#e83e8c" class="color-sample"></span>
                                            <span style="background:#fd7e14" class="color-sample"></span>
                                        </div>
                                        Créatif
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-between">
                    <div>
                        <button type="button" class="btn btn-outline-warning" id="reset-customization">
                            <i class="fas fa-undo"></i> Réinitialiser
                        </button>
                    </div>
                    <div>
                        <a href="{{ route('opac.templates.preview', ['template' => $template, 'customize' => 1]) }}"
                           class="btn btn-outline-info me-2" target="_blank" id="preview-btn">
                            <i class="fas fa-eye"></i> Aperçu
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Sauvegarder
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Aperçu en temps réel -->
        <div class="col-md-4">
            <div class="sticky-top" style="top: 20px;">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-eye me-2"></i>Aperçu en direct
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div id="live-preview" style="height: 300px; overflow: hidden;">
                            <!-- L'aperçu sera généré ici -->
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Actions rapides</h6>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="randomizeColors()">
                                <i class="fas fa-random"></i> Couleurs aléatoires
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="applyAndReturn()">
                                <i class="fas fa-check"></i> Appliquer et retour
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.color-sample {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 2px;
    margin-right: 2px;
    border: 1px solid #dee2e6;
}

.preset-colors {
    display: flex;
    align-items: center;
}

.form-control-color {
    width: 50px;
    height: 38px;
}

#live-preview {
    background: #f8f9fa;
    border-radius: 0.375rem;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Synchronisation des inputs couleur et texte
    document.querySelectorAll('input[type="color"]').forEach(colorInput => {
        const textInput = document.querySelector(`input[data-color-input="${colorInput.id}"]`);

        colorInput.addEventListener('input', function() {
            textInput.value = this.value;
            updatePreview();
        });

        textInput.addEventListener('input', function() {
            if(/^#[0-9A-F]{6}$/i.test(this.value)) {
                colorInput.value = this.value;
                updatePreview();
            }
        });
    });

    // Presets de couleurs
    document.querySelectorAll('.color-preset').forEach(button => {
        button.addEventListener('click', function() {
            const preset = JSON.parse(this.dataset.preset);
            Object.keys(preset).forEach(key => {
                const colorInput = document.getElementById(key);
                const textInput = document.querySelector(`input[data-color-input="${key}"]`);
                if(colorInput && textInput) {
                    colorInput.value = preset[key];
                    textInput.value = preset[key];
                }
            });
            updatePreview();
        });
    });

    // Réinitialisation
    document.getElementById('reset-customization').addEventListener('click', function() {
        if(confirm('Êtes-vous sûr de vouloir réinitialiser toutes les personnalisations ?')) {
            const defaults = @json($template->variables);
            Object.keys(defaults).forEach(key => {
                const colorInput = document.getElementById(key);
                const textInput = document.querySelector(`input[data-color-input="${key}"]`);
                if(colorInput && textInput && defaults[key]) {
                    colorInput.value = defaults[key];
                    textInput.value = defaults[key];
                }
            });
            updatePreview();
        }
    });

    // Mise à jour de l'aperçu
    function updatePreview() {
        const primaryColor = document.getElementById('primary_color').value;
        const secondaryColor = document.getElementById('secondary_color').value;
        const accentColor = document.getElementById('accent_color').value;
        const backgroundColor = document.getElementById('background_color').value;

        const preview = document.getElementById('live-preview');
        preview.innerHTML = `
            <div style="background: ${backgroundColor}; height: 100%; padding: 15px;">
                <div style="background: ${primaryColor}; color: white; padding: 15px 10px; margin-bottom: 8px; border-radius: 3px; font-size: 14px; font-weight: bold;">
                    {{ config('app.name', 'Bibliothèque') }}
                </div>
                <div style="background: white; padding: 8px; border-radius: 3px; margin-bottom: 6px; border: 1px solid ${secondaryColor};">
                    <div style="font-size: 12px; color: #666;">Rechercher dans le catalogue...</div>
                </div>
                <div style="display: flex; gap: 4px; margin-bottom: 8px;">
                    <div style="background: ${secondaryColor}; color: white; padding: 6px 8px; border-radius: 2px; font-size: 11px; flex: 1; text-align: center;">Livres</div>
                    <div style="background: ${accentColor}; color: white; padding: 6px 8px; border-radius: 2px; font-size: 11px; flex: 1; text-align: center;">Articles</div>
                </div>
                <div style="background: white; padding: 8px; border-radius: 3px; border-left: 3px solid ${primaryColor};">
                    <div style="font-size: 11px; font-weight: bold; margin-bottom: 3px;">Nouvelle acquisition</div>
                    <div style="font-size: 10px; color: #666; line-height: 1.3;">Lorem ipsum dolor sit amet consectetur...</div>
                </div>
            </div>
        `;

        // Mise à jour du lien d'aperçu
        const previewBtn = document.getElementById('preview-btn');
        const url = new URL(previewBtn.href);
        url.searchParams.set('primary_color', primaryColor);
        url.searchParams.set('secondary_color', secondaryColor);
        url.searchParams.set('accent_color', accentColor);
        url.searchParams.set('background_color', backgroundColor);
        previewBtn.href = url.toString();
    }

    // Aperçu initial
    updatePreview();

    // Mise à jour en temps réel
    document.querySelectorAll('input[type="color"], input.color-text').forEach(input => {
        input.addEventListener('input', updatePreview);
    });
});

// Fonctions utilitaires
function randomizeColors() {
    const colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9'];
    document.getElementById('primary_color').value = colors[Math.floor(Math.random() * colors.length)];
    document.getElementById('secondary_color').value = colors[Math.floor(Math.random() * colors.length)];
    document.getElementById('accent_color').value = colors[Math.floor(Math.random() * colors.length)];

    // Synchronisation
    document.querySelectorAll('input[type="color"]').forEach(input => {
        const textInput = document.querySelector(`input[data-color-input="${input.id}"]`);
        if(textInput) textInput.value = input.value;
    });

    updatePreview();
}

function applyAndReturn() {
    document.getElementById('customization-form').addEventListener('submit', function() {
        setTimeout(() => {
            window.location.href = '{{ route("opac.templates.index") }}';
        }, 1000);
    });
    document.getElementById('customization-form').submit();
}
</script>
@endpush
@endsection
