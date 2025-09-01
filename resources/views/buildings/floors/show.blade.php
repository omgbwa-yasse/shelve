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
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
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

/* Cards harmonisées */
.info-card {
    background: #ffffff;
    border-radius: var(--radius);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
    height: 100%;
    overflow: hidden;
}

.info-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
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

/* Floor preview */
.floor-preview {
    background: linear-gradient(135deg, #e0f2fe 0%, #b3e5fc 100%);
    border-radius: var(--radius);
    padding: 1rem;
    margin: 1rem 0;
    border: 1px solid #81d4fa;
    text-align: center;
    transition: var(--transition);
}

.floor-preview:hover {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* Info items */
.info-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    padding: 0.5rem;
    background: var(--light-bg);
    border-radius: var(--radius);
    border-left: 3px solid var(--info-color);
}

.info-item:last-child {
    margin-bottom: 0;
}

.info-icon {
    width: 20px;
    text-align: center;
    margin-top: 0.125rem;
    color: var(--info-color);
}

.info-content {
    flex: 1;
}

.info-label {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.125rem;
    font-size: 0.875rem;
}

.info-value {
    color: var(--secondary-color);
    font-size: 0.875rem;
}

/* Rooms section */
.rooms-section {
    background: #ffffff;
    border-radius: var(--radius);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.rooms-header {
    background: linear-gradient(135deg, var(--info-color) 0%, #0ea5e9 100%);
    color: white;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.rooms-count {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 600;
}

/* Room items */
.room-item {
    border-bottom: 1px solid var(--border-color);
    transition: var(--transition);
    background: #ffffff;
}

.room-item:hover {
    background: var(--light-bg);
    transform: translateX(4px);
}

.room-item:last-child {
    border-bottom: none;
}

.room-content {
    padding: 0.75rem 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.room-info {
    flex: 1;
}

.room-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.room-description {
    color: var(--secondary-color);
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.room-stats {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
    color: var(--secondary-color);
}

.room-actions {
    display: flex;
    gap: 0.5rem;
}

/* Boutons harmonisés */
.btn-modern {
    border-radius: var(--radius);
    font-weight: 500;
    padding: 0.5rem 1rem;
    transition: var(--transition);
    border: 1px solid transparent;
    font-size: 0.875rem;
}

.btn-modern:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.action-btn {
    padding: 0.375rem 0.75rem;
    border-radius: var(--radius);
    font-size: 0.875rem;
    transition: var(--transition);
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 2rem 1rem;
    color: var(--secondary-color);
}

.empty-state i {
    font-size: 2.5rem;
    margin-bottom: 0.75rem;
    opacity: 0.5;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        padding: 0.75rem;
    }
    
    .page-title {
        font-size: 1.25rem;
    }
    
    .room-content {
        flex-direction: column;
        align-items: stretch;
    }
    
    .room-actions {
        justify-content: flex-end;
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
</style>
@endpush

@section('content')
<div class="compact-container">
    <!-- Header moderne -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-layers text-warning"></i>
                    {{ $floor->name ?? 'Niveau sans nom' }}
                </h1>
                <p class="page-subtitle">
                    <i class="bi bi-building me-1"></i>
                    {{ $floor->building->name ?? 'N/A' }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('floors.edit', $floor->id) }}" class="btn btn-warning btn-modern">
                    <i class="bi bi-pencil me-1"></i>{{ __('Modifier') }}
                </a>
                <a href="{{ route('buildings.show', $building) }}" class="btn btn-outline-secondary btn-modern">
                    <i class="bi bi-arrow-left me-1"></i>{{ __('Retour') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Breadcrumb moderne -->
    <nav class="modern-breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="{{ route('buildings.index') }}" class="breadcrumb-item">
                    <i class="bi bi-building text-primary me-1"></i>{{ __('Dépôts') }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('buildings.show', $building) }}" class="breadcrumb-item">
                    <i class="bi bi-building text-primary me-1"></i>{{ $floor->building->name ?? 'Bâtiment' }}
                </a>
            </li>
            <li class="breadcrumb-item active">
                <i class="bi bi-layers me-1"></i>{{ $floor->name ?? 'Niveau #'.$floor->id }}
            </li>
        </ol>
    </nav>

    <div class="row g-3">
        <!-- Informations du niveau -->
        <div class="col-lg-4">
            <div class="info-card fade-in-up">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="bi bi-info-circle text-info"></i>
                        {{ __('Informations du niveau') }}
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Aperçu visuel du niveau -->
                    <div class="floor-preview">
                        <i class="bi bi-layers display-4 text-warning mb-2"></i>
                        <div class="floor-type-badge">
                            <span class="badge bg-warning">Niveau</span>
                        </div>
                    </div>
                    
                    <!-- Informations détaillées -->
                    <div class="info-item">
                        <i class="bi bi-tag info-icon"></i>
                        <div class="info-content">
                            <div class="info-label">{{ __('Nom') }}</div>
                            <div class="info-value">{{ $floor->name ?? 'Sans nom' }}</div>
                        </div>
                    </div>
                    
                    @if($floor->description)
                    <div class="info-item">
                        <i class="bi bi-file-text info-icon"></i>
                        <div class="info-content">
                            <div class="info-label">{{ __('Description') }}</div>
                            <div class="info-value">{{ $floor->description }}</div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="info-item">
                        <i class="bi bi-building info-icon"></i>
                        <div class="info-content">
                            <div class="info-label">{{ __('Bâtiment') }}</div>
                            <div class="info-value">{{ $floor->building->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="bi bi-house-door info-icon"></i>
                        <div class="info-content">
                            <div class="info-label">{{ __('Salles') }}</div>
                            <div class="info-value">{{ $floor->rooms->count() ?? 0 }} salle(s)</div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="bi bi-hash info-icon"></i>
                        <div class="info-content">
                            <div class="info-label">{{ __('ID') }}</div>
                            <div class="info-value text-muted">#{{ $floor->id }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('rooms.create') }}?floor_id={{ $floor->id }}" class="btn btn-success btn-modern w-100">
                        <i class="bi bi-plus-circle me-1"></i>{{ __('Ajouter une salle') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Salles du niveau -->
        <div class="col-lg-8">
            <div class="rooms-section fade-in-up">
                <div class="rooms-header">
                    <h5 class="mb-0">
                        <i class="bi bi-house-door me-2"></i>{{ __('Salles du niveau') }}
                    </h5>
                    <span class="rooms-count">{{ $floor->rooms->count() ?? 0 }} salle(s)</span>
                </div>
                
                @forelse($floor->rooms ?? [] as $index => $room)
                    <div class="room-item">
                        <div class="room-content">
                            <div class="room-info">
                                <div class="room-title">
                                    <i class="bi bi-house-door text-info"></i>
                                    <strong>{{ $room->name ?? 'Sans nom' }}</strong>
                                </div>
                                
                                @if($room->description)
                                    <div class="room-description">{{ $room->description }}</div>
                                @endif
                                
                                <div class="room-stats">
                                    <div class="stat-item">
                                        <i class="bi bi-bookshelf text-success"></i>
                                        <span>{{ $room->shelves->count() ?? 0 }} étagère(s)</span>
                                    </div>
                                    <div class="stat-item">
                                        <i class="bi bi-archive text-primary"></i>
                                        <span>{{ $room->shelves->sum(function($shelf) { return $shelf->containers->count(); }) ?? 0 }} contenant(s)</span>
                                    </div>
                                    <div class="stat-item">
                                        <i class="bi bi-eye text-warning"></i>
                                        <span>{{ ucfirst($room->visibility ?? 'N/A') }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="room-actions">
                                <a href="{{ route('rooms.show', $room) }}" 
                                   class="btn btn-outline-primary action-btn" 
                                   title="{{ __('Voir les détails') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('rooms.edit', $room) }}" 
                                   class="btn btn-outline-warning action-btn" 
                                   title="{{ __('Modifier') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="{{ route('shelves.index') }}?room_id={{ $room->id }}" 
                                   class="btn btn-outline-success action-btn" 
                                   title="{{ __('Voir les étagères') }}">
                                    <i class="bi bi-bookshelf"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="bi bi-house-door"></i>
                        <h5>{{ __('Aucune salle') }}</h5>
                        <p>{{ __('Ce niveau ne contient encore aucune salle.') }}</p>
                        <a href="{{ route('rooms.create') }}?floor_id={{ $floor->id }}" class="btn btn-primary btn-modern">
                            <i class="bi bi-plus-circle me-1"></i>{{ __('Créer la première salle') }}
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Animation d'apparition des éléments
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>
@endpush
@endsection
