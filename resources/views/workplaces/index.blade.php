@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3 gd-workplaces">

    {{-- ===== TOOLBAR ===== --}}
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <h5 class="mb-0 me-auto fw-semibold">
            <i class="bi bi-grid-3x3-gap-fill text-primary me-2"></i>Espaces de travail
        </h5>
        @can('create_workplaces')
        <a href="{{ route('workplaces.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nouveau
        </a>
        @endcan
        {{-- View toggle --}}
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary active" id="viewGrid" title="Grille">
                <i class="bi bi-grid"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary" id="viewList" title="Liste">
                <i class="bi bi-list-ul"></i>
            </button>
        </div>
    </div>

    {{-- ===== FILTER BAR ===== --}}
    <div class="gd-filterbar d-flex align-items-center gap-2 mb-4 flex-wrap">
        <form method="GET" action="{{ route('workplaces.index') }}" class="d-flex align-items-center gap-2 flex-wrap w-100" id="filterForm">
            <div class="input-group input-group-sm" style="max-width:280px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Rechercher un espace..." value="{{ request('search') }}" style="box-shadow:none;">
            </div>
            <select name="category" class="form-select form-select-sm" style="max-width:180px;" onchange="document.getElementById('filterForm').submit()">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select form-select-sm" style="max-width:160px;" onchange="document.getElementById('filterForm').submit()">
                <option value="">Tous les statuts</option>
                <option value="active"    {{ request('status')=='active'    ? 'selected' : '' }}>Actif</option>
                <option value="archived"  {{ request('status')=='archived'  ? 'selected' : '' }}>Archivé</option>
                <option value="suspended" {{ request('status')=='suspended' ? 'selected' : '' }}>Suspendu</option>
            </select>
            @if(request()->hasAny(['search','category','status']))
            <a href="{{ route('workplaces.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
            @endif
        </form>
    </div>

    {{-- ===== GRID VIEW ===== --}}
    <div id="gridView">
        <div class="row g-3">
            @forelse($workplaces as $workplace)
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <div class="gd-card card border-0 shadow-sm h-100 position-relative" tabindex="0"
                     ondblclick="window.location='{{ route('workplaces.show', $workplace) }}'">

                    {{-- Color strip --}}
                    <div class="gd-card-strip" style="background:{{ $workplace->color ?? '#4285f4' }};"></div>

                    {{-- Thumb --}}
                    <div class="gd-card-thumb d-flex align-items-center justify-content-center">
                        <div class="gd-thumb-icon" style="background:{{ $workplace->color ?? '#4285f4' }}22; color:{{ $workplace->color ?? '#4285f4' }};">
                            <i class="bi {{ $workplace->icon ?? 'bi-building' }}"></i>
                        </div>
                    </div>

                    <div class="card-body p-2 pt-1">
                        <div class="gd-card-name text-truncate fw-semibold small" title="{{ $workplace->name }}">
                            {{ $workplace->name }}
                        </div>
                        <div class="text-muted d-flex align-items-center gap-1 mt-1" style="font-size:.68rem;">
                            <span class="badge rounded-pill px-2 py-0"
                                  style="font-size:.6rem;background:{{ $workplace->status=='active' ? '#e6f4ea' : '#f1f3f4' }};color:{{ $workplace->status=='active' ? '#188038' : '#5f6368' }};">
                                {{ $workplace->status=='active' ? 'Actif' : ($workplace->status=='archived' ? 'Archivé' : 'Suspendu') }}
                            </span>
                            <span class="text-truncate">{{ $workplace->category->name ?? '' }}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-1" style="font-size:.66rem;color:#5f6368;">
                            <span><i class="bi bi-people me-1"></i>{{ $workplace->members_count }}</span>
                            <span><i class="bi bi-folder me-1"></i>{{ $workplace->folders_count ?? 0 }}</span>
                            <span><i class="bi bi-file-earmark me-1"></i>{{ $workplace->documents_count ?? 0 }}</span>
                        </div>
                        @if($workplace->max_storage_mb)
                        <div class="progress mt-1" style="height:3px;border-radius:2px;">
                            <div class="progress-bar" style="width:{{ $workplace->storagePercentage }}%;background:{{ $workplace->color ?? '#4285f4' }};"></div>
                        </div>
                        @endif
                    </div>

                    {{-- Hover actions --}}
                    <div class="gd-hover-actions">
                        <a href="{{ route('workplaces.show', $workplace) }}" class="btn btn-sm btn-light rounded-circle p-1" title="Ouvrir">
                            <i class="bi bi-arrow-right-circle"></i>
                        </a>
                        @can('update', $workplace)
                        <a href="{{ route('workplaces.edit', $workplace) }}" class="btn btn-sm btn-light rounded-circle p-1" title="Modifier">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endcan
                    </div>

                    {{-- Owner footer --}}
                    <div class="card-footer border-0 bg-transparent px-2 pb-2 pt-0" style="font-size:.63rem;color:#5f6368;">
                        <i class="bi bi-person me-1"></i>{{ $workplace->owner->name ?? '—' }}
                        · {{ $workplace->created_at->format('d/m/Y') }}
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="gd-empty text-center py-5">
                    <i class="bi bi-grid-3x3-gap d-block mb-3" style="font-size:3rem;opacity:.2;"></i>
                    <p class="text-muted mb-2">Aucun espace de travail trouvé.</p>
                    @can('create_workplaces')
                    <a href="{{ route('workplaces.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Créer un espace
                    </a>
                    @endcan
                </div>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ===== LIST VIEW ===== --}}
    <div id="listView" style="display:none;">
        <div class="card border-0 shadow-sm overflow-hidden">
            <table class="table table-hover mb-0 gd-table" style="font-size:.83rem;">
                <thead style="background:#f8f9fa;font-size:.72rem;color:#5f6368;text-transform:uppercase;letter-spacing:.04em;">
                    <tr>
                        <th class="px-3 py-2 border-0">Nom</th>
                        <th class="py-2 border-0">Catégorie</th>
                        <th class="py-2 border-0">Statut</th>
                        <th class="py-2 border-0 text-center">Membres</th>
                        <th class="py-2 border-0 text-center">Stockage</th>
                        <th class="py-2 border-0">Propriétaire</th>
                        <th class="py-2 border-0">Créé le</th>
                        <th class="py-2 border-0"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($workplaces as $workplace)
                    <tr class="gd-table-row" onclick="window.location='{{ route('workplaces.show', $workplace) }}'" style="cursor:pointer;">
                        <td class="px-3 py-2">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:30px;height:30px;border-radius:8px;background:{{ $workplace->color ?? '#4285f4' }}22;display:flex;align-items:center;justify-content:center;color:{{ $workplace->color ?? '#4285f4' }};flex-shrink:0;">
                                    <i class="bi {{ $workplace->icon ?? 'bi-building' }}" style="font-size:.9rem;"></i>
                                </div>
                                <span class="fw-semibold text-truncate" style="max-width:180px;" title="{{ $workplace->name }}">{{ $workplace->name }}</span>
                            </div>
                        </td>
                        <td class="py-2 text-muted">{{ $workplace->category->name ?? '—' }}</td>
                        <td class="py-2">
                            <span class="badge rounded-pill px-2"
                                  style="font-size:.65rem;background:{{ $workplace->status=='active' ? '#e6f4ea' : '#f1f3f4' }};color:{{ $workplace->status=='active' ? '#188038' : '#5f6368' }};">
                                {{ $workplace->status=='active' ? 'Actif' : ($workplace->status=='archived' ? 'Archivé' : 'Suspendu') }}
                            </span>
                        </td>
                        <td class="py-2 text-center text-muted">{{ $workplace->members_count }}/{{ $workplace->max_members ?? '∞' }}</td>
                        <td class="py-2 text-center text-muted">{{ number_format($workplace->storageUsedMb, 1) }} MB</td>
                        <td class="py-2 text-muted">{{ $workplace->owner->name ?? '—' }}</td>
                        <td class="py-2 text-muted">{{ $workplace->created_at->format('d/m/Y') }}</td>
                        <td class="py-2">
                            <div class="d-flex gap-1" onclick="event.stopPropagation()">
                                <a href="{{ route('workplaces.show', $workplace) }}" class="btn btn-sm btn-outline-primary" title="Ouvrir">
                                    <i class="bi bi-arrow-right-circle"></i>
                                </a>
                                @can('update', $workplace)
                                <a href="{{ route('workplaces.edit', $workplace) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Aucun espace trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===== PAGINATION ===== --}}
    @if($workplaces->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $workplaces->appends(request()->query())->links() }}
    </div>
    @endif

