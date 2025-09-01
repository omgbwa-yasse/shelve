@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">{{ __('Salles') }}</h1>

            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="{{ route('report.dashboard') }}">{{ __('Accueil') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('buildings.index') }}">{{ __('Dépôts') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Salles') }}</li>
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
                                       placeholder="Rechercher par nom, code, description, bâtiment..." autocomplete="off">
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
                                <div class="col-6">
                                    <div class="text-success">
                                        <h4>{{ $rooms->where('visibility', 'public')->count() }}</h4>
                                        <small>{{ __('Publiques') }}</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-danger">
                                        <h4>{{ $rooms->where('visibility', 'private')->count() }}</h4>
                                        <small>{{ __('Privées') }}</small>
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
                    <span class="text-muted">{{ $rooms->count() }} salle(s) trouvée(s)</span>
                </div>
                <div>
                    <a href="{{ route('rooms.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> {{ __('Nouvelle salle') }}
                    </a>
                </div>
            </div>

            <!-- Rooms Grid -->
            <div id="roomList" class="row">
                @forelse ($rooms as $room)
                    <div class="col-xl-6 col-lg-12 mb-4 room-card" data-search="{{ strtolower($room->code ?? '') }} {{ strtolower($room->name ?? '') }} {{ strtolower($room->description ?? '') }} {{ strtolower($room->floor->building->name ?? '') }} {{ strtolower($room->floor->name ?? '') }} {{ strtolower($room->type ?? '') }}">
                        <div class="card h-100 shadow-sm border-left-primary">
                            <div class="card-body d-flex flex-column">
                                <!-- Room Header -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-1">
                                            <span class="badge bg-primary me-2">{{ $room->code ?? 'N/A' }}</span>
                                            <strong>{{ $room->name ?? 'Sans nom' }}</strong>
                                        </h5>
                                        <small class="text-muted">ID: {{ $room->id }}</small>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="{{ route('rooms.show', $room->id) }}"><i class="bi bi-eye"></i> {{ __('Voir') }}</a></li>
                                            <li><a class="dropdown-item" href="{{ route('rooms.edit', $room->id) }}"><i class="bi bi-pencil"></i> {{ __('Modifier') }}</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteRoom({{ $room->id }})"><i class="bi bi-trash"></i> {{ __('Supprimer') }}</a></li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Room Preview -->
                                <div class="room-preview mb-3 p-3 bg-light rounded">
                                    <div class="room-visual d-flex align-items-center justify-content-center" style="height: 120px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 2px dashed #dee2e6; border-radius: 8px;">
                                        <div class="text-center">
                                            <i class="bi bi-house-door display-4 text-secondary mb-2"></i>
                                            <div class="small text-muted">
                                                @if($room->type === 'archives')
                                                    <span class="badge bg-primary">Salle d'archives</span>
                                                @elseif($room->type === 'producer')
                                                    <span class="badge bg-info">Local tampon</span>
                                                @else
                                                    <span class="badge bg-secondary">Type non défini</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Room Info -->
                                <div class="room-info flex-grow-1">
                                    <div class="row g-2 mb-2">
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-building me-2 text-primary"></i>
                                                <small><strong>{{ __('Bâtiment') }}:</strong> {{ $room->floor->building->name ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-layers me-2 text-info"></i>
                                                <small><strong>{{ __('Niveau') }}:</strong> {{ $room->floor->name ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($room->description)
                                        <div class="mb-2">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-file-text me-2 text-muted mt-1"></i>
                                                <small class="text-muted">{{ Str::limit($room->description, 100) }}</small>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Room Footer -->
                                <div class="room-footer mt-3 pt-3 border-top">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="visibility-status">
                                            <span class="badge bg-{{ $room->visibility == 'public' ? 'success' : ($room->visibility == 'private' ? 'danger' : 'warning') }}">
                                                <i class="bi bi-{{ $room->visibility == 'public' ? 'unlock' : ($room->visibility == 'private' ? 'lock' : 'arrow-repeat') }}"></i>
                                                @switch($room->visibility)
                                                    @case('public')
                                                        {{ __('Public') }}
                                                        @break
                                                    @case('private')
                                                        {{ __('Privé') }}
                                                        @break
                                                    @case('inherit')
                                                        {{ __('Hériter') }}
                                                        @break
                                                    @default
                                                        {{ __('Non défini') }}
                                                @endswitch
                                            </span>
                                        </div>
                                        <div class="action-buttons">
                                            <a href="{{ route('rooms.show', $room->id) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Voir les détails') }}">
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
                                <i class="bi bi-house-door display-1 text-muted mb-3"></i>
                                <h5 class="text-muted">{{ __('Aucune salle trouvée') }}</h5>
                                <p class="text-muted">{{ __('Commencez par créer votre première salle.') }}</p>
                                <a href="{{ route('rooms.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> {{ __('Créer une salle') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
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
                {{ __('Êtes-vous sûr de vouloir supprimer cette salle ? Cette action ne peut pas être annulée.') }}
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
const roomCards = document.querySelectorAll('.room-card');

// Search functionality
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

// Hide search results when clicking outside
document.addEventListener('click', function(e) {
    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.style.display = 'none';
    }
});

function filterRooms(query) {
    roomCards.forEach(card => {
        const searchText = card.getAttribute('data-search');
        if (searchText.includes(query)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function showAllRooms() {
    roomCards.forEach(card => {
        card.style.display = 'block';
    });
}

function showSearchResults(query) {
    const visibleCards = Array.from(roomCards).filter(card => card.style.display !== 'none');
    
    if (visibleCards.length === 0) {
        searchResults.innerHTML = '<div class="p-3 text-muted">Aucun résultat trouvé</div>';
    } else {
        const results = visibleCards.slice(0, 5).map(card => {
            const title = card.querySelector('.card-title').textContent.trim();
            const roomId = card.querySelector('a[href*="rooms"]').href.split('/').pop();
            return `<a href="{{ url('rooms') }}/${roomId}" class="dropdown-item"><i class="bi bi-house-door me-2"></i>${title}</a>`;
        }).join('');
        
        searchResults.innerHTML = results + 
            (visibleCards.length > 5 ? '<div class="p-2 text-muted small text-center">Et ' + (visibleCards.length - 5) + ' autres...</div>' : '');
    }
    
    searchResults.style.display = 'block';
}

function deleteRoom(roomId) {
    const form = document.getElementById('deleteForm');
    form.action = `{{ url('rooms') }}/${roomId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<style>
.border-left-primary {
    border-left: 4px solid #007bff !important;
}

.room-preview {
    transition: all 0.3s ease;
}

.card:hover .room-preview {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
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
</style>
@endsection
