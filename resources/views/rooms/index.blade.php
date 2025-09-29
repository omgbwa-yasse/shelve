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

/* Layout optimisé - Utilise toute la largeur */
.compact-container {
  
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
.room-card {
    background: #ffffff;
    border-radius: var(--radius);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
    height: 100%;
    overflow: hidden;
}

.room-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--info-color);
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

/* Room preview */
.room-preview {
    background: linear-gradient(135deg, #e0f2fe 0%, #b3e5fc 100%);
    border-radius: var(--radius);
    padding: 0.75rem;
    margin: 0.5rem 0;
    border: 1px solid #29b6f6;
}

.room-visual {
    height: 80px;
    background: linear-gradient(135deg, #e1f5fe 0%, #b3e5fc 100%);
    border: 2px dashed #29b6f6;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}

.room-card:hover .room-visual {
    background: linear-gradient(135deg, #e1f5fe 0%, #ffcdd2 100%);
    border-color: #f44336;
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
.highlight-room {
    animation: highlight-pulse 2s ease-in-out;
}

@keyframes highlight-pulse {
    0%, 100% { 
        box-shadow: 0 0 0 0 rgba(41, 182, 246, 0.4); 
    }
    50% { 
        box-shadow: 0 0 0 8px rgba(41, 182, 246, 0); 
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

/* Capacity indicator */
.capacity-info {
    background: var(--light-bg);
    border-radius: var(--radius);
    padding: 0.375rem;
    font-size: 0.625rem;
    color: var(--secondary-color);
}

/* Occupancy bar */
.occupancy-bar {
    width: 100%;
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
    margin-top: 0.25rem;
}

.occupancy-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--success-color) 0%, var(--warning-color) 50%, var(--danger-color) 100%);
    border-radius: 3px;
    transition: width 0.3s ease;
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
                    <i class="bi bi-house-door text-info"></i>
                    {{ __('Salles d\'archives') }}
                </h1>
                <p class="page-subtitle">Gestion et organisation de vos salles de dépôt</p>
            </div>
            <div class="d-flex gap-2">
                <div class="search-container">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" class="form-control search-input" id="globalSearch" 
                           placeholder="Rechercher par nom, bâtiment, étage...">
                    <div class="search-results-dropdown" id="searchResults"></div>
                </div>
                <a href="{{ route('rooms.create') }}" class="btn btn-primary btn-modern">
                    <i class="bi bi-plus-circle me-2"></i>{{ __('Nouvelle') }}
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
                <i class="bi bi-house-door me-1"></i>{{ __('Salles') }}
            </li>
        </ol>
    </nav>

    <!-- Statistiques compactes -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number text-primary">{{ $rooms->count() }}</div>
            <div class="stat-label">{{ __('Salles Total') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-success">{{ $rooms->where('visibility', 'public')->count() }}</div>
            <div class="stat-label">{{ __('Visibles') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-warning">{{ $rooms->sum('shelves_count') }}</div>
            <div class="stat-label">{{ __('Étagères Total') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-info">{{ $rooms->unique('floor_id')->count() }}</div>
            <div class="stat-label">{{ __('Étages Utilisés') }}</div>
        </div>
    </div>

    <!-- Grille des salles -->
    <div class="row g-3" id="roomList">
        @forelse ($rooms as $room)
            <div class="col-xl-4 col-lg-6 col-md-12 mb-3 fade-in-up" data-search="{{ strtolower($room->name ?? '') }} {{ strtolower($room->description ?? '') }} {{ strtolower($room->floor->name ?? '') }} {{ strtolower($room->floor->building->name ?? '') }}">
                <div class="room-card">
                    <!-- Header -->
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h5 class="card-title">
                                    <span class="status-badge bg-info text-white">{{ $room->name ?? 'N/A' }}</span>
                                </h5>
                                <small class="text-muted">ID: {{ $room->id }}</small>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('rooms.show', $room->id) }}">
                                        <i class="bi bi-eye me-2"></i>{{ __('Voir') }}
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('rooms.edit', $room->id) }}">
                                        <i class="bi bi-pencil me-2"></i>{{ __('Modifier') }}
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteRoom({{ $room->id }})">
                                        <i class="bi bi-trash me-2"></i>{{ __('Supprimer') }}
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="card-body">
                        <!-- Aperçu de la salle -->
                        <div class="room-preview">
                            <div class="room-visual">
                                <div class="text-center">
                                    <i class="bi bi-house-door display-6 text-info mb-2"></i>
                                    <div class="small text-muted">
                                        <span class="status-badge bg-light text-dark">{{ __('Salle') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informations -->
                        <div class="mt-3">
                            @if($room->description)
                                <div class="info-item">
                                    <i class="bi bi-card-text text-secondary"></i>
                                    <small><strong>{{ __('Description') }}:</strong> {{ Str::limit($room->description, 50) }}</small>
                                </div>
                            @endif
                            @if($room->floor)
                                <div class="info-item">
                                    <i class="bi bi-layers text-primary"></i>
                                    <small><strong>{{ __('Étage') }}:</strong> {{ $room->floor->name ?? 'N/A' }}</small>
                                </div>
                            @endif
                            @if($room->floor && $room->floor->building)
                                <div class="info-item">
                                    <i class="bi bi-building text-success"></i>
                                    <small><strong>{{ __('Bâtiment') }}:</strong> {{ $room->floor->building->name ?? 'N/A' }}</small>
                                </div>
                            @endif
                            <div class="info-item">
                                <i class="bi bi-bookshelf text-warning"></i>
                                <small><strong>{{ __('Étagères') }}:</strong> {{ $room->shelves_count ?? 0 }}</small>
                            </div>
                            <div class="info-item">
                                <i class="bi bi-eye text-info"></i>
                                <small><strong>{{ __('Visibilité') }}:</strong> 
                                    @if($room->is_visible)
                                        <span class="text-success">{{ __('Visible') }}</span>
                                    @else
                                        <span class="text-muted">{{ __('Masquée') }}</span>
                                    @endif
                                </small>
                            </div>
                        </div>

                        <!-- Barre d'occupation -->
                        @if($room->shelves_count > 0)
                            <div class="mt-2">
                                <div class="capacity-info">
                                    <small><strong>{{ __('Occupation') }}:</strong> {{ $room->shelves_count ?? 0 }} étagères</small>
                                    <div class="occupancy-bar">
                                        @php
                                            $occupancyPercent = min(100, ($room->shelves_count ?? 0) * 10);
                                        @endphp
                                        <div class="occupancy-fill" style="width: {{ $occupancyPercent }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Footer -->
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="capacity-info">
                                <i class="bi bi-calendar me-1"></i>
                                Créée le {{ $room->created_at ? $room->created_at->format('d/m/Y') : 'N/A' }}
                            </div>
                            <div class="actions-group">
                                <a href="{{ route('rooms.show', $room->id) }}" class="btn btn-outline-primary action-btn" title="{{ __('Voir les détails') }}">
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="room-card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-house-door display-1 text-muted mb-3"></i>
                        <h5 class="text-muted">{{ __('Aucune salle trouvée') }}</h5>
                        <p class="text-muted">{{ __('Commencez par créer votre première salle de dépôt.') }}</p>
                        <a href="{{ route('rooms.create') }}" class="btn btn-primary btn-modern">
                            <i class="bi bi-plus-circle me-2"></i>{{ __('Créer une salle') }}
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pas de pagination nécessaire pour une collection simple -->
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
                <p>{{ __('Êtes-vous sûr de vouloir supprimer cette salle ?') }}</p>
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
const roomCards = document.querySelectorAll('.room-card');

function initializeSearch() {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.toLowerCase().trim();
        
        searchTimeout = setTimeout(() => {
            if (query === '') {
                searchResults.style.display = 'none';
                showAllRooms();
                return;
            }
            
            filterRooms(query);
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

function filterRooms(query) {
    roomCards.forEach(card => {
        const searchData = card.dataset.search;
        if (searchData && searchData.includes(query)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function showSearchResults(query) {
    const visibleCards = Array.from(roomCards).filter(card => 
        card.style.display !== 'none'
    );

    if (visibleCards.length === 0) {
        searchResults.innerHTML = '<div class="p-3 text-center text-muted">Aucun résultat trouvé</div>';
    } else {
        let html = '';
        visibleCards.slice(0, 5).forEach(card => {
            const name = card.querySelector('.card-title').textContent.trim();
            html += `<div class="search-result-item" onclick="scrollToRoom('${name}')">${name}</div>`;
        });
        searchResults.innerHTML = html;
    }
    
    searchResults.style.display = 'block';
}

function showAllRooms() {
    roomCards.forEach(card => {
        card.style.display = 'block';
    });
}

function scrollToRoom(name) {
    roomCards.forEach(card => {
        const cardName = card.querySelector('.card-title').textContent.trim();
        if (cardName === name) {
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            card.classList.add('highlight-room');
            setTimeout(() => {
                card.classList.remove('highlight-room');
            }, 2000);
        }
    });
    
    searchResults.style.display = 'none';
    searchInput.value = '';
}

function deleteRoom(roomId) {
    document.getElementById('confirmDelete').onclick = function() {
        fetch(`/rooms/${roomId}`, {
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
