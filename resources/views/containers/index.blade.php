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


/* Header harmonisé */
.page-header {
    background: linear-gradient(135deg, var(--light-bg) 0%, #ffffff 100%);
    border-radius: var(--radius);
    padding: 1rem;
    margin-bottom: 1rem;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
}

.page-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.page-subtitle {
    color: var(--secondary-color);
    margin: 0.25rem 0 0 0;
    font-size: 0.875rem;
}

/* Breadcrumb moderne */
.modern-breadcrumb {
    background: #ffffff;
    border-radius: var(--radius);
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    margin-bottom: 1rem;
}

.breadcrumb-item {
    color: var(--secondary-color);
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition);
    font-size: 0.875rem;
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
    padding: 0.5rem 0.75rem 0.5rem 2rem;
    font-size: 0.875rem;
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
    left: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--secondary-color);
    z-index: 10;
}

/* Stats cards compactes */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: #ffffff;
    border-radius: var(--radius);
    padding: 1rem;
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
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stat-label {
    color: var(--secondary-color);
    font-size: 0.875rem;
    font-weight: 500;
}

/* Cards harmonisées */
.container-card {
    background: #ffffff;
    border-radius: var(--radius);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
    height: 100%;
    overflow: hidden;
}

.container-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--warning-color);
}

.card-header {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid var(--border-color);
    padding: 0.75rem;
}

.card-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-body {
    padding: 0.75rem;
}

.card-footer {
    background: #f8fafc;
    border-top: 1px solid var(--border-color);
    padding: 0.5rem 0.75rem;
}

/* Container preview */
.container-preview {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-radius: var(--radius);
    padding: 0.75rem;
    margin: 0.5rem 0;
    border: 1px solid #fbbf24;
}

