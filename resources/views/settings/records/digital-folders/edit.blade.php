@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Modifier le type de dossier: {{ $folderType->name }}</h1>
        <a href="{{ route('settings.folder-types.show', $folderType) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('settings.folder-types.update', $folderType) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror"
                                           id="code" name="code" value="{{ old('code', $folderType->code) }}" required
                                           maxlength="50">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $folderType->name) }}" required
                                           maxlength="255">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3">{{ old('description', $folderType->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="icon" class="form-label">Icône (classe Bootstrap Icon)</label>
                                    <input type="text" class="form-control @error('icon') is-invalid @enderror"
                                           id="icon" name="icon" value="{{ old('icon', $folderType->icon) }}"
                                           maxlength="100">
                                    @error('icon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="color" class="form-label">Couleur</label>
                                    <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror"
                                           id="color" name="color" value="{{ old('color', $folderType->color ?? '#000000') }}"
                                           style="height: 38px;">
                                    @error('color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5>Configuration avancée</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code_prefix" class="form-label">Préfixe de code</label>
                                    <input type="text" class="form-control @error('code_prefix') is-invalid @enderror"
                                           id="code_prefix" name="code_prefix" value="{{ old('code_prefix', $folderType->code_prefix) }}"
                                           maxlength="10">
                                    @error('code_prefix')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code_pattern" class="form-label">Motif de code</label>
                                    <input type="text" class="form-control @error('code_pattern') is-invalid @enderror"
                                           id="code_pattern" name="code_pattern" value="{{ old('code_pattern', $folderType->code_pattern) }}"
                                           maxlength="50">
                                    @error('code_pattern')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="default_access_level" class="form-label">Niveau d'accès par défaut</label>
                                    <select class="form-select @error('default_access_level') is-invalid @enderror"
                                            id="default_access_level" name="default_access_level">
                                        <option value="">-- Aucun (hérité) --</option>
                                        <option value="public" {{ old('default_access_level', $folderType->default_access_level) === 'public' ? 'selected' : '' }}>Public</option>
                                        <option value="internal" {{ old('default_access_level', $folderType->default_access_level) === 'internal' ? 'selected' : '' }}>Interne</option>
                                        <option value="confidential" {{ old('default_access_level', $folderType->default_access_level) === 'confidential' ? 'selected' : '' }}>Confidentiel</option>
                                        <option value="secret" {{ old('default_access_level', $folderType->default_access_level) === 'secret' ? 'selected' : '' }}>Secret</option>
                                    </select>
                                    @error('default_access_level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="metadata_template_id" class="form-label">Template de métadonnées</label>
                                    <select class="form-select @error('metadata_template_id') is-invalid @enderror"
                                            id="metadata_template_id" name="metadata_template_id">
                                        <option value="">-- Aucun --</option>
                                        @foreach($metadataTemplates as $template)
                                            <option value="{{ $template->id }}"
                                                {{ old('metadata_template_id', $folderType->metadata_template_id) == $template->id ? 'selected' : '' }}>
                                                {{ $template->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('metadata_template_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="requires_approval"
                                           name="requires_approval" value="1"
                                           {{ old('requires_approval', $folderType->requires_approval) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="requires_approval">
                                        Nécessite une approbation
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="is_active"
                                           name="is_active" value="1"
                                           {{ old('is_active', $folderType->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Actif
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('settings.folder-types.show', $folderType) }}" class="btn btn-secondary">Annuler</a>
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
                        <dd class="col-sm-6">{{ $folderType->created_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-6">Modifié le</dt>
                        <dd class="col-sm-6">{{ $folderType->updated_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-6">Type</dt>
                        <dd class="col-sm-6">
                            @if($folderType->is_system)
                                <span class="badge bg-info">Système</span>
                            @else
                                <span class="badge bg-secondary">Personnalisé</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
