@extends('layouts.app')

@include('deposits.partials._deposit-styles')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="deposit-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="deposit-title">
                    <i class="bi bi-layers text-warning"></i> {{ $floor->name ?? 'Niveau sans nom' }}
                </h1>
                <p class="deposit-subtitle">
                    <i class="bi bi-building me-1"></i> {{ $floor->building->name ?? 'N/A' }}
                </p>
            </div>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('buildings.show', $building) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
                <a href="{{ route('floors.edit', [$building, $floor]) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Modifier
                </a>
            </div>
        </div>
    </div>

    {{-- Breadcrumb --}}
    <nav class="deposit-breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('buildings.index') }}"><i class="bi bi-building me-1"></i>Dépôts</a></li>
            <li class="breadcrumb-item"><a href="{{ route('buildings.show', $building) }}">{{ $floor->building->name ?? 'Bâtiment' }}</a></li>
            <li class="breadcrumb-item active"><i class="bi bi-layers me-1"></i>{{ $floor->name ?? 'Niveau #'.$floor->id }}</li>
        </ol>
    </nav>

    <div class="row g-3">
        {{-- Info sidebar --}}
        <div class="col-lg-4">
            <div class="deposit-info-card">
                <div class="card-header">
                    <h5><i class="bi bi-info-circle text-info me-1"></i> Informations du niveau</h5>
                </div>
                <div class="card-body">
                    <div class="deposit-info-item">
                        <i class="bi bi-tag"></i>
                        <div>
                            <div class="info-label">Nom</div>
                            <div class="info-value">{{ $floor->name ?? 'Sans nom' }}</div>
                        </div>
                    </div>
                    @if($floor->description)
                    <div class="deposit-info-item">
                        <i class="bi bi-file-text"></i>
                        <div>
                            <div class="info-label">Description</div>
                            <div class="info-value">{{ $floor->description }}</div>
                        </div>
                    </div>
                    @endif
                    <div class="deposit-info-item">
                        <i class="bi bi-building"></i>
                        <div>
                            <div class="info-label">Bâtiment</div>
                            <div class="info-value">{{ $floor->building->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="deposit-info-item">
                        <i class="bi bi-house-door"></i>
                        <div>
                            <div class="info-label">Salles</div>
                            <div class="info-value">{{ $floor->rooms->count() ?? 0 }} salle(s)</div>
                        </div>
                    </div>
                    <div class="deposit-info-item">
                        <i class="bi bi-hash"></i>
                        <div>
                            <div class="info-label">ID</div>
                            <div class="info-value text-muted">#{{ $floor->id }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('rooms.create') }}?floor_id={{ $floor->id }}" class="btn btn-success btn-sm w-100">
                        <i class="bi bi-plus-circle me-1"></i> Ajouter une salle
                    </a>
                </div>
            </div>
        </div>

        {{-- Rooms list --}}
        <div class="col-lg-8">
            <div class="deposit-list-section">
                <div class="deposit-list-header">
                    <h5><i class="bi bi-house-door me-1"></i> Salles du niveau</h5>
                    <span class="deposit-list-count">{{ $floor->rooms->count() ?? 0 }} salle(s)</span>
                </div>
                @forelse($floor->rooms ?? [] as $room)
                    <div class="deposit-list-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">
                                    <i class="bi bi-house-door text-info me-1"></i>
                                    {{ $room->name ?? 'Sans nom' }}
                                </h6>
                                @if($room->description)
                                    <p class="text-muted mb-1">{{ Str::limit($room->description, 80) }}</p>
                                @endif
                                <div class="d-flex gap-3">
                                    <small class="text-muted">
                                        <i class="bi bi-bookshelf me-1"></i>{{ $room->shelves->count() ?? 0 }} étagère(s)
                                    </small>
                                    <small class="text-muted">
                                        <i class="bi bi-archive me-1"></i>{{ $room->shelves->sum(fn($s) => $s->containers->count()) ?? 0 }} contenant(s)
                                    </small>
                                    <small class="text-muted">
                                        <i class="bi bi-eye me-1"></i>{{ ucfirst($room->visibility ?? 'N/A') }}
                                    </small>
                                </div>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('rooms.show', $room) }}" class="btn btn-outline-primary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('rooms.edit', $room) }}" class="btn btn-outline-warning" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="{{ route('shelves.index') }}?room_id={{ $room->id }}" class="btn btn-outline-success" title="Étagères">
                                    <i class="bi bi-bookshelf"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="deposit-empty">
                        <i class="bi bi-house-door d-block"></i>
                        <h6>Aucune salle</h6>
                        <p class="mb-2">Ce niveau ne contient encore aucune salle.</p>
                        <a href="{{ route('rooms.create') }}?floor_id={{ $floor->id }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> Créer la première salle
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
