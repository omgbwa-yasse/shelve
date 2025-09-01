@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<style>
    .building-preview {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-radius: 8px;
        padding: 12px;
        margin: 8px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .floors-indicator {
        display: flex;
        flex-direction: column;
        gap: 2px;
        margin-left: 8px;
    }
    
    .floor-bar {
        width: 20px;
        height: 4px;
        background: #1976d2;
        border-radius: 2px;
    }
    
    .search-container {
        position: relative;
    }
    
    .search-results-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-top: none;
        border-radius: 0 0 0.375rem 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        z-index: 1000;
        max-height: 300px;
        overflow-y: auto;
        display: none;
    }
    
    .search-result-item {
        cursor: pointer;
        transition: background-color 0.2s;
        padding: 8px 12px;
        border-bottom: 1px solid #f8f9fa;
    }
    
    .search-result-item:hover {
        background-color: #f8f9fa;
    }
    
    .highlight-building {
        animation: highlight-pulse 3s ease-in-out;
        transform: scale(1.02);
    }
    
    @keyframes highlight-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(25, 118, 210, 0.4); }
        50% { box-shadow: 0 0 0 10px rgba(25, 118, 210, 0); }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Enhanced Breadcrumb with Search -->
    <div class="enhanced-breadcrumb-container mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-light p-3 rounded mb-0">
                        <li class="breadcrumb-item active" aria-current="page">
                            <i class="bi bi-building text-primary"></i> Bâtiments
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-6">
                <div class="search-container">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="globalBuildingSearch" 
                               placeholder="Rechercher un bâtiment...">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-funnel"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Filtrer par</h6></li>
                            <li><a class="dropdown-item" href="#" data-filter="all">
                                <i class="bi bi-check-all"></i> Tout afficher
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" data-filter="public">
                                <i class="bi bi-globe"></i> Public
                            </a></li>
                            <li><a class="dropdown-item" href="#" data-filter="private">
                                <i class="bi bi-lock"></i> Privé
                            </a></li>
                            <li><a class="dropdown-item" href="#" data-filter="floors">
                                <i class="bi bi-layers"></i> Multi-niveaux
                            </a></li>
                        </ul>
                    </div>
                    <div class="search-results-dropdown" id="searchResults">
                        <!-- Search results will appear here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Header with Statistics -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <h1 class="h2 mb-0"><i class="bi bi-building"></i> Gestion des Bâtiments</h1>
            <p class="text-muted">Vue d'ensemble de vos infrastructures</p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <a href="{{ route('buildings.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nouveau Bâtiment
            </a>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        @php
            $totalBuildings = $buildings->total();
            $publicBuildings = $buildings->where('visibility', 'public')->count();
            $privateBuildings = $buildings->where('visibility', 'private')->count();
            $floorsCount = $buildings->sum(function($building) { return $building->floors->count(); });
        @endphp
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-gradient-to-r from-blue-50 to-blue-100">
                <div class="card-body text-center">
                    <div class="display-4 text-blue-600 mb-2">{{ $totalBuildings }}</div>
                    <div class="text-muted fw-bold">Bâtiments Total</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-gradient-to-r from-green-50 to-green-100">
                <div class="card-body text-center">
                    <div class="display-4 text-green-600 mb-2">{{ $publicBuildings }}</div>
                    <div class="text-muted fw-bold">Public</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-gradient-to-r from-red-50 to-red-100">
                <div class="card-body text-center">
                    <div class="display-4 text-red-600 mb-2">{{ $privateBuildings }}</div>
                    <div class="text-muted fw-bold">Privé</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-gradient-to-r from-purple-50 to-purple-100">
                <div class="card-body text-center">
                    <div class="display-4 text-purple-600 mb-2">{{ $floorsCount }}</div>
                    <div class="text-muted fw-bold">Étages Total</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Buildings Grid -->
    <div class="row" id="buildingList">
        @forelse ($buildings as $building)
            <div class="col-xxl-4 col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100 hover:shadow-lg transition-shadow duration-200">
                    <!-- Header with Building Info -->
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title mb-1 font-weight-bold text-gray-800">
                                    <i class="bi bi-building text-primary me-2"></i>
                                    {{ $building->name ?? 'N/A' }}
                                </h5>
                                <small class="text-muted">
                                    ID: {{ $building->id }} • 
                                    {{ $building->floors->count() }} étage(s)
                                </small>
                            </div>
                            <span class="badge bg-{{ $building->visibility == 'public' ? 'success' : ($building->visibility == 'private' ? 'danger' : 'warning') }} fs-6">
                                {{ ucfirst($building->visibility ?? 'N/A') }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Building Preview Visualization -->
                        <div class="building-preview">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-building display-6 text-primary me-3"></i>
                                <div class="floors-indicator">
                                    @for($i = 0; $i < min(5, $building->floors->count()); $i++)
                                        <div class="floor-bar"></div>
                                    @endfor
                                    @if($building->floors->count() > 5)
                                        <small class="text-muted mt-1">+{{ $building->floors->count() - 5 }}</small>
                                    @endif
                                </div>
                                <div class="ms-auto">
                                    <div class="text-center">
                                        <div class="fw-bold text-primary">{{ $building->floors->count() }}</div>
                                        <small class="text-muted">Étages</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Building Details -->
                        @if($building->description)
                            <div class="mt-3">
                                <div class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    {{ Str::limit($building->description, 80) }}
                                </div>
                            </div>
                        @endif

                        <!-- Quick Stats -->
                        <div class="row g-2 mt-2">
                            <div class="col-6">
                                <div class="text-center p-2 bg-light rounded">
                                    <div class="fw-bold text-info">{{ $building->floors->sum(function($floor) { return $floor->rooms->count(); }) }}</div>
                                    <small class="text-muted">Salles</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-2 bg-light rounded">
                                    <div class="fw-bold text-success">{{ $building->created_at?->format('Y') ?? 'N/A' }}</div>
                                    <small class="text-muted">Créé en</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card-footer bg-white border-top-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="btn-group" role="group">
                                <a href="{{ route('buildings.show', $building->id) }}" class="btn btn-sm btn-outline-primary" title="Vue détaillée">
                                    <i class="bi bi-eye"></i> Voir
                                </a>
                                <a href="{{ route('buildings.edit', $building->id) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                            
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('floors.index', $building->id) }}">
                                        <i class="bi bi-layers me-2"></i>Voir étages
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('rooms.index') }}?building_id={{ $building->id }}">
                                        <i class="bi bi-door-open me-2"></i>Voir salles
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('buildings.destroy', $building->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger" 
                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce bâtiment ?')">
                                                <i class="bi bi-trash me-2"></i>Supprimer
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-building display-1 text-muted mb-3"></i>
                        <h5 class="text-muted">Aucun bâtiment trouvé</h5>
                        <p class="text-muted">Commencez par créer votre premier bâtiment</p>
                        <a href="{{ route('buildings.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Créer un bâtiment
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
    initializeFilters();
});

