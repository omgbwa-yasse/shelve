@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 py-2 gd-wp-docs">

    @include('workplaces.partials.site-header', ['activeTab' => 'documents'])

    {{-- Toolbar --}}
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <span class="text-muted small me-auto">
            <i class="bi bi-file-earmark-text me-1"></i>
            {{ $documents->count() }} document(s) partagé(s)
        </span>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary active" id="btnGrid" title="Grille"><i class="bi bi-grid"></i></button>
            <button class="btn btn-outline-secondary" id="btnList" title="Liste"><i class="bi bi-list-ul"></i></button>
        </div>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#shareDocumentModal">
            <i class="bi bi-file-earmark-plus me-1"></i>Partager un document
        </button>
    </div>

    @forelse($documents as $doc)
    @php $d = $doc->document; @endphp
    @empty
    @endforelse

    {{-- GRID --}}
    <div id="gridView">
        <div class="row g-2">
            @forelse($documents as $doc)
            @php
                $d   = $doc->document;
                $ext = strtolower(pathinfo($d->file_path ?? '', PATHINFO_EXTENSION));
                [$ic, $icColor] = match(true) {
                    $ext === 'pdf'                          => ['bi-file-earmark-pdf', '#e53935'],
                    in_array($ext, ['doc','docx'])          => ['bi-file-earmark-word', '#1a73e8'],
                    in_array($ext, ['xls','xlsx'])          => ['bi-file-earmark-excel', '#188038'],
                    in_array($ext, ['ppt','pptx'])          => ['bi-file-earmark-ppt', '#e8710a'],
                    in_array($ext, ['jpg','jpeg','png','gif','webp']) => ['bi-file-earmark-image', '#00897b'],
                    default                                 => ['bi-file-earmark-text', '#5f6368'],
                };
            @endphp
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <div class="gd-item-card rounded-3 p-2 text-center position-relative">
                    @if($doc->is_featured)
                    <span class="position-absolute top-0 start-0 m-1">
                        <i class="bi bi-star-fill text-warning" style="font-size:.7rem;"></i>
                    </span>
                    @endif
                    <div style="width:48px;height:48px;background:{{ $icColor }}18;border-radius:10px;display:flex;align-items:center;justify-content:center;margin:0 auto .4rem;">
                        <i class="bi {{ $ic }}" style="font-size:1.5rem;color:{{ $icColor }};"></i>
                    </div>
                    <div class="fw-semibold text-truncate" style="font-size:.78rem;color:#202124;" title="{{ $d->name ?? '' }}">
                        {{ $d->name ?? 'Sans titre' }}
                    </div>
                    <div class="text-muted" style="font-size:.63rem;">
                        <span class="badge bg-light text-dark border px-1" style="font-size:.58rem;">
                            {{ ucfirst($doc->access_level) }}
                        </span>
                        @if($doc->sharedBy)· {{ $doc->sharedBy->name ?? '' }}@endif
                    </div>
                    {{-- Hover actions --}}
                    <div class="gd-hover-actions">
                        <a href="{{ route('workplaces.content.viewDocument', [$workplace, $doc]) }}" class="gd-icon-btn" title="Voir">
                            <i class="bi bi-eye"></i>
                        </a>
                        <form method="POST" action="{{ route('workplaces.content.featureDocument', [$workplace, $doc]) }}" style="display:contents;">
                            @csrf
                            <button type="submit" class="gd-icon-btn border-0" title="{{ $doc->is_featured ? 'Retirer vedette' : 'Mettre en vedette' }}" style="background:rgba(255,255,255,.95);cursor:pointer;">
                                <i class="bi bi-star{{ $doc->is_featured ? '-fill text-warning' : '' }}"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('workplaces.content.unshareDocument', [$workplace, $doc]) }}" style="display:contents;"
                              onsubmit="return confirm('Retirer ce document de l\'espace ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="gd-icon-btn border-0 text-danger" title="Retirer" style="background:rgba(255,255,255,.95);cursor:pointer;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="gd-empty text-center py-5">
                    <i class="bi bi-file-earmark-x d-block mb-3" style="font-size:3rem;opacity:.2;"></i>
                    <p class="text-muted mb-2">Aucun document partagé dans cet espace.</p>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#shareDocumentModal">
                        <i class="bi bi-file-earmark-plus me-1"></i>Partager un document
                    </button>
                </div>
            </div>
            @endforelse
        </div>
    </div>

    {{-- LIST --}}
    <div id="listView" style="display:none;">
        <div class="card border-0 shadow-sm overflow-hidden">
            <table class="table table-hover mb-0" style="font-size:.82rem;">
                <thead style="background:#f8f9fa;font-size:.7rem;color:#5f6368;text-transform:uppercase;letter-spacing:.04em;">
                    <tr>
                        <th class="px-3 py-2 border-0">Nom</th>
                        <th class="py-2 border-0">Accès</th>
                        <th class="py-2 border-0">Partagé par</th>
                        <th class="py-2 border-0">Date</th>
                        <th class="py-2 border-0 text-center">Vues</th>
                        <th class="py-2 border-0"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                    @php
                        $d2  = $doc->document;
                        $ext2 = strtolower(pathinfo($d2->file_path ?? '', PATHINFO_EXTENSION));
                        $ic2 = match(true) {
                            $ext2 === 'pdf'                          => 'bi-file-earmark-pdf text-danger',
                            in_array($ext2, ['doc','docx'])          => 'bi-file-earmark-word text-primary',
                            in_array($ext2, ['xls','xlsx'])          => 'bi-file-earmark-excel text-success',
                            in_array($ext2, ['jpg','jpeg','png','gif','webp']) => 'bi-file-earmark-image text-info',
                            default                                  => 'bi-file-earmark-text text-muted',
                        };
                    @endphp
                    <tr>
                        <td class="px-3 py-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi {{ $ic2 }}" style="font-size:1.1rem;flex-shrink:0;"></i>
                                <a href="{{ route('workplaces.content.viewDocument', [$workplace, $doc]) }}" class="fw-semibold text-dark text-decoration-none text-truncate" style="max-width:200px;">
                                    {{ $d2->name ?? 'Sans titre' }}
                                </a>
                                @if($doc->is_featured)
                                <i class="bi bi-star-fill text-warning" style="font-size:.7rem;" title="En vedette"></i>
                                @endif
                            </div>
                        </td>
                        <td class="py-2">
                            <span class="badge bg-light text-dark border" style="font-size:.65rem;">{{ ucfirst($doc->access_level) }}</span>
                        </td>
                        <td class="py-2 text-muted">{{ $doc->sharedBy->name ?? '—' }}</td>
                        <td class="py-2 text-muted">{{ $doc->shared_at->format('d/m/Y') }}</td>
                        <td class="py-2 text-center text-muted">{{ $doc->views_count ?? 0 }}</td>
                        <td class="py-2">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('workplaces.content.viewDocument', [$workplace, $doc]) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <form method="POST" action="{{ route('workplaces.content.featureDocument', [$workplace, $doc]) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="{{ $doc->is_featured ? 'Retirer vedette' : 'Vedette' }}">
                                        <i class="bi bi-star{{ $doc->is_featured ? '-fill' : '' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('workplaces.content.unshareDocument', [$workplace, $doc]) }}"
                                      onsubmit="return confirm('Retirer ce document ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Retirer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun document partagé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ===== MODAL: Partager un document ===== --}}
