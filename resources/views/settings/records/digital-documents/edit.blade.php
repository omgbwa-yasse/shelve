@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Modifier le type de document: {{ $documentType->name }}</h1>
        <a href="{{ route('settings.document-types.show', $documentType) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('settings.document-types.update', $documentType) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror"
                                           id="code" name="code" value="{{ old('code', $documentType->code) }}" required maxlength="50">
                                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $documentType->name) }}" required maxlength="255">
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3">{{ old('description', $documentType->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="icon" class="form-label">Icône</label>
                                    <input type="text" class="form-control @error('icon') is-invalid @enderror"
                                           id="icon" name="icon" value="{{ old('icon', $documentType->icon) }}" maxlength="100">
                                    @error('icon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="color" class="form-label">Couleur</label>
                                    <input type="color" class="form-control form-control-color" id="color" name="color"
                                           value="{{ old('color', $documentType->color ?? '#000000') }}" style="height: 38px;">
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5>Configuration avancée</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code_prefix" class="form-label">Préfixe de code</label>
                                    <input type="text" class="form-control" id="code_prefix" name="code_prefix"
                                           value="{{ old('code_prefix', $documentType->code_prefix) }}" maxlength="10">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code_pattern" class="form-label">Motif de code</label>
                                    <input type="text" class="form-control" id="code_pattern" name="code_pattern"
                                           value="{{ old('code_pattern', $documentType->code_pattern) }}" maxlength="50">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_file_size" class="form-label">Taille maximale (octets)</label>
                                    <input type="number" class="form-control" id="max_file_size" name="max_file_size"
                                           value="{{ old('max_file_size', $documentType->max_file_size) }}" min="1">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="default_access_level" class="form-label">Niveau d'accès par défaut</label>
                                    <select class="form-select" id="default_access_level" name="default_access_level">
                                        <option value="">-- Aucun --</option>
                                        <option value="public" {{ old('default_access_level', $documentType->default_access_level) === 'public' ? 'selected' : '' }}>Public</option>
                                        <option value="internal" {{ old('default_access_level', $documentType->default_access_level) === 'internal' ? 'selected' : '' }}>Interne</option>
                                        <option value="confidential" {{ old('default_access_level', $documentType->default_access_level) === 'confidential' ? 'selected' : '' }}>Confidentiel</option>
                                        <option value="secret" {{ old('default_access_level', $documentType->default_access_level) === 'secret' ? 'selected' : '' }}>Secret</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="metadata_template_id" class="form-label">Template de métadonnées</label>
                                    <select class="form-select" id="metadata_template_id" name="metadata_template_id">
                                        <option value="">-- Aucun --</option>
                                        @foreach($metadataTemplates as $template)
                                            <option value="{{ $template->id }}"
                                                {{ old('metadata_template_id', $documentType->metadata_template_id) == $template->id ? 'selected' : '' }}>
                                                {{ $template->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="require_virus_scan"
                                           name="require_virus_scan" value="1"
                                           {{ old('require_virus_scan', $documentType->require_virus_scan) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="require_virus_scan">Analyser les virus</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="requires_approval"
                                           name="requires_approval" value="1"
                                           {{ old('requires_approval', $documentType->requires_approval) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="requires_approval">Approbation requise</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_active"
                                           name="is_active" value="1"
                                           {{ old('is_active', $documentType->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Actif</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('settings.document-types.show', $documentType) }}" class="btn btn-secondary">Annuler</a>
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
                        <dd class="col-sm-6">{{ $documentType->created_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-6">Modifié le</dt>
                        <dd class="col-sm-6">{{ $documentType->updated_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-6">Type</dt>
                        <dd class="col-sm-6">
                            {{ $documentType->is_system ? 'Système' : 'Personnalisé' }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
