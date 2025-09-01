@extends('layouts.app')

@push('styles')
<style>
/* Design System Harmonisé */
:root {
    --primary-color: #2563eb;
    --secondary-color: #64748b;
    --success-color: #059669;
    --warning-color: #d97706;
    --danger-color: #dc2626;
    --info-color: #0891b2;
    --light-bg: #f8fafc;
    --border-color: #e2e8f0;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
    --radius: 0.5rem;
    --transition: all 0.2s ease-in-out;
}

/* Layout optimisé */
.compact-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1rem;
}

/* Header harmonisé */
.page-header {
    background: linear-gradient(135deg, var(--light-bg) 0%, #ffffff 100%);
    border-radius: var(--radius);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
}

.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.page-subtitle {
    color: var(--secondary-color);
    margin: 0.5rem 0 0 0;
    font-size: 0.95rem;
}

/* Breadcrumb moderne */
.modern-breadcrumb {
    background: #ffffff;
    border-radius: var(--radius);
    padding: 0.75rem 1rem;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    margin-bottom: 1.5rem;
}

.breadcrumb-item {
    color: var(--secondary-color);
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition);
}

.breadcrumb-item:hover {
    color: var(--primary-color);
}

.breadcrumb-item.active {
    color: #1e293b;
    font-weight: 600;
}

/* Search bar améliorée */
.search-container {
    position: relative;
    max-width: 400px;
}

.search-input {
    border: 2px solid var(--border-color);
    border-radius: var(--radius);
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    font-size: 0.95rem;
    transition: var(--transition);
    background: #ffffff;
}

.search-input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
    outline: none;
}

.search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--secondary-color);
    z-index: 10;
}

/* Stats cards compactes */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: #ffffff;
    border-radius: var(--radius);
    padding: 1.25rem;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
    text-align: center;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stat-label {
    color: var(--secondary-color);
    font-size: 0.875rem;
    font-weight: 500;
}

/* Cards harmonisées */
.building-card {
    background: #ffffff;
    border-radius: var(--radius);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
    height: 100%;
    overflow: hidden;
}

.building-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary-color);
}

.card-header {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid var(--border-color);
    padding: 1rem;
}

.card-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-body {
    padding: 1rem;
}

.card-footer {
    background: #f8fafc;
    border-top: 1px solid var(--border-color);
    padding: 0.75rem 1rem;
}

/* Building preview */
.building-preview {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-radius: var(--radius);
    padding: 1rem;
    margin: 0.75rem 0;
    border: 1px solid #bfdbfe;
}

.floors-indicator {
    display: flex;
    flex-direction: column;
    gap: 2px;
    margin-left: 0.5rem;
}

.floor-bar {
    width: 16px;
    height: 3px;
    background: var(--primary-color);
    border-radius: 1px;
}

/* Badges modernes */
.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

/* Boutons harmonisés */
.btn-modern {
    border-radius: var(--radius);
    font-weight: 500;
    padding: 0.5rem 1rem;
    transition: var(--transition);
    border: 1px solid transparent;
}

.btn-modern:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

/* Actions compactes */
.actions-group {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.action-btn {
    padding: 0.375rem 0.75rem;
    border-radius: var(--radius);
    font-size: 0.875rem;
    transition: var(--transition);
}

/* Responsive */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .page-header {
        padding: 1rem;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in-up {
    animation: fadeInUp 0.3s ease-out;
}

/* Search results */
.search-results-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #ffffff;
    border: 1px solid var(--border-color);
    border-top: none;
    border-radius: 0 0 var(--radius) var(--radius);
    box-shadow: var(--shadow-lg);
    z-index: 1000;
    max-height: 300px;
    overflow-y: auto;
    display: none;
}

.search-result-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--border-color);
    cursor: pointer;
    transition: var(--transition);
}

.search-result-item:hover {
    background: var(--light-bg);
}

.search-result-item:last-child {
    border-bottom: none;
}

/* Highlight animation */
.highlight-building {
    animation: highlight-pulse 2s ease-in-out;
}

@keyframes highlight-pulse {
    0%, 100% { 
        box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4); 
    }
    50% { 
        box-shadow: 0 0 0 8px rgba(37, 99, 235, 0); 
    }
}
</style>
@endpush

