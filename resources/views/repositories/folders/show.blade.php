@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3">
                    <i class="bi bi-folder"></i> {{ $folder->name }}
                </h1>
                <div>
                    <a href="{{ route('folders.edit', $folder) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Modifier
                    </a>
                    <a href="{{ route('folders.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Breadcrumb -->
    @if($breadcrumb->count() > 0)
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('folders.index') }}">Dossiers</a></li>
                @foreach($breadcrumb as $item)
                    @if($loop->last)
                        <li class="breadcrumb-item active" aria-current="page">{{ $item->name }}</li>
                    @else
                        <li class="breadcrumb-item"><a href="{{ route('folders.show', $item) }}">{{ $item->name }}</a></li>
                    @endif
                @endforeach
            </ol>
        </nav>
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
                        <dd class="col-sm-9"><code>{{ $folder->code }}</code></dd>

                        <dt class="col-sm-3">Type</dt>
                        <dd class="col-sm-9">{{ $folder->type->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Description</dt>
                        <dd class="col-sm-9">{{ $folder->description ?? '--' }}</dd>

                        <dt class="col-sm-3">Organisation</dt>
                        <dd class="col-sm-9">{{ $folder->organisation->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Créateur</dt>
                        <dd class="col-sm-9">{{ $folder->creator->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Responsable</dt>
                        <dd class="col-sm-9">{{ $folder->assignedUser->name ?? '--' }}</dd>

                        <dt class="col-sm-3">Statut</dt>
                        <dd class="col-sm-9">
                            @if($folder->status === 'active')
                                <span class="badge bg-success">Actif</span>
                            @elseif($folder->status === 'archived')
                                <span class="badge bg-warning">Archivé</span>
                            @else
                                <span class="badge bg-secondary">Fermé</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Niveau d'accès</dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-info">{{ ucfirst($folder->access_level) }}</span>
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Sous-dossiers -->
            @if($folder->children->count() > 0)
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Sous-dossiers ({{ $folder->children_count }})</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($folder->children as $child)
                            <a href="{{ route('folders.show', $child) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-folder"></i> {{ $child->name }}
                                        <small class="text-muted">- {{ $child->type->name ?? '' }}</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-primary">{{ $child->documents_count }} docs</span>
                                        <span class="badge bg-info">{{ $child->children_count }} dossiers</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Documents -->
            @if($folder->documents->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Documents ({{ $folder->documents_count }})</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($folder->documents as $doc)
                            <a href="{{ route('documents.show', $doc) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-file-earmark-text"></i> {{ $doc->name }}
                                        <small class="text-muted">- v{{ $doc->version_number }}</small>
                                    </div>
                                    <div>
                                        @if($doc->signature_status === 'signed')
                                            <i class="bi bi-patch-check-fill text-success"></i>
                                        @endif
                                        <span class="badge bg-secondary">{{ $doc->type->name ?? '' }}</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Panneau latéral -->
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Statistiques</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-7">Documents</dt>
                        <dd class="col-sm-5 text-end"><span class="badge bg-primary">{{ $folder->documents_count }}</span></dd>

                        <dt class="col-sm-7">Sous-dossiers</dt>
                        <dd class="col-sm-5 text-end"><span class="badge bg-info">{{ $folder->children_count }}</span></dd>

                        <dt class="col-sm-7">Taille totale</dt>
                        <dd class="col-sm-5 text-end">{{ $folder->total_size_human }}</dd>
                    </dl>
                </div>
            </div>

            @if($folder->requires_approval && $folder->approved_at)
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Approbation</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Approuvé par :</strong> {{ $folder->approver->name ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Date :</strong> {{ $folder->approved_at->format('d/m/Y H:i') }}</p>
                        @if($folder->approval_notes)
                            <p class="mb-0"><strong>Notes :</strong> {{ $folder->approval_notes }}</p>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('documents.create', ['folder_id' => $folder->id]) }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-plus-circle"></i> Ajouter un document
                    </a>
                    <a href="{{ route('folders.create', ['parent_id' => $folder->id]) }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-folder-plus"></i> Créer un sous-dossier
                    </a>
                    <a href="{{ route('folders.edit', $folder) }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-pencil"></i> Modifier
                    </a>
                    @if($folder->documents_count === 0 && $folder->children_count === 0)
                        <form action="{{ route('folders.destroy', $folder) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="list-group-item list-group-item-action text-danger">
                                <i class="bi bi-trash"></i> Supprimer
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
