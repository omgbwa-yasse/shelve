@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Créer un Template OPAC</h1>
            <p class="text-muted">Créez un nouveau template d'affichage pour le catalogue public</p>
        </div>
        <a href="{{ route('public.opac-templates.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <h6>Erreurs de validation :</h6>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('public.opac-templates.store') }}">
        @csrf

        <div class="row">
            <!-- Informations générales -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informations générales</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nom du template <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Statut</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Actif</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Variables de couleur -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Couleurs et style</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="primary_color" class="form-label">Couleur primaire</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color"
                                           id="primary_color" name="variables[primary_color]"
                                           value="{{ old('variables.primary_color', '#007bff') }}">
                                    <input type="text" class="form-control"
                                           value="{{ old('variables.primary_color', '#007bff') }}"
                                           onchange="this.previousElementSibling.value = this.value">
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="secondary_color" class="form-label">Couleur secondaire</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color"
                                           id="secondary_color" name="variables[secondary_color]"
                                           value="{{ old('variables.secondary_color', '#6c757d') }}">
                                    <input type="text" class="form-control"
                                           value="{{ old('variables.secondary_color', '#6c757d') }}"
                                           onchange="this.previousElementSibling.value = this.value">
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="accent_color" class="form-label">Couleur d'accent</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color"
                                           id="accent_color" name="variables[accent_color]"
                                           value="{{ old('variables.accent_color', '#28a745') }}">
                                    <input type="text" class="form-control"
                                           value="{{ old('variables.accent_color', '#28a745') }}"
                                           onchange="this.previousElementSibling.value = this.value">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="font_family" class="form-label">Police principale</label>
                                <select class="form-select" id="font_family" name="variables[font_family]">
                                    <option value="Inter, sans-serif" {{ old('variables.font_family') === 'Inter, sans-serif' ? 'selected' : '' }}>Inter (défaut)</option>
                                    <option value="Arial, sans-serif" {{ old('variables.font_family') === 'Arial, sans-serif' ? 'selected' : '' }}>Arial</option>
                                    <option value="Georgia, serif" {{ old('variables.font_family') === 'Georgia, serif' ? 'selected' : '' }}>Georgia</option>
                                    <option value="Times New Roman, serif" {{ old('variables.font_family') === 'Times New Roman, serif' ? 'selected' : '' }}>Times New Roman</option>
                                    <option value="Roboto, sans-serif" {{ old('variables.font_family') === 'Roboto, sans-serif' ? 'selected' : '' }}>Roboto</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="border_radius" class="form-label">Rayon des bordures</label>
                                <select class="form-select" id="border_radius" name="variables[border_radius]">
                                    <option value="4px" {{ old('variables.border_radius', '4px') === '4px' ? 'selected' : '' }}>Petit (4px)</option>
                                    <option value="8px" {{ old('variables.border_radius') === '8px' ? 'selected' : '' }}>Moyen (8px)</option>
                                    <option value="12px" {{ old('variables.border_radius') === '12px' ? 'selected' : '' }}>Grand (12px)</option>
                                    <option value="0px" {{ old('variables.border_radius') === '0px' ? 'selected' : '' }}>Aucun (0px)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenu personnalisé -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Contenu HTML personnalisé</h5>
                        <small class="text-muted">HTML et CSS personnalisés pour le template</small>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="content" class="form-label">HTML du template</label>
                            <textarea class="form-control @error('content') is-invalid @enderror"
                                      id="content" name="content" rows="10"
                                      placeholder="<div>Votre HTML personnalisé...</div>">{{ old('content', '<div class="opac-template">
    <h1>{{ $title ?? "Mon Catalogue" }}</h1>
    <div class="search-section">
        <!-- Zone de recherche -->
    </div>
    <div class="results-section">
        <!-- Zone des résultats -->
    </div>
</div>') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aperçu et actions -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Aperçu</h5>
                    </div>
                    <div class="card-body">
                        <div id="template-preview" class="border rounded p-3" style="min-height: 200px; background: #f8f9fa;">
                            <div class="preview-header" style="background: var(--primary-color, #007bff); color: white; padding: 10px; border-radius: 4px; margin-bottom: 10px;">
                                <h5 class="mb-0">Mon Catalogue OPAC</h5>
                            </div>
                            <div class="preview-search" style="background: white; padding: 15px; border-radius: 4px; margin-bottom: 10px; border: 1px solid #dee2e6;">
                                <input type="text" class="form-control" placeholder="Rechercher dans le catalogue..." disabled>
                            </div>
                            <div class="preview-results">
                                <div style="background: white; padding: 10px; border-radius: 4px; margin-bottom: 5px; border: 1px solid #dee2e6;">
                                    <div style="background: var(--secondary-color, #6c757d); height: 15px; width: 80%; margin-bottom: 5px; border-radius: 2px;"></div>
                                    <div style="background: #dee2e6; height: 10px; width: 60%; border-radius: 1px;"></div>
                                </div>
                                <div style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #dee2e6;">
                                    <div style="background: var(--accent-color, #28a745); height: 15px; width: 70%; margin-bottom: 5px; border-radius: 2px;"></div>
                                    <div style="background: #dee2e6; height: 10px; width: 50%; border-radius: 1px;"></div>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted">L'aperçu se met à jour automatiquement</small>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Créer le template
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="updatePreview()">
                                <i class="fas fa-eye"></i> Actualiser l'aperçu
                            </button>
                        </div>

                        <hr>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Activer immédiatement ce template
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Synchroniser les couleurs avec l'aperçu
    const colorInputs = document.querySelectorAll('input[type="color"]');
    const textInputs = document.querySelectorAll('input[type="text"]');

    colorInputs.forEach(input => {
        input.addEventListener('change', function() {
            this.nextElementSibling.value = this.value;
            updatePreview();
        });
    });

    textInputs.forEach(input => {
        input.addEventListener('change', function() {
            this.previousElementSibling.value = this.value;
            updatePreview();
        });
    });

    // Mise à jour initiale
    updatePreview();
});

function updatePreview() {
    const primaryColor = document.getElementById('primary_color').value;
    const secondaryColor = document.getElementById('secondary_color').value;
    const accentColor = document.getElementById('accent_color').value;

    document.documentElement.style.setProperty('--primary-color', primaryColor);
    document.documentElement.style.setProperty('--secondary-color', secondaryColor);
    document.documentElement.style.setProperty('--accent-color', accentColor);
}
</script>
@endsection
