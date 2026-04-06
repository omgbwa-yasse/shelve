@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 py-2 gd-wp-folders">

    @include('workplaces.partials.site-header', ['activeTab' => 'folders'])

    {{-- Toolbar --}}
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <span class="text-muted small me-auto">
            <i class="bi bi-folder me-1"></i>
            {{ $folders->count() }} dossier(s) partagé(s)
        </span>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary active" id="btnGrid" title="Grille"><i class="bi bi-grid"></i></button>
            <button class="btn btn-outline-secondary" id="btnList" title="Liste"><i class="bi bi-list-ul"></i></button>
        </div>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#shareFolderModal">
            <i class="bi bi-folder-plus me-1"></i>Partager un dossier
        </button>
    </div>

    {{-- GRID --}}
    <div id="gridView">
        <div class="row g-2">
            @forelse($folders as $folder)
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <div class="gd-item-card rounded-3 p-2 text-center position-relative">
                    @if($folder->is_pinned)
                    <span class="position-absolute top-0 start-0 m-1">
                        <i class="bi bi-pin-fill text-info" style="font-size:.7rem;"></i>
                    </span>
                    @endif
                    <i class="bi bi-folder-fill d-block mb-1" style="font-size:2.2rem;color:#fbbc04;"></i>
                    <div class="fw-semibold text-truncate" style="font-size:.78rem;color:#202124;" title="{{ $folder->folder->name ?? '' }}">
                        {{ $folder->folder->name ?? 'Sans titre' }}
                    </div>
                    <div class="text-muted" style="font-size:.63rem;">
                        <span class="badge bg-light text-dark border px-1" style="font-size:.58rem;">
                            {{ ucfirst($folder->access_level) }}
                        </span>
                        @if($folder->sharedBy)· {{ $folder->sharedBy->name ?? '' }}@endif
                    </div>
                    {{-- Hover actions --}}
                    <div class="gd-hover-actions">
                        <a href="{{ route('workplaces.content.viewFolder', [$workplace, $folder]) }}" class="gd-icon-btn" title="Ouvrir">
                            <i class="bi bi-arrow-right-circle"></i>
                        </a>
                        <form method="POST" action="{{ route('workplaces.content.pinFolder', [$workplace, $folder]) }}" style="display:contents;">
                            @csrf
                            <button type="submit" class="gd-icon-btn border-0" title="{{ $folder->is_pinned ? 'Désépingler' : 'Épingler' }}"
                                    style="background:rgba(255,255,255,.95);cursor:pointer;">
                                <i class="bi bi-pin{{ $folder->is_pinned ? '-fill text-info' : '' }}"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('workplaces.content.unshareFolder', [$workplace, $folder]) }}" style="display:contents;"
                              onsubmit="return confirm('Retirer ce dossier de l\'espace ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="gd-icon-btn border-0 text-danger" title="Retirer"
                                    style="background:rgba(255,255,255,.95);cursor:pointer;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-folder-x d-block mb-3" style="font-size:3rem;opacity:.2;"></i>
                    <p class="text-muted mb-2">Aucun dossier partagé dans cet espace.</p>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#shareFolderModal">
                        <i class="bi bi-folder-plus me-1"></i>Partager un dossier
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
                        <th class="py-2 border-0"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($folders as $folder)
                    <tr>
                        <td class="px-3 py-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-folder-fill text-warning" style="font-size:1rem;flex-shrink:0;"></i>
                                <a href="{{ route('workplaces.content.viewFolder', [$workplace, $folder]) }}"
                                   class="fw-semibold text-dark text-decoration-none text-truncate" style="max-width:200px;">
                                    {{ $folder->folder->name ?? 'Sans titre' }}
                                </a>
                                @if($folder->is_pinned)
                                <i class="bi bi-pin-fill text-info" style="font-size:.7rem;" title="Épinglé"></i>
                                @endif
                            </div>
                        </td>
                        <td class="py-2">
                            <span class="badge bg-light text-dark border" style="font-size:.65rem;">{{ ucfirst($folder->access_level) }}</span>
                        </td>
                        <td class="py-2 text-muted">{{ $folder->sharedBy->name ?? '—' }}</td>
                        <td class="py-2 text-muted">{{ $folder->shared_at->format('d/m/Y') }}</td>
                        <td class="py-2">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('workplaces.content.viewFolder', [$workplace, $folder]) }}"
                                   class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="bi bi-arrow-right-circle"></i>
                                </a>
                                <form method="POST" action="{{ route('workplaces.content.pinFolder', [$workplace, $folder]) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $folder->is_pinned ? 'info' : 'secondary' }}"
                                            title="{{ $folder->is_pinned ? 'Désépingler' : 'Épingler' }}">
                                        <i class="bi bi-pin{{ $folder->is_pinned ? '-fill' : '' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('workplaces.content.unshareFolder', [$workplace, $folder]) }}"
                                      onsubmit="return confirm('Retirer ce dossier ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Retirer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Aucun dossier partagé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ===== MODAL: Partager un dossier ===== --}}
