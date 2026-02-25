@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">{{ $documentType->name }}</h1>
            <p class="text-muted">Type de document numérique</p>
        </div>
        <div class="btn-group" role="group">
            <a href="{{ route('settings.document-types.edit', $documentType) }}" class="btn btn-primary"
               @if($documentType->is_system) disabled @endif>
                <i class="bi bi-pencil"></i> Modifier
            </a>
            <a href="{{ route('settings.document-types.metadata-profiles.index', $documentType) }}" class="btn btn-info">
                <i class="bi bi-gear"></i> Métadonnées
            </a>
            <a href="{{ route('settings.document-types.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
        </div>
    </div>

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
                            <span class="badge bg-secondary">{{ $documentType->code }}</span>
                            @if($documentType->is_system)
                                <span class="badge bg-info">Système</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Nom</dt>
                        <dd class="col-sm-8">
                            @if($documentType->icon)
                                <i class="{{ $documentType->icon }}"></i>
                            @endif
                            {{ $documentType->name }}
                        </dd>

                        <dt class="col-sm-4">Description</dt>
                        <dd class="col-sm-8">{{ $documentType->description ?? '—' }}</dd>

                        <dt class="col-sm-4">Statut</dt>
                        <dd class="col-sm-8">
                            @if($documentType->is_active)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Taille max</dt>
                        <dd class="col-sm-8">
                            @if($documentType->max_file_size)
                                {{ number_format($documentType->max_file_size / 1048576, 2) }} MB
                            @else
                                —
                            @endif
                        </dd>

                        <dt class="col-sm-4">Scan antivirus</dt>
                        <dd class="col-sm-8">
                            @if($documentType->require_virus_scan)
                                <span class="badge bg-success">Oui</span>
                            @else
                                <span class="badge bg-secondary">Non</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Créé le</dt>
                        <dd class="col-sm-8">{{ $documentType->created_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Utilisation</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Documents créés</dt>
                        <dd class="col-sm-6">
                            <strong>{{ $documentsCount }}</strong>
                        </dd>

                        <dt class="col-sm-6">Métadonnées</dt>
                        <dd class="col-sm-6">
                            <strong>{{ $documentType->metadataProfiles->count() }}</strong>
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Configuration</h5>
                </div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Préfixe</dt>
                        <dd class="col-sm-6">{{ $documentType->code_prefix ?? '—' }}</dd>

                        <dt class="col-sm-6">Motif</dt>
                        <dd class="col-sm-6">{{ $documentType->code_pattern ? $documentType->code_pattern : '—' }}</dd>

                        <dt class="col-sm-6">Approbation</dt>
                        <dd class="col-sm-6">
                            {{ $documentType->requires_approval ? 'Oui' : 'Non' }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    @if($documentType->metadataProfiles->count() > 0)
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
                        @foreach($documentType->metadataProfiles as $profile)
                            <tr>
                                <td>{{ $profile->metadataDefinition->name }}</td>
                                <td><span class="badge bg-info">{{ $profile->metadataDefinition->data_type }}</span></td>
                                <td>{{ $profile->mandatory ? '✓' : '—' }}</td>
                                <td>{{ $profile->visible ? '✓' : '✗' }}</td>
                                <td>{{ $profile->readonly ? '🔒' : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-light">
                <a href="{{ route('settings.document-types.metadata-profiles.index', $documentType) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-gear"></i> Gérer
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