.container-visual {
    height: 80px;
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border: 2px dashed #f39c12;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}

.container-card:hover .container-visual {
    background: linear-gradient(135deg, #fff3cd 0%, #f8d7da 100%);
    border-color: #e74c3c;
}

/* Badges modernes */
.status-badge {
    padding: 0.25rem 0.5rem;
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
    padding: 0.375rem 0.75rem;
    transition: var(--transition);
    border: 1px solid transparent;
    font-size: 0.875rem;
}

.btn-modern:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

/* Actions compactes */
.actions-group {
    display: flex;
    gap: 0.375rem;
    align-items: center;
}

.action-btn {
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius);
    font-size: 0.75rem;
    transition: var(--transition);
}

/* Responsive */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .page-header {
        padding: 0.75rem;
    }
    
    .page-title {
        font-size: 1.25rem;
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
    padding: 0.5rem 0.75rem;
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
.highlight-container {
    animation: highlight-pulse 2s ease-in-out;
}

@keyframes highlight-pulse {
    0%, 100% { 
        box-shadow: 0 0 0 0 rgba(217, 119, 6, 0.4); 
    }
    50% { 
        box-shadow: 0 0 0 8px rgba(217, 119, 6, 0); 
    }
}

/* Info items */
.info-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    margin-bottom: 0.375rem;
    font-size: 0.75rem;
}

.info-item i {
    width: 14px;
    text-align: center;
}

/* Position indicator */
.position-info {
    background: var(--light-bg);
    border-radius: var(--radius);
    padding: 0.375rem;
    font-size: 0.625rem;
    color: var(--secondary-color);
}
</style>
@endpush

@section('content')
<div>
    <!-- Header moderne -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-archive text-warning"></i>
                    {{ __('Contenants d\'archives') }}
                </h1>
                <p class="page-subtitle">Gestion et organisation de vos contenants</p>
            </div>
            <div class="d-flex gap-2">
                <div class="search-container">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" class="form-control search-input" id="globalSearch" 
                           placeholder="Rechercher par code, étagère, statut...">
                    <div class="search-results-dropdown" id="searchResults"></div>
                </div>
                <a href="{{ route('containers.create') }}" class="btn btn-primary btn-modern">
                    <i class="bi bi-plus-circle me-2"></i>{{ __('Nouveau') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Breadcrumb moderne -->
    <nav class="modern-breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="{{ route('report.dashboard') }}" class="breadcrumb-item">
                    <i class="bi bi-house me-1"></i>{{ __('Accueil') }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('buildings.index') }}" class="breadcrumb-item">
                    <i class="bi bi-building me-1"></i>{{ __('Dépôts') }}
                </a>
            </li>
            <li class="breadcrumb-item active">
                <i class="bi bi-archive me-1"></i>{{ __('Contenants') }}
            </li>
        </ol>
    </nav>

    <!-- Statistiques compactes -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number text-primary">{{ $containers->total() }}</div>
            <div class="stat-label">{{ __('Contenants Total') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-success">{{ $containers->where('status.name', 'Disponible')->count() }}</div>
            <div class="stat-label">{{ __('Disponibles') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-warning">{{ $containers->where('status.name', 'Occupé')->count() }}</div>
            <div class="stat-label">{{ __('Occupés') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-info">{{ $containers->unique('shelf_id')->count() }}</div>
            <div class="stat-label">{{ __('Étagères Utilisées') }}</div>
        </div>
    </div>

    <!-- Grille des contenants -->
    <div class="row g-3" id="containerList">
        @forelse ($containers as $container)
            <div class="col-xl-4 col-lg-6 col-md-12 mb-3 fade-in-up" data-search="{{ strtolower($container->code ?? '') }} {{ strtolower($container->shelf->code ?? '') }} {{ strtolower($container->status->name ?? '') }} {{ strtolower($container->property->name ?? '') }}">
                <div class="container-card">
                    <!-- Header -->
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h5 class="card-title">
                                    <span class="status-badge bg-warning text-dark">{{ $container->code ?? 'N/A' }}</span>
                                </h5>
                                <small class="text-muted">ID: {{ $container->id }}</small>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('containers.show', $container->id) }}">
                                        <i class="bi bi-eye me-2"></i>{{ __('Voir') }}
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('containers.edit', $container->id) }}">
                                        <i class="bi bi-pencil me-2"></i>{{ __('Modifier') }}
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteContainer({{ $container->id }})">
                                        <i class="bi bi-trash me-2"></i>{{ __('Supprimer') }}
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="card-body">
                        <!-- Aperçu du contenant -->
                        <div class="container-preview">
                            <div class="container-visual">
                                <div class="text-center">
                                    <i class="bi bi-archive display-6 text-warning mb-2"></i>
                                    <div class="small text-muted">
                                        <span class="status-badge bg-light text-dark">{{ __('Contenant') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informations -->
                        <div class="mt-3">
                            <div class="info-item">
                                <i class="bi bi-bookshelf text-primary"></i>
                                <small><strong>{{ __('Étagère') }}:</strong> {{ $container->shelf->code ?? 'N/A' }}</small>
                            </div>
                            @if($container->shelf && $container->shelf->room)
                                <div class="info-item">
                                    <i class="bi bi-house-door text-info"></i>
                                    <small><strong>{{ __('Salle') }}:</strong> {{ $container->shelf->room->name ?? 'N/A' }}</small>
                                </div>
                            @endif
                            @if($container->status)
                                <div class="info-item">
                                    <i class="bi bi-flag text-success"></i>
                                    <small><strong>{{ __('Statut') }}:</strong> {{ $container->status->name ?? 'N/A' }}</small>
                                </div>
                            @endif
                            @if($container->property)
                                <div class="info-item">
                                    <i class="bi bi-building text-secondary"></i>
                                    <small><strong>{{ __('Propriété') }}:</strong> {{ $container->property->name ?? 'N/A' }}</small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="position-info">
                                @if($container->shelf)
                                    <i class="bi bi-geo-alt me-1"></i>
                                    Position: {{ $container->x_position ?? '?' }},{{ $container->y_position ?? '?' }},{{ $container->z_position ?? '?' }}
                                @endif
                            </div>
                            <div class="actions-group">
                                <a href="{{ route('containers.show', $container->id) }}" class="btn btn-outline-primary action-btn" title="{{ __('Voir les détails') }}">
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="container-card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-archive display-1 text-muted mb-3"></i>
                        <h5 class="text-muted">{{ __('Aucun contenant trouvé') }}</h5>
                        <p class="text-muted">{{ __('Commencez par créer votre premier contenant d\'archives.') }}</p>
                        <a href="{{ route('containers.create') }}" class="btn btn-primary btn-modern">
                            <i class="bi bi-plus-circle me-2"></i>{{ __('Créer un contenant') }}
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination compacte -->
    @if(method_exists($containers, 'hasPages') && $containers->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $containers->links() }}
        </div>
    @endif
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Confirmer la suppression') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Êtes-vous sûr de vouloir supprimer ce contenant ?') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">{{ __('Supprimer') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
    initializeAnimations();
});

let searchTimeout;
const searchInput = document.getElementById('globalSearch');
const searchResults = document.getElementById('searchResults');
const containerCards = document.querySelectorAll('.container-card');

function initializeSearch() {
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

    // Fermer les résultats en cliquant à l'extérieur
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
}

function filterContainers(query) {
    containerCards.forEach(card => {
        const searchData = card.dataset.search;
        if (searchData && searchData.includes(query)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function showSearchResults(query) {
    const visibleCards = Array.from(containerCards).filter(card => 
        card.style.display !== 'none'
    );

    if (visibleCards.length === 0) {
        searchResults.innerHTML = '<div class="p-3 text-center text-muted">Aucun résultat trouvé</div>';
    } else {
        let html = '';
        visibleCards.slice(0, 5).forEach(card => {
            const code = card.querySelector('.card-title').textContent.trim();
            html += `<div class="search-result-item" onclick="scrollToContainer('${code}')">${code}</div>`;
        });
        searchResults.innerHTML = html;
    }
    
    searchResults.style.display = 'block';
}

function showAllContainers() {
    containerCards.forEach(card => {
        card.style.display = 'block';
    });
}

function scrollToContainer(code) {
    containerCards.forEach(card => {
        const cardCode = card.querySelector('.card-title').textContent.trim();
        if (cardCode === code) {
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            card.classList.add('highlight-container');
            setTimeout(() => {
                card.classList.remove('highlight-container');
            }, 2000);
        }
    });
    
    searchResults.style.display = 'none';
    searchInput.value = '';
}

function deleteContainer(containerId) {
    document.getElementById('confirmDelete').onclick = function() {
        fetch(`/containers/${containerId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        }).then(response => {
            if (response.ok) {
                window.location.reload();
            }
        });
    };
    
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
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
</script>
@endpush
@endsection
