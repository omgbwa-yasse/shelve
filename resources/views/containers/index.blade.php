@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">{{ __('Contenants d\'archives') }}</h1>

            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="{{ route('report.dashboard') }}">{{ __('Accueil') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('buildings.index') }}">{{ __('Dépôts') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Contenants') }}</li>
                </ol>
            </nav>

            <!-- Search and Statistics Section -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-search"></i> {{ __('Recherche rapide') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="position-relative">
                                <input type="text" id="globalSearch" class="form-control form-control-lg" 
                                       placeholder="Rechercher par code, étagère, statut, propriété..." autocomplete="off">
                                <div id="searchResults" class="search-dropdown position-absolute w-100 mt-1 bg-white border rounded shadow-lg" style="display: none; z-index: 1000; max-height: 300px; overflow-y: auto;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-bar-chart"></i> {{ __('Statistiques') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-12">
                                    <div class="text-primary">
                                        <h4>{{ $containers->total() }}</h4>
                                        <small>{{ __('Contenants total') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <span class="text-muted">{{ $containers->total() }} contenant(s) - Page {{ $containers->currentPage() }} sur {{ $containers->lastPage() }}</span>
                </div>
                <div>
                    <a href="{{ route('containers.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> {{ __('Nouveau contenant') }}
                    </a>
                </div>
            </div>

            <!-- Containers Grid -->
            <div id="containerList" class="row">
                @forelse ($containers as $container)
                    <div class="col-xl-4 col-lg-6 col-md-12 mb-4 container-card" data-search="{{ strtolower($container->code ?? '') }} {{ strtolower($container->shelf->code ?? '') }} {{ strtolower($container->status->name ?? '') }} {{ strtolower($container->property->name ?? '') }}">
                        <div class="card h-100 shadow-sm border-left-warning">
                            <div class="card-body d-flex flex-column">
                                <!-- Container Header -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-1">
                                            <span class="badge bg-warning text-dark me-2">{{ $container->code ?? 'N/A' }}</span>
                                        </h5>
                                        <small class="text-muted">ID: {{ $container->id }}</small>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="{{ route('containers.show', $container->id) }}"><i class="bi bi-eye"></i> {{ __('Voir') }}</a></li>
                                            <li><a class="dropdown-item" href="{{ route('containers.edit', $container->id) }}"><i class="bi bi-pencil"></i> {{ __('Modifier') }}</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteContainer({{ $container->id }})"><i class="bi bi-trash"></i> {{ __('Supprimer') }}</a></li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Container Preview -->
                                <div class="container-preview mb-3 p-3 bg-light rounded">
                                    <div class="container-visual d-flex align-items-center justify-content-center" style="height: 120px; background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border: 2px dashed #f39c12; border-radius: 8px;">
                                        <div class="text-center">
                                            <i class="bi bi-archive display-4 text-warning mb-2"></i>
                                            <div class="small text-muted">
                                                <span class="badge bg-light text-dark">{{ __('Contenant') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Container Info -->
                                <div class="container-info flex-grow-1">
                                    <div class="row g-2 mb-2">
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-bookshelf me-2 text-primary"></i>
                                                <small><strong>{{ __('Étagère') }}:</strong> {{ $container->shelf->code ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                        @if($container->shelf && $container->shelf->room)
                                            <div class="col-12">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-house-door me-2 text-info"></i>
                                                    <small><strong>{{ __('Salle') }}:</strong> {{ $container->shelf->room->name ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        @endif
                                        @if($container->status)
                                            <div class="col-12">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-flag me-2 text-success"></i>
                                                    <small><strong>{{ __('Statut') }}:</strong> {{ $container->status->name ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        @endif
                                        @if($container->property)
                                            <div class="col-12">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-building me-2 text-secondary"></i>
                                                    <small><strong>{{ __('Propriété') }}:</strong> {{ $container->property->name ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Container Footer -->
                                <div class="container-footer mt-3 pt-3 border-top">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="status-info">
                                            @if($container->shelf)
                                                <small class="text-muted">
                                                    <i class="bi bi-geo-alt"></i>
                                                    Position: {{ $container->x_position ?? '?' }},{{ $container->y_position ?? '?' }},{{ $container->z_position ?? '?' }}
                                                </small>
                                            @endif
                                        </div>
                                        <div class="action-buttons">
                                            <a href="{{ route('containers.show', $container->id) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Voir les détails') }}">
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-archive display-1 text-muted mb-3"></i>
                                <h5 class="text-muted">{{ __('Aucun contenant trouvé') }}</h5>
                                <p class="text-muted">{{ __('Commencez par créer votre premier contenant d\'archives.') }}</p>
                                <a href="{{ route('containers.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> {{ __('Créer un contenant') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Enhanced Pagination -->
            @if($containers->hasPages())
                <div class="card mt-4">
                    <div class="card-body">
                        <nav aria-label="Page navigation">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="pagination-info">
                                    <span class="text-muted small">
                                        Affichage de {{ $containers->firstItem() ?? 0 }} à {{ $containers->lastItem() ?? 0 }} 
                                        sur {{ $containers->total() }} résultats
                                    </span>
                                </div>
                                <ul class="pagination mb-0">
                                    <li class="page-item {{ $containers->onFirstPage() ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ $containers->previousPageUrl() }}" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    @foreach ($containers->getUrlRange(1, $containers->lastPage()) as $page => $url)
                                        <li class="page-item {{ $page == $containers->currentPage() ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                        </li>
                                    @endforeach
                                    <li class="page-item {{ $containers->hasMorePages() ? '' : 'disabled' }}">
                                        <a class="page-link" href="{{ $containers->nextPageUrl() }}" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Confirmer la suppression') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{ __('Êtes-vous sûr de vouloir supprimer ce contenant ? Cette action ne peut pas être annulée.') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('Supprimer') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let searchTimeout;
const searchInput = document.getElementById('globalSearch');
const searchResults = document.getElementById('searchResults');
const containerCards = document.querySelectorAll('.container-card');

// Search functionality
searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.toLowerCase().trim();
    
    searchTimeout = setTimeout(() => {
        if (query === '') {
            searchResults.style.display = 'none';
            showAllContainers();
            return;
        }
        
        filterContainers(query);
        showSearchResults(query);
    }, 300);
});

// Hide search results when clicking outside
document.addEventListener('click', function(e) {
    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.style.display = 'none';
    }
});

function filterContainers(query) {
    containerCards.forEach(card => {
        const searchText = card.getAttribute('data-search');
        if (searchText.includes(query)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function showAllContainers() {
    containerCards.forEach(card => {
        card.style.display = 'block';
    });
}

function showSearchResults(query) {
    const visibleCards = Array.from(containerCards).filter(card => card.style.display !== 'none');
    
    if (visibleCards.length === 0) {
        searchResults.innerHTML = '<div class="p-3 text-muted">Aucun résultat trouvé</div>';
    } else {
        const results = visibleCards.slice(0, 5).map(card => {
            const title = card.querySelector('.card-title').textContent.trim();
            const containerId = card.querySelector('a[href*="containers"]').href.split('/').pop();
            return `<a href="{{ url('containers') }}/${containerId}" class="dropdown-item"><i class="bi bi-archive me-2"></i>${title}</a>`;
        }).join('');
        
        searchResults.innerHTML = results + 
            (visibleCards.length > 5 ? '<div class="p-2 text-muted small text-center">Et ' + (visibleCards.length - 5) + ' autres...</div>' : '');
    }
    
    searchResults.style.display = 'block';
}

function deleteContainer(containerId) {
    const form = document.getElementById('deleteForm');
    form.action = `{{ url('containers') }}/${containerId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<style>
.border-left-warning {
    border-left: 4px solid #f39c12 !important;
}

.container-preview {
    transition: all 0.3s ease;
}

.card:hover .container-preview {
    background: linear-gradient(135deg, #fff3cd 0%, #f8d7da 100%) !important;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.12) !important;
}

.search-dropdown .dropdown-item {
    padding: 0.5rem 1rem;
    border-bottom: 1px solid #f8f9fa;
}

.search-dropdown .dropdown-item:hover {
    background-color: #f8f9fa;
}

.search-dropdown .dropdown-item:last-child {
    border-bottom: none;
}

.pagination-info {
    margin-right: 1rem;
}
</style>
@endsection
