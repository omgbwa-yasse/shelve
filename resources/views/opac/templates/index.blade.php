@extends('layouts.opac')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bold text-primary">Choisissez votre thème</h1>
                <p class="lead text-muted">Personnalisez l'apparence de votre catalogue selon vos préférences</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Template actuel -->
            @if($currentTemplate)
                <div class="card border-primary mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-star me-2"></i>Thème actuel : {{ $currentTemplate->name }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">{{ $currentTemplate->description }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Appliqué depuis le {{ session('template_applied_at', now())->format('d/m/Y à H:i') }}</span>
                            <a href="{{ route('opac.templates.customize', $currentTemplate) }}" class="btn btn-outline-primary">
                                <i class="fas fa-palette"></i> Personnaliser
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Liste des templates disponibles -->
            <div class="row">
                @foreach($templates as $template)
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100 template-card {{ $currentTemplate && $currentTemplate->id === $template->id ? 'border-primary current-template' : '' }}">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">{{ $template->name }}</h6>
                                @if($currentTemplate && $currentTemplate->id === $template->id)
                                    <span class="badge bg-primary">Actuel</span>
                                @endif
                            </div>

                            <div class="card-body">
                                <!-- Aperçu miniature -->
                                <div class="template-preview mb-3"
                                     style="height: 120px; border: 1px solid #dee2e6; border-radius: 0.375rem; overflow: hidden; position: relative;">
                                    <div class="preview-header"
                                         style="background: {{ $template->variables['primary_color'] ?? '#007bff' }}; height: 25px; position: relative;">
                                        <div style="background: {{ $template->variables['secondary_color'] ?? '#6c757d' }}; height: 8px; width: 60%; position: absolute; top: 50%; left: 10px; transform: translateY(-50%); border-radius: 2px;"></div>
                                    </div>
                                    <div class="preview-content p-2">
                                        <div class="d-flex mb-1">
                                            <div style="background: {{ $template->variables['accent_color'] ?? '#28a745' }}; height: 6px; width: 80%; border-radius: 1px;"></div>
                                        </div>
                                        <div class="d-flex mb-1">
                                            <div style="background: #dee2e6; height: 4px; width: 60%; border-radius: 1px;"></div>
                                        </div>
                                        <div class="d-flex mb-1">
                                            <div style="background: #dee2e6; height: 4px; width: 70%; border-radius: 1px;"></div>
                                        </div>
                                        <div class="d-flex gap-1 mt-2">
                                            <div style="background: {{ $template->variables['primary_color'] ?? '#007bff' }}; height: 12px; width: 30%; border-radius: 2px;"></div>
                                            <div style="background: {{ $template->variables['secondary_color'] ?? '#6c757d' }}; height: 12px; width: 25%; border-radius: 2px;"></div>
                                        </div>
                                    </div>
                                </div>

                                <p class="card-text text-muted small">{{ $template->description }}</p>

                                <!-- Palette de couleurs -->
                                <div class="color-palette mb-3">
                                    <small class="text-muted">Couleurs :</small>
                                    <div class="d-flex gap-1 mt-1">
                                        <div class="color-dot"
                                             style="width: 20px; height: 20px; border-radius: 50%; background: {{ $template->variables['primary_color'] ?? '#007bff' }}; border: 2px solid #fff; box-shadow: 0 0 3px rgba(0,0,0,0.3);"
                                             title="Couleur primaire"></div>
                                        <div class="color-dot"
                                             style="width: 20px; height: 20px; border-radius: 50%; background: {{ $template->variables['secondary_color'] ?? '#6c757d' }}; border: 2px solid #fff; box-shadow: 0 0 3px rgba(0,0,0,0.3);"
                                             title="Couleur secondaire"></div>
                                        <div class="color-dot"
                                             style="width: 20px; height: 20px; border-radius: 50%; background: {{ $template->variables['accent_color'] ?? '#28a745' }}; border: 2px solid #fff; box-shadow: 0 0 3px rgba(0,0,0,0.3);"
                                             title="Couleur d'accent"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="d-grid gap-2">
                                    @if(!$currentTemplate || $currentTemplate->id !== $template->id)
                                        <form method="POST" action="{{ route('opac.templates.apply') }}">
                                            @csrf
                                            <input type="hidden" name="template_id" value="{{ $template->id }}">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-check"></i> Appliquer ce thème
                                            </button>
                                        </form>
                                    @endif

                                    <div class="btn-group w-100">
                                        <a href="{{ route('opac.templates.preview', $template) }}"
                                           class="btn btn-outline-info" target="_blank">
                                            <i class="fas fa-eye"></i> Aperçu
                                        </a>
                                        <a href="{{ route('opac.templates.customize', $template) }}"
                                           class="btn btn-outline-secondary">
                                            <i class="fas fa-palette"></i> Personnaliser
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($templates->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-palette fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Aucun thème disponible</h4>
                    <p class="text-muted">Aucun template n'est actuellement disponible. Contactez l'administrateur.</p>
                </div>
            @endif

            <!-- Retour à l'OPAC -->
            <div class="text-center mt-4">
                <a href="{{ route('opac.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Retour au catalogue
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.template-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.template-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.current-template {
    box-shadow: 0 0 20px rgba(13, 110, 253, 0.3);
}

.template-preview {
    background: #f8f9fa;
}

.color-dot {
    cursor: help;
}

.preview-header::after {
    content: '';
    position: absolute;
    top: 8px;
    right: 10px;
    width: 8px;
    height: 8px;
    background: rgba(255,255,255,0.8);
    border-radius: 50%;
}
</style>
@endsection
