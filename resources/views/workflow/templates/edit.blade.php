@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/workflow-templates.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">
                <i class="bi bi-pencil-square me-2"></i>
                {{ __('Modifier le modèle de workflow') }}
            </h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('workflows.templates.show', $template) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('Retour aux détails') }}
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('workflows.templates.update', $template) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="name" class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $template->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="description" class="form-label">{{ __('Description') }}</label>
                    <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"
                              rows="3">{{ old('description', $template->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                           {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">{{ __('Modèle actif') }}</label>
                </div>

                <!-- Section Configuration des Étapes -->
                <div class="card border-secondary mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="bi bi-list-check me-2"></i>
                            {{ __('Gestion des Étapes') }}
                        </h6>
                        <span class="badge bg-success">{{ $template->steps->count() }} étape(s)</span>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            {{ __('Pour gérer les étapes du workflow, utilisez la section dédiée disponible depuis la') }}
                            <a href="{{ route('workflows.templates.show', $template) }}" class="alert-link">
                                {{ __('page de détails du template') }}
                            </a>.
                        </div>


                        </div>
                    </div>
                </div>

                <div class="border-top pt-3 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>
                        {{ __('Enregistrer les modifications') }}
                    </button>
                    <a href="{{ route('workflows.templates.show', $template) }}" class="btn btn-outline-secondary ms-2">
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
    // Aucun code JavaScript spécifique nécessaire pour l'édition de base du template
});


</script>
@endsection
