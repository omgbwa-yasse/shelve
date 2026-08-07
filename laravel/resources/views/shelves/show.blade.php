@extends('layouts.app')

@include('deposits.partials._deposit-styles')

@push('styles')
<style>
    .container-list { max-height: 500px; overflow-y: auto; }
    .container-item {
        border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 12px;
        margin: 6px 0; background: #fff; transition: border-color .2s;
    }
    .container-item:hover { border-color: #3b82f6; }
    .container-item.occupied { border-left: 4px solid #ef4444; background: #fef2f2; }
    .container-item.available { border-left: 4px solid #10b981; background: #f0fdf4; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="deposit-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="deposit-title"><i class="bi bi-bookshelf text-warning"></i> Étagère {{ $shelf->code }}</h1>
                @if($shelf->observation)
                    <p class="deposit-subtitle">{{ $shelf->observation }}</p>
                @endif
            </div>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('shelves.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Retour</a>
                <a href="{{ route('shelves.edit', $shelf) }}" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i>Modifier</a>
                <a href="{{ route('containers.create') }}?shelf_id={{ $shelf->id }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Conteneur</a>
            </div>
        </div>
    </div>

    {{-- Breadcrumb --}}
    <nav class="deposit-breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('buildings.index') }}"><i class="bi bi-building me-1"></i>{{ $shelf->room->floor->building->name ?? 'Bâtiment' }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('rooms.index') }}"><i class="bi bi-layers me-1"></i>{{ $shelf->room->floor->name ?? 'Étage' }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('rooms.show', $shelf->room) }}"><i class="bi bi-door-open me-1"></i>{{ $shelf->room->name }}</a></li>
            <li class="breadcrumb-item active"><i class="bi bi-bookshelf me-1"></i>{{ $shelf->code }}</li>
        </ol>
    </nav>

    {{-- Stats --}}
    @php
        $pct = $shelf->occupancy_percentage;
        $color = $pct >= 90 ? 'danger' : ($pct >= 70 ? 'warning' : 'success');
    @endphp
    <div class="deposit-stats">
        <div class="deposit-stat">
            <span class="deposit-stat-value text-primary">{{ $shelf->total_capacity }}</span>
            <span class="deposit-stat-label">Capacité ({{ $shelf->face }}F×{{ $shelf->ear }}T×{{ $shelf->shelf }}N)</span>
        </div>
        <div class="deposit-stat">
            <span class="deposit-stat-value text-danger">{{ $shelf->occupied_spots }}</span>
            <span class="deposit-stat-label">Occupés</span>
        </div>
        <div class="deposit-stat">
            <span class="deposit-stat-value text-success">{{ $shelf->available_spots }}</span>
            <span class="deposit-stat-label">Disponibles</span>
        </div>
        <div class="deposit-stat">
            <span class="deposit-stat-value text-{{ $color }}">{{ $pct }}%</span>
            <span class="deposit-stat-label">Occupation</span>
        </div>
        <div class="deposit-stat">
            <span class="deposit-stat-value text-warning">{{ $shelf->volumetry_ml }}</span>
            <span class="deposit-stat-label">ml</span>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="row g-3">
        {{-- Container list --}}
        <div class="col-lg-8">
            <div class="deposit-list-section">
                <div class="deposit-list-header">
                    <span><i class="bi bi-box me-1"></i>Conteneurs</span>
                    <div class="d-flex align-items-center gap-2">
                        <span class="deposit-list-count">{{ $shelf->containers->count() }}</span>
                        <input type="text" class="form-control form-control-sm" id="containerSearch"
                               placeholder="Rechercher..." style="max-width:200px;">
                    </div>
                </div>

                {{-- Occupancy bar --}}
                <div class="px-3 pt-2">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="fw-semibold">Occupation</small>
                        <small class="text-muted">{{ $shelf->occupied_spots }}/{{ $shelf->total_capacity }}</small>
                    </div>
                    <div class="deposit-occupancy-bar">
                        <div class="deposit-occupancy-fill bg-{{ $color }}" style="width:{{ $pct }}%"></div>
                    </div>
                </div>

                <div class="container-list p-3" id="containerList">
                    @forelse($shelf->containers as $index => $container)
                        <div class="container-item occupied" data-code="{{ $container->code }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold">{{ $container->code }}</span>
                                    <small class="text-muted ms-2">
                                        #{{ $index + 1 }} &middot;
                                        {{ $container->status->name ?? 'N/A' }} &middot;
                                        {{ $container->property->name ?? 'Standard' }}
                                    </small>
                                </div>
                                <a href="{{ route('containers.show', $container) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="deposit-empty py-3">
                            <i class="bi bi-inbox d-block"></i>
                            <span>Aucun conteneur</span>
                        </div>
                    @endforelse

                    @for($i = $shelf->occupied_spots; $i < min($shelf->total_capacity, $shelf->occupied_spots + 10); $i++)
                        <div class="container-item available">
                            <small class="text-muted"><i class="bi bi-circle text-success me-1"></i>Emplacement {{ $i + 1 }} — Disponible</small>
                        </div>
                    @endfor

                    @if($shelf->available_spots > 10)
                        <div class="text-center py-2 text-muted">
                            <small>... et {{ $shelf->available_spots - 10 }} autres emplacements disponibles</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="deposit-info-card mb-3">
                <h6 class="mb-3"><i class="bi bi-info-circle text-info me-1"></i>Informations Techniques</h6>
                <div class="deposit-info-item"><span class="info-label">Code</span><span class="info-value">{{ $shelf->code }}</span></div>
                <div class="deposit-info-item"><span class="info-label">Faces</span><span class="info-value">{{ $shelf->face }}</span></div>
                <div class="deposit-info-item"><span class="info-label">Travées</span><span class="info-value">{{ $shelf->ear }}</span></div>
                <div class="deposit-info-item"><span class="info-label">Tablettes</span><span class="info-value">{{ $shelf->shelf }}</span></div>
                <div class="deposit-info-item"><span class="info-label">Longueur</span><span class="info-value">{{ $shelf->shelf_length ?? '—' }} cm</span></div>
                <div class="deposit-info-item"><span class="info-label">Salle</span><span class="info-value">{{ $shelf->room->name }}</span></div>
                <div class="deposit-info-item"><span class="info-label">Créé le</span><span class="info-value">{{ $shelf->created_at?->format('d/m/Y') }}</span></div>
            </div>

            <div class="deposit-info-card">
                <h6 class="mb-3"><i class="bi bi-lightning text-warning me-1"></i>Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('containers.create') }}?shelf_id={{ $shelf->id }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>Ajouter un Conteneur
                    </a>
                    <a href="{{ route('containers.index') }}?shelf_id={{ $shelf->id }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-box me-1"></i>Tous les Conteneurs
                    </a>
                    <a href="{{ route('shelves.edit', $shelf) }}" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-pencil me-1"></i>Modifier
                    </a>
                    <form action="{{ route('shelves.destroy', $shelf) }}" method="POST"
                          onsubmit="return confirm('Supprimer cette étagère et tous ses conteneurs ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-trash me-1"></i>Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('containerSearch')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#containerList .container-item').forEach(c => {
        c.style.display = c.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
@endpush
@endsection