@section('content')
<div class="compact-container">
    <!-- Header moderne -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-building text-primary"></i>
                    Gestion des Bâtiments
                </h1>
                <p class="page-subtitle">Vue d'ensemble de vos infrastructures</p>
            </div>
            <div class="d-flex gap-2">
                <div class="search-container">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" class="form-control search-input" id="globalBuildingSearch" 
                           placeholder="Rechercher un bâtiment...">
                    <div class="search-results-dropdown" id="searchResults"></div>
                </div>
                <a href="{{ route('buildings.create') }}" class="btn btn-primary btn-modern">
                    <i class="bi bi-plus-circle me-2"></i>Nouveau
                </a>
            </div>
        </div>
    </div>

    <!-- Breadcrumb moderne -->
    <nav class="modern-breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="{{ route('report.dashboard') }}" class="breadcrumb-item">
                    <i class="bi bi-house me-1"></i>Accueil
                </a>
            </li>
            <li class="breadcrumb-item active">
                <i class="bi bi-building me-1"></i>Bâtiments
            </li>
        </ol>
    </nav>

    <!-- Statistiques compactes -->
    <div class="stats-grid">
        @php
            $totalBuildings = $buildings->total();
            $publicBuildings = $buildings->where('visibility', 'public')->count();
            $privateBuildings = $buildings->where('visibility', 'private')->count();
            $floorsCount = $buildings->sum(function($building) { return $building->floors->count(); });
        @endphp
        
        <div class="stat-card">
            <div class="stat-number text-primary">{{ $totalBuildings }}</div>
            <div class="stat-label">Bâtiments Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-success">{{ $publicBuildings }}</div>
            <div class="stat-label">Public</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-danger">{{ $privateBuildings }}</div>
            <div class="stat-label">Privé</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-info">{{ $floorsCount }}</div>
            <div class="stat-label">Étages Total</div>
        </div>
    </div>

    <!-- Grille des bâtiments -->
    <div class="row g-3" id="buildingList">
        @forelse ($buildings as $building)
            <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-12 fade-in-up">
                <div class="building-card">
                    <!-- Header -->
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h5 class="card-title">
                                    <i class="bi bi-building text-primary"></i>
                                    {{ $building->name ?? 'N/A' }}
                                </h5>
                                <small class="text-muted">
                                    ID: {{ $building->id }} • {{ $building->floors->count() }} étage(s)
                                </small>
                            </div>
                            <span class="status-badge bg-{{ $building->visibility == 'public' ? 'success' : ($building->visibility == 'private' ? 'danger' : 'warning') }}">
                                {{ ucfirst($building->visibility ?? 'N/A') }}
                            </span>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="card-body">
                        <!-- Aperçu du bâtiment -->
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
                                <div class="ms-auto text-center">
                                    <div class="fw-bold text-primary">{{ $building->floors->count() }}</div>
                                    <small class="text-muted">Étages</small>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        @if($building->description)
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    {{ Str::limit($building->description, 80) }}
                                </small>
                            </div>
                        @endif

                        <!-- Stats rapides -->
                        <div class="row g-2 mt-3">
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

                    <!-- Footer -->
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="actions-group">
                                <a href="{{ route('buildings.show', $building->id) }}" class="btn btn-outline-primary action-btn" title="Vue détaillée">
                                    <i class="bi bi-eye"></i> Voir
                                </a>
                                <a href="{{ route('buildings.edit', $building->id) }}" class="btn btn-outline-secondary action-btn" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                            
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
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
                <div class="building-card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-building display-1 text-muted mb-3"></i>
                        <h5 class="text-muted">Aucun bâtiment trouvé</h5>
                        <p class="text-muted">Commencez par créer votre premier bâtiment</p>
                        <a href="{{ route('buildings.create') }}" class="btn btn-primary btn-modern">
                            <i class="bi bi-plus-circle me-2"></i>Créer un bâtiment
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination compacte -->
    @if($buildings->hasPages())
        <div class="d-flex justify-content-center mt-4">
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <li class="page-item {{ $buildings->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $buildings->previousPageUrl() }}" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    @foreach ($buildings->getUrlRange(1, $buildings->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $buildings->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach
                    <li class="page-item {{ $buildings->hasMorePages() ? '' : 'disabled' }}">
                        <a class="page-link" href="{{ $buildings->nextPageUrl() }}" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
    initializeAnimations();
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

    // Fermer les résultats en cliquant à l'extérieur
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            searchResults.style.display = 'none';
        }
    });
}

function performSearch(query) {
    const searchResults = document.getElementById('searchResults');
    
    // Afficher le chargement
    searchResults.innerHTML = '<div class="p-3 text-center"><i class="bi bi-arrow-clockwise spin"></i> Recherche...</div>';
    searchResults.style.display = 'block';

    // Rechercher dans les bâtiments
    const buildings = document.querySelectorAll('#buildingList .col-xxl-4');
    const results = [];
    let hasVisibleResults = false;

    buildings.forEach(building => {
        const buildingCard = building.querySelector('.building-card');
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

    // Afficher les résultats
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
            }, 2000);
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
    // Gérer l'affichage quand aucun résultat
}

function initializeAnimations() {
    // Animation d'apparition des cartes
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });

    document.querySelectorAll('.fade-in-up').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        observer.observe(el);
    });
}

// CSS pour l'animation de rotation
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
@endsection