</div>
@endsection

@push('styles')
<style>
/* ============ GOOGLE DRIVE WORKPLACES ============ */
.gd-workplaces {
    --gd-border: #e0e0e0;
    --gd-hover: #f8f9fa;
    --gd-selected: #e8f0fe;
}

/* Filter bar */
.gd-filterbar .form-control,
.gd-filterbar .form-select,
.gd-filterbar .input-group-text {
    background: #fff;
    border-color: var(--gd-border);
    font-size: .82rem;
}
.gd-filterbar .form-control:focus,
.gd-filterbar .form-select:focus {
    border-color: #4285f4;
    box-shadow: 0 0 0 2px rgba(66,133,244,.15);
}

/* Card */
.gd-card {
    border-radius: 10px !important;
    transition: box-shadow .15s, transform .12s;
    cursor: default;
    overflow: hidden;
    background: #fff;
}
.gd-card:hover, .gd-card:focus {
    box-shadow: 0 4px 16px rgba(0,0,0,.12) !important;
    transform: translateY(-2px);
    outline: none;
}
.gd-card-strip {
    height: 4px;
    width: 100%;
}
.gd-card-thumb {
    height: 80px;
    background: #fafafa;
}
.gd-thumb-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
}
.gd-card-name {
    color: #202124;
    font-size: .82rem;
    line-height: 1.3;
}

