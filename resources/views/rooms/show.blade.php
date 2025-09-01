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

/* Room preview */
.room-preview {
    background: linear-gradient(135deg, #e0f2fe 0%, #b3e5fc 100%);
    border-radius: var(--radius);
    padding: 1.5rem;
    margin: 1rem 0;
    border: 1px solid #81d4fa;
    text-align: center;
    transition: var(--transition);
}

.room-preview:hover {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
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

/* Info items */
.info-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding: 0.75rem;
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
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
}

.info-value {
    color: var(--secondary-color);
    font-size: 0.95rem;
}

/* Shelves section */
.shelves-section {
    background: #ffffff;
    border-radius: var(--radius);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.shelves-header {
    background: linear-gradient(135deg, var(--info-color) 0%, #0ea5e9 100%);
    color: white;
    padding: 1rem;
    display: flex;
    justify-content: between;
    align-items: center;
}

.shelves-count {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 600;
}

/* Shelf items */
.shelf-item {
    border-bottom: 1px solid var(--border-color);
    transition: var(--transition);
    background: #ffffff;
}

.shelf-item:hover {
    background: var(--light-bg);
    transform: translateX(4px);
}

.shelf-item:last-child {
    border-bottom: none;
}

.shelf-content {
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
}

.shelf-info {
    flex: 1;
}

.shelf-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.shelf-description {
    color: var(--secondary-color);
    font-size: 0.875rem;
    margin-bottom: 0.75rem;
}

.shelf-stats {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.875rem;
    color: var(--secondary-color);
}

.shelf-actions {
    display: flex;
    gap: 0.5rem;
}

/* Occupancy indicator */
.occupancy-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.occupancy-bar {
    flex: 1;
    height: 6px;
    background: #e5e7eb;
    border-radius: 3px;
    overflow: hidden;
}

.occupancy-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.3s ease;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--secondary-color);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        padding: 1rem;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .shelf-content {
        flex-direction: column;
        align-items: stretch;
    }
    
    .shelf-actions {
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
                    <i class="bi bi-house-door text-info"></i>
                    {{ $room->name ?? 'Salle sans nom' }}
                </h1>
                <p class="page-subtitle">
                    <i class="bi bi-building me-1"></i>
                    {{ $room->floor->building->name ?? 'N/A' }} → 
                    {{ $room->floor->name ?? 'N/A' }}
                </p>
            </div>
            <div class="actions-group">
                <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary btn-modern">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Retour') }}
                </a>
                <a href="{{ route('rooms.edit', $room->id) }}" class="btn btn-warning btn-modern">
                    <i class="bi bi-pencil me-2"></i>{{ __('Modifier') }}
                </a>
                <button type="button" class="btn btn-danger btn-modern" onclick="deleteRoom()">
                    <i class="bi bi-trash me-2"></i>{{ __('Supprimer') }}
                </button>
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
                <a href="{{ route('rooms.index') }}" class="breadcrumb-item">
                    <i class="bi bi-house-door text-info me-1"></i>{{ __('Salles') }}
                </a>
            </li>
            <li class="breadcrumb-item active">
                <i class="bi bi-info-circle me-1"></i>{{ $room->name ?? 'Salle #'.$room->id }}
            </li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Informations de la salle -->
        <div class="col-lg-4">
            <div class="info-card fade-in-up">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="bi bi-info-circle text-info"></i>
                        {{ __('Informations de la salle') }}
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Aperçu visuel de la salle -->
                    <div class="room-preview">
                        <i class="bi bi-house-door display-4 text-info mb-3"></i>
                        <div class="room-type-badge">
                            @if($room->type === 'archives')
                                <span class="status-badge bg-primary">Salle d'archives</span>
                            @elseif($room->type === 'producer')
                                <span class="status-badge bg-info">Local tampon</span>
                            @else
                                <span class="status-badge bg-secondary">Type non défini</span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Informations détaillées -->
                    <div class="info-item">
                        <i class="bi bi-hash info-icon"></i>
                        <div class="info-content">
                            <div class="info-label">{{ __('Code') }}</div>
                            <div class="info-value">{{ $room->code ?? 'Non défini' }}</div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="bi bi-tag info-icon"></i>
                        <div class="info-content">
                            <div class="info-label">{{ __('Nom') }}</div>
                            <div class="info-value">{{ $room->name ?? 'Sans nom' }}</div>
                        </div>
                    </div>
                    
                    @if($room->description)
                    <div class="info-item">
                        <i class="bi bi-file-text info-icon"></i>
                        <div class="info-content">
                            <div class="info-label">{{ __('Description') }}</div>
                            <div class="info-value">{{ $room->description }}</div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="info-item">
                        <i class="bi bi-building info-icon"></i>
                        <div class="info-content">
                            <div class="info-label">{{ __('Bâtiment') }}</div>
                            <div class="info-value">{{ $room->floor->building->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="bi bi-layers info-icon"></i>
                        <div class="info-content">
                            <div class="info-label">{{ __('Niveau') }}</div>
                            <div class="info-value">{{ $room->floor->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="bi bi-eye info-icon"></i>
                        <div class="info-content">
                            <div class="info-label">{{ __('Visibilité') }}</div>
                            <div class="info-value">
                                <span class="status-badge bg-{{ $room->visibility == 'public' ? 'success' : ($room->visibility == 'private' ? 'danger' : 'warning') }}">
                                    <i class="bi bi-{{ $room->visibility == 'public' ? 'unlock' : ($room->visibility == 'private' ? 'lock' : 'arrow-repeat') }} me-1"></i>
                                    @switch($room->visibility)
                                        @case('public')
                                            {{ __('Public') }}
                                            @break
                                        @case('private')
                                            {{ __('Privé') }}
                                            @break
                                        @case('inherit')
                                            {{ __('Hériter') }} ({{ $room->getEffectiveVisibility() ?? 'N/A' }})
                                            @break
                                        @default
                                            {{ __('Non défini') }}
                                    @endswitch
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="bi bi-gear info-icon"></i>
                        <div class="info-content">
                            <div class="info-label">{{ __('Type') }}</div>
                            <div class="info-value">
                                <span class="status-badge bg-{{ $room->type == 'archives' ? 'primary' : 'info' }}">
                                    @switch($room->type)
                                        @case('archives')
                                            {{ __('Salle d\'archives') }}
                                            @break
                                        @case('producer')
                                            {{ __('Local tampon') }}
                                            @break
                                        @default
                                            {{ __('Non défini') }}
                                    @endswitch
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="bi bi-hash info-icon"></i>
                        <div class="info-content">
                            <div class="info-label">{{ __('ID') }}</div>
                            <div class="info-value text-muted">#{{ $room->id }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('shelves.create') }}?room_id={{ $room->id }}" class="btn btn-success btn-modern w-100">
                        <i class="bi bi-plus-circle me-2"></i>{{ __('Ajouter une étagère') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Étagères de la salle -->
        <div class="col-lg-8">
            <div class="shelves-section fade-in-up">
                <div class="shelves-header">
                    <h5 class="mb-0">
                        <i class="bi bi-bookshelf me-2"></i>{{ __('Étagères de la salle') }}
                    </h5>
                    <span class="shelves-count">{{ $room->shelves->count() ?? 0 }} étagère(s)</span>
                </div>
                
                @forelse($room->shelves ?? [] as $index => $shelf)
                    @php
                        $occupancyPercentage = $shelf->capacity > 0 ? ($shelf->containers->count() / $shelf->capacity) * 100 : 0;
                        $occupancyColor = $occupancyPercentage >= 90 ? '#dc3545' : ($occupancyPercentage >= 70 ? '#ffc107' : '#198754');
                    @endphp
                    
                    <div class="shelf-item">
                        <div class="shelf-content">
                            <div class="shelf-info">
                                <div class="shelf-title">
                                    <i class="bi bi-bookshelf text-info"></i>
                                    <strong>{{ $shelf->code ?? 'Sans code' }}</strong>
                                </div>
                                
                                @if($shelf->name)
                                    <div class="shelf-description">{{ $shelf->name }}</div>
                                @endif
                                
                                <div class="shelf-stats">
                                    <div class="stat-item">
                                        <i class="bi bi-archive text-primary"></i>
                                        <span>{{ $shelf->containers->count() ?? 0 }}/{{ $shelf->capacity ?? 0 }} contenants</span>
                                    </div>
                                    <div class="stat-item">
                                        <i class="bi bi-box text-success"></i>
                                        <span>{{ $shelf->face ?? 0 }} faces</span>
                                    </div>
                                    <div class="stat-item">
                                        <i class="bi bi-layers text-warning"></i>
                                        <span>{{ $shelf->shelf ?? 0 }} tablettes</span>
                                    </div>
                                </div>
                                
                                @if($shelf->capacity > 0)
                                    <div class="occupancy-indicator">
                                        <small class="text-muted">Occupation:</small>
                                        <div class="occupancy-bar">
                                            <div class="occupancy-fill" 
                                                 style="width: {{ $occupancyPercentage }}%; background-color: {{ $occupancyColor }};">
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ round($occupancyPercentage) }}%</small>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="shelf-actions">
                                <a href="{{ route('shelves.show', $shelf) }}" 
                                   class="btn btn-outline-primary action-btn" 
                                   title="{{ __('Voir les détails') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('shelves.edit', $shelf) }}" 
                                   class="btn btn-outline-warning action-btn" 
                                   title="{{ __('Modifier') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="{{ route('containers.index') }}?shelf_id={{ $shelf->id }}" 
                                   class="btn btn-outline-info action-btn" 
                                   title="{{ __('Voir les contenants') }}">
                                    <i class="bi bi-archive"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="bi bi-bookshelf"></i>
                        <h5>{{ __('Aucune étagère') }}</h5>
                        <p>{{ __('Cette salle ne contient encore aucune étagère.') }}</p>
                        <a href="{{ route('shelves.create') }}?room_id={{ $room->id }}" class="btn btn-primary btn-modern">
                            <i class="bi bi-plus-circle me-2"></i>{{ __('Créer la première étagère') }}
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
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
                {{ __('Êtes-vous sûr de vouloir supprimer cette salle ? Cette action supprimera également toutes les étagères et contenants associés. Cette action ne peut pas être annulée.') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                <form id="deleteForm" action="{{ route('rooms.destroy', $room->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('Supprimer définitivement') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function deleteRoom() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

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
