@extends('layouts.app')

@include('deposits.partials._deposit-styles')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="deposit-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="deposit-title">
                    <i class="bi bi-building text-primary"></i> {{ $building->name }}
                </h1>
                <p class="deposit-subtitle">
                    <span class="badge bg-{{ $building->visibility == 'public' ? 'success' : ($building->visibility == 'private' ? 'danger' : 'warning') }} me-1">
                        {{ ucfirst($building->visibility ?? 'N/A') }}
                    </span>
                    {{ $building->floors->count() }} étage(s)
                    @if($building->created_at) • Créé le {{ $building->created_at->format('d/m/Y') }}@endif
                </p>
            </div>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('buildings.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
                <a href="{{ route('buildings.edit', $building->id) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Modifier
                </a>
                <button type="button" class="btn btn-danger" onclick="new bootstrap.Modal(document.getElementById('deleteModal')).show()">
                    <i class="bi bi-trash"></i> Supprimer
                </button>
            </div>
        </div>
    </div>

    {{-- Breadcrumb --}}
    <nav class="deposit-breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('buildings.index') }}"><i class="bi bi-building me-1"></i>Dépôts</a></li>
            <li class="breadcrumb-item active">{{ $building->name }}</li>
        </ol>
    </nav>

    <div class="row g-3">
        {{-- Info sidebar --}}
        <div class="col-lg-4">
            <div class="deposit-info-card">
                <div class="card-header">
                    <h5><i class="bi bi-info-circle text-info me-1"></i> Informations</h5>
                </div>
                <div class="card-body">
                    <div class="deposit-info-item">
                        <i class="bi bi-tag"></i>
                        <div>
                            <div class="info-label">Nom</div>
                            <div class="info-value">{{ $building->name }}</div>
                        </div>
                    </div>
                    @if($building->description)
                    <div class="deposit-info-item">
                        <i class="bi bi-file-text"></i>
                        <div>
                            <div class="info-label">Description</div>
                            <div class="info-value">{{ $building->description }}</div>
                        </div>
                    </div>
                    @endif
                    <div class="deposit-info-item">
                        <i class="bi bi-eye"></i>
                        <div>
                            <div class="info-label">Visibilité</div>
                            <div class="info-value">
                                <span class="badge bg-{{ $building->visibility == 'public' ? 'success' : ($building->visibility == 'private' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($building->visibility ?? 'Non défini') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="deposit-info-item">
                        <i class="bi bi-hash"></i>
                        <div>
                            <div class="info-label">ID</div>
                            <div class="info-value text-muted">#{{ $building->id }}</div>
                        </div>
                    </div>
                    @if($building->created_at)
                    <div class="deposit-info-item">
                        <i class="bi bi-calendar-plus"></i>
                        <div>
                            <div class="info-label">Créé le</div>
                            <div class="info-value">{{ $building->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('floors.create', $building) }}" class="btn btn-success btn-sm w-100">
                        <i class="bi bi-plus-circle me-1"></i> Ajouter un étage
                    </a>
                </div>
            </div>
        </div>

        {{-- Floors list --}}
        <div class="col-lg-8">
            <div class="deposit-list-section">
                <div class="deposit-list-header">
                    <h5><i class="bi bi-layers me-1"></i> Étages du bâtiment</h5>
                    <span class="deposit-list-count">{{ $building->floors->count() }} étage(s)</span>
                </div>
                @forelse($building->floors as $floor)
                    <div class="deposit-list-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">
                                    <i class="bi bi-layers text-info me-1"></i>
                                    {{ $floor->name }}
                                </h6>
                                @if($floor->description)
                                    <p class="text-muted mb-1">{{ Str::limit($floor->description, 80) }}</p>
                                @endif
                                <small class="text-muted">
                                    <i class="bi bi-house-door me-1"></i>{{ $floor->rooms->count() }} salle(s)
                                </small>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('floors.show', [$building, $floor]) }}" class="btn btn-outline-primary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('floors.edit', [$building, $floor]) }}" class="btn btn-outline-warning" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="deposit-empty">
                        <i class="bi bi-layers d-block"></i>
                        <h6>Aucun étage</h6>
                        <p class="mb-2">Ce bâtiment ne contient encore aucun étage.</p>
                        <a href="{{ route('floors.create', $building) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> Créer le premier étage
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Delete modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir supprimer ce bâtiment ? Tous les étages et salles associés seront supprimés.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form action="{{ route('buildings.destroy', $building->id) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
