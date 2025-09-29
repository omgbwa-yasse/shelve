@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">
                    <i class="bi bi-archive"></i> {{ $container->code ?? 'Contenant sans code' }}
                </h1>
                <div class="btn-group" role="group">
                    <a href="{{ route('containers.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
                    </a>
                    <a href="{{ route('containers.edit', $container->id) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> {{ __('Modifier') }}
                    </a>
                    <button type="button" class="btn btn-danger" onclick="deleteContainer()">
                        <i class="bi bi-trash"></i> {{ __('Supprimer') }}
                    </button>
                </div>
            </div>

            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="{{ route('buildings.index') }}">{{ __('Dépôts') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('containers.index') }}">{{ __('Contenants') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $container->code ?? 'Contenant #'.$container->id }}</li>
                </ol>
            </nav>

            <div class="row">
                <!-- Container Information -->
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="bi bi-info-circle"></i> {{ __('Informations du contenant') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="container-visual mb-3 p-4 bg-light rounded text-center">
                                <i class="bi bi-archive display-1 text-warning mb-2"></i>
                                <div class="container-stats">
                                    @if($container->status)
                                        <span class="badge bg-primary fs-6">{{ $container->status->name }}</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="info-list">
                                <div class="info-item mb-3">
                                    <strong><i class="bi bi-hash"></i> {{ __('Code') }}:</strong>
                                    <span class="ms-2">{{ $container->code ?? 'Non défini' }}</span>
                                </div>
                                
                                @if($container->name)
                                <div class="info-item mb-3">
                                    <strong><i class="bi bi-tag"></i> {{ __('Nom') }}:</strong>
                                    <span class="ms-2">{{ $container->name }}</span>
                                </div>
                                @endif
                                
                                @if($container->description)
                                <div class="info-item mb-3">
                                    <strong><i class="bi bi-file-text"></i> {{ __('Description') }}:</strong>
                                    <p class="ms-4 mt-1 mb-0">{{ $container->description }}</p>
                                </div>
                                @endif
                                
                                <div class="info-item mb-3">
                                    <strong><i class="bi bi-flag"></i> {{ __('Statut') }}:</strong>
                                    <span class="ms-2">
                                        @if($container->status)
                                            <span class="badge bg-primary">{{ $container->status->name }}</span>
                                        @else
                                            <span class="text-muted">{{ __('Non défini') }}</span>
                                        @endif
                                    </span>
                                </div>
                                
                                <div class="info-item mb-3">
                                    <strong><i class="bi bi-building"></i> {{ __('Propriété') }}:</strong>
                                    <span class="ms-2">
                                        @if($container->property)
                                            <span class="badge bg-info">{{ $container->property->name }}</span>
                                        @else
                                            <span class="text-muted">{{ __('Non définie') }}</span>
                                        @endif
                                    </span>
                                </div>
                                
                                @if($container->shelf)
                                    <div class="info-item mb-3">
                                        <strong><i class="bi bi-bookshelf"></i> {{ __('Étagère') }}:</strong>
                                        <span class="ms-2">
                                            <a href="{{ route('shelves.show', $container->shelf) }}" class="text-decoration-none">
                                                {{ $container->shelf->code ?? 'N/A' }}
                                                <i class="bi bi-box-arrow-up-right ms-1"></i>
                                            </a>
                                        </span>
                                    </div>
                                    
                                    @if($container->shelf->room)
                                        <div class="info-item mb-3">
                                            <strong><i class="bi bi-house-door"></i> {{ __('Salle') }}:</strong>
                                            <span class="ms-2">
                                                <a href="{{ route('rooms.show', $container->shelf->room) }}" class="text-decoration-none">
                                                    {{ $container->shelf->room->name ?? $container->shelf->room->code ?? 'N/A' }}
                                                    <i class="bi bi-box-arrow-up-right ms-1"></i>
                                                </a>
                                            </span>
                                        </div>
                                        
                                        @if($container->shelf->room->floor)
                                            <div class="info-item mb-3">
                                                <strong><i class="bi bi-layers"></i> {{ __('Bâtiment/Étage') }}:</strong>
                                                <span class="ms-2">
                                                    {{ $container->shelf->room->floor->building->name ?? 'N/A' }} - 
                                                    {{ $container->shelf->room->floor->name ?? 'N/A' }}
                                                </span>
                                            </div>
                                        @endif
                                    @endif
                                @endif
                                
                                <div class="info-item mb-0">
                                    <strong><i class="bi bi-hash"></i> {{ __('ID') }}:</strong>
                                    <span class="ms-2 text-muted">#{{ $container->id }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Container Position & Actions -->
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-gear"></i> {{ __('Emplacement et actions') }}</h5>
                        </div>
                        <div class="card-body">
                            @if($container->shelf)
                                <div class="shelf-info mb-4">
                                    <h6><i class="bi bi-bookshelf"></i> {{ __('Emplacement sur l\'\u00e9tagère') }}</h6>
                                    <div class="shelf-display p-3 bg-light rounded">
                                        <div class="text-center">
                                            <div class="shelf-name mb-2">
                                                <strong>{{ $container->shelf->code }}</strong>
                                            </div>
                                            <div class="shelf-capacity">
                                                <small class="text-muted">
                                                    Capacité: {{ $container->shelf->face ?? 0 }}F × {{ $container->shelf->ear ?? 0 }}T × {{ $container->shelf->shelf ?? 0 }}N
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="actions-section">
                                <h6><i class="bi bi-gear"></i> {{ __('Actions disponibles') }}</h6>
                                <div class="d-grid gap-2">
                                    <a href="{{ route('containers.edit', $container) }}" class="btn btn-warning">
                                        <i class="bi bi-pencil"></i> {{ __('Modifier le contenant') }}
                                    </a>
                                    
                                    @if($container->shelf)
                                        <a href="{{ route('shelves.show', $container->shelf) }}" class="btn btn-info">
                                            <i class="bi bi-bookshelf"></i> {{ __('Voir l\'\u00e9tagère') }}
                                        </a>
                                    @endif
                                    
                                    <button type="button" class="btn btn-outline-primary" onclick="moveContainer()">
                                        <i class="bi bi-arrows-move"></i> {{ __('Déplacer le contenant') }}
                                    </button>
                                    
                                    <hr>
                                    
                                    <button type="button" class="btn btn-danger" onclick="deleteContainer()">
                                        <i class="bi bi-trash"></i> {{ __('Supprimer le contenant') }}
                                    </button>
                                </div>
                            </div>
                            
                            @if($container->created_at)
                                <div class="creation-info mt-4 pt-3 border-top">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar-plus"></i> 
                                        {{ __('Créé le') }}: {{ $container->created_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            @endif
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
                {{ __('Êtes-vous sûr de vouloir supprimer ce contenant ? Cette action ne peut pas être annulée.') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                <form id="deleteForm" action="{{ route('containers.destroy', $container->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('Supprimer définitivement') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Move Container Modal -->
<div class="modal fade" id="moveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Déplacer le contenant') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Fonctionnalité de déplacement en cours de développement.') }}</p>
                <p class="text-muted">{{ __('Vous pourrez bientôt déplacer ce contenant vers une autre position ou étagère.') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Fermer') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
function deleteContainer() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function moveContainer() {
    new bootstrap.Modal(document.getElementById('moveModal')).show();
}
</script>

<style>
.container-visual {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border: 2px dashed #f39c12;
    transition: all 0.3s ease;
}

.container-visual:hover {
    background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);
    transform: translateY(-2px);
}

.shelf-display {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
}

.shelf-name {
    font-size: 1.2em;
    color: #007bff;
}

.info-item {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child {
    border-bottom: none;
}

.actions-section .btn {
    justify-content: flex-start;
}

</style>
@endsection
