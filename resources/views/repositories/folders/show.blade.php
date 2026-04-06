@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 py-2 gd-folder-view">

    {{-- ===== TOP BAR ===== --}}
    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">

        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="me-auto">
            <ol class="breadcrumb mb-0" style="font-size:.82rem;">
                <li class="breadcrumb-item">
                    <a href="{{ route('folders.index') }}" class="text-decoration-none"><i class="bi bi-folder me-1"></i>Dossiers</a>
                </li>
                @foreach($breadcrumb as $item)
                    @if($loop->last)
                        <li class="breadcrumb-item active fw-semibold">{{ $item->name }}</li>
                    @else
                        <li class="breadcrumb-item"><a href="{{ route('folders.show', $item) }}" class="text-decoration-none">{{ $item->name }}</a></li>
                    @endif
                @endforeach
            </ol>
        </nav>

        {{-- Actions --}}
        <div class="d-flex gap-2 align-items-center flex-shrink-0">
            <a href="{{ route('documents.create', ['folder_id' => $folder->id]) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-file-earmark-plus me-1"></i>Nouveau document
            </a>
            <a href="{{ route('folders.create', ['parent_id' => $folder->id]) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-folder-plus me-1"></i>Sous-dossier
            </a>
            <a href="{{ route('folders.edit', $folder) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-pencil"></i>
            </a>

            {{-- View toggle --}}
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-secondary active" id="btnGrid" title="Grille"><i class="bi bi-grid"></i></button>
                <button class="btn btn-outline-secondary" id="btnList" title="Liste"><i class="bi bi-list-ul"></i></button>
            </div>

            {{-- Info panel toggle --}}
            <button class="btn btn-outline-secondary btn-sm" id="btnInfo" title="Informations">
                <i class="bi bi-info-circle"></i>
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 mb-2" style="font-size:.83rem;" role="alert">
        {{ session('success') }}<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- ===== FOLDER HEADER CHIP ===== --}}
    <div class="gd-folder-chip d-flex align-items-center gap-3 px-3 py-2 mb-3 rounded-3">
        <div class="gd-folder-icon"><i class="bi bi-folder-fill"></i></div>
        <div class="flex-grow-1 min-w-0">
            <div class="fw-semibold text-truncate">{{ $folder->name }}</div>
            <div class="d-flex gap-3 flex-wrap" style="font-size:.72rem;color:#5f6368;">
                <span><code style="font-size:.7rem;background:transparent;padding:0;">{{ $folder->code }}</code></span>
                <span>{{ $folder->type->name ?? '' }}</span>
                <span><i class="bi bi-building me-1"></i>{{ $folder->organisation->name ?? '' }}</span>
                @if($folder->status === 'active')
                    <span class="text-success"><i class="bi bi-circle-fill me-1" style="font-size:.4rem;vertical-align:middle;"></i>Actif</span>
                @elseif($folder->status === 'archived')
                    <span class="text-warning"><i class="bi bi-archive me-1"></i>Archivé</span>
                @else
                    <span class="text-secondary"><i class="bi bi-slash-circle me-1"></i>Fermé</span>
                @endif
                <span><i class="bi bi-shield me-1"></i>{{ ucfirst($folder->access_level) }}</span>
            </div>
        </div>
        <div class="d-flex gap-3 text-center flex-shrink-0 d-none d-md-flex" style="font-size:.72rem;color:#5f6368;">
            <div><div class="fw-bold text-dark" style="font-size:1rem;">{{ $folder->documents_count }}</div>Documents</div>
            <div><div class="fw-bold text-dark" style="font-size:1rem;">{{ $folder->children_count }}</div>Sous-dossiers</div>
            <div><div class="fw-bold text-dark" style="font-size:1rem;">{{ $folder->total_size_human }}</div>Taille</div>
        </div>
    </div>

    {{-- ===== MAIN LAYOUT ===== --}}
    <div class="d-flex gap-3">

        {{-- ===== CONTENT AREA ===== --}}
        <div class="gd-content flex-grow-1 min-w-0">

            {{-- --- Sous-dossiers --- --}}
            @if($folder->children->count() > 0)
            <div class="mb-3">
                <div class="gd-section-label">Dossiers ({{ $folder->children_count }})</div>

                {{-- GRID --}}
                <div id="subGridView" class="row g-2">
                    @foreach($folder->children as $child)
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <a href="{{ route('folders.show', $child) }}" class="text-decoration-none">
                            <div class="gd-item-card gd-folder-card d-flex flex-column align-items-center p-2 rounded-3 text-center">
                                <i class="bi bi-folder-fill gd-folder-icon-sm mb-1"></i>
                                <div class="gd-item-name text-truncate w-100 fw-semibold" title="{{ $child->name }}" style="font-size:.78rem;color:#202124;">{{ $child->name }}</div>
                                <div class="text-muted" style="font-size:.65rem;">{{ $child->documents_count }} doc · {{ $child->children_count }} dossier</div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>

                {{-- LIST --}}
                <div id="subListView" style="display:none;" class="card border-0 shadow-sm overflow-hidden">
                    <table class="table table-hover mb-0 gd-table" style="font-size:.82rem;">
                        <thead style="background:#f8f9fa;font-size:.7rem;color:#5f6368;text-transform:uppercase;letter-spacing:.04em;">
                            <tr>
                                <th class="px-3 py-2 border-0">Nom</th>
                                <th class="py-2 border-0">Type</th>
                                <th class="py-2 border-0 text-center">Docs</th>
                                <th class="py-2 border-0 text-center">Sous-dossiers</th>
                                <th class="py-2 border-0"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($folder->children as $child)
                            <tr onclick="window.location='{{ route('folders.show', $child) }}'" style="cursor:pointer;">
                                <td class="px-3 py-2">
                                    <i class="bi bi-folder-fill text-warning me-2"></i>
                                    <span class="fw-semibold">{{ $child->name }}</span>
                                </td>
                                <td class="py-2 text-muted">{{ $child->type->name ?? '—' }}</td>
                                <td class="py-2 text-center"><span class="badge bg-primary rounded-pill">{{ $child->documents_count }}</span></td>
                                <td class="py-2 text-center"><span class="badge bg-info rounded-pill">{{ $child->children_count }}</span></td>
                                <td class="py-2">
                                    <a href="{{ route('folders.show', $child) }}" class="btn btn-sm btn-outline-secondary" onclick="event.stopPropagation()">
                                        <i class="bi bi-arrow-right-circle"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- --- Documents --- --}}
            <div class="mb-3">
                @if($folder->documents->count() > 0)
                <div class="gd-section-label">Documents ({{ $folder->documents_count }})</div>

                {{-- GRID --}}
                <div id="docGridView" class="row g-2">
                    @foreach($folder->documents as $doc)
                    @php
                        $ext = strtolower(pathinfo($doc->file_path ?? '', PATHINFO_EXTENSION));
                        $iconClass = match(true) {
                            in_array($ext, ['pdf'])                     => 'bi-file-earmark-pdf text-danger',
                            in_array($ext, ['doc','docx'])              => 'bi-file-earmark-word text-primary',
                            in_array($ext, ['xls','xlsx'])              => 'bi-file-earmark-excel text-success',
                            in_array($ext, ['ppt','pptx'])              => 'bi-file-earmark-ppt',
                            in_array($ext, ['jpg','jpeg','png','gif','webp']) => 'bi-file-earmark-image text-info',
                            in_array($ext, ['zip','rar','7z'])          => 'bi-file-earmark-zip text-warning',
                            in_array($ext, ['txt','md'])                => 'bi-file-earmark-text text-secondary',
                            default                                     => 'bi-file-earmark text-muted',
                        };
                    @endphp
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <a href="{{ route('documents.show', $doc) }}" class="text-decoration-none">
                            <div class="gd-item-card gd-doc-card d-flex flex-column align-items-center p-2 rounded-3 text-center position-relative">
                                @if($doc->signature_status === 'signed')
                                <span class="position-absolute top-0 end-0 m-1"><i class="bi bi-patch-check-fill text-success" style="font-size:.75rem;"></i></span>
                                @endif
                                <i class="bi {{ $iconClass }} mb-1" style="font-size:2rem;"></i>
                                <div class="gd-item-name text-truncate w-100 fw-semibold" title="{{ $doc->name }}" style="font-size:.78rem;color:#202124;">{{ $doc->name }}</div>
                                <div class="text-muted d-flex align-items-center gap-1" style="font-size:.63rem;">
                                    <span class="badge bg-light text-muted border px-1" style="font-size:.58rem;">v{{ $doc->version_number }}</span>
                                    <span>{{ $doc->type->name ?? '' }}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>

                {{-- LIST --}}
                <div id="docListView" style="display:none;" class="card border-0 shadow-sm overflow-hidden">
                    <table class="table table-hover mb-0 gd-table" style="font-size:.82rem;">
                        <thead style="background:#f8f9fa;font-size:.7rem;color:#5f6368;text-transform:uppercase;letter-spacing:.04em;">
                            <tr>
                                <th class="px-3 py-2 border-0">Nom</th>
                                <th class="py-2 border-0">Type</th>
                                <th class="py-2 border-0">Version</th>
                                <th class="py-2 border-0">Taille</th>
                                <th class="py-2 border-0">Modifié le</th>
                                <th class="py-2 border-0"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($folder->documents as $doc)
                            @php
                                $ext2 = strtolower(pathinfo($doc->file_path ?? '', PATHINFO_EXTENSION));
                                $iconClass2 = match(true) {
                                    in_array($ext2, ['pdf'])                     => 'bi-file-earmark-pdf text-danger',
                                    in_array($ext2, ['doc','docx'])              => 'bi-file-earmark-word text-primary',
                                    in_array($ext2, ['xls','xlsx'])              => 'bi-file-earmark-excel text-success',
                                    in_array($ext2, ['jpg','jpeg','png','gif','webp']) => 'bi-file-earmark-image text-info',
                                    default                                      => 'bi-file-earmark text-muted',
                                };
                            @endphp
                            <tr onclick="window.location='{{ route('documents.show', $doc) }}'" style="cursor:pointer;">
                                <td class="px-3 py-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi {{ $iconClass2 }}" style="font-size:1.1rem;flex-shrink:0;"></i>
                                        <span class="fw-semibold text-truncate" style="max-width:200px;" title="{{ $doc->name }}">{{ $doc->name }}</span>
                                        @if($doc->signature_status === 'signed')
                                        <i class="bi bi-patch-check-fill text-success" title="Signé"></i>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-2 text-muted">{{ $doc->type->name ?? '—' }}</td>
                                <td class="py-2"><span class="badge bg-light text-muted border">v{{ $doc->version_number }}</span></td>
                                <td class="py-2 text-muted">{{ $doc->file_size_human ?? '—' }}</td>
                                <td class="py-2 text-muted">{{ $doc->updated_at->format('d/m/Y') }}</td>
                                <td class="py-2" onclick="event.stopPropagation()">
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('documents.show', $doc) }}" class="btn btn-sm btn-outline-primary" title="Voir"><i class="bi bi-eye"></i></a>
                                        @if($doc->file_path)
                                        <a href="{{ asset('storage/' . $doc->file_path) }}" class="btn btn-sm btn-outline-secondary" title="Télécharger" download><i class="bi bi-download"></i></a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="gd-dropzone rounded-3 text-center py-5 text-muted" id="dropzone">
                    <i class="bi bi-file-earmark-plus d-block mb-2" style="font-size:2.5rem;opacity:.3;"></i>
                    <div class="mb-2" style="font-size:.85rem;">Ce dossier est vide</div>
                    <a href="{{ route('documents.create', ['folder_id' => $folder->id]) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-file-earmark-plus me-1"></i>Ajouter un document
                    </a>
                </div>
                @endif
            </div>

        </div>

        {{-- ===== INFO PANEL ===== --}}
        <div class="gd-info-panel" id="infoPanel">
            <div class="card border-0 shadow-sm" style="width:260px;min-width:260px;font-size:.8rem;">
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <span class="fw-semibold">Informations</span>
                    <button class="btn btn-sm btn-link p-0 text-muted" id="closeInfo"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="card-body p-0">

                    {{-- Folder thumb --}}
                    <div class="text-center py-3" style="background:#fafbfc;border-bottom:1px solid #f0f0f0;">
                        <i class="bi bi-folder-fill text-warning" style="font-size:3rem;"></i>
                        <div class="fw-semibold mt-1" style="font-size:.85rem;">{{ $folder->name }}</div>
                    </div>

                    <div class="px-3 py-2">
                        <div class="gd-info-row"><span class="gd-info-label">Code</span><span><code style="font-size:.72rem;">{{ $folder->code }}</code></span></div>
                        <div class="gd-info-row"><span class="gd-info-label">Type</span><span>{{ $folder->type->name ?? '—' }}</span></div>
                        <div class="gd-info-row"><span class="gd-info-label">Organisation</span><span>{{ $folder->organisation->name ?? '—' }}</span></div>
                        <div class="gd-info-row"><span class="gd-info-label">Créateur</span><span>{{ $folder->creator->name ?? '—' }}</span></div>
                        <div class="gd-info-row"><span class="gd-info-label">Responsable</span><span>{{ $folder->assignedUser->name ?? '—' }}</span></div>
                        <div class="gd-info-row"><span class="gd-info-label">Accès</span>
                            <span class="badge bg-info text-white" style="font-size:.65rem;">{{ ucfirst($folder->access_level) }}</span>
                        </div>
                        @if($folder->description)
                        <div class="gd-info-row flex-column align-items-start">
                            <span class="gd-info-label mb-1">Description</span>
                            <span class="text-muted">{{ $folder->description }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="border-top px-3 py-2">
                        <div class="fw-semibold mb-2" style="font-size:.75rem;color:#5f6368;text-transform:uppercase;letter-spacing:.04em;">Actions</div>
                        <a href="{{ route('documents.create', ['folder_id' => $folder->id]) }}" class="gd-action-btn d-flex align-items-center gap-2 py-2 rounded text-decoration-none text-dark">
                            <i class="bi bi-file-earmark-plus text-primary"></i>Ajouter un document
                        </a>
                        <a href="{{ route('folders.create', ['parent_id' => $folder->id]) }}" class="gd-action-btn d-flex align-items-center gap-2 py-2 rounded text-decoration-none text-dark">
                            <i class="bi bi-folder-plus text-warning"></i>Créer un sous-dossier
                        </a>
                        <a href="{{ route('folders.edit', $folder) }}" class="gd-action-btn d-flex align-items-center gap-2 py-2 rounded text-decoration-none text-dark">
                            <i class="bi bi-pencil text-secondary"></i>Modifier le dossier
                        </a>
                        @if($folder->documents_count === 0 && $folder->children_count === 0)
                        <form action="{{ route('folders.destroy', $folder) }}" method="POST" onsubmit="return confirm('Supprimer ce dossier ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="gd-action-btn d-flex align-items-center gap-2 py-2 rounded border-0 bg-transparent text-danger w-100">
                                <i class="bi bi-trash"></i>Supprimer
                            </button>
                        </form>
                        @endif
                    </div>

                    @if($folder->requires_approval && $folder->approved_at)
                    <div class="border-top px-3 py-2">
                        <div class="fw-semibold mb-1" style="font-size:.75rem;color:#5f6368;text-transform:uppercase;letter-spacing:.04em;">Approbation</div>
                        <div class="gd-info-row"><span class="gd-info-label">Approuvé par</span><span>{{ $folder->approver->name ?? '—' }}</span></div>
                        <div class="gd-info-row"><span class="gd-info-label">Date</span><span>{{ $folder->approved_at->format('d/m/Y') }}</span></div>
                    </div>
                    @endif

                </div>
            </div>
        </div>

    </div>{{-- /d-flex --}}
</div>
@endsection

@push('styles')
<style>
/* ========== GOOGLE DRIVE FOLDER VIEW ========== */
.gd-folder-view {
    --gd-border: #e0e0e0;
    --gd-hover: #f8f9fa;
    --gd-yellow: #fbbc04;
}

.gd-folder-chip {
    background: #fef9f0;
    border: 1px solid #f0e8d0;
}
.gd-folder-icon {
    font-size: 2.2rem;
    color: var(--gd-yellow);
    flex-shrink: 0;
}

.gd-section-label {
    font-size: .72rem;
    font-weight: 600;
    color: #5f6368;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: .5rem;
    padding: 0 .25rem;
}

/* Item cards */
.gd-item-card {
    border: 1px solid transparent;
    background: #fff;
    transition: background .12s, border-color .12s, box-shadow .12s;
    cursor: pointer;
    height: 100%;
}
.gd-item-card:hover {
    background: #f0f4ff;
    border-color: #c5d3f5;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
}
.gd-folder-card:hover .bi-folder-fill { color: #f0a500; }
.gd-folder-icon-sm {
    font-size: 2rem;
    color: var(--gd-yellow);
}
.gd-item-name {
    line-height: 1.3;
}

/* Table */
.gd-table th { font-weight: 600; white-space: nowrap; }
.gd-table tr:hover td { background: var(--gd-hover); }

/* Drop zone */
.gd-dropzone {
    border: 2px dashed #dadce0;
    background: #fafafa;
    transition: background .15s, border-color .15s;
}
.gd-dropzone.drag-over {
    background: #e8f0fe;
    border-color: #4285f4;
}

/* Info panel */
.gd-info-panel {
    flex-shrink: 0;
    transition: width .2s;
}
.gd-info-panel.hidden {
    display: none !important;
}
.gd-info-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .25rem 0;
    border-bottom: 1px solid #f5f5f5;
    gap: .5rem;
    font-size: .78rem;
}
.gd-info-row:last-child { border-bottom: none; }
.gd-info-label {
    color: #5f6368;
    flex-shrink: 0;
    min-width: 80px;
}
.gd-action-btn {
    font-size: .8rem;
    padding: .3rem .4rem;
    transition: background .12s;
}
.gd-action-btn:hover { background: #f0f4ff; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ---- View toggle ----
    const btnGrid = document.getElementById('btnGrid');
    const btnList = document.getElementById('btnList');
    const subGrid = document.getElementById('subGridView');
    const subList = document.getElementById('subListView');
    const docGrid = document.getElementById('docGridView');
    const docList = document.getElementById('docListView');
    const saved   = localStorage.getItem('folder_view') || 'grid';

    function setView(v) {
        if (v === 'list') {
            if(subGrid) subGrid.style.display = 'none';
            if(subList) subList.style.display = 'block';
            if(docGrid) docGrid.style.display = 'none';
            if(docList) docList.style.display = 'block';
            btnGrid.classList.remove('active');
            btnList.classList.add('active');
        } else {
            if(subGrid) subGrid.style.display = '';
            if(subList) subList.style.display = 'none';
            if(docGrid) docGrid.style.display = '';
            if(docList) docList.style.display = 'none';
            btnGrid.classList.add('active');
            btnList.classList.remove('active');
        }
        localStorage.setItem('folder_view', v);
    }

    setView(saved);
    btnGrid.addEventListener('click', () => setView('grid'));
    btnList.addEventListener('click', () => setView('list'));

    // ---- Info panel toggle ----
    const infoPanel = document.getElementById('infoPanel');
    const btnInfo   = document.getElementById('btnInfo');
    const closeInfo = document.getElementById('closeInfo');
    const infoSaved = localStorage.getItem('folder_info') !== 'hidden';

    function setInfo(show) {
        infoPanel.classList.toggle('hidden', !show);
        localStorage.setItem('folder_info', show ? 'visible' : 'hidden');
    }
    setInfo(infoSaved);
    btnInfo.addEventListener('click', () => setInfo(infoPanel.classList.contains('hidden')));
    if (closeInfo) closeInfo.addEventListener('click', () => setInfo(false));

    // ---- Drag & Drop on drop zone ----
    const dz = document.getElementById('dropzone');
    if (dz) {
        ['dragenter','dragover'].forEach(e => dz.addEventListener(e, ev => { ev.preventDefault(); dz.classList.add('drag-over'); }));
        ['dragleave','drop'].forEach(e => dz.addEventListener(e, ev => { ev.preventDefault(); dz.classList.remove('drag-over'); }));
    }
});
</script>
@endpush
