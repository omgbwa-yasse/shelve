@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<style>
    .shelf-preview-grid {
        display: grid;
        gap: 1px;
        background: #e5e7eb;
        padding: 8px;
        border-radius: 8px;
        max-width: 200px;
    }
    
    .shelf-spot {
        width: 12px;
        height: 12px;
        border-radius: 2px;
        transition: all 0.2s ease;
    }
    
    .shelf-spot.occupied {
        background: #ef4444;
        box-shadow: 0 0 4px rgba(239, 68, 68, 0.5);
    }
    
    .shelf-spot.available {
        background: #10b981;
        box-shadow: 0 0 4px rgba(16, 185, 129, 0.5);
    }
    
    .shelf-spot.reserved {
        background: #f59e0b;
        box-shadow: 0 0 4px rgba(245, 158, 11, 0.5);
    }
    
    .shelf-spot:hover {
        transform: scale(1.2);
        z-index: 10;
        position: relative;
    }
    
    .occupancy-bar {
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
        margin: 8px 0;
    }
    
    .occupancy-fill {
        height: 100%;
        transition: width 0.3s ease;
        border-radius: 4px;
    }
    
    .hierarchical-breadcrumb {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-radius: 12px;
        padding: 12px 16px;
        margin-bottom: 20px;
        border: 1px solid #e2e8f0;
    }
    
    .breadcrumb-item {
        color: #64748b;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }
    
    .breadcrumb-item:hover {
        color: #0f172a;
    }
    
    .breadcrumb-item.active {
        color: #0f172a;
        font-weight: 600;
    }
    
    .shelf-stats-mini {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 8px;
    }
    
    .stat-badge {
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 600;
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
                        <li class="breadcrumb-item">
                            <a href="{{ route('buildings.index') }}" class="text-decoration-none">
                                <i class="bi bi-building text-primary"></i> Bâtiments
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('rooms.index') }}" class="text-decoration-none">
                                <i class="bi bi-door-open text-success"></i> Salles
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <i class="bi bi-bookshelf text-warning"></i> Étagères
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
                        <input type="text" class="form-control" id="globalShelfSearch" 
                               placeholder="Rechercher une étagère, salle, conteneur...">
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
                            <li><a class="dropdown-item" href="#" data-filter="building">
                                <i class="bi bi-building"></i> Par bâtiment
                            </a></li>
                            <li><a class="dropdown-item" href="#" data-filter="room">
                                <i class="bi bi-door-open"></i> Par salle
                            </a></li>
                            <li><a class="dropdown-item" href="#" data-filter="occupancy">
                                <i class="bi bi-percent"></i> Par occupation
                            </a></li>
                            <li><a class="dropdown-item" href="#" data-filter="capacity">
                                <i class="bi bi-box"></i> Par capacité
                            </a></li>
                        </ul>
                    </div>
                    <div class="search-results-dropdown" id="searchResults" style="display: none;">
                        <!-- Search results will appear here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Header with Statistics -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <h1 class="h2 mb-0"><i class="bi bi-bookshelf"></i> Gestion des Étagères</h1>
            <p class="text-muted">Vue d'ensemble et gestion de vos espaces de stockage</p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <a href="{{ route('shelves.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nouvelle Étagère
            </a>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        @php
            $totalShelves = $shelves->count();
            $totalCapacity = 0;
            $totalOccupied = 0;
            
            foreach($shelves as $shelf) {
                $capacity = $shelf->face * $shelf->ear * $shelf->shelf;
                $occupied = $shelf->containers->count();
                $totalCapacity += $capacity;
                $totalOccupied += $occupied;
            }
            
            $globalOccupancy = $totalCapacity > 0 ? round(($totalOccupied / $totalCapacity) * 100, 1) : 0;
        @endphp
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-gradient-to-r from-blue-50 to-blue-100">
                <div class="card-body text-center">
                    <div class="display-4 text-blue-600 mb-2">{{ $totalShelves }}</div>
                    <div class="text-muted fw-bold">Étagères Total</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-gradient-to-r from-green-50 to-green-100">
                <div class="card-body text-center">
                    <div class="display-4 text-green-600 mb-2">{{ $totalCapacity }}</div>
                    <div class="text-muted fw-bold">Capacité Totale</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-gradient-to-r from-red-50 to-red-100">
                <div class="card-body text-center">
                    <div class="display-4 text-red-600 mb-2">{{ $totalOccupied }}</div>
                    <div class="text-muted fw-bold">Emplacements Occupés</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-gradient-to-r from-purple-50 to-purple-100">
                <div class="card-body text-center">
                    <div class="display-4 text-purple-600 mb-2">{{ $globalOccupancy }}%</div>
                    <div class="text-muted fw-bold">Taux d'Occupation</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Shelves Grid -->
    <div class="row" id="shelfList">
        @forelse ($shelves as $shelf)
            @php
                $occupancyClass = $shelf->occupancy_percentage >= 90 ? 'danger' : ($shelf->occupancy_percentage >= 70 ? 'warning' : ($shelf->occupancy_percentage >= 50 ? 'info' : 'success'));
                $occupancyColor = $shelf->occupancy_percentage >= 90 ? '#dc3545' : ($shelf->occupancy_percentage >= 70 ? '#ffc107' : ($shelf->occupancy_percentage >= 50 ? '#0dcaf0' : '#198754'));
            @endphp
            
            <div class="col-xxl-4 col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100 hover:shadow-lg transition-shadow duration-200">
                    <!-- Header with Shelf Info -->
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title mb-1 font-weight-bold text-gray-800">
                                    <i class="bi bi-bookshelf text-purple-600 me-2"></i>
                                    {{ $shelf->code }}
                                </h5>
                                <small class="text-muted">
                                    <i class="bi bi-building me-1"></i>
                                    {{ $shelf->room->floor->building->name ?? 'N/A' }} → 
                                    {{ $shelf->room->floor->name ?? 'N/A' }} → 
                                    {{ $shelf->room->name ?? 'N/A' }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $occupancyClass }} fs-6">
                                {{ $shelf->occupancy_percentage }}%
                            </span>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Shelf Preview Visualization -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted fw-bold">Aperçu d'occupation</small>
                                <small class="text-muted">{{ $shelf->occupied_spots }}/{{ $shelf->total_capacity }}</small>
                            </div>
                            
                            <!-- Occupancy Progress Bar -->
                            <div class="occupancy-bar">
                                <div class="occupancy-fill" 
                                     style="width: {{ $shelf->occupancy_percentage }}%; background-color: {{ $occupancyColor }};">
                                </div>
                            </div>

                            <!-- 3D Grid Preview (simplified for many containers) -->
                            @if($shelf->total_capacity <= 100)
                                <div class="shelf-preview-grid" 
                                     style="grid-template-columns: repeat({{ min(10, $shelf->ear * $shelf->shelf) }}, 1fr);">
                                    @for($i = 0; $i < min(100, $shelf->total_capacity); $i++)
                                        <div class="shelf-spot {{ $i < $shelf->occupied_spots ? 'occupied' : 'available' }}"
                                             data-bs-toggle="tooltip"
                                             title="{{ $i < $shelf->occupied_spots ? 'Occupé' : 'Disponible' }}">
                                        </div>
                                    @endfor
                                    @if($shelf->total_capacity > 100)
                                        <div class="shelf-spot" style="background: #6b7280; position: relative;">
                                            <span style="font-size: 8px; color: white; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">+</span>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="text-center py-3 bg-gray-50 rounded">
                                    <i class="bi bi-archive display-6 text-muted mb-2"></i>
                                    <div class="small text-muted">Capacité élevée: {{ $shelf->total_capacity }} emplacements</div>
                                    <div class="small">{{ $shelf->occupied_spots }} occupés, {{ $shelf->available_spots }} disponibles</div>
                                </div>
                            @endif
                        </div>

                        <!-- Shelf Details -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="text-center p-2 bg-light rounded">
                                    <div class="fw-bold text-primary">{{ $shelf->face }}</div>
                                    <small class="text-muted">Face(s)</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-2 bg-light rounded">
                                    <div class="fw-bold text-info">{{ $shelf->ear }}</div>
                                    <small class="text-muted">Travée(s)</small>
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="text-center p-2 bg-light rounded">
                                    <div class="fw-bold text-success">{{ $shelf->shelf }}</div>
                                    <small class="text-muted">Tablette(s)</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-2 bg-light rounded">
                                    <div class="fw-bold text-warning">{{ $shelf->volumetry_ml }}</div>
                                    <small class="text-muted">ml</small>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="shelf-stats-mini">
                            <span class="stat-badge bg-success text-white">
                                <i class="bi bi-check-circle"></i> {{ $shelf->available_spots }} libres
                            </span>
                            <span class="stat-badge bg-primary text-white">
                                <i class="bi bi-box-fill"></i> {{ $shelf->containers->count() }} conteneurs
                            </span>
                        </div>

                        @if($shelf->observation)
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    {{ Str::limit($shelf->observation, 60) }}
                                </small>
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="card-footer bg-white border-top-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="btn-group" role="group">
                                <a href="{{ route('shelves.show', $shelf->id) }}" class="btn btn-sm btn-outline-primary" title="Vue détaillée">
                                    <i class="bi bi-eye"></i> Voir
                                </a>
                                <a href="{{ route('shelves.edit', $shelf->id) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                            
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('containers.index') }}?shelf_id={{ $shelf->id }}">
                                        <i class="bi bi-box me-2"></i>Voir conteneurs
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('containers.create') }}?shelf_id={{ $shelf->id }}">
                                        <i class="bi bi-plus-circle me-2"></i>Ajouter conteneur
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('shelves.destroy', $shelf->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger" 
                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette étagère ?')">
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
                        <i class="bi bi-bookshelf display-1 text-muted mb-3"></i>
                        <h5 class="text-muted">Aucune étagère trouvée</h5>
                        <p class="text-muted">Commencez par créer votre première étagère</p>
                        <a href="{{ route('shelves.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Créer une étagère
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
    initializeTooltips();
    initializeShelfSpotEffects();
    initializeSearch();
    initializeFilters();
});

function initializeTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function initializeShelfSpotEffects() {
    document.querySelectorAll('.shelf-spot').forEach(spot => {
        spot.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.3)';
            this.style.zIndex = '10';
        });
        
        spot.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
            this.style.zIndex = '1';
        });
    });
}

function initializeSearch() {
    const searchInput = document.getElementById('globalShelfSearch');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            searchResults.style.display = 'none';
            showAllShelves();
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

    // Search in shelves
    const shelves = document.querySelectorAll('#shelfList .col-xxl-4');
    const results = [];
    let hasVisibleResults = false;

    shelves.forEach(shelf => {
        const shelfCard = shelf.querySelector('.card');
        const shelfCode = shelfCard.querySelector('.card-title').textContent;
        const buildingPath = shelfCard.querySelector('small').textContent;
        const isMatch = shelfCode.toLowerCase().includes(query.toLowerCase()) ||
                       buildingPath.toLowerCase().includes(query.toLowerCase());

        if (isMatch) {
            shelf.style.display = 'block';
            hasVisibleResults = true;
            results.push({
                type: 'shelf',
                code: shelfCode.trim(),
                path: buildingPath.trim(),
                element: shelf
            });
        } else {
            shelf.style.display = 'none';
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
    
    results.slice(0, 8).forEach(result => {
        html += `
            <div class="search-result-item p-2 border-bottom" onclick="scrollToShelf('${result.code}')">
                <div class="d-flex align-items-center">
                    <i class="bi bi-bookshelf text-warning me-2"></i>
                    <div>
                        <div class="fw-bold">${result.code}</div>
                        <small class="text-muted">${result.path}</small>
                    </div>
                </div>
            </div>
        `;
    });

    if (results.length > 8) {
        html += `<div class="p-2 text-center text-muted small">... et ${results.length - 8} autres résultats</div>`;
    }

    html += `</div>`;
    searchResults.innerHTML = html;
}

function scrollToShelf(shelfCode) {
    const shelves = document.querySelectorAll('#shelfList .card-title');
    shelves.forEach(titleEl => {
        if (titleEl.textContent.includes(shelfCode)) {
            const shelfCard = titleEl.closest('.col-xxl-4');
            shelfCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            shelfCard.classList.add('highlight-shelf');
            setTimeout(() => {
                shelfCard.classList.remove('highlight-shelf');
            }, 3000);
        }
    });
    document.getElementById('searchResults').style.display = 'none';
}

function showAllShelves() {
    const shelves = document.querySelectorAll('#shelfList .col-xxl-4');
    shelves.forEach(shelf => {
        shelf.style.display = 'block';
    });
}

function showNoResults() {
    const shelfList = document.getElementById('shelfList');
    if (shelfList.querySelectorAll('.col-xxl-4[style*="block"]').length === 0) {
        if (!shelfList.querySelector('.no-results')) {
            const noResults = document.createElement('div');
            noResults.className = 'col-12 no-results';
            noResults.innerHTML = `
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-search display-1 text-muted mb-3"></i>
                        <h5 class="text-muted">Aucun résultat trouvé</h5>
                        <p class="text-muted">Essayez avec d'autres mots-clés</p>
                        <button class="btn btn-outline-primary" onclick="clearSearch()">
                            <i class="bi bi-x-circle me-2"></i>Effacer la recherche
                        </button>
                    </div>
                </div>
            `;
            shelfList.appendChild(noResults);
        }
    }
}

function clearSearch() {
    document.getElementById('globalShelfSearch').value = '';
    document.getElementById('searchResults').style.display = 'none';
    const noResults = document.querySelector('.no-results');
    if (noResults) noResults.remove();
    showAllShelves();
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
    const shelves = document.querySelectorAll('#shelfList .col-xxl-4');
    
    shelves.forEach(shelf => {
        let shouldShow = true;
        
        switch(filterType) {
            case 'all':
                shouldShow = true;
                break;
            case 'occupancy':
                const occupancyBadge = shelf.querySelector('.badge');
                const occupancy = parseInt(occupancyBadge.textContent.replace('%', ''));
                shouldShow = occupancy >= 80; // Show shelves with high occupancy
                break;
            case 'capacity':
                const capacityText = shelf.querySelector('.display-4').textContent;
                const capacity = parseInt(capacityText);
                shouldShow = capacity >= 100; // Show high capacity shelves
                break;
            // Add more filter cases as needed
        }
        
        shelf.style.display = shouldShow ? 'block' : 'none';
    });
}

// CSS animations
const style = document.createElement('style');
style.textContent = `
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
    }
    
    .search-result-item {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .search-result-item:hover {
        background-color: #f8f9fa;
    }
    
    .search-container {
        position: relative;
    }
    
    .highlight-shelf {
        animation: highlight-pulse 3s ease-in-out;
        transform: scale(1.02);
    }
    
    @keyframes highlight-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4); }
        50% { box-shadow: 0 0 0 10px rgba(13, 110, 253, 0); }
    }
    
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
@endsection