<div class="modal fade" id="shareDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="bi bi-file-earmark-plus me-2 text-primary"></i>Partager un document</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('workplaces.content.shareDocument', $workplace) }}" id="shareDocumentForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Document <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="text" class="form-control form-control-sm" id="document_search"
                                   placeholder="Tapez pour rechercher..." autocomplete="off">
                            <input type="hidden" id="document_id" name="document_id" required>
                            <div class="spinner-border spinner-border-sm position-absolute text-muted d-none"
                                 id="docSearchSpinner" style="right:10px;top:8px;" role="status"></div>
                        </div>
                        <div id="docSearchResults" class="list-group mt-1 shadow-sm"
                             style="position:absolute;z-index:1055;width:calc(100% - 2rem);max-height:220px;overflow-y:auto;display:none;"></div>
                        <div id="docSelected" class="alert alert-success mt-2 d-none py-2 px-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-file-earmark-text me-1"></i>
                                    <strong id="docSelectedName"></strong>
                                    <small class="text-muted ms-2" id="docSelectedCode"></small>
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" id="docClearBtn"><i class="bi bi-x"></i></button>
                            </div>
                            <small class="text-muted" id="docSelectedFolder"></small>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Niveau d'accès</label>
                            <select class="form-select form-select-sm" name="access_level" required>
                                <option value="view">Lecture seule</option>
                                <option value="edit" selected>Lecture et modification</option>
                                <option value="full">Accès complet</option>
                            </select>
                        </div>
                        <div class="col-6 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1">
                                <label class="form-check-label small" for="is_featured">Mettre en vedette</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label class="form-label small fw-semibold">Note (optionnel)</label>
                        <textarea class="form-control form-control-sm" name="share_note" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="shareDocSubmit" disabled>
                        <i class="bi bi-share me-1"></i>Partager
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.gd-item-card {
    border: 1px solid transparent;
    background: #fff;
    cursor: default;
    transition: background .12s, border-color .12s, box-shadow .12s;
}
.gd-item-card:hover {
    background: #f0f4ff;
    border-color: #c5d3f5;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
}
.gd-hover-actions {
    position: absolute;
    top: 5px; right: 5px;
    display: none;
    gap: 3px;
    flex-direction: column;
}
.gd-item-card:hover .gd-hover-actions { display: flex; }
.gd-icon-btn {
    width: 24px; height: 24px;
    display: flex; align-items: center; justify-content: center;
    background: rgba(255,255,255,.95);
    border: 1px solid #e0e0e0;
    border-radius: 50%;
    color: #5f6368;
    font-size: .72rem;
    text-decoration: none;
    box-shadow: 0 1px 3px rgba(0,0,0,.1);
    transition: background .1s, color .1s;
    padding: 0;
}
.gd-icon-btn:hover { background: #e8f0fe; color: #1a73e8; border-color: #1a73e8; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // View toggle
    const btnGrid = document.getElementById('btnGrid');
    const btnList = document.getElementById('btnList');
    const gridV   = document.getElementById('gridView');
    const listV   = document.getElementById('listView');
    const saved   = localStorage.getItem('wp_docs_view') || 'grid';

    function setView(v) {
        gridV.style.display = v === 'grid' ? '' : 'none';
        listV.style.display = v === 'list' ? 'block' : 'none';
        btnGrid.classList.toggle('active', v === 'grid');
        btnList.classList.toggle('active', v === 'list');
        localStorage.setItem('wp_docs_view', v);
    }
    setView(saved);
    btnGrid.addEventListener('click', () => setView('grid'));
    btnList.addEventListener('click', () => setView('list'));

    // Document search
    const docSearch   = document.getElementById('document_search');
    const docIdInput  = document.getElementById('document_id');
    const docResults  = document.getElementById('docSearchResults');
    const docSpinner  = document.getElementById('docSearchSpinner');
    const docSelected = document.getElementById('docSelected');
    const docClearBtn = document.getElementById('docClearBtn');
    const submitBtn   = document.getElementById('shareDocSubmit');
    let timeout = null;

    if (docSearch) {
        docSearch.addEventListener('input', function () {
            clearTimeout(timeout);
            const q = this.value.trim();
            if (q.length < 2) { docResults.style.display = 'none'; return; }
            docSpinner.classList.remove('d-none');
            timeout = setTimeout(() => {
                fetch('{{ route("workplaces.content.searchDocuments", $workplace) }}?q=' + encodeURIComponent(q), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    docSpinner.classList.add('d-none');
                    docResults.innerHTML = '';
                    if (!data.length) {
                        docResults.innerHTML = '<div class="list-group-item text-muted small"><i class="bi bi-info-circle me-1"></i>Aucun résultat</div>';
                        docResults.style.display = 'block'; return;
                    }
                    data.forEach(doc => {
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action py-2';
                        item.innerHTML = `<div class="d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-text text-primary"></i>
                            <div><div class="fw-semibold small">${doc.name}</div>
                            <div class="text-muted" style="font-size:.7rem;">${doc.code}${doc.folder_name ? ' · ' + doc.folder_name : ''}</div></div></div>`;
                        item.addEventListener('click', e => {
                            e.preventDefault();
                            docIdInput.value = doc.id;
                            document.getElementById('docSelectedName').textContent = doc.name;
                            document.getElementById('docSelectedCode').textContent = doc.code;
                            document.getElementById('docSelectedFolder').textContent = doc.folder_name ? 'Dossier: ' + doc.folder_name : '';
                            docSelected.classList.remove('d-none');
                            docSearch.value = '';
                            docResults.style.display = 'none';
                            submitBtn.disabled = false;
                        });
                        docResults.appendChild(item);
                    });
                    docResults.style.display = 'block';
                })
                .catch(() => { docSpinner.classList.add('d-none'); });
            }, 300);
        });

        docClearBtn?.addEventListener('click', () => {
            docIdInput.value = '';
            docSelected.classList.add('d-none');
            docSearch.value = '';
            submitBtn.disabled = true;
        });

        document.addEventListener('click', e => {
            if (!docSearch.contains(e.target) && !docResults.contains(e.target)) {
                docResults.style.display = 'none';
            }
        });

        document.getElementById('shareDocumentModal')?.addEventListener('hidden.bs.modal', () => {
            docIdInput.value = '';
            docSearch.value = '';
            docSelected.classList.add('d-none');
            docResults.style.display = 'none';
            submitBtn.disabled = true;
        });
    }
});
</script>
@endpush