/* Hover actions overlay */
.gd-hover-actions {
    position: absolute;
    top: 8px; right: 8px;
    display: none;
    gap: 4px;
    flex-direction: column;
}
.gd-card:hover .gd-hover-actions,
.gd-card:focus .gd-hover-actions {
    display: flex;
}
.gd-hover-actions .btn {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    background: rgba(255,255,255,.95);
    border: 1px solid #e0e0e0;
    color: #5f6368;
    font-size: .8rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.12);
}
.gd-hover-actions .btn:hover {
    background: #e8f0fe;
    color: #1a73e8;
    border-color: #1a73e8;
}

/* Table list view */
.gd-table th {
    font-weight: 600;
    white-space: nowrap;
}
.gd-table-row:hover td {
    background: var(--gd-hover);
}

/* Empty state */
.gd-empty {
    color: #5f6368;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    const btnGrid = document.getElementById('viewGrid');
    const btnList = document.getElementById('viewList');
    const savedView = localStorage.getItem('wp_view') || 'grid';

    function setView(v) {
        if (v === 'list') {
            gridView.style.display = 'none';
            listView.style.display = 'block';
            btnGrid.classList.remove('active');
            btnList.classList.add('active');
        } else {
            gridView.style.display = 'block';
            listView.style.display = 'none';
            btnGrid.classList.add('active');
            btnList.classList.remove('active');
        }
        localStorage.setItem('wp_view', v);
    }

    setView(savedView);
    btnGrid.addEventListener('click', () => setView('grid'));
    btnList.addEventListener('click', () => setView('list'));
});
</script>
@endpush
