@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3 gd-folders-index">

    {{-- ===== TOP BAR ===== --}}
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <div class="me-auto">
            <h5 class="mb-0 fw-semibold"><i class="bi bi-folder text-warning me-2"></i>Dossiers Numériques</h5>
        </div>
        <a href="{{ route('folders.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-folder-plus me-1"></i>Nouveau dossier
        </a>
        <a href="{{ route('folders.tree.view') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-diagram-3 me-1"></i>Arborescence
        </a>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary active" id="btnGrid" title="Grille"><i class="bi bi-grid"></i></button>
            <button class="btn btn-outline-secondary" id="btnList" title="Liste"><i class="bi bi-list-ul"></i></button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 mb-3" style="font-size:.83rem;">
        {{ session('success') }}<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" style="font-size:.83rem;">
        {{ session('error') }}<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- ===== FILTER BAR ===== --}}
    <form method="GET" action="{{ route('folders.index') }}" class="d-flex align-items-center gap-2 mb-4 flex-wrap" id="filterForm">
        <div class="input-group input-group-sm" style="max-width:260px;">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Rechercher un dossier..." value="{{ request('search') }}" style="box-shadow:none;">
        </div>
        <select name="type_id" class="form-select form-select-sm" style="max-width:160px;" onchange="document.getElementById('filterForm').submit()">
            <option value="">Tous les types</option>
            @foreach($types as $type)
            <option value="{{ $type->id }}" {{ request('type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
            @endforeach
        </select>
        <select name="status" class="form-select form-select-sm" style="max-width:140px;" onchange="document.getElementById('filterForm').submit()">
            <option value="">Tous les statuts</option>
            <option value="active"   {{ request('status')=='active'   ? 'selected' : '' }}>Actif</option>
            <option value="archived" {{ request('status')=='archived' ? 'selected' : '' }}>Archivé</option>
            <option value="closed"   {{ request('status')=='closed'   ? 'selected' : '' }}>Fermé</option>
        </select>
        <select name="organisation_id" class="form-select form-select-sm" style="max-width:190px;" onchange="document.getElementById('filterForm').submit()">
            <option value="">Toutes les organisations</option>
            @foreach($organisations as $org)
            <option value="{{ $org->id }}" {{ request('organisation_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-funnel"></i></button>
        @if(request()->hasAny(['search','type_id','status','organisation_id']))
        <a href="{{ route('folders.index') }}" class="btn btn-outline-secondary btn-sm" title="Effacer les filtres"><i class="bi bi-x-lg"></i></a>
        @endif
    </form>

    @if($folders->count() > 0)

    {{-- ===== GRID VIEW ===== --}}
    <div id="gridView">
        <div class="row g-2">
            @foreach($folders as $folder)
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <div class="gd-item-card rounded-3 p-2 text-center position-relative"
                     ondblclick="window.location='{{ route('folders.show', $folder) }}'">
                    <i class="bi bi-folder-fill gd-folder-icon-lg d-block mb-1"></i>
                    <div class="fw-semibold text-truncate" style="font-size:.8rem;color:#202124;" title="{{ $folder->name }}">{{ $folder->name }}</div>
                    <div class="text-muted" style="font-size:.65rem;">
                        {{ $folder->type->name ?? '' }}
                    </div>
                    <div class="d-flex justify-content-center gap-2 mt-1" style="font-size:.62rem;color:#5f6368;">
                        <span><i class="bi bi-file-earmark me-1"></i>{{ $folder->documents_count }}</span>
                        <span><i class="bi bi-folder me-1"></i>{{ $folder->children_count }}</span>
                        @if($folder->status === 'active')
                        <span class="text-success"><i class="bi bi-circle-fill" style="font-size:.4rem;vertical-align:middle;"></i></span>
                        @elseif($folder->status === 'archived')
                        <span class="text-warning"><i class="bi bi-archive" style="font-size:.65rem;"></i></span>
                        @else
                        <span class="text-muted"><i class="bi bi-slash-circle" style="font-size:.65rem;"></i></span>
                        @endif
                    </div>
                    {{-- Hover actions --}}
                    <div class="gd-hover-actions">
                        <a href="{{ route('folders.show', $folder) }}" class="gd-icon-btn" title="Ouvrir"><i class="bi bi-arrow-right-circle"></i></a>
                        <a href="{{ route('folders.edit', $folder) }}" class="gd-icon-btn" title="Modifier"><i class="bi bi-pencil"></i></a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ===== LIST VIEW ===== --}}
    <div id="listView" style="display:none;">
        <div class="card border-0 shadow-sm overflow-hidden">
            <table class="table table-hover mb-0" style="font-size:.82rem;">
                <thead style="background:#f8f9fa;font-size:.7rem;color:#5f6368;text-transform:uppercase;letter-spacing:.04em;">
                    <tr>
                        <th class="px-3 py-2 border-0">Nom</th>
                        <th class="py-2 border-0">Type</th>
                        <th class="py-2 border-0">Parent</th>
                        <th class="py-2 border-0">Statut</th>
                        <th class="py-2 border-0 text-center">Docs</th>
                        <th class="py-2 border-0 text-center">Dossiers</th>
                        <th class="py-2 border-0">Taille</th>
                        <th class="py-2 border-0"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($folders as $folder)
                    <tr onclick="window.location='{{ route('folders.show', $folder) }}'" style="cursor:pointer;">
                        <td class="px-3 py-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-folder-fill text-warning" style="font-size:1rem;flex-shrink:0;"></i>
                                <span class="fw-semibold">{{ $folder->name }}</span>
                                @if($folder->code)
                                <code style="font-size:.68rem;color:#5f6368;background:transparent;">{{ $folder->code }}</code>
                                @endif
                            </div>
                        </td>
                        <td class="py-2"><span class="badge bg-light text-dark border" style="font-size:.65rem;">{{ $folder->type->name ?? '—' }}</span></td>
                        <td class="py-2 text-muted" style="font-size:.78rem;">
                            @if($folder->parent)
                            <a href="{{ route('folders.show', $folder->parent) }}" class="text-muted text-decoration-none" onclick="event.stopPropagation()">{{ $folder->parent->name }}</a>
                            @else —
                            @endif
                        </td>
                        <td class="py-2">
                            @if($folder->status === 'active')
                            <span class="badge" style="background:#e6f4ea;color:#188038;font-size:.65rem;">Actif</span>
                            @elseif($folder->status === 'archived')
                            <span class="badge" style="background:#fff3e0;color:#e65100;font-size:.65rem;">Archivé</span>
                            @else
                            <span class="badge bg-light text-muted border" style="font-size:.65rem;">Fermé</span>
                            @endif
                        </td>
                        <td class="py-2 text-center"><span class="badge bg-primary rounded-pill">{{ $folder->documents_count }}</span></td>
                        <td class="py-2 text-center"><span class="badge bg-info rounded-pill">{{ $folder->children_count }}</span></td>
                        <td class="py-2 text-muted" style="font-size:.75rem;">{{ $folder->total_size_human }}</td>
                        <td class="py-2" onclick="event.stopPropagation()">
                            <div class="d-flex gap-1">
                                <a href="{{ route('folders.show', $folder) }}" class="btn btn-sm btn-outline-primary" title="Voir"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('folders.edit', $folder) }}" class="btn btn-sm btn-outline-secondary" title="Modifier"><i class="bi bi-pencil"></i></a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $folders->withQueryString()->links() }}
    </div>

    @else
    <div class="text-center py-5 text-muted">
        <i class="bi bi-folder-x d-block mb-3" style="font-size:3rem;opacity:.2;"></i>
        <p class="mb-2">Aucun dossier trouvé.</p>
        <a href="{{ route('folders.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-folder-plus me-1"></i>Créer un dossier
        </a>
    </div>
    @endif

</div>
@endsection

@push('styles')
<style>
.gd-item-card {
    border: 1px solid transparent;
    background: #fff;
    cursor: default;
    transition: background .12s, border-color .12s, box-shadow .12s;
    position: relative;
}
.gd-item-card:hover {
    background: #f0f4ff;
    border-color: #c5d3f5;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
}
.gd-folder-icon-lg {
    font-size: 2.2rem;
    color: #fbbc04;
}
.gd-hover-actions {
    position: absolute;
    top: 6px; right: 6px;
    display: none;
    gap: 3px;
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
}
.gd-icon-btn:hover { background: #e8f0fe; color: #1a73e8; border-color: #1a73e8; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnGrid = document.getElementById('btnGrid');
    const btnList = document.getElementById('btnList');
    const gridV   = document.getElementById('gridView');
    const listV   = document.getElementById('listView');
    const saved   = localStorage.getItem('folders_idx_view') || 'grid';

    function setView(v) {
        gridV.style.display = v === 'grid' ? '' : 'none';
        listV.style.display = v === 'list' ? 'block' : 'none';
        btnGrid.classList.toggle('active', v === 'grid');
        btnList.classList.toggle('active', v === 'list');
        localStorage.setItem('folders_idx_view', v);
    }
    setView(saved);
    btnGrid.addEventListener('click', () => setView('grid'));
    btnList.addEventListener('click', () => setView('list'));
});
</script>
@endpush
