@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Créer un nouveau type de dossier numérique</h1>
        <a href="{{ route('settings.folder-types.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('settings.folder-types.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror"
                                           id="code" name="code" value="{{ old('code') }}" required
                                           placeholder="ex: DOC_ADMIN" maxlength="50">
                                    <small class="form-text text-muted">Code unique pour identifier ce type</small>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required
                                           placeholder="ex: Dossier Administratif" maxlength="255">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3"
                                      placeholder="Décrivez ce type de dossier...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="icon" class="form-label">Icône (classe Bootstrap Icon)</label>
                                    <input type="text" class="form-control @error('icon') is-invalid @enderror"
                                           id="icon" name="icon" value="{{ old('icon') }}"
                                           placeholder="ex: bi bi-folder" maxlength="100">
                                    <small class="form-text text-muted">Utilisez les classes Bootstrap Icons</small>
                                    @error('icon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="color" class="form-label">Couleur</label>
                                    <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror"
                                           id="color" name="color" value="{{ old('color', '#000000') }}" style="height: 38px;">
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
                                           id="code_prefix" name="code_prefix" value="{{ old('code_prefix') }}"
                                           placeholder="ex: DOC" maxlength="10">
                                    <small class="form-text text-muted">Préfixe automatique pour les codes générés</small>
                                    @error('code_prefix')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code_pattern" class="form-label">Motif de code</label>
                                    <input type="text" class="form-control @error('code_pattern') is-invalid @enderror"
                                           id="code_pattern" name="code_pattern" value="{{ old('code_pattern') }}"
                                           placeholder="ex: {PREFIX}-{YEAR}-{SEQUENCE}" maxlength="50">
                                    <small class="form-text text-muted">Motif de génération automatique du code</small>
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
                                        <option value="public" {{ old('default_access_level') === 'public' ? 'selected' : '' }}>Public</option>
                                        <option value="internal" {{ old('default_access_level') === 'internal' ? 'selected' : '' }}>Interne</option>
                                        <option value="confidential" {{ old('default_access_level') === 'confidential' ? 'selected' : '' }}>Confidentiel</option>
                                        <option value="secret" {{ old('default_access_level') === 'secret' ? 'selected' : '' }}>Secret</option>
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
                                            <option value="{{ $template->id }}" {{ old('metadata_template_id') == $template->id ? 'selected' : '' }}>
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
                                           name="requires_approval" value="1" {{ old('requires_approval') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="requires_approval">
                                        Nécessite une approbation
                                    </label>
                                    <small class="form-text text-muted d-block">Les dossiers de ce type devront être approuvés</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="is_active"
                                           name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Actif
                                    </label>
                                    <small class="form-text text-muted d-block">Rendre ce type disponible</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('settings.folder-types.index') }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Créer le type
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Aide</h5>
                </div>
                <div class="card-body">
                    <h6>Points clés</h6>
                    <ul class="small">
                        <li>Le <strong>code</strong> doit être unique</li>
                        <li>Le <strong>nom</strong> est affiché aux utilisateurs</li>
                        <li>Vous pouvez ajouter des <strong>métadonnées personnalisées</strong> après la création</li>
                        <li>Les types <strong>système</strong> ne peuvent pas être supprimés</li>
                    </ul>

                    <h6 class="mt-3">Prochaines étapes</h6>
                    <p class="small">Après création, vous pourrez :</p>
                    <ul class="small">
                        <li>Ajouter des définitions de métadonnées</li>
                        <li>Configurer le motif de génération de codes</li>
                        <li>Définir les permissions d'accès</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
