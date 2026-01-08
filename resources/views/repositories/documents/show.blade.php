@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <h1 class="h3">
                <i class="bi bi-file-earmark-text"></i> {{ $document->name }}
                <span class="badge bg-info">v{{ $document->version_number }}</span>
            </h1>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Informations principales -->
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Informations générales</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Code</dt>
                        <dd class="col-sm-9"><code>{{ $document->code }}</code></dd>

                        <dt class="col-sm-3">Type</dt>
                        <dd class="col-sm-9">{{ $document->type->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Dossier</dt>
                        <dd class="col-sm-9">
                            @if($document->folder)
                                <a href="{{ route('folders.show', $document->folder) }}">{{ $document->folder->name }}</a>
                            @else
                                --
                            @endif
                        </dd>

                        <dt class="col-sm-3">Description</dt>
                        <dd class="col-sm-9">{{ $document->description ?? '--' }}</dd>

                        <dt class="col-sm-3">Version</dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-info">v{{ $document->version_number }}</span>
                            @if($document->is_current_version)
                                <span class="badge bg-success">Version actuelle</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Statut</dt>
                        <dd class="col-sm-9">
                            @if($document->status === 'active')
                                <span class="badge bg-success">Actif</span>
                            @elseif($document->status === 'draft')
                                <span class="badge bg-secondary">Brouillon</span>
                            @elseif($document->status === 'archived')
                                <span class="badge bg-warning">Archivé</span>
                            @else
                                <span class="badge bg-danger">Obsolète</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Signature</dt>
                        <dd class="col-sm-9">
                            @if($document->signature_status === 'signed')
                                <i class="bi bi-patch-check-fill text-success"></i> Signé
                                @if($document->signer)
                                    par {{ $document->signer->name }} le {{ $document->signed_at->format('d/m/Y H:i') }}
                                @endif
                            @elseif($document->signature_status === 'pending')
                                <i class="bi bi-hourglass text-warning"></i> En attente de signature
                            @elseif($document->signature_status === 'rejected')
                                <i class="bi bi-x-circle-fill text-danger"></i> Signature rejetée
                            @else
                                <i class="bi bi-circle text-muted"></i> Non signé
                            @endif
                        </dd>

                        @if($document->isCheckedOut())
                            <dt class="col-sm-3">Réservation</dt>
                            <dd class="col-sm-9">
                                <i class="bi bi-lock-fill text-warning"></i>
                                Réservé par {{ $document->checkedOutUser->name ?? 'N/A' }}
                                le {{ $document->checked_out_at->format('d/m/Y H:i') }}
                            </dd>
                        @endif

                        <dt class="col-sm-3">Créateur</dt>
                        <dd class="col-sm-9">{{ $document->creator->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Date du document</dt>
                        <dd class="col-sm-9">{{ $document->document_date?->format('d/m/Y') ?? '--' }}</dd>

                        <dt class="col-sm-3">Niveau d'accès</dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-info">{{ ucfirst($document->access_level) }}</span>
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Historique des versions -->
            @if($versions->count() > 1)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Historique des versions ({{ $versions->count() }})</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($versions as $ver)
                            <div class="list-group-item {{ $ver->is_current_version ? 'active' : '' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Version {{ $ver->version_number }}</strong>
                                        @if($ver->is_current_version)
                                            <span class="badge bg-light text-dark">Actuelle</span>
                                        @endif
                                        <br>
                                        <small>
                                            Créée par {{ $ver->creator->name ?? 'N/A' }}
                                            le {{ $ver->created_at->format('d/m/Y H:i') }}
                                        </small>
                                        @if($ver->version_notes)
                                            <br><small class="text-muted">{{ $ver->version_notes }}</small>
                                        @endif
                                    </div>
                                    <div>
                                        @include('repositories.documents.partials.version-actions', [
                                            'version' => $ver,
                                            'currentDocument' => $document
                                        ])
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Panneau latéral -->
        <div class="col-md-4">
            {{-- Vignette et fichier --}}
            @include('repositories.documents.partials.thumbnail')

            {{-- Workflow Partials - Phase 3 --}}
            @include('repositories.documents.partials.checkout')
            @include('repositories.documents.partials.signature')
            @include('repositories.documents.partials.workflow')

            @if($document->requires_approval && $document->approved_at)
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Approbation</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Approuvé par :</strong> {{ $document->approver->name ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Date :</strong> {{ $document->approved_at->format('d/m/Y H:i') }}</p>
                        @if($document->approval_notes)
                            <p class="mb-0"><strong>Notes :</strong> {{ $document->approval_notes }}</p>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Statistiques</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-7">Consultations</dt>
                        <dd class="col-sm-5 text-end">{{ $document->download_count }}</dd>

                        @if($document->last_viewed_at)
                            <dt class="col-sm-7">Dernière vue</dt>
                            <dd class="col-sm-5 text-end">
                                <small>{{ $document->last_viewed_at->format('d/m/Y') }}</small>
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="list-group list-group-flush">
                    @if($document->is_current_version)
                        <a href="{{ route('documents.edit', $document) }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-pencil"></i> Modifier
                        </a>
                        <button type="button" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#uploadVersionModal">
                            <i class="bi bi-upload"></i> Nouvelle version
                        </button>
                    @endif

                    <a href="{{ route('documents.versions', $document) }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-clock-history"></i> Toutes les versions
                    </a>

                    @if($document->is_current_version && $document->requires_approval && !$document->approved_at)
                        <form action="{{ route('documents.approve', $document) }}" method="POST">
                            @csrf
                            <button type="submit" class="list-group-item list-group-item-action text-success">
                                <i class="bi bi-check-circle"></i> Approuver
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('documents.index') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-arrow-left"></i> Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal upload nouvelle version -->
<div class="modal fade" id="uploadVersionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('documents.upload', $document) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle version</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Fichier</label>
                        <input type="file" class="form-control" id="file" name="file" required>
                    </div>
                    <div class="mb-3">
                        <label for="version_notes" class="form-label">Notes de version</label>
                        <textarea class="form-control" id="version_notes" name="version_notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Téléverser</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
