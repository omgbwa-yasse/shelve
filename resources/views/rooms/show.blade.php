@extends('layouts.app')

@include('deposits.partials._deposit-styles')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="deposit-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="deposit-title">
                    <i class="bi bi-house-door text-info"></i> {{ $room->name ?? 'Salle sans nom' }}
                </h1>
                <p class="deposit-subtitle">
                    <i class="bi bi-building me-1"></i>{{ $room->floor->building->name ?? 'N/A' }}
                    <i class="bi bi-chevron-right mx-1"></i>{{ $room->floor->name ?? 'N/A' }}
                </p>
            </div>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
                <a href="{{ route('rooms.edit', $room) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Modifier
                </a>
                <form action="{{ route('rooms.destroy', $room) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Supprimer cette salle et toutes ses étagères ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </div>
    </div>

    {{-- Breadcrumb --}}
    <nav class="deposit-breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('buildings.index') }}"><i class="bi bi-building me-1"></i>Dépôts</a></li>
            <li class="breadcrumb-item"><a href="{{ route('rooms.index') }}"><i class="bi bi-house-door me-1"></i>Salles</a></li>
            <li class="breadcrumb-item active">{{ $room->name ?? 'Salle #'.$room->id }}</li>
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
                        <i class="bi bi-hash"></i>
                        <div>
                            <div class="info-label">Code</div>
                            <div class="info-value">{{ $room->code ?? 'Non défini' }}</div>
                        </div>
                    </div>
                    <div class="deposit-info-item">
                        <i class="bi bi-tag"></i>
                        <div>
                            <div class="info-label">Nom</div>
                            <div class="info-value">{{ $room->name ?? 'Sans nom' }}</div>
                        </div>
                    </div>
                    @if($room->description)
                    <div class="deposit-info-item">
                        <i class="bi bi-file-text"></i>
                        <div>
                            <div class="info-label">Description</div>
                            <div class="info-value">{{ $room->description }}</div>
                        </div>
                    </div>
                    @endif
                    <div class="deposit-info-item">
                        <i class="bi bi-building"></i>
                        <div>
                            <div class="info-label">Bâtiment</div>
                            <div class="info-value">{{ $room->floor->building->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="deposit-info-item">
                        <i class="bi bi-layers"></i>
                        <div>
                            <div class="info-label">Niveau</div>
                            <div class="info-value">{{ $room->floor->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="deposit-info-item">
                        <i class="bi bi-eye"></i>
                        <div>
                            <div class="info-label">Visibilité</div>
                            <div class="info-value">
                                @if($room->visibility == 'public')
                                    <span class="badge bg-success">Public</span>
                                @elseif($room->visibility == 'private')
                                    <span class="badge bg-danger">Privé</span>
                                @else
                                    <span class="badge bg-warning text-dark">Hériter</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="deposit-info-item">
                        <i class="bi bi-gear"></i>
                        <div>
                            <div class="info-label">Type</div>
                            <div class="info-value">
                                @if($room->type == 'archives')
                                    <span class="badge bg-primary">Salle d'archives</span>
                                @elseif($room->type == 'producer')
                                    <span class="badge bg-info">Local tampon</span>
                                @else
                                    <span class="badge bg-secondary">Non défini</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="deposit-info-item">
                        <i class="bi bi-hash"></i>
                        <div>
                            <div class="info-label">ID</div>
                            <div class="info-value text-muted">#{{ $room->id }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('shelves.create') }}?room_id={{ $room->id }}" class="btn btn-success btn-sm w-100">
                        <i class="bi bi-plus-circle me-1"></i> Ajouter une étagère
                    </a>
                </div>
            </div>
        </div>

        {{-- Shelves list --}}
        <div class="col-lg-8">
            <div class="deposit-list-section">
                <div class="deposit-list-header">
                    <h5><i class="bi bi-bookshelf me-1"></i> Étagères</h5>
                    <span class="deposit-list-count">{{ $room->shelves->count() ?? 0 }}</span>
                </div>
                @forelse($room->shelves ?? [] as $shelf)
                    @php
                        $occupancyPct = $shelf->capacity > 0 ? round(($shelf->containers->count() / $shelf->capacity) * 100) : 0;
                        $occColor = $occupancyPct >= 90 ? 'danger' : ($occupancyPct >= 70 ? 'warning' : 'success');
                    @endphp
                    <div class="deposit-list-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    <i class="bi bi-bookshelf text-info me-1"></i>
                                    <strong>{{ $shelf->code ?? 'Sans code' }}</strong>
                                    @if($shelf->name)
                                        <small class="text-muted ms-1">— {{ $shelf->name }}</small>
                                    @endif
                                </h6>
                                <div class="d-flex gap-3 flex-wrap">
                                    <small class="text-muted">
                                        <i class="bi bi-archive me-1"></i>{{ $shelf->containers->count() }}/{{ $shelf->capacity ?? 0 }} contenants
                                    </small>
                                    <small class="text-muted">
                                        <i class="bi bi-box me-1"></i>{{ $shelf->face ?? 0 }} faces
                                    </small>
                                    <small class="text-muted">
                                        <i class="bi bi-layers me-1"></i>{{ $shelf->shelf ?? 0 }} tablettes
                                    </small>
                                </div>
                                @if($shelf->capacity > 0)
                                <div class="deposit-occupancy mt-1" style="max-width:250px;">
                                    <div class="deposit-occupancy-bar">
                                        <div class="deposit-occupancy-fill bg-{{ $occColor }}" style="width:{{ $occupancyPct }}%"></div>
                                    </div>
                                    <small class="text-{{ $occColor }}">{{ $occupancyPct }}%</small>
                                </div>
                                @endif
                            </div>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('shelves.show', $shelf) }}" class="btn btn-outline-primary" title="Voir"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('shelves.edit', $shelf) }}" class="btn btn-outline-warning" title="Modifier"><i class="bi bi-pencil"></i></a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="deposit-empty">
                        <i class="bi bi-bookshelf d-block"></i>
                        <h6>Aucune étagère</h6>
                        <p class="mb-2">Cette salle ne contient encore aucune étagère.</p>
                        <a href="{{ route('shelves.create') }}?room_id={{ $room->id }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> Créer la première étagère
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
