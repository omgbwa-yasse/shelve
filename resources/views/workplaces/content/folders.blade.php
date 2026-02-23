@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('workplaces.partials.site-header', ['activeTab' => 'folders'])

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0 text-muted"><i class="bi bi-folder me-2"></i>Dossiers partagés</h5>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#shareFolderModal">
            <i class="bi bi-folder-plus me-1"></i>Partager un dossier
        </button>
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
                <h5 class="modal-title"><i class="bi bi-folder-plus me-2"></i>Partager un dossier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('workplaces.content.shareFolder', $workplace) }}" id="shareFolderForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="folder_search" class="form-label">Rechercher un dossier <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="text" class="form-control" id="folder_search" placeholder="Tapez au moins 2 caractères pour rechercher..." autocomplete="off">
                            <input type="hidden" id="folder_id" name="folder_id" required>
                            <div class="spinner-border spinner-border-sm position-absolute text-muted d-none" id="folderSearchSpinner" style="right: 10px; top: 10px;" role="status">
                                <span class="visually-hidden">Recherche...</span>
                            </div>
                        </div>
                        <div id="folderSearchResults" class="list-group mt-1 shadow-sm" style="position: absolute; z-index: 1055; width: calc(100% - 2rem); max-height: 250px; overflow-y: auto; display: none;"></div>
                        <div id="folderSelected" class="alert alert-success mt-2 d-none p-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-folder-fill text-warning me-1"></i>
                                    <strong id="folderSelectedName"></strong>
                                    <small class="text-muted ms-2" id="folderSelectedCode"></small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="folderClearBtn">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            <small class="text-muted" id="folderSelectedDesc"></small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="folder_access_level" class="form-label">Niveau d'accès</label>
                        <select class="form-select" id="folder_access_level" name="access_level" required>
                            <option value="view">Lecture seule</option>
                            <option value="edit" selected>Lecture et modification</option>
                            <option value="full">Accès complet</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="folder_share_note" class="form-label">Note (optionnel)</label>
                        <textarea class="form-control" id="folder_share_note" name="share_note" rows="2"></textarea>
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
                    <button type="submit" class="btn btn-primary" id="shareFolderSubmit" disabled>Partager</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===================== FOLDER SEARCH =====================
    const folderSearch = document.getElementById('folder_search');
    const folderIdInput = document.getElementById('folder_id');
    const folderResults = document.getElementById('folderSearchResults');
    const folderSpinner = document.getElementById('folderSearchSpinner');
    const folderSelected = document.getElementById('folderSelected');
    const folderSelectedName = document.getElementById('folderSelectedName');
    const folderSelectedCode = document.getElementById('folderSelectedCode');
    const folderSelectedDesc = document.getElementById('folderSelectedDesc');
    const folderClearBtn = document.getElementById('folderClearBtn');
    const shareFolderSubmit = document.getElementById('shareFolderSubmit');
    let folderSearchTimeout = null;

    folderSearch.addEventListener('input', function() {
        clearTimeout(folderSearchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            folderResults.style.display = 'none';
            folderResults.innerHTML = '';
            return;
        }

        folderSpinner.classList.remove('d-none');

        folderSearchTimeout = setTimeout(function() {
            fetch('{{ route("workplaces.content.searchFolders", $workplace) }}?q=' + encodeURIComponent(query), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                folderSpinner.classList.add('d-none');
                folderResults.innerHTML = '';

                if (data.length === 0) {
                    folderResults.innerHTML = '<div class="list-group-item text-muted"><i class="bi bi-info-circle me-1"></i>Aucun dossier trouvé</div>';
                    folderResults.style.display = 'block';
                    return;
                }

                data.forEach(function(folder) {
                    const item = document.createElement('a');
                    item.href = '#';
                    item.className = 'list-group-item list-group-item-action';
                    item.innerHTML = '<div class="d-flex justify-content-between align-items-center">' +
                        '<div>' +
                            '<i class="bi bi-folder-fill text-warning me-1"></i>' +
                            '<strong>' + folder.name + '</strong>' +
                            '<small class="text-muted ms-2">' + folder.code + '</small>' +
                        '</div>' +
                        '<span class="badge bg-secondary">' + folder.documents_count + ' doc(s)</span>' +
                    '</div>' +
                    (folder.description ? '<small class="text-muted">' + folder.description + '</small>' : '');

                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        selectFolder(folder);
                    });
                    folderResults.appendChild(item);
                });
                folderResults.style.display = 'block';
            })
            .catch(function() {
                folderSpinner.classList.add('d-none');
                folderResults.innerHTML = '<div class="list-group-item text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Erreur de recherche</div>';
                folderResults.style.display = 'block';
            });
        }, 300);
    });

    function selectFolder(folder) {
        folderIdInput.value = folder.id;
        folderSelectedName.textContent = folder.name;
        folderSelectedCode.textContent = folder.code;
        folderSelectedDesc.textContent = folder.description || '';
        folderSelected.classList.remove('d-none');
        folderSearch.value = '';
        folderResults.style.display = 'none';
        shareFolderSubmit.disabled = false;
    }

    folderClearBtn.addEventListener('click', function() {
        folderIdInput.value = '';
        folderSelected.classList.add('d-none');
        folderSearch.value = '';
        shareFolderSubmit.disabled = true;
    });

    // Close results when clicking outside
    document.addEventListener('click', function(e) {
        if (!folderSearch.contains(e.target) && !folderResults.contains(e.target)) {
            folderResults.style.display = 'none';
        }
    });

    // Reset modal on close
    document.getElementById('shareFolderModal').addEventListener('hidden.bs.modal', function() {
        folderIdInput.value = '';
        folderSearch.value = '';
        folderSelected.classList.add('d-none');
        folderResults.style.display = 'none';
        shareFolderSubmit.disabled = true;
    });
});
</script>
@endpush
