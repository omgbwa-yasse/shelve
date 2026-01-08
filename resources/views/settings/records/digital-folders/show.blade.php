@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">{{ $folderType->name }}</h1>
            <p class="text-muted">Type de dossier numérique</p>
        </div>
        <div class="btn-group" role="group">
            <a href="{{ route('settings.folder-types.edit', $folderType) }}" class="btn btn-primary"
               @if($folderType->is_system) disabled @endif>
                <i class="bi bi-pencil"></i> Modifier
            </a>
            <a href="{{ route('settings.folder-types.metadata-profiles.index', $folderType) }}" class="btn btn-info">
                <i class="bi bi-gear"></i> Métadonnées
            </a>
            <a href="{{ route('settings.folder-types.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Informations générales</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Code</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-secondary">{{ $folderType->code }}</span>
                            @if($folderType->is_system)
                                <span class="badge bg-info">Système</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Nom</dt>
                        <dd class="col-sm-8">
                            @if($folderType->icon)
                                <i class="{{ $folderType->icon }}"></i>
                            @endif
                            {{ $folderType->name }}
                        </dd>

                        <dt class="col-sm-4">Description</dt>
                        <dd class="col-sm-8">{{ $folderType->description ?? '—' }}</dd>

                        <dt class="col-sm-4">Statut</dt>
                        <dd class="col-sm-8">
                            @if($folderType->is_active)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Couleur</dt>
                        <dd class="col-sm-8">
                            @if($folderType->color)
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 30px; height: 30px; background-color: {{ $folderType->color }}; border: 1px solid #ddd; border-radius: 3px;"></div>
                                    <code>{{ $folderType->color }}</code>
                                </div>
                            @else
                                —
                            @endif
                        </dd>

                        <dt class="col-sm-4">Approbation requise</dt>
                        <dd class="col-sm-8">
                            @if($folderType->requires_approval)
                                <span class="badge bg-warning">Oui</span>
                            @else
                                <span class="badge bg-secondary">Non</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Créé le</dt>
                        <dd class="col-sm-8">{{ $folderType->created_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-4">Modifié le</dt>
                        <dd class="col-sm-8">{{ $folderType->updated_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Configuration de code</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Préfixe</dt>
                        <dd class="col-sm-8">{{ $folderType->code_prefix ?? '—' }}</dd>

                        <dt class="col-sm-4">Motif</dt>
                        <dd class="col-sm-8">
                            @if($folderType->code_pattern)
                                <code>{{ $folderType->code_pattern }}</code>
                            @else
                                —
                            @endif
                        </dd>

                        <dt class="col-sm-4">Ordre d'affichage</dt>
                        <dd class="col-sm-8">{{ $folderType->display_order }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Niveau d'accès par défaut</h5>
                </div>
                <div class="card-body">
                    @if($folderType->default_access_level)
                        <p class="mb-0">
                            <strong>{{ ucfirst($folderType->default_access_level) }}</strong>
                        </p>
                    @else
                        <p class="text-muted mb-0">Aucun niveau par défaut</p>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Template de métadonnées</h5>
                </div>
                <div class="card-body">
                    @if($folderType->metadataTemplate)
                        <p class="mb-0">
                            <strong>{{ $folderType->metadataTemplate->name }}</strong>
                        </p>
                    @else
                        <p class="text-muted mb-0">Aucun template associé</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Utilisation</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Dossiers créés</dt>
                        <dd class="col-sm-6">
                            <strong>{{ $foldersCount }}</strong>
                        </dd>

                        <dt class="col-sm-6">Métadonnées</dt>
                        <dd class="col-sm-6">
                            <strong>{{ $folderType->metadataProfiles->count() }}</strong>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    @if($folderType->metadataProfiles->count() > 0)
        <div class="card mt-3">
            <div class="card-header bg-light">
                <h5 class="mb-0">Métadonnées associées</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Champ</th>
                            <th>Type</th>
                            <th>Obligatoire</th>
                            <th>Visible</th>
                            <th>Lecture seule</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($folderType->metadataProfiles as $profile)
                            <tr>
                                <td>{{ $profile->metadataDefinition->name }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $profile->metadataDefinition->data_type }}</span>
                                </td>
                                <td>
                                    @if($profile->mandatory)
                                        <i class="bi bi-check-circle text-success"></i>
                                    @else
                                        <i class="bi bi-x-circle text-muted"></i>
                                    @endif
                                </td>
                                <td>
                                    @if($profile->visible)
                                        <i class="bi bi-eye text-success"></i>
                                    @else
                                        <i class="bi bi-eye-slash text-muted"></i>
                                    @endif
                                </td>
                                <td>
                                    @if($profile->readonly)
                                        <i class="bi bi-lock text-warning"></i>
                                    @else
                                        <i class="bi bi-unlock text-muted"></i>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-light">
                <a href="{{ route('settings.folder-types.metadata-profiles.index', $folderType) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-gear"></i> Gérer les métadonnées
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
