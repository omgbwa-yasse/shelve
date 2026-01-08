@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Breadcrumb + Messages + Action Buttons --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('documents.index') }}">{{ __('documents') ?? 'Documents' }}</a>
                </li>
                @if($document->folder)
                    <li class="breadcrumb-item">
                        <a href="{{ route('folders.show', $document->folder) }}">{{ $document->folder->name }}</a>
                    </li>
                @endif
                <li class="breadcrumb-item active">{{ $document->name }}</li>
            </ol>
        </nav>
        <div class="d-flex gap-2 flex-wrap">
            {{-- Action Buttons --}}
            <div class="btn-group">
                @if($document->is_current_version)
                    <a href="{{ route('documents.edit', $document) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i> {{ __('edit') ?? 'Modifier' }}
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#uploadVersionModal">
                        <i class="bi bi-upload"></i> {{ __('new_version') ?? 'Nouvelle version' }}
                    </button>
                @endif

                <a href="{{ route('documents.versions', $document) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-clock-history"></i> {{ __('versions') ?? 'Versions' }}
                </a>

                @if($document->is_current_version && $document->requires_approval && !$document->approved_at)
                    <form action="{{ route('documents.approve', $document) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-check-circle"></i> {{ __('approve') ?? 'Approuver' }}
                        </button>
                    </form>
                @endif

                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="bi bi-trash"></i> {{ __('delete') ?? 'Supprimer' }}
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Lecteur de documents à gauche -->
        <div class="col-md-8">
            <!-- Lecteur de fichier -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-text"></i> {{ $document->name }}
                            <span class="badge bg-info">v{{ $document->version_number }}</span>
                        </h5>
                        <div>
                            @if($document->is_current_version)
                                <span class="badge bg-success">{{ __('current_version') ?? 'Version actuelle' }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body" style="min-height: 500px; background-color: #f5f5f5;">
                    {{-- Lecteur de documents PDF/images --}}
                    @if($document->file_path)
                        @php
                            $ext = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
                        @endphp

                        @if(in_array($ext, ['pdf']))
                            <iframe
                                src="{{ asset('storage/' . $document->file_path) }}#toolbar=1&navpanes=0&scrollbar=1"
                                width="100%"
                                height="500px"
                                style="border: none; border-radius: 4px;">
                            </iframe>
                        @elseif(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                            <div class="d-flex justify-content-center align-items-center" style="height: 500px;">
                                <img src="{{ asset('storage/' . $document->file_path) }}"
                                     alt="{{ $document->name }}"
                                     style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="bi bi-file-earmark"></i>
                                {{ __('file_type_not_supported') ?? 'Type de fichier non supporté pour la lecture' }}
                            </div>
                            <a href="{{ asset('storage/' . $document->file_path) }}" class="btn btn-primary" download>
                                <i class="bi bi-download"></i> {{ __('download') ?? 'Télécharger' }}
                            </a>
                        @endif
                    @else
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            {{ __('no_file_attached') ?? 'Aucun fichier attaché' }}
                        </div>
                    @endif
                </div>
                @if($document->file_path)
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-file-earmark"></i> {{ basename($document->file_path) }}
                            </small>
                            <a href="{{ asset('storage/' . $document->file_path) }}" class="btn btn-sm btn-outline-primary" download>
                                <i class="bi bi-download"></i> {{ __('download') ?? 'Télécharger' }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Informations générales -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('general_information') ?? 'Informations générales' }}</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">{{ __('code') ?? 'Code' }}</dt>
                        <dd class="col-sm-8"><code>{{ $document->code }}</code></dd>

                        <dt class="col-sm-4">{{ __('type') ?? 'Type' }}</dt>
                        <dd class="col-sm-8">{{ $document->type->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-4">{{ __('folder') ?? 'Dossier' }}</dt>
                        <dd class="col-sm-8">
                            @if($document->folder)
                                <a href="{{ route('folders.show', $document->folder) }}">{{ $document->folder->name }}</a>
                            @else
                                --
                            @endif
                        </dd>

                        <dt class="col-sm-4">{{ __('description') ?? 'Description' }}</dt>
                        <dd class="col-sm-8">{{ $document->description ?? '--' }}</dd>

                        <dt class="col-sm-4">{{ __('created_at') ?? 'Créé le' }}</dt>
                        <dd class="col-sm-8">{{ $document->created_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-4">{{ __('creator') ?? 'Créateur' }}</dt>
                        <dd class="col-sm-8">{{ $document->creator->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-4">{{ __('document_date') ?? 'Date du document' }}</dt>
                        <dd class="col-sm-8">{{ $document->document_date?->format('d/m/Y') ?? '--' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Panneau latéral droit avec métadonnées -->
        <div class="col-md-4">
            <!-- Vignette du document -->
            @include('repositories.documents.partials.thumbnail')

            <!-- Statuts et badges -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Statuts</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Statut du document</small><br>
                        @if($document->status === 'active')
                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Actif</span>
                        @elseif($document->status === 'draft')
                            <span class="badge bg-secondary"><i class="bi bi-pencil"></i> Brouillon</span>
                        @elseif($document->status === 'archived')
                            <span class="badge bg-warning"><i class="bi bi-archive"></i> Archivé</span>
                        @else
                            <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Obsolète</span>
                        @endif
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Réservation</small><br>
                        @if($document->isCheckedOut())
                            <span class="badge bg-warning"><i class="bi bi-lock-fill"></i> Réservé par {{ $document->checkedOutUser->name ?? 'N/A' }}</span>
                        @else
                            <span class="badge bg-light text-dark"><i class="bi bi-unlock"></i> Disponible</span>
                        @endif
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Signature</small><br>
                        @if($document->signature_status === 'signed')
                            <span class="badge bg-success"><i class="bi bi-patch-check-fill"></i> Signé</span>
                        @elseif($document->signature_status === 'pending')
                            <span class="badge bg-warning"><i class="bi bi-hourglass"></i> En attente</span>
                        @elseif($document->signature_status === 'rejected')
                            <span class="badge bg-danger"><i class="bi bi-x-circle-fill"></i> Rejetée</span>
                        @else
                            <span class="badge bg-light text-dark"><i class="bi bi-circle"></i> Non signé</span>
                        @endif
                    </div>

                    @if($document->requires_approval)
                        <div>
                            <small class="text-muted">Approbation</small><br>
                            @if($document->approved_at)
                                <span class="badge bg-success"><i class="bi bi-check2-circle"></i> Approuvé</span>
                            @else
                                <span class="badge bg-warning"><i class="bi bi-clock"></i> En attente d'approbation</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Détails de réservation -->
            @if($document->isCheckedOut())
                <div class="card mb-3 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-lock-fill"></i> Réservation active</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Réservé par :</strong> {{ $document->checkedOutUser->name ?? 'N/A' }}</p>
                        <p class="mb-0"><strong>Depuis :</strong> {{ $document->checked_out_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            @endif

            <!-- Détails de signature -->
            @include('repositories.documents.partials.signature')

            <!-- Workflow et approbation -->
            @include('repositories.documents.partials.workflow')
            @include('repositories.documents.partials.checkout')

            @if($document->requires_approval && $document->approved_at)
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-check2-circle"></i> Approbation</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Approuvé par :</strong> {{ $document->approver->name ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Date :</strong> {{ $document->approved_at->format('d/m/Y H:i') }}</p>
                        @if($document->approval_notes)
                            <p class="mb-0"><strong>Notes :</strong></p>
                            <p class="mb-0 text-muted">{{ $document->approval_notes }}</p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Statistiques -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Statistiques</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-6">Consultations</dt>
                        <dd class="col-6 text-end"><strong>{{ $document->download_count ?? 0 }}</strong></dd>

                        @if($document->last_viewed_at)
                            <dt class="col-6">Dernière vue</dt>
                            <dd class="col-6 text-end">
                                <small>{{ $document->last_viewed_at->format('d/m/Y H:i') }}</small>
                            </dd>
                        @endif

                        <dt class="col-6">Accès</dt>
                        <dd class="col-6 text-end">
                            <small class="badge bg-info">{{ ucfirst($document->access_level) }}</small>
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Historique des versions -->
            @if($versions->count() > 1)
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Historique des versions</h5>
                        <small class="text-muted">Total: {{ $versions->count() }} version(s)</small>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($versions->take(5) as $ver)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>v{{ $ver->version_number }}</strong>
                                        @if($ver->is_current_version)
                                            <span class="badge bg-light text-dark ms-1">Actuelle</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">
                                            {{ $ver->created_at->format('d/m/Y H:i') }}<br>
                                            par {{ $ver->creator->name ?? 'N/A' }}
                                        </small>
                                    </div>
                                    @include('repositories.documents.partials.version-actions', [
                                        'version' => $ver,
                                        'currentDocument' => $document
                                    ])
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($versions->count() > 5)
                        <div class="card-footer">
                            <a href="{{ route('documents.versions', $document) }}" class="btn btn-sm btn-outline-primary w-100">
                                <i class="bi bi-clock-history"></i> Voir toutes les versions
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Actions rapides -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Actions</h5>
                </div>
                <div class="list-group list-group-flush">
                    @if($document->is_current_version)
                        <a href="{{ route('documents.edit', $document) }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-pencil"></i> Modifier le document
                        </a>
                        <button type="button" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#uploadVersionModal">
                            <i class="bi bi-upload"></i> Nouvelle version
                        </button>
                    @endif

                    @if($document->is_current_version && $document->requires_approval && !$document->approved_at)
                        <form action="{{ route('documents.approve', $document) }}" method="POST" class="w-100">
                            @csrf
                            <button type="submit" class="list-group-item list-group-item-action text-success w-100 text-start">
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
