@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('workplaces.partials.site-header', ['activeTab' => 'documents'])

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0 text-muted"><i class="bi bi-file-earmark-text me-2"></i>Documents partagés</h5>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#shareDocumentModal">
            <i class="bi bi-file-earmark-plus me-1"></i>Partager un document
        </button>
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
                <h5 class="modal-title"><i class="bi bi-file-earmark-plus me-2"></i>Partager un document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('workplaces.content.shareDocument', $workplace) }}" id="shareDocumentForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="document_search" class="form-label">Rechercher un document <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="text" class="form-control" id="document_search" placeholder="Tapez au moins 2 caractères pour rechercher..." autocomplete="off">
                            <input type="hidden" id="document_id" name="document_id" required>
                            <div class="spinner-border spinner-border-sm position-absolute text-muted d-none" id="docSearchSpinner" style="right: 10px; top: 10px;" role="status">
                                <span class="visually-hidden">Recherche...</span>
                            </div>
                        </div>
                        <div id="docSearchResults" class="list-group mt-1 shadow-sm" style="position: absolute; z-index: 1055; width: calc(100% - 2rem); max-height: 250px; overflow-y: auto; display: none;"></div>
                        <div id="docSelected" class="alert alert-success mt-2 d-none p-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-file-earmark-text me-1"></i>
                                    <strong id="docSelectedName"></strong>
                                    <small class="text-muted ms-2" id="docSelectedCode"></small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="docClearBtn">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            <small class="text-muted" id="docSelectedFolder"></small>
                            <small class="text-muted d-block" id="docSelectedDesc"></small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="doc_access_level" class="form-label">Niveau d'accès</label>
                        <select class="form-select" id="doc_access_level" name="access_level" required>
                            <option value="view">Lecture seule</option>
                            <option value="edit" selected>Lecture et modification</option>
                            <option value="full">Accès complet</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="doc_share_note" class="form-label">Note (optionnel)</label>
                        <textarea class="form-control" id="doc_share_note" name="share_note" rows="2"></textarea>
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
                    <button type="submit" class="btn btn-primary" id="shareDocSubmit" disabled>Partager</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===================== DOCUMENT SEARCH =====================
    const docSearch = document.getElementById('document_search');
    const docIdInput = document.getElementById('document_id');
    const docResults = document.getElementById('docSearchResults');
    const docSpinner = document.getElementById('docSearchSpinner');
    const docSelected = document.getElementById('docSelected');
    const docSelectedName = document.getElementById('docSelectedName');
    const docSelectedCode = document.getElementById('docSelectedCode');
    const docSelectedFolder = document.getElementById('docSelectedFolder');
    const docSelectedDesc = document.getElementById('docSelectedDesc');
    const docClearBtn = document.getElementById('docClearBtn');
    const shareDocSubmit = document.getElementById('shareDocSubmit');
    let docSearchTimeout = null;

    docSearch.addEventListener('input', function() {
        clearTimeout(docSearchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            docResults.style.display = 'none';
            docResults.innerHTML = '';
            return;
        }

        docSpinner.classList.remove('d-none');

        docSearchTimeout = setTimeout(function() {
            fetch('{{ route("workplaces.content.searchDocuments", $workplace) }}?q=' + encodeURIComponent(query), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                docSpinner.classList.add('d-none');
                docResults.innerHTML = '';

                if (data.length === 0) {
                    docResults.innerHTML = '<div class="list-group-item text-muted"><i class="bi bi-info-circle me-1"></i>Aucun document trouvé</div>';
                    docResults.style.display = 'block';
                    return;
                }

                data.forEach(function(doc) {
                    const item = document.createElement('a');
                    item.href = '#';
                    item.className = 'list-group-item list-group-item-action';
                    item.innerHTML = '<div class="d-flex justify-content-between align-items-center">' +
                        '<div>' +
                            '<i class="bi bi-file-earmark-text me-1"></i>' +
                            '<strong>' + doc.name + '</strong>' +
                            '<small class="text-muted ms-2">' + doc.code + '</small>' +
                        '</div>' +
                    '</div>' +
                    (doc.folder_name ? '<small class="text-muted"><i class="bi bi-folder me-1"></i>' + doc.folder_name + '</small>' : '') +
                    (doc.description ? '<br><small class="text-muted">' + doc.description + '</small>' : '');

                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        selectDocument(doc);
                    });
                    docResults.appendChild(item);
                });
                docResults.style.display = 'block';
            })
            .catch(function() {
                docSpinner.classList.add('d-none');
                docResults.innerHTML = '<div class="list-group-item text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Erreur de recherche</div>';
                docResults.style.display = 'block';
            });
        }, 300);
    });

    function selectDocument(doc) {
        docIdInput.value = doc.id;
        docSelectedName.textContent = doc.name;
        docSelectedCode.textContent = doc.code;
        docSelectedFolder.textContent = doc.folder_name ? 'Dossier : ' + doc.folder_name : '';
        docSelectedDesc.textContent = doc.description || '';
        docSelected.classList.remove('d-none');
        docSearch.value = '';
        docResults.style.display = 'none';
        shareDocSubmit.disabled = false;
    }

    docClearBtn.addEventListener('click', function() {
        docIdInput.value = '';
        docSelected.classList.add('d-none');
        docSearch.value = '';
        shareDocSubmit.disabled = true;
    });

    // Close results when clicking outside
    document.addEventListener('click', function(e) {
        if (!docSearch.contains(e.target) && !docResults.contains(e.target)) {
            docResults.style.display = 'none';
        }
    });

    // Reset modal on close
    document.getElementById('shareDocumentModal').addEventListener('hidden.bs.modal', function() {
        docIdInput.value = '';
        docSearch.value = '';
        docSelected.classList.add('d-none');
        docResults.style.display = 'none';
        shareDocSubmit.disabled = true;
    });
});
</script>
@endpush
