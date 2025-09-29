@extends('layouts.app')


<style>
    /* Container List styles */
    .container-list {
        max-height: 500px;
        overflow-y: auto;
    }

    .container-item {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px;
        margin: 8px 0;
        background: white;
        transition: all 0.2s ease;
    }

    .container-item:hover {
        border-color: #3b82f6;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
    }

    .container-item.occupied {
        border-left: 4px solid #ef4444;
        background: #fef2f2;
    }

    .container-item.available {
        border-left: 4px solid #10b981;
        background: #f0fdf4;
    }

    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }

    .search-highlight {
        background: #fef3c7;
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
    }
</style>


@section('content')
<div class="container-fluid">
    <!-- Hierarchical Navigation -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-light p-3 rounded">
            <li class="breadcrumb-item">
                <a href="{{ route('buildings.index') }}" class="text-decoration-none">
                    <i class="bi bi-building text-primary"></i> {{ $shelf->room->floor->building->name ?? 'Bâtiment' }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('rooms.index') }}" class="text-decoration-none">
                    <i class="bi bi-layers text-info"></i> {{ $shelf->room->floor->name ?? 'Étage' }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('rooms.show', $shelf->room->id) }}" class="text-decoration-none">
                    <i class="bi bi-door-open text-success"></i> {{ $shelf->room->name }}
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <i class="bi bi-bookshelf text-warning"></i> {{ $shelf->code }}
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <h1 class="display-5 mb-2">
                <i class="bi bi-bookshelf text-primary"></i> 
                Étagère {{ $shelf->code }}
            </h1>
            @if($shelf->observation)
                <p class="text-muted">{{ $shelf->observation }}</p>
            @endif
        </div>
        <div class="col-lg-4 text-lg-end">
            <div class="btn-group" role="group">
                <a href="{{ route('shelves.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
                <a href="{{ route('shelves.edit', $shelf->id) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil"></i> Modifier
                </a>
                <a href="{{ route('containers.create') }}?shelf_id={{ $shelf->id }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Ajouter Conteneur
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="display-6 mb-2">{{ $shelf->total_capacity }}</h3>
                    <div class="fw-bold">Capacité Totale</div>
                    <small class="opacity-75">{{ $shelf->face }}F × {{ $shelf->ear }}T × {{ $shelf->shelf }}N</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="display-6 mb-2">{{ $shelf->occupied_spots }}</h3>
                    <div class="fw-bold">Emplacements Occupés</div>
                    <small class="opacity-75">{{ $shelf->containers->count() }} conteneurs</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="display-6 mb-2">{{ $shelf->available_spots }}</h3>
                    <div class="fw-bold">Places Disponibles</div>
                    <small class="opacity-75">{{ round($shelf->available_spots / $shelf->total_capacity * 100, 1) }}% libre</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <h3 class="display-6 mb-2">{{ $shelf->volumetry_ml }}</h3>
                    <div class="fw-bold">Volumétrie (ml)</div>
                    <small class="opacity-75">{{ $shelf->shelf_length }}cm longueur</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- List View for all shelves -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul text-primary me-2"></i>
                            Conteneurs ({{ $shelf->total_capacity }} emplacements)
                        </h5>
                        <div class="input-group" style="width: 300px;">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="containerSearch" placeholder="Rechercher un conteneur...">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Summary Bar -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold">Occupation actuelle</span>
                            <span class="text-muted">{{ $shelf->occupied_spots }}/{{ $shelf->total_capacity }} ({{ $shelf->occupancy_percentage }}%)</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                 style="width: {{ $shelf->occupancy_percentage }}%"></div>
                        </div>
                    </div>

                    <div class="container-list" id="containerList">
                        @forelse($shelf->containers as $index => $container)
                            <div class="container-item occupied" data-container-code="{{ $container->code }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="status-indicator" style="background: #ef4444;"></div>
                                        <div>
                                            <div class="fw-bold">{{ $container->code }}</div>
                                            <small class="text-muted">
                                                Position approximative: {{ $index + 1 }} |
                                                Statut: {{ $container->status->name ?? 'N/A' }} |
                                                Type: {{ $container->property->name ?? 'Standard' }}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary"
                                                onclick="locateContainer('{{ $container->code }}')">
                                            <i class="bi bi-geo-alt"></i>
                                        </button>
                                        <a href="{{ route('containers.show', $container->id) }}"
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="bi bi-inbox display-4 text-muted mb-3"></i>
                                <p class="text-muted">Aucun conteneur dans cette étagère</p>
                            </div>
                        @endforelse

                        <!-- Show available spots -->
                        @for($i = $shelf->occupied_spots; $i < min($shelf->total_capacity, $shelf->occupied_spots + 20); $i++)
                            <div class="container-item available">
                                <div class="d-flex align-items-center">
                                    <div class="status-indicator" style="background: #10b981;"></div>
                                    <div>
                                        <div class="text-muted fw-bold">Emplacement {{ $i + 1 }} - Disponible</div>
                                        <small class="text-muted">Prêt pour un nouveau conteneur</small>
                                    </div>
                                </div>
                            </div>
                        @endfor

                        @if($shelf->available_spots > 20)
                            <div class="text-center py-3 border-top">
                                <p class="text-muted mb-2">
                                    ... et {{ $shelf->available_spots - 20 }} autres emplacements disponibles
                                </p>
                                <button class="btn btn-outline-primary btn-sm" onclick="showAllAvailable()">
                                    <i class="bi bi-plus-circle me-2"></i>Afficher tous les emplacements
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar with Details and Actions -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle text-info me-2"></i>
                        Informations Techniques
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Code:</strong></td>
                            <td>{{ $shelf->code }}</td>
                        </tr>
                        <tr>
                            <td><strong>Faces:</strong></td>
                            <td>{{ $shelf->face }}</td>
                        </tr>
                        <tr>
                            <td><strong>Travées:</strong></td>
                            <td>{{ $shelf->ear }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tablettes:</strong></td>
                            <td>{{ $shelf->shelf }}</td>
                        </tr>
                        <tr>
                            <td><strong>Longueur:</strong></td>
                            <td>{{ $shelf->shelf_length }} cm</td>
                        </tr>
                        <tr>
                            <td><strong>Salle:</strong></td>
                            <td>{{ $shelf->room->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Créé le:</strong></td>
                            <td>{{ $shelf->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning text-warning me-2"></i>
                        Actions Rapides
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('containers.create') }}?shelf_id={{ $shelf->id }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Ajouter un Conteneur
                        </a>
                        <a href="{{ route('containers.index') }}?shelf_id={{ $shelf->id }}" class="btn btn-outline-secondary">
                            <i class="bi bi-box me-2"></i>Voir tous les Conteneurs
                        </a>
                        <button class="btn btn-outline-info" onclick="optimizeShelf()">
                            <i class="bi bi-arrows-expand me-2"></i>Optimiser l'Espace
                        </button>
                        <hr>
                        <a href="{{ route('shelves.edit', $shelf->id) }}" class="btn btn-outline-warning">
                            <i class="bi bi-pencil me-2"></i>Modifier l'Étagère
                        </a>
                        <form action="{{ route('shelves.destroy', $shelf->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100" 
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette étagère et tous ses conteneurs ?')">
                                <i class="bi bi-trash me-2"></i>Supprimer l'Étagère
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Shelf data for JavaScript
const shelfData = {
    face: {{ $shelf->face }},
    ear: {{ $shelf->ear }},
    shelf: {{ $shelf->shelf }},
    total_capacity: {{ $shelf->total_capacity }},
    occupied_spots: {{ $shelf->occupied_spots }},
    containers: @json($shelf->containers->toArray())
};

document.addEventListener('DOMContentLoaded', function() {
    initializeContainerSearch();
    initializeTooltips();
});

function initializeContainerSearch() {
    const searchInput = document.getElementById('containerSearch');
    if (!searchInput) return;
    
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const containers = document.querySelectorAll('.container-item');
        
        containers.forEach(container => {
            const code = container.dataset.containerCode;
            const text = container.textContent.toLowerCase();
            
            if (query === '' || text.includes(query)) {
                container.style.display = 'block';
                container.classList.remove('search-highlight');
            } else {
                container.style.display = 'none';
            }
            
            if (query !== '' && text.includes(query)) {
                container.classList.add('search-highlight');
            }
        });
    });
}

function initializeTooltips() {
    const tooltips = document.querySelectorAll('[title]');
    tooltips.forEach(el => {
        new bootstrap.Tooltip(el);
    });
}

function locateContainer(containerCode) {
    // Highlight container in the list
    const containers = document.querySelectorAll('.container-item[data-container-code]');
    containers.forEach(container => {
        if (container.dataset.containerCode === containerCode) {
            container.scrollIntoView({ behavior: 'smooth', block: 'center' });
            container.style.animation = 'pulse 2s infinite';
            setTimeout(() => {
                container.style.animation = '';
            }, 4000);
        }
    });

    // Show toast notification
    showToast(`Container ${containerCode} localisé dans la liste`, 'success');
}

function showContainerDetails(container) {
    const modal = new bootstrap.Modal(document.createElement('div'));
    // Implementation would show container details modal
    console.log('Show details for container:', container);
}



function optimizeShelf() {
    showToast('Fonctionnalité d\'optimisation en développement', 'info');
}

function showAllAvailable() {
    // Implementation to show all available spots
    showToast('Affichage de tous les emplacements...', 'info');
}

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>

@endsection
