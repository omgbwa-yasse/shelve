@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">
                    <i class="bi bi-house-door"></i> {{ $room->name ?? 'Salle sans nom' }}
                </h1>
                <div class="btn-group" role="group">
                    <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
                    </a>
                    <a href="{{ route('rooms.edit', $room->id) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> {{ __('Modifier') }}
                    </a>
                    <button type="button" class="btn btn-danger" onclick="deleteRoom()">
                        <i class="bi bi-trash"></i> {{ __('Supprimer') }}
                    </button>
                </div>
            </div>

            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="{{ route('buildings.index') }}">{{ __('Dépôts') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('rooms.index') }}">{{ __('Salles') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $room->name ?? 'Salle #'.$room->id }}</li>
                </ol>
            </nav>

            <div class="row">
                <!-- Room Information -->
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-info-circle"></i> {{ __('Informations de la salle') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="room-visual mb-3 p-4 bg-light rounded text-center">
                                <i class="bi bi-house-door display-1 text-primary mb-2"></i>
                                <div class="room-type-badge">
                                    @if($room->type === 'archives')
                                        <span class="badge bg-primary fs-6">Salle d'archives</span>
                                    @elseif($room->type === 'producer')
                                        <span class="badge bg-info fs-6">Local tampon</span>
                                    @else
                                        <span class="badge bg-secondary fs-6">Type non défini</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="info-list">
                                <div class="info-item mb-3">
                                    <strong><i class="bi bi-hash"></i> {{ __('Code') }}:</strong>
                                    <span class="ms-2">{{ $room->code ?? 'Non défini' }}</span>
                                </div>
                                
                                <div class="info-item mb-3">
                                    <strong><i class="bi bi-tag"></i> {{ __('Nom') }}:</strong>
                                    <span class="ms-2">{{ $room->name ?? 'Sans nom' }}</span>
                                </div>
                                
                                @if($room->description)
                                <div class="info-item mb-3">
                                    <strong><i class="bi bi-file-text"></i> {{ __('Description') }}:</strong>
                                    <p class="ms-4 mt-1 mb-0">{{ $room->description }}</p>
                                </div>
                                @endif
                                
                                <div class="info-item mb-3">
                                    <strong><i class="bi bi-building"></i> {{ __('Bâtiment') }}:</strong>
                                    <span class="ms-2">{{ $room->floor->building->name ?? 'N/A' }}</span>
                                </div>
                                
                                <div class="info-item mb-3">
                                    <strong><i class="bi bi-layers"></i> {{ __('Niveau') }}:</strong>
                                    <span class="ms-2">{{ $room->floor->name ?? 'N/A' }}</span>
                                </div>
                                
                                <div class="info-item mb-3">
                                    <strong><i class="bi bi-eye"></i> {{ __('Visibilité') }}:</strong>
                                    <span class="ms-2">
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
                                                    {{ __('Hériter') }} ({{ $room->getEffectiveVisibility() ?? 'N/A' }})
                                                    @break
                                                @default
                                                    {{ __('Non défini') }}
                                            @endswitch
                                        </span>
                                    </span>
                                </div>
                                
                                <div class="info-item mb-3">
                                    <strong><i class="bi bi-gear"></i> {{ __('Type') }}:</strong>
                                    <span class="ms-2">
                                        <span class="badge bg-{{ $room->type == 'archives' ? 'primary' : 'info' }}">
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
                                    </span>
                                </div>
                                
                                <div class="info-item mb-0">
                                    <strong><i class="bi bi-hash"></i> {{ __('ID') }}:</strong>
                                    <span class="ms-2 text-muted">#{{ $room->id }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('shelves.create') }}?room_id={{ $room->id }}" class="btn btn-success w-100">
                                <i class="bi bi-plus-circle"></i> {{ __('Ajouter une étagère') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Room Shelves -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-bookshelf"></i> {{ __('Étagères de la salle') }}</h5>
                            <span class="badge bg-light text-dark">{{ $room->shelves->count() ?? 0 }} étagère(s)</span>
                        </div>
                        <div class="card-body p-0">
                            @forelse($room->shelves ?? [] as $index => $shelf)
                                <div class="shelf-item border-bottom {{ $index === 0 ? '' : 'border-top-0' }}" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                    <div class="p-4">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-2">
                                                    <i class="bi bi-bookshelf text-info me-2"></i>
                                                    <strong>{{ $shelf->code ?? 'Sans code' }}</strong>
                                                </h6>
                                                @if($shelf->name)
                                                    <p class="text-muted mb-2">{{ $shelf->name }}</p>
                                                @endif
                                                <div class="shelf-stats">
                                                    <small class="text-muted">
                                                        <i class="bi bi-archive me-1"></i>
                                                        {{ $shelf->containers->count() ?? 0 }}/{{ $shelf->capacity ?? 0 }} contenants
                                                        @if($shelf->capacity > 0)
                                                            <span class="ms-2 badge bg-{{ ($shelf->containers->count() / $shelf->capacity) > 0.8 ? 'danger' : (($shelf->containers->count() / $shelf->capacity) > 0.6 ? 'warning' : 'success') }}">
                                                                {{ round(($shelf->containers->count() / $shelf->capacity) * 100) }}%
                                                            </span>
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="shelf-actions">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('shelves.show', $shelf) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="{{ __('Voir les détails') }}">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('shelves.edit', $shelf) }}" 
                                                       class="btn btn-sm btn-outline-warning" 
                                                       title="{{ __('Modifier') }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="bi bi-bookshelf display-1 text-muted mb-3"></i>
                                    <h5 class="text-muted">{{ __('Aucune étagère') }}</h5>
                                    <p class="text-muted">{{ __('Cette salle ne contient encore aucune étagère.') }}</p>
                                    <a href="{{ route('shelves.create') }}?room_id={{ $room->id }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle"></i> {{ __('Créer la première étagère') }}
                                    </a>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
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

<script>
function deleteRoom() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<style>
.room-visual {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 2px dashed #dee2e6;
    transition: all 0.3s ease;
}

.room-visual:hover {
    background: linear-gradient(135deg, #e8f4f8 0%, #d1ecf1 100%);
    transform: translateY(-2px);
}

.shelf-item:hover {
    background: linear-gradient(135deg, #e8f4f8 0%, #d1ecf1 100%) !important;
    transform: translateX(5px);
    transition: all 0.3s ease;
}

.info-item {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child {
    border-bottom: none;
}
</style>
@endsection
