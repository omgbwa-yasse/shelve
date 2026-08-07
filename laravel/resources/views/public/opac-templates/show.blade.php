@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ $template->name }}</h1>
            <p class="text-muted">Détails du template OPAC</p>
        </div>
        <div>
            <a href="{{ route('public.opac-templates.edit', $template) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit"></i> Modifier
            </a>
            <a href="{{ route('public.opac-templates.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Informations principales -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informations générales</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Nom du template</h6>
                            <p class="text-muted">{{ $template->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Statut</h6>
                            <span class="badge bg-{{ $template->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($template->status) }}
                            </span>
                        </div>
                    </div>

                    @if($template->description)
                        <hr>
                        <h6>Description</h6>
                        <p class="text-muted">{{ $template->description }}</p>
                    @endif

                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <h6>Créé le</h6>
                            <p class="text-muted">{{ $template->created_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6>Modifié le</h6>
                            <p class="text-muted">{{ $template->updated_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6>Actif</h6>
                            <span class="badge bg-{{ $template->is_active ? 'success' : 'secondary' }}">
                                {{ $template->is_active ? 'Oui' : 'Non' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Variables de style -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Configuration des couleurs</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @php
                            $variables = $template->variables ?? [];
                        @endphp

                        <div class="col-md-4 mb-3">
                            <h6>Couleur primaire</h6>
                            <div class="d-flex align-items-center">
                                <div class="color-sample me-3"
                                     style="width: 30px; height: 30px; background: {{ $variables['primary_color'] ?? '#007bff' }}; border-radius: 4px; border: 1px solid #dee2e6;"></div>
                                <code>{{ $variables['primary_color'] ?? '#007bff' }}</code>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <h6>Couleur secondaire</h6>
                            <div class="d-flex align-items-center">
                                <div class="color-sample me-3"
                                     style="width: 30px; height: 30px; background: {{ $variables['secondary_color'] ?? '#6c757d' }}; border-radius: 4px; border: 1px solid #dee2e6;"></div>
                                <code>{{ $variables['secondary_color'] ?? '#6c757d' }}</code>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <h6>Couleur d'accent</h6>
                            <div class="d-flex align-items-center">
                                <div class="color-sample me-3"
                                     style="width: 30px; height: 30px; background: {{ $variables['accent_color'] ?? '#28a745' }}; border-radius: 4px; border: 1px solid #dee2e6;"></div>
                                <code>{{ $variables['accent_color'] ?? '#28a745' }}</code>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6>Police</h6>
                            <p class="text-muted" style="font-family: {{ $variables['font_family'] ?? 'Inter, sans-serif' }}">
                                {{ $variables['font_family'] ?? 'Inter, sans-serif' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Rayon des bordures</h6>
                            <p class="text-muted">{{ $variables['border_radius'] ?? '4px' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenu HTML -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Contenu HTML</h5>
                </div>
                <div class="card-body">
                    <pre><code class="language-html">{{ $template->content }}</code></pre>
                </div>
            </div>
        </div>

        <!-- Aperçu et actions -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Aperçu du template</h5>
                </div>
                <div class="card-body">
                    <div class="template-preview border rounded p-3" style="background: #f8f9fa; min-height: 300px;">
                        <!-- Header avec couleur primaire -->
                        <div class="preview-header mb-3"
                             style="background: {{ $variables['primary_color'] ?? '#007bff' }}; color: white; padding: 15px; border-radius: {{ $variables['border_radius'] ?? '4px' }};">
                            <h5 class="mb-0" style="font-family: {{ $variables['font_family'] ?? 'Inter, sans-serif' }}">
                                {{ $template->name }}
                            </h5>
                        </div>

                        <!-- Zone de recherche -->
                        <div class="preview-search mb-3"
                             style="background: white; padding: 15px; border-radius: {{ $variables['border_radius'] ?? '4px' }}; border: 1px solid #dee2e6;">
                            <input type="text" class="form-control" placeholder="Rechercher..." disabled
                                   style="border-radius: {{ $variables['border_radius'] ?? '4px' }}; font-family: {{ $variables['font_family'] ?? 'Inter, sans-serif' }}">
                        </div>

                        <!-- Résultats simulés -->
                        <div class="preview-results">
                            <div class="result-item mb-2 p-3"
                                 style="background: white; border-radius: {{ $variables['border_radius'] ?? '4px' }}; border: 1px solid #dee2e6;">
                                <div class="result-title mb-2"
                                     style="background: {{ $variables['secondary_color'] ?? '#6c757d' }}; height: 20px; width: 80%; border-radius: 2px;"></div>
                                <div class="result-meta"
                                     style="background: #dee2e6; height: 12px; width: 60%; border-radius: 1px;"></div>
                            </div>
                            <div class="result-item mb-2 p-3"
                                 style="background: white; border-radius: {{ $variables['border_radius'] ?? '4px' }}; border: 1px solid #dee2e6;">
                                <div class="result-title mb-2"
                                     style="background: {{ $variables['accent_color'] ?? '#28a745' }}; height: 20px; width: 70%; border-radius: 2px;"></div>
                                <div class="result-meta"
                                     style="background: #dee2e6; height: 12px; width: 50%; border-radius: 1px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('public.opac-templates.preview', $template) }}"
                           class="btn btn-primary" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Aperçu complet
                        </a>

                        <a href="{{ route('public.opac-templates.edit', $template) }}"
                           class="btn btn-warning">
                            <i class="fas fa-edit"></i> Modifier
                        </a>

                        <form method="POST" action="{{ route('public.opac-templates.duplicate', $template) }}">
                            @csrf
                            <button type="submit" class="btn btn-info w-100">
                                <i class="fas fa-copy"></i> Dupliquer
                            </button>
                        </form>

                        <a href="{{ route('public.opac-templates.export', $template) }}"
                           class="btn btn-success">
                            <i class="fas fa-download"></i> Exporter
                        </a>

                        <hr>

                        <form method="POST" action="{{ route('public.opac-templates.destroy', $template) }}"
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce template ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.template-preview {
    font-family: {{ $template->variables['font_family'] ?? 'Inter, sans-serif' }};
}

pre code {
    font-size: 0.875rem;
    line-height: 1.5;
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/components/prism-core.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/plugins/autoloader/prism-autoloader.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/themes/prism.min.css" rel="stylesheet">
@endsection
