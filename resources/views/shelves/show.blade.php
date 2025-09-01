@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<style>
    /* Realistic Shelf Visualization */
    .shelf-3d-container {
        perspective: 1200px;
        min-height: 500px;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-radius: 12px;
        padding: 30px;
        position: relative;
        overflow: auto;
        border: 2px solid #e2e8f0;
    }
    
    .realistic-shelf {
        position: relative;
        transform-style: preserve-3d;
        margin: 50px auto;
        transition: transform 0.5s ease;
    }
    
    /* Physical shelf structure */
    .shelf-frame {
        position: absolute;
        background: linear-gradient(145deg, #64748b, #475569);
        border-radius: 4px;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);
    }
    
    .vertical-post {
        width: 8px;
        background: linear-gradient(90deg, #64748b, #475569, #64748b);
        box-shadow: 2px 0 8px rgba(0,0,0,0.3);
    }
    
    .horizontal-shelf {
        height: 6px;
        background: linear-gradient(0deg, #6b7280, #9ca3af, #6b7280);
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    
    /* Individual compartments */
    .shelf-compartment {
        position: absolute;
        background: rgba(248, 250, 252, 0.95);
        border: 1px solid #cbd5e1;
        border-radius: 3px;
        transition: all 0.3s ease;
        cursor: pointer;
        backdrop-filter: blur(2px);
    }
    
    .shelf-compartment::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(145deg, 
            rgba(255,255,255,0.1), 
            rgba(0,0,0,0.05));
        border-radius: 3px;
        pointer-events: none;
    }
    
    .shelf-3d-grid {
        transform-style: preserve-3d;
        margin: 40px auto;
        position: relative;
        transition: transform 0.3s ease;
    }
    
    .shelf-face {
        position: absolute;
        border: 2px solid #64748b;
        background: rgba(248, 250, 252, 0.9);
        border-radius: 8px;
    }
    
    .shelf-level {
        position: absolute;
        border: 1px solid #cbd5e1;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 4px;
        backdrop-filter: blur(4px);
    }
    
    .container-slot {
        position: absolute;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 3px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: bold;
        color: #64748b;
    }
    
    .container-slot.occupied {
        background: linear-gradient(45deg, #ef4444, #dc2626);
        color: white;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        border-color: #dc2626;
    }
    
    .container-slot.available {
        background: linear-gradient(45deg, #10b981, #059669);
        color: white;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        border-color: #059669;
    }
    
    .container-slot.reserved {
        background: linear-gradient(45deg, #f59e0b, #d97706);
        color: white;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
        border-color: #d97706;
    }
    
    .container-slot:hover {
        transform: scale(1.1) translateZ(10px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        z-index: 100;
    }
    
    .shelf-controls {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 200;
    }
    
    .view-control {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid #e2e8f0;
        backdrop-filter: blur(8px);
        border-radius: 8px;
        padding: 8px 12px;
        margin: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .view-control:hover {
        background: rgba(59, 130, 246, 0.1);
        border-color: #3b82f6;
    }
    
    .view-control.active {
        background: #3b82f6;
        color: white;
        border-color: #2563eb;
    }
    
    /* Container List for high-capacity shelves */
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
    
    .minimap {
        position: absolute;
        bottom: 20px;
        right: 20px;
        width: 120px;
        height: 80px;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 8px;
        backdrop-filter: blur(8px);
    }
    
    .minimap-grid {
        width: 100%;
        height: 100%;
        display: grid;
        gap: 1px;
        background: #e2e8f0;
    }
    
    .minimap-spot {
        border-radius: 1px;
    }
    
    .legend {
        position: absolute;
        bottom: 20px;
        left: 20px;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px;
        backdrop-filter: blur(8px);
        font-size: 12px;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        margin: 4px 0;
    }
    
    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: 3px;
        margin-right: 8px;
    }
</style>
@endpush

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
        @if($shelf->total_capacity <= 300)
            <!-- 3D Visualization for manageable sizes -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="bi bi-grid-3x3-gap text-primary me-2"></i>
                            Prévisualisation 3D de l'Étagère
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="shelf-3d-container" id="shelf3DContainer">
                            <!-- 3D Shelf will be generated by JavaScript -->
                        </div>
                        
                        <!-- Controls -->
                        <div class="shelf-controls">
                            <button class="view-control active" data-view="isometric">
                                <i class="bi bi-grid-3x3-gap"></i> Isométrique
                            </button>
                            <button class="view-control" data-view="front">
                                <i class="bi bi-front"></i> Face
                            </button>
                            <button class="view-control" data-view="top">
                                <i class="bi bi-view-stacked"></i> Dessus
                            </button>
                        </div>
                        
                        <!-- Legend -->
                        <div class="legend">
                            <div class="legend-item">
                                <div class="legend-color" style="background: linear-gradient(45deg, #ef4444, #dc2626);"></div>
                                Occupé
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background: linear-gradient(45deg, #10b981, #059669);"></div>
                                Disponible
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background: linear-gradient(45deg, #f59e0b, #d97706);"></div>
                                Réservé
                            </div>
                        </div>
                        
                        <!-- Minimap -->
                        <div class="minimap">
                            <div class="minimap-grid" id="minimapGrid">
                                <!-- Minimap spots will be generated -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- List View for high-capacity shelves -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-list-ul text-primary me-2"></i>
                                Conteneurs (Capacité élevée: {{ $shelf->total_capacity }} emplacements)
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
        @endif

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

@push('scripts')
<script>
// Shelf data for JavaScript
const shelfData = {
    face: {{ $shelf->face }},
    ear: {{ $shelf->ear }},
    shelf: {{ $shelf->shelf }},
    total_capacity: {{ $shelf->total_capacity }},
    occupied_spots: {{ $shelf->occupied_spots }},
    containers: @json($shelf->containers->toArray()),
    shelfGrid: @json($shelfGrid)
};

document.addEventListener('DOMContentLoaded', function() {
    @if($shelf->total_capacity <= 300)
        initializeShelf3D();
    @endif
    
    initializeContainerSearch();
    initializeTooltips();
});

@if($shelf->total_capacity <= 300)
function initializeShelf3D() {
    const container = document.getElementById('shelf3DContainer');
    if (!container) return;
    
    let currentView = 'isometric';
    const compartmentWidth = 35;
    const compartmentHeight = 25;
    const compartmentDepth = 30;
    const gap = 3;
    
    function render3DShelf() {
        container.innerHTML = '';
        
        const realisticShelf = document.createElement('div');
        realisticShelf.className = 'realistic-shelf';
        
        // Calculate total dimensions
        const totalWidth = shelfData.ear * (compartmentWidth + gap) + gap;
        const totalHeight = shelfData.shelf * (compartmentHeight + gap) + gap;
        const totalDepth = shelfData.face * (compartmentDepth + gap) + gap;
        
        // Apply view transformation
        let transform = '';
        switch(currentView) {
            case 'isometric':
                transform = 'rotateX(-20deg) rotateY(30deg) scale(0.8)';
                break;
            case 'front':
                transform = 'rotateX(0deg) rotateY(0deg) scale(0.9)';
                break;
            case 'top':
                transform = 'rotateX(-85deg) rotateY(0deg) scale(0.8)';
                break;
        }
        
        realisticShelf.style.transform = transform;
        realisticShelf.style.width = totalWidth + 'px';
        realisticShelf.style.height = totalHeight + 'px';
        
        container.appendChild(realisticShelf);
        updateMinimap();
    }
    
    function createShelfStructure(parent) {
        const totalWidth = shelfData.ear * (compartmentWidth + gap);
        const totalHeight = shelfData.shelf * (compartmentHeight + gap);
        const totalDepth = shelfData.face * (compartmentDepth + gap);
        
        // Create vertical posts (corners)
        for (let corner = 0; corner < 4; corner++) {
            const post = document.createElement('div');
            post.className = 'shelf-frame vertical-post';
            
            const x = (corner % 2) * totalWidth;
            const z = Math.floor(corner / 2) * totalDepth;
            
            post.style.left = x + 'px';
            post.style.top = '0px';
            post.style.height = totalHeight + 'px';
            post.style.transform = `translateZ(${z}px)`;
            
            parent.appendChild(post);
        }
        
        // Create horizontal shelves
        for (let shelfLevel = 0; shelfLevel <= shelfData.shelf; shelfLevel++) {
            for (let face = 0; face < shelfData.face; face++) {
                const horizontalShelf = document.createElement('div');
                horizontalShelf.className = 'shelf-frame horizontal-shelf';
                
                const y = shelfLevel * (compartmentHeight + gap);
                const z = face * (compartmentDepth + gap);
                
                horizontalShelf.style.left = '0px';
                horizontalShelf.style.top = y + 'px';
                horizontalShelf.style.width = totalWidth + 'px';
                horizontalShelf.style.transform = `translateZ(${z}px)`;
                
                parent.appendChild(horizontalShelf);
                
                // Add depth shelves
                const depthShelf = document.createElement('div');
                depthShelf.className = 'shelf-frame horizontal-shelf';
                depthShelf.style.left = '0px';
                depthShelf.style.top = y + 'px';
                depthShelf.style.width = totalWidth + 'px';
                depthShelf.style.transform = `translateZ(${z + compartmentDepth}px)`;
                
                parent.appendChild(depthShelf);
            }
        }
    }
    
    function createCompartments(parent) {
        // Create compartments for containers
        for (let face = 1; face <= shelfData.face; face++) {
            for (let ear = 1; ear <= shelfData.ear; ear++) {
                for (let shelfLevel = 1; shelfLevel <= shelfData.shelf; shelfLevel++) {
                    const compartment = document.createElement('div');
                    compartment.className = 'shelf-compartment';
                    
                    // Position the compartment
                    const x = (ear - 1) * (compartmentWidth + gap) + gap;
                    const y = (shelfData.shelf - shelfLevel) * (compartmentHeight + gap) + gap;
                    const z = (face - 1) * (compartmentDepth + gap) + gap;
                    
                    compartment.style.left = x + 'px';
                    compartment.style.top = y + 'px';
                    compartment.style.width = compartmentWidth + 'px';
                    compartment.style.height = compartmentHeight + 'px';
                    compartment.style.transform = `translateZ(${z}px)`;
                    
                    // Check if this position has a container
                    const container = shelfData.shelfGrid[face] && 
                                    shelfData.shelfGrid[face][ear] && 
                                    shelfData.shelfGrid[face][ear][shelfLevel];
                    
                    if (container) {
                        compartment.classList.add('occupied');
                        compartment.innerHTML = `
                            <div style="padding: 3px; font-size: 10px; font-weight: bold; text-align: center; color: white; background: rgba(239, 68, 68, 0.9); border-radius: 2px; margin: 2px;">
                                ${container.code.substring(0, 6)}
                                <br><small>${container.status || 'N/A'}</small>
                            </div>
                        `;
                        compartment.title = `Container: ${container.code}\nStatut: ${container.status || 'N/A'}`;
                    } else {
                        compartment.classList.add('available');
                        compartment.innerHTML = `
                            <div style="padding: 3px; font-size: 12px; text-align: center; color: #059669; display: flex; align-items: center; justify-content: center; height: 100%; background: rgba(16, 185, 129, 0.1); border-radius: 2px; border: 2px dashed #059669;">
                                <i class="bi bi-plus-lg"></i>
                            </div>
                        `;
                        compartment.title = `Position disponible\nFace: ${face}, Travée: ${ear}, Niveau: ${shelfLevel}\nCliquez pour ajouter un conteneur`;
                    }
                    
                    // Add click handler
                    compartment.addEventListener('click', function() {
                        if (container) {
                            showContainerDetails(container);
                        } else {
                            showAddContainerDialog(face, ear, shelfLevel);
                        }
                    });
                    
                    // Add hover effects
                    compartment.addEventListener('mouseenter', function() {
                        this.style.transform = `translateZ(${z + 5}px) scale(1.05)`;
                        this.style.boxShadow = '0 5px 20px rgba(0,0,0,0.2)';
                        this.style.zIndex = '100';
                    });
                    
                    compartment.addEventListener('mouseleave', function() {
                        this.style.transform = `translateZ(${z}px) scale(1)`;
                        this.style.boxShadow = 'none';
                        this.style.zIndex = '1';
                    });
                    
                    parent.appendChild(compartment);
                }
            }
        }
    }
    
    function updateMinimap() {
        const minimap = document.getElementById('minimapGrid');
        if (!minimap) return;
        
        minimap.innerHTML = '';
        minimap.style.gridTemplateColumns = `repeat(${Math.min(10, shelfData.ear)}, 1fr)`;
        
        const totalVisible = Math.min(50, shelfData.total_capacity);
        for (let i = 0; i < totalVisible; i++) {
            const spot = document.createElement('div');
            spot.className = 'minimap-spot';
            
            if (i < shelfData.occupied_spots) {
                spot.style.background = '#ef4444';
            } else {
                spot.style.background = '#10b981';
            }
            
            minimap.appendChild(spot);
        }
    }
    
    // View control handlers
    document.querySelectorAll('.view-control').forEach(control => {
        control.addEventListener('click', function() {
            document.querySelectorAll('.view-control').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            currentView = this.dataset.view;
            render3DShelf();
        });
    });
    
    // Initial render
    render3DShelf();
}
@endif

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
    // Highlight container in 3D view
    const slots = document.querySelectorAll('.container-slot');
    slots.forEach(slot => {
        if (slot.textContent.includes(containerCode.substring(0, 4))) {
            slot.style.animation = 'pulse 2s infinite';
            setTimeout(() => {
                slot.style.animation = '';
            }, 4000);
        }
    });
    
    // Show toast notification
    showToast(`Container ${containerCode} localisé`, 'success');
}

function showContainerDetails(container) {
    const modal = new bootstrap.Modal(document.createElement('div'));
    // Implementation would show container details modal
    console.log('Show details for container:', container);
}

function showAddContainerDialog(face, ear, shelfLevel) {
    // Show a modal for quick container creation
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle text-primary"></i>
                        Ajouter un conteneur
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-geo-alt"></i>
                        <strong>Position :</strong> Face ${face}, Travée ${ear}, Niveau ${shelfLevel}
                        <br><small>Étagère : {{ $shelf->code }}</small>
                    </div>
                    
                    <form id="quickContainerForm">
                        <div class="mb-3">
                            <label class="form-label">Code du conteneur *</label>
                            <input type="text" class="form-control" id="containerCode" required 
                                   placeholder="Ex: BOX${face}${ear}${shelfLevel}-001">
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <label class="form-label">Type</label>
                                <select class="form-select" id="containerType">
                                    <option value="standard">Standard</option>
                                    <option value="large">Grande</option>
                                    <option value="small">Petite</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Statut</label>
                                <select class="form-select" id="containerStatus">
                                    <option value="available">Disponible</option>
                                    <option value="occupied">Occupé</option>
                                    <option value="reserved">Réservé</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <button type="button" class="btn btn-success" onclick="createQuickContainer(${face}, ${ear}, ${shelfLevel})">
                        <i class="bi bi-check-circle"></i>
                        Créer le conteneur
                    </button>
                    <a href="{{ route('containers.create') }}?shelf_id={{ $shelf->id }}&position=${face}-${ear}-${shelfLevel}" 
                       class="btn btn-primary">
                        <i class="bi bi-gear"></i>
                        Création avancée
                    </a>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
    
    // Auto-focus on code input
    modal.addEventListener('shown.bs.modal', function() {
        document.getElementById('containerCode').focus();
    });
    
    // Remove modal from DOM when closed
    modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
    });
}

function createQuickContainer(face, ear, shelfLevel) {
    const code = document.getElementById('containerCode').value.trim();
    const type = document.getElementById('containerType').value;
    const status = document.getElementById('containerStatus').value;
    
    if (!code) {
        showToast('Le code du conteneur est requis', 'warning');
        return;
    }
    
    // Show loading
    const createButton = event.target;
    const originalText = createButton.innerHTML;
    createButton.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Création...';
    createButton.disabled = true;
    
    // Simulate API call (replace with actual AJAX call)
    setTimeout(() => {
        // Add to visual grid (simulation)
        const newContainer = {
            id: Date.now(),
            code: code,
            status: status,
            type: type
        };
        
        // Update the shelf grid data
        if (!shelfData.shelfGrid[face]) shelfData.shelfGrid[face] = {};
        if (!shelfData.shelfGrid[face][ear]) shelfData.shelfGrid[face][ear] = {};
        shelfData.shelfGrid[face][ear][shelfLevel] = newContainer;
        
        // Re-render the 3D view
        initializeShelf3D();
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.querySelector('.modal'));
        modal.hide();
        
        showToast(`Conteneur ${code} créé avec succès à la position Face ${face}, Travée ${ear}, Niveau ${shelfLevel}`, 'success');
        
        // In real implementation, you would make an AJAX call like this:
        /*
        fetch('{{ route("containers.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                code: code,
                shelf_id: {{ $shelf->id }},
                position_face: face,
                position_ear: ear,
                position_shelf: shelfLevel,
                type: type,
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the visual representation
                // Reload the page or update the data
                location.reload();
            } else {
                showToast('Erreur lors de la création: ' + data.message, 'error');
            }
        });
        */
    }, 1000);
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
@endpush
@endsection
