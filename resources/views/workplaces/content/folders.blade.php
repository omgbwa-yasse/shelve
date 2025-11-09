@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Dossiers partagés - {{ $workplace->name }}</h2>
        </div>
        <div class="col-md-4 text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#shareFolderModal">
                <i class="bi bi-folder-plus"></i> Partager un dossier
            </button>
        </div>
    </div>

    <div class="row">
        @forelse($folders as $folder)
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h5 class="card-title">
                            <i class="bi bi-folder-fill text-warning"></i>
                            {{ $folder->folder->name ?? 'Sans titre' }}
                        </h5>
                        @if($folder->is_pinned)
                        <span class="badge bg-info">📌</span>
                        @endif
                    </div>

                    <p class="text-muted small">
                        Accès: <span class="badge bg-{{ $folder->access_level == 'full' ? 'success' : ($folder->access_level == 'edit' ? 'primary' : 'secondary') }}">
                            {{ ucfirst($folder->access_level) }}
                        </span>
                    </p>

                    @if($folder->share_note)
                    <p class="small">{{ $folder->share_note }}</p>
                    @endif

                    <div class="mt-3">
                        <small class="text-muted">
                            Partagé par <strong>{{ $folder->sharedBy->name }}</strong>
                            · {{ $folder->shared_at->diffForHumans() }}
                        </small>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="btn-group btn-group-sm w-100">
                        <a href="#" class="btn btn-outline-primary">
                            <i class="bi bi-eye"></i> Voir
                        </a>
                        <form method="POST" action="{{ route('workplaces.content.pinFolder', [$workplace, $folder]) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="bi bi-pin"></i> {{ $folder->is_pinned ? 'Désépingler' : 'Épingler' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('workplaces.content.unshareFolder', [$workplace, $folder]) }}" style="display: inline;">
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
                Aucun dossier partagé dans cet espace de travail.
            </div>
        </div>
        @endforelse
    </div>
</div>

<!-- Share Folder Modal -->
<div class="modal fade" id="shareFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Partager un dossier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('workplaces.content.shareFolder', $workplace) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="folder_id" class="form-label">Dossier</label>
                        <select class="form-select" id="folder_id" name="folder_id" required>
                            <option value="">Sélectionner un dossier</option>
                            <!-- TODO: Load available folders dynamically -->
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
                        <input class="form-check-input" type="checkbox" id="is_pinned" name="is_pinned" value="1">
                        <label class="form-check-label" for="is_pinned">
                            Épingler ce dossier
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
