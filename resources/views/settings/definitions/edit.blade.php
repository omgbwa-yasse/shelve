@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Modifier le paramètre</h5>
                    <a href="{{ route('settings.definitions.show', $setting) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.definitions.update', $setting) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Catégorie *</label>
                                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                        <option value="">Sélectionner une catégorie</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ (old('category_id', $setting->category_id) == $category->id) ? 'selected' : '' }}>
                                                {{ str_repeat('— ', $category->depth ?? 0) }}{{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="key" class="form-label">Clé *</label>
                                    <input type="text"
                                           class="form-control @error('key') is-invalid @enderror"
                                           id="key"
                                           name="key"
                                           value="{{ old('key', $setting->key) }}"
                                           required
                                           placeholder="ex: max_upload_size">
                                    @error('key')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Identifiant unique du paramètre (snake_case recommandé)
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="label" class="form-label">Libellé *</label>
                                    <input type="text"
                                           class="form-control @error('label') is-invalid @enderror"
                                           id="label"
                                           name="label"
                                           value="{{ old('label', $setting->label) }}"
                                           required
                                           placeholder="ex: Taille maximale d'upload">
                                    @error('label')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Type *</label>
                                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                        <option value="">Sélectionner un type</option>
                                        <option value="string" {{ old('type', $setting->type) == 'string' ? 'selected' : '' }}>Chaîne de caractères</option>
                                        <option value="integer" {{ old('type', $setting->type) == 'integer' ? 'selected' : '' }}>Nombre entier</option>
                                        <option value="boolean" {{ old('type', $setting->type) == 'boolean' ? 'selected' : '' }}>Booléen</option>
                                        <option value="json" {{ old('type', $setting->type) == 'json' ? 'selected' : '' }}>JSON</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                              id="description"
                                              name="description"
                                              rows="3"
                                              placeholder="Description détaillée du paramètre...">{{ old('description', $setting->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="default_value" class="form-label">Valeur par défaut</label>
                                    <textarea class="form-control @error('default_value') is-invalid @enderror"
                                              id="default_value"
                                              name="default_value"
                                              rows="3"
                                              placeholder="Valeur par défaut (JSON)...">{{ old('default_value', $setting->default_value) }}</textarea>
                                    @error('default_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Format JSON requis (ex: "texte", 123, true, {"key": "value"})
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="constraints" class="form-label">Contraintes</label>
                                    <textarea class="form-control @error('constraints') is-invalid @enderror"
                                              id="constraints"
                                              name="constraints"
                                              rows="3"
                                              placeholder="Contraintes (JSON)...">{{ old('constraints', $setting->constraints) }}</textarea>
                                    @error('constraints')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Format JSON : {"min": 1, "max": 100} ou {"min_length": 5, "max_length": 255}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="is_required"
                                               name="is_required"
                                               {{ old('is_required', $setting->is_required) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_required">
                                            Paramètre obligatoire
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="is_active"
                                               name="is_active"
                                               {{ old('is_active', $setting->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Paramètre actif
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">Annuler</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('type').addEventListener('change', function() {
    const type = this.value;
    const constraintsField = document.getElementById('constraints');
    const defaultValueField = document.getElementById('default_value');

    // Proposer des exemples selon le type
    if (type === 'string') {
        constraintsField.placeholder = '{"min_length": 1, "max_length": 255}';
        defaultValueField.placeholder = '"Valeur par défaut"';
    } else if (type === 'integer') {
        constraintsField.placeholder = '{"min": 0, "max": 100}';
        defaultValueField.placeholder = '10';
    } else if (type === 'boolean') {
        constraintsField.placeholder = '{}';
        defaultValueField.placeholder = 'true';
    } else if (type === 'json') {
        constraintsField.placeholder = '{}';
        defaultValueField.placeholder = '{"key": "value"}';
    }
});
</script>
@endsection
