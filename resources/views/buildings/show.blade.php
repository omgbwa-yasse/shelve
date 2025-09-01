@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">
                    <i class="bi bi-building"></i> {{ $building->name }}
                </h1>
                <div class="btn-group" role="group">
                    <a href="{{ route('buildings.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
                    </a>
                    <a href="{{ route('buildings.edit', $building->id) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> {{ __('Modifier') }}
                    </a>
                    <button type="button" class="btn btn-danger" onclick="deleteBuilding()">
                        <i class="bi bi-trash"></i> {{ __('Supprimer') }}
                    </button>
                </div>
            </div>

            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="{{ route('buildings.index') }}">{{ __('Dépôts') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $building->name }}</li>
                </ol>
            </nav>

            <div class="row">
                <!-- Building Information -->
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-info-circle"></i> {{ __('Informations du bâtiment') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="building-visual mb-3 p-4 bg-light rounded text-center">
                                <i class="bi bi-building display-1 text-primary mb-2"></i>
                                <div class="building-stats">
                                    <span class="badge bg-info fs-6">{{ $building->floors->count() }} étage(s)</span>
                                </div>
                            </div>
                            
                            <div class="info-list">
                                <div class="info-item mb-3">
                                    <strong><i class="bi bi-tag"></i> {{ __('Nom') }}:</strong>
                                    <span class="ms-2">{{ $building->name }}</span>
                                </div>
                                
                                @if($building->description)
                                <div class="info-item mb-3">
                                    <strong><i class="bi bi-file-text"></i> {{ __('Description') }}:</strong>
                                    <p class="ms-4 mt-1 mb-0">{{ $building->description }}</p>
                                </div>
                                @endif
                                
                                <div class="info-item mb-3">
                                    <strong><i class="bi bi-eye"></i> {{ __('Visibilité') }}:</strong>
                                    <span class="ms-2">
                                        <span class="badge bg-{{ $building->visibility == 'public' ? 'success' : ($building->visibility == 'private' ? 'danger' : 'warning') }}">
                                            <i class="bi bi-{{ $building->visibility == 'public' ? 'unlock' : ($building->visibility == 'private' ? 'lock' : 'arrow-repeat') }}"></i>
                                            @switch($building->visibility)
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
                                    </span>
                                </div>
                                
                                <div class="info-item mb-3">
                                    <strong><i class="bi bi-hash"></i> {{ __('ID') }}:</strong>
                                    <span class="ms-2 text-muted">#{{ $building->id }}</span>
                                </div>
                                
                                @if($building->created_at)
                                <div class="info-item mb-0">
                                    <strong><i class="bi bi-calendar-plus"></i> {{ __('Créé le') }}:</strong>
                                    <span class="ms-2 text-muted">{{ $building->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('floors.create', $building) }}" class="btn btn-success w-100">
                                <i class="bi bi-plus-circle"></i> {{ __('Ajouter un étage') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Floors List -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-layers"></i> {{ __('Étages du bâtiment') }}</h5>
                            <span class="badge bg-light text-dark">{{ $building->floors->count() }} étage(s)</span>
                        </div>
                        <div class="card-body p-0">
                            @forelse($building->floors as $index => $floor)
                                <div class="floor-item border-bottom {{ $index === 0 ? '' : 'border-top-0' }}" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                    <div class="p-4">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-2">
                                                    <i class="bi bi-building text-info me-2"></i>
                                                    <strong>{{ $floor->name }}</strong>
                                                </h6>
                                                @if($floor->description)
                                                    <p class="text-muted mb-2">{{ $floor->description }}</p>
                                                @endif
                                                <div class="floor-stats">
                                                    <small class="text-muted">
                                                        <i class="bi bi-house-door me-1"></i>
                                                        {{ $floor->rooms->count() }} salle(s)
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="floor-actions">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('floors.show', [$building, $floor]) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="{{ __('Voir les détails') }}">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('floors.edit', [$building, $floor]) }}" 
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
                                    <i class="bi bi-building display-1 text-muted mb-3"></i>
                                    <h5 class="text-muted">{{ __('Aucun étage') }}</h5>
                                    <p class="text-muted">{{ __('Ce bâtiment ne contient encore aucun étage.') }}</p>
                                    <a href="{{ route('floors.create', $building) }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle"></i> {{ __('Créer le premier étage') }}
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
                {{ __('Êtes-vous sûr de vouloir supprimer ce bâtiment ? Cette action supprimera également tous les étages et salles associés. Cette action ne peut pas être annulée.') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                <form id="deleteForm" action="{{ route('buildings.destroy', $building->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('Supprimer définitivement') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function deleteBuilding() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<style>
.building-visual {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border: 2px dashed #2196f3;
    transition: all 0.3s ease;
}

.building-visual:hover {
    background: linear-gradient(135deg, #bbdefb 0%, #90caf9 100%);
    transform: translateY(-2px);
}

.floor-item:hover {
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
