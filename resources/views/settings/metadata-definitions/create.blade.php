@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Créer une nouvelle définition de métadonnée</h1>
        <a href="{{ route('settings.metadata-definitions.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('settings.metadata-definitions.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required maxlength="100">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror"
                                           id="code" name="code" value="{{ old('code') }}" required maxlength="50"
                                           placeholder="ex: author_name">
                                    <small class="form-text text-muted">Code unique utilisé en interne</small>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="2">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="data_type" class="form-label">Type de données <span class="text-danger">*</span></label>
                            <select class="form-select @error('data_type') is-invalid @enderror"
                                    id="data_type" name="data_type" required onchange="updateReferenceListVisibility()">
                                <option value="">-- Sélectionner un type --</option>
                                @foreach($dataTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('data_type') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('data_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="reference-list-container" style="display: none;">
                            <label for="reference_list_id" class="form-label">Liste de référence</label>
                            <select class="form-select @error('reference_list_id') is-invalid @enderror"
                                    id="reference_list_id" name="reference_list_id">
                                <option value="">-- Aucune --</option>
                                @foreach($referenceLists as $list)
                                    <option value="{{ $list->id }}" {{ old('reference_list_id') == $list->id ? 'selected' : '' }}>
                                        {{ $list->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Requise pour les types "Liste de référence"</small>
                            @error('reference_list_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="validation_rules" class="form-label">Règles de validation (JSON)</label>
                            <textarea class="form-control @error('validation_rules') is-invalid @enderror"
                                      id="validation_rules" name="validation_rules" rows="3"
                                      placeholder='{"min": 1, "max": 100}'>{{ old('validation_rules') }}</textarea>
                            <small class="form-text text-muted">Règles de validation au format JSON</small>
                            @error('validation_rules')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="searchable"
                                           name="searchable" value="1" {{ old('searchable') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="searchable">
                                        Champ recherchable
                                    </label>
                                    <small class="form-text text-muted d-block">Permet de rechercher par ce champ</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="active"
                                           name="active" value="1" {{ old('active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="active">
                                        Actif
                                    </label>
                                    <small class="form-text text-muted d-block">Rendre cette définition disponible</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('settings.metadata-definitions.index') }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Créer la définition
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Types de données disponibles</h5>
                </div>
                <div class="card-body small">
                    <dl class="mb-0">
                        <dt>Text / Texte</dt>
                        <dd>Champ texte simple</dd>

                        <dt>Textarea / Zone de texte</dt>
                        <dd>Texte multiligne</dd>

                        <dt>Number / Nombre</dt>
                        <dd>Valeur numérique</dd>

                        <dt>Date</dt>
                        <dd>Date (YYYY-MM-DD)</dd>

                        <dt>DateTime / Date et heure</dt>
                        <dd>Date et heure</dd>

                        <dt>Boolean / Oui-Non</dt>
                        <dd>Case à cocher</dd>

                        <dt>Select / Sélect</dt>
                        <dd>Liste déroulante</dd>

                        <dt>Reference List</dt>
                        <dd>Liste de référence prédéfinie</dd>

                        <dt>Email</dt>
                        <dd>Adresse email validée</dd>

                        <dt>URL</dt>
                        <dd>Adresse web validée</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateReferenceListVisibility() {
    const dataType = document.getElementById('data_type').value;
    const refListContainer = document.getElementById('reference-list-container');

    if (dataType === 'reference_list' || dataType === 'select' || dataType === 'multi_select') {
        refListContainer.style.display = 'block';
    } else {
        refListContainer.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    updateReferenceListVisibility();
});
</script>
@endpush
@endsection