<div class="modal fade" id="shareFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="bi bi-folder-plus me-2 text-warning"></i>Partager un dossier</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('workplaces.content.shareFolder', $workplace) }}" id="shareFolderForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Dossier <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="text" class="form-control form-control-sm" id="folder_search"
                                   placeholder="Tapez pour rechercher..." autocomplete="off">
                            <input type="hidden" id="folder_id" name="folder_id" required>
                            <div class="spinner-border spinner-border-sm position-absolute text-muted d-none"
                                 id="folderSearchSpinner" style="right:10px;top:8px;" role="status"></div>
                        </div>
                        <div id="folderSearchResults" class="list-group mt-1 shadow-sm"
                             style="position:absolute;z-index:1055;width:calc(100% - 2rem);max-height:220px;overflow-y:auto;display:none;"></div>
                        <div id="folderSelected" class="alert alert-success mt-2 d-none py-2 px-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-folder-fill text-warning me-1"></i>
                                    <strong id="folderSelectedName"></strong>
                                    <small class="text-muted ms-2" id="folderSelectedCode"></small>
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" id="folderClearBtn"><i class="bi bi-x"></i></button>
                            </div>
                            <small class="text-muted" id="folderSelectedDesc"></small>
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
                                <input class="form-check-input" type="checkbox" id="is_pinned" name="is_pinned" value="1">
                                <label class="form-check-label small" for="is_pinned">Épingler ce dossier</label>
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
                    <button type="submit" class="btn btn-primary btn-sm" id="shareFolderSubmit" disabled>
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
    const saved   = localStorage.getItem('wp_folders_view') || 'grid';

    function setView(v) {
        gridV.style.display = v === 'grid' ? '' : 'none';
        listV.style.display = v === 'list' ? 'block' : 'none';
        btnGrid.classList.toggle('active', v === 'grid');
        btnList.classList.toggle('active', v === 'list');
        localStorage.setItem('wp_folders_view', v);
    }
    setView(saved);
    btnGrid.addEventListener('click', () => setView('grid'));
    btnList.addEventListener('click', () => setView('list'));

    // Folder search
    const folderSearch  = document.getElementById('folder_search');
    const folderIdInput = document.getElementById('folder_id');
    const folderResults = document.getElementById('folderSearchResults');
    const folderSpinner = document.getElementById('folderSearchSpinner');
    const folderSel     = document.getElementById('folderSelected');
    const folderClearBtn= document.getElementById('folderClearBtn');
    const submitBtn     = document.getElementById('shareFolderSubmit');
    let timeout = null;

    if (folderSearch) {
        folderSearch.addEventListener('input', function () {
            clearTimeout(timeout);
            const q = this.value.trim();
            if (q.length < 2) { folderResults.style.display = 'none'; return; }
            folderSpinner.classList.remove('d-none');
            timeout = setTimeout(() => {
                fetch('{{ route("workplaces.content.searchFolders", $workplace) }}?q=' + encodeURIComponent(q), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    folderSpinner.classList.add('d-none');
                    folderResults.innerHTML = '';
                    if (!data.length) {
                        folderResults.innerHTML = '<div class="list-group-item text-muted small"><i class="bi bi-info-circle me-1"></i>Aucun résultat</div>';
                        folderResults.style.display = 'block'; return;
                    }
                    data.forEach(f => {
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action py-2';
                        item.innerHTML = `<div class="d-flex align-items-center gap-2">
                            <i class="bi bi-folder-fill text-warning"></i>
                            <div><div class="fw-semibold small">${f.name}</div>
                            <div class="text-muted" style="font-size:.7rem;">${f.code}${f.description ? ' · ' + f.description : ''}</div></div>
                            <span class="badge bg-secondary ms-auto">${f.documents_count} doc</span></div>`;
                        item.addEventListener('click', e => {
                            e.preventDefault();
                            folderIdInput.value = f.id;
                            document.getElementById('folderSelectedName').textContent = f.name;
                            document.getElementById('folderSelectedCode').textContent = f.code;
                            document.getElementById('folderSelectedDesc').textContent = f.description || '';
                            folderSel.classList.remove('d-none');
                            folderSearch.value = '';
                            folderResults.style.display = 'none';
                            submitBtn.disabled = false;
                        });
                        folderResults.appendChild(item);
                    });
                    folderResults.style.display = 'block';
                })
                .catch(() => { folderSpinner.classList.add('d-none'); });
            }, 300);
        });

        folderClearBtn?.addEventListener('click', () => {
            folderIdInput.value = '';
            folderSel.classList.add('d-none');
            folderSearch.value = '';
            submitBtn.disabled = true;
        });

        document.addEventListener('click', e => {
            if (!folderSearch.contains(e.target) && !folderResults.contains(e.target)) {
                folderResults.style.display = 'none';
            }
        });

        document.getElementById('shareFolderModal')?.addEventListener('hidden.bs.modal', () => {
            folderIdInput.value = '';
            folderSearch.value = '';
            folderSel.classList.add('d-none');
            folderResults.style.display = 'none';
            submitBtn.disabled = true;
        });
    }
});
</script>
@endpush
