@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Modifier la définition: {{ $metadataDefinition->name }}</h1>
        <a href="{{ route('settings.metadata-definitions.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('settings.metadata-definitions.update', $metadataDefinition) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $metadataDefinition->name) }}"
                                           required maxlength="100">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror"
                                           id="code" name="code" value="{{ old('code', $metadataDefinition->code) }}"
                                           required maxlength="50">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="2">{{ old('description', $metadataDefinition->description) }}</textarea>
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
                                    <option value="{{ $key }}"
                                        {{ old('data_type', $metadataDefinition->data_type) === $key ? 'selected' : '' }}>
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
                                    <option value="{{ $list->id }}"
                                        {{ old('reference_list_id', $metadataDefinition->reference_list_id) == $list->id ? 'selected' : '' }}>
                                        {{ $list->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('reference_list_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="validation_rules" class="form-label">Règles de validation (JSON)</label>
                            <textarea class="form-control @error('validation_rules') is-invalid @enderror"
                                      id="validation_rules" name="validation_rules" rows="3">{{ old('validation_rules', $metadataDefinition->validation_rules) }}</textarea>
                            <small class="form-text text-muted">Règles de validation au format JSON</small>
                            @error('validation_rules')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="searchable"
                                           name="searchable" value="1"
                                           {{ old('searchable', $metadataDefinition->searchable) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="searchable">
                                        Champ recherchable
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="active"
                                           name="active" value="1"
                                           {{ old('active', $metadataDefinition->active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="active">
                                        Actif
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('settings.metadata-definitions.index') }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Informations système</h5>
                </div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Créé le</dt>
                        <dd class="col-sm-6">{{ $metadataDefinition->created_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-6">Modifié le</dt>
                        <dd class="col-sm-6">{{ $metadataDefinition->updated_at->format('d/m/Y H:i') }}</dd>

                        @if($metadataDefinition->creator)
                            <dt class="col-sm-6">Créé par</dt>
                            <dd class="col-sm-6">{{ $metadataDefinition->creator->name }}</dd>
                        @endif
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
