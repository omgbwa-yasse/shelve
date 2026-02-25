@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <h1 class="h3">
                <i class="bi bi-file-earmark-text"></i> Documents Numériques
            </h1>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filtres et recherche -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('documents.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type_id" class="form-select">
                        <option value="">-- Type --</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" {{ request('type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="folder_id" class="form-select">
                        <option value="">-- Dossier --</option>
                        @foreach($folders as $folder)
                            <option value="{{ $folder->id }}" {{ request('folder_id') == $folder->id ? 'selected' : '' }}>
                                {{ $folder->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">-- Statut --</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Brouillon</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archivé</option>
                        <option value="obsolete" {{ request('status') == 'obsolete' ? 'selected' : '' }}>Obsolète</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="signature_status" class="form-select">
                        <option value="">-- Signature --</option>
                        <option value="unsigned" {{ request('signature_status') == 'unsigned' ? 'selected' : '' }}>Non signé</option>
                        <option value="pending" {{ request('signature_status') == 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="signed" {{ request('signature_status') == 'signed' ? 'selected' : '' }}>Signé</option>
                        <option value="rejected" {{ request('signature_status') == 'rejected' ? 'selected' : '' }}>Rejeté</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i>
                        </button>
                        <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Actions -->
    <div class="mb-3">
        <a href="{{ route('documents.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Nouveau document
        </a>
    </div>

    <!-- Liste des documents -->
    <div class="card">
        <div class="card-body">
            @if($documents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Vignette</th>
                                <th>Code</th>
                                <th>Nom</th>
                                <th>Type</th>
                                <th>Dossier</th>
                                <th>Version</th>
                                <th>Statut</th>
                                <th>Signature</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $document)
                                <tr>
                                    <td>
                                        @if($document->attachment)
                                            <img src="{{ $document->attachment->getThumbnailUrl() }}"
                                                 alt="{{ $document->name }}"
                                                 class="img-thumbnail"
                                                 style="width: 60px; height: 75px; object-fit: cover;">
                                        @else
                                            <svg class="bi text-muted" style="width: 60px; height: 75px;" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zm-3 0A1.5 1.5 0 0 0 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h-2z"/>
                            </svg>
                        @endif
                    </td>
                                    <td>
                                        <code>{{ $document->code }}</code>
                                    </td>
                                    <td>
                                        <a href="{{ route('documents.show', $document) }}">
                                            <i class="bi bi-file-earmark-text"></i> {{ $document->name }}
                                        </a>
                                        @if($document->isCheckedOut())
                                            <i class="bi bi-lock-fill text-warning" title="Réservé"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $document->type->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        @if($document->folder)
                                            <a href="{{ route('folders.show', $document->folder) }}" class="text-muted">
                                                {{ $document->folder->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">v{{ $document->version_number }}</span>
                                    </td>
                                    <td>
                                        @if($document->status === 'active')
                                            <span class="badge bg-success">Actif</span>
                                        @elseif($document->status === 'draft')
                                            <span class="badge bg-secondary">Brouillon</span>
                                        @elseif($document->status === 'archived')
                                            <span class="badge bg-warning">Archivé</span>
                                        @else
                                            <span class="badge bg-danger">Obsolète</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($document->signature_status === 'signed')
                                            <i class="bi bi-patch-check-fill text-success" title="Signé"></i>
                                        @elseif($document->signature_status === 'pending')
                                            <i class="bi bi-hourglass text-warning" title="En attente"></i>
                                        @elseif($document->signature_status === 'rejected')
                                            <i class="bi bi-x-circle-fill text-danger" title="Rejeté"></i>
                                        @else
                                            <i class="bi bi-circle text-muted" title="Non signé"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $document->document_date?->format('d/m/Y') ?? '--' }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('documents.show', $document) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($document->is_current_version)
                                                <a href="{{ route('documents.edit', $document) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $documents->withQueryString()->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Aucun document trouvé.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