function initializeSearch() {
    const searchInput = document.getElementById('globalBuildingSearch');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            searchResults.style.display = 'none';
            showAllBuildings();
            return;
        }

        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            searchResults.style.display = 'none';
        }
    });
}

function performSearch(query) {
    const searchResults = document.getElementById('searchResults');
    
    // Show loading
    searchResults.innerHTML = '<div class="p-3 text-center"><i class="bi bi-arrow-clockwise spin"></i> Recherche...</div>';
    searchResults.style.display = 'block';

    // Search in buildings
    const buildings = document.querySelectorAll('#buildingList .col-xxl-4');
    const results = [];
    let hasVisibleResults = false;

    buildings.forEach(building => {
        const buildingCard = building.querySelector('.card');
        const buildingName = buildingCard.querySelector('.card-title').textContent;
        const buildingDesc = buildingCard.querySelector('.text-muted').textContent;
        const isMatch = buildingName.toLowerCase().includes(query.toLowerCase()) ||
                       buildingDesc.toLowerCase().includes(query.toLowerCase());

        if (isMatch) {
            building.style.display = 'block';
            hasVisibleResults = true;
            results.push({
                type: 'building',
                name: buildingName.trim(),
                desc: buildingDesc.trim(),
                element: building
            });
        } else {
            building.style.display = 'none';
        }
    });

    // Show search results dropdown
    displaySearchResults(results, query);
    
    if (!hasVisibleResults) {
        showNoResults();
    }
}

function displaySearchResults(results, query) {
    const searchResults = document.getElementById('searchResults');
    
    if (results.length === 0) {
        searchResults.innerHTML = `
            <div class="p-3 text-center text-muted">
                <i class="bi bi-search"></i> Aucun résultat pour "${query}"
            </div>
        `;
        return;
    }

    let html = `<div class="search-results-list">`;
    
    results.slice(0, 5).forEach(result => {
        html += `
            <div class="search-result-item" onclick="scrollToBuilding('${result.name}')">
                <div class="d-flex align-items-center">
                    <i class="bi bi-building text-primary me-2"></i>
                    <div>
                        <div class="fw-bold">${result.name}</div>
                        <small class="text-muted">${result.desc}</small>
                    </div>
                </div>
            </div>
        `;
    });

    if (results.length > 5) {
        html += `<div class="p-2 text-center text-muted small">... et ${results.length - 5} autres résultats</div>`;
    }

    html += `</div>`;
    searchResults.innerHTML = html;
}

function scrollToBuilding(buildingName) {
    const buildings = document.querySelectorAll('#buildingList .card-title');
    buildings.forEach(titleEl => {
        if (titleEl.textContent.includes(buildingName)) {
            const buildingCard = titleEl.closest('.col-xxl-4');
            buildingCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            buildingCard.classList.add('highlight-building');
            setTimeout(() => {
                buildingCard.classList.remove('highlight-building');
            }, 3000);
        }
    });
    document.getElementById('searchResults').style.display = 'none';
}

function showAllBuildings() {
    const buildings = document.querySelectorAll('#buildingList .col-xxl-4');
    buildings.forEach(building => {
        building.style.display = 'block';
    });
}

function showNoResults() {
    // Add no results message if needed
}

function initializeFilters() {
    document.querySelectorAll('[data-filter]').forEach(filterBtn => {
        filterBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const filterType = this.dataset.filter;
            applyFilter(filterType);
        });
    });
}

function applyFilter(filterType) {
    const buildings = document.querySelectorAll('#buildingList .col-xxl-4');
    
    buildings.forEach(building => {
        let shouldShow = true;
        
        switch(filterType) {
            case 'all':
                shouldShow = true;
                break;
            case 'public':
                const publicBadge = building.querySelector('.badge');
                shouldShow = publicBadge && publicBadge.textContent.toLowerCase().includes('public');
                break;
            case 'private':
                const privateBadge = building.querySelector('.badge');
                shouldShow = privateBadge && privateBadge.textContent.toLowerCase().includes('private');
                break;
            case 'floors':
                const floorsCount = building.querySelector('.floors-indicator .floor-bar');
                shouldShow = floorsCount !== null;
                break;
        }
        
        building.style.display = shouldShow ? 'block' : 'none';
    });
}

// CSS for spin animation
const style = document.createElement('style');
style.textContent = `
    .spin {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);
</script>
@endpush

    <!-- Pagination compacte -->
    @if(method_exists($buildings, 'hasPages') && $buildings->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $buildings->links() }}
        </div>
    @endif
</div>
@endsection
