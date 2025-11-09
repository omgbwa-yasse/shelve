@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Documents partagés - {{ $workplace->name }}</h2>
        </div>
        <div class="col-md-4 text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#shareDocumentModal">
                <i class="bi bi-file-earmark-plus"></i> Partager un document
            </button>
        </div>
    </div>

    <div class="row">
        @forelse($documents as $document)
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="card-title">
                            <i class="bi bi-file-earmark-text"></i>
                            {{ $document->document->name ?? 'Sans titre' }}
                        </h5>
                        @if($document->is_featured)
                        <span class="badge bg-warning text-dark">★</span>
                        @endif
                    </div>

                    <p class="text-muted small">
                        Accès: <span class="badge bg-{{ $document->access_level == 'full' ? 'success' : ($document->access_level == 'edit' ? 'primary' : 'secondary') }}">
                            {{ ucfirst($document->access_level) }}
                        </span>
                    </p>

                    @if($document->share_note)
                    <p class="small">{{ $document->share_note }}</p>
                    @endif

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted">
                            <i class="bi bi-eye"></i> {{ $document->views_count }} vues
                        </small>
                        @if($document->last_viewed_at)
                        <small class="text-muted">
                            Vu {{ $document->last_viewed_at->diffForHumans() }}
                        </small>
                        @endif
                    </div>

                    <div class="mt-2">
                        <small class="text-muted">
                            Partagé par <strong>{{ $document->sharedBy->name }}</strong>
                            · {{ $document->shared_at->diffForHumans() }}
                        </small>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="btn-group btn-group-sm w-100">
                        <a href="{{ route('workplaces.content.viewDocument', [$workplace, $document]) }}" class="btn btn-outline-primary">
                            <i class="bi bi-eye"></i> Voir
                        </a>
                        <form method="POST" action="{{ route('workplaces.content.featureDocument', [$workplace, $document]) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning">
                                <i class="bi bi-star"></i> {{ $document->is_featured ? 'Retirer' : 'Vedette' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('workplaces.content.unshareDocument', [$workplace, $document]) }}" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Confirmer ?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">
                Aucun document partagé dans cet espace de travail.
            </div>
        </div>
        @endforelse
    </div>
</div>

<!-- Share Document Modal -->
<div class="modal fade" id="shareDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Partager un document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('workplaces.content.shareDocument', $workplace) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="document_id" class="form-label">Document</label>
                        <select class="form-select" id="document_id" name="document_id" required>
                            <option value="">Sélectionner un document</option>
                            <!-- TODO: Load available documents dynamically -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="access_level" class="form-label">Niveau d'accès</label>
                        <select class="form-select" id="access_level" name="access_level" required>
                            <option value="view">Lecture seule</option>
                            <option value="edit" selected>Lecture et modification</option>
                            <option value="full">Accès complet</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="share_note" class="form-label">Note (optionnel)</label>
                        <textarea class="form-control" id="share_note" name="share_note" rows="2"></textarea>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1">
                        <label class="form-check-label" for="is_featured">
                            Mettre en vedette
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Partager</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
