@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">
                <i class="bi bi-diagram-3 me-2"></i>
                {{ __('Créer une instance de workflow') }}
            </h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('workflow.instances.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('Retour à la liste') }}
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('workflow.instances.store') }}" method="POST">
                @csrf

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="template_id" class="form-label">{{ __('Modèle de workflow') }} <span class="text-danger">*</span></label>
                            <select class="form-control @error('template_id') is-invalid @enderror" id="template_id" name="template_id" required>
                                <option value="">{{ __('Sélectionnez un modèle') }}</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                        {{ $template->name }} - {{ $template->category }}
                                    </option>
                                @endforeach
                            </select>
                            @error('template_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Nom de l\'instance') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="reference" class="form-label">{{ __('Référence externe') }}</label>
                            <input type="text" class="form-control @error('reference') is-invalid @enderror" id="reference" name="reference" value="{{ old('reference') }}">
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">{{ __('Identifiant ou référence d\'un système externe') }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('Description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="entity_type" class="form-label">{{ __('Type d\'entité liée') }}</label>
                            <select class="form-control @error('entity_type') is-invalid @enderror" id="entity_type" name="entity_type">
                                <option value="">{{ __('Aucune entité liée') }}</option>
                                <option value="App\Models\Record" {{ old('entity_type') == 'App\Models\Record' ? 'selected' : '' }}>{{ __('Dossier') }}</option>
                                <option value="App\Models\Document" {{ old('entity_type') == 'App\Models\Document' ? 'selected' : '' }}>{{ __('Document') }}</option>
                                <option value="App\Models\Communication" {{ old('entity_type') == 'App\Models\Communication' ? 'selected' : '' }}>{{ __('Communication') }}</option>
                            </select>
                            @error('entity_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="entity_id_container" style="display: none;">
                            <label for="entity_id" class="form-label">{{ __('ID de l\'entité liée') }}</label>
                            <input type="text" class="form-control @error('entity_id') is-invalid @enderror" id="entity_id" name="entity_id" value="{{ old('entity_id') }}">
                            @error('entity_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('Options de démarrage') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="auto_start" name="auto_start" value="1" {{ old('auto_start') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="auto_start">{{ __('Démarrer automatiquement') }}</label>
                                    <div class="form-text">{{ __('L\'instance sera automatiquement mise en statut "en cours" dès sa création.') }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="skip_first_step" name="skip_first_step" value="1" {{ old('skip_first_step') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="skip_first_step">{{ __('Ignorer la première étape') }}</label>
                                    <div class="form-text">{{ __('La première étape sera automatiquement marquée comme complétée.') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> {{ __('Créer l\'instance') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const entityTypeSelect = document.getElementById('entity_type');
        const entityIdContainer = document.getElementById('entity_id_container');

        // Affiche ou masque le champ d'ID d'entité en fonction de la sélection
        function toggleEntityIdField() {
            if (entityTypeSelect.value) {
                entityIdContainer.style.display = 'block';
            } else {
                entityIdContainer.style.display = 'none';
            }
        }

        // Initial state
        toggleEntityIdField();

        // Listener for changes
        entityTypeSelect.addEventListener('change', toggleEntityIdField);

        // Charge les détails du modèle sélectionné
        const templateSelect = document.getElementById('template_id');
        templateSelect.addEventListener('change', function() {
            const templateId = this.value;
            if (!templateId) return;

            // Vous pourriez charger des informations supplémentaires sur le modèle ici
            // via une requête AJAX et les afficher
        });
    });
</script>
@endpush
@endsection
