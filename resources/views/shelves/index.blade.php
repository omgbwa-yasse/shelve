@extends('layouts.app')

@include('deposits.partials._deposit-styles')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="deposit-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="deposit-title"><i class="bi bi-bookshelf text-warning"></i> Gestion des Étagères</h1>
                <p class="deposit-subtitle">Vue d'ensemble de vos espaces de stockage</p>
            </div>
            <div class="d-flex gap-2">
                <input type="text" class="form-control form-control-sm" id="searchInput"
                       placeholder="Rechercher..." style="max-width:220px;">
                <a href="{{ route('shelves.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>Nouvelle
                </a>
            </div>
        </div>
    </div>

    {{-- Breadcrumb --}}
    <nav class="deposit-breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('buildings.index') }}"><i class="bi bi-building me-1"></i>Bâtiments</a></li>
            <li class="breadcrumb-item"><a href="{{ route('rooms.index') }}"><i class="bi bi-house-door me-1"></i>Salles</a></li>
            <li class="breadcrumb-item active"><i class="bi bi-bookshelf me-1"></i>Étagères</li>
        </ol>
    </nav>

    {{-- Stats --}}
    @php
        $totalShelves = $shelves->count();
        $totalCapacity = 0;
        $totalOccupied = 0;
        foreach($shelves as $s) {
            $totalCapacity += $s->face * $s->ear * $s->shelf;
            $totalOccupied += $s->containers->count();
        }
        $globalOccupancy = $totalCapacity > 0 ? round(($totalOccupied / $totalCapacity) * 100, 1) : 0;
    @endphp
    <div class="deposit-stats">
        <div class="deposit-stat">
            <span class="deposit-stat-value text-primary">{{ $totalShelves }}</span>
            <span class="deposit-stat-label">Étagères</span>
        </div>
        <div class="deposit-stat">
            <span class="deposit-stat-value text-success">{{ $totalCapacity }}</span>
            <span class="deposit-stat-label">Capacité</span>
        </div>
        <div class="deposit-stat">
            <span class="deposit-stat-value text-danger">{{ $totalOccupied }}</span>
            <span class="deposit-stat-label">Occupés</span>
        </div>
        <div class="deposit-stat">
            <span class="deposit-stat-value text-info">{{ $globalOccupancy }}%</span>
            <span class="deposit-stat-label">Occupation</span>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-responsive">
        <table class="deposit-table" id="shelvesTable">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Emplacement</th>
                    <th>F / T / Tab</th>
                    <th>Occupation</th>
                    <th>ML</th>
                    <th>Observation</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($shelves as $shelf)
                @php
                    $cap = $shelf->face * $shelf->ear * $shelf->shelf;
                    $occ = $shelf->containers->count();
                    $pct = $cap > 0 ? round(($occ / $cap) * 100) : 0;
                    $color = $pct >= 90 ? 'danger' : ($pct >= 70 ? 'warning' : 'success');
                @endphp
                <tr>
                    <td>
                        <a href="{{ route('shelves.show', $shelf) }}" class="fw-semibold text-decoration-none">
                            <i class="bi bi-bookshelf text-warning me-1"></i>{{ $shelf->code }}
                        </a>
                    </td>
                    <td>
                        <small>
                            {{ $shelf->room->floor->building->name ?? '?' }}
                            <i class="bi bi-chevron-right text-muted" style="font-size:.65rem"></i>
                            {{ $shelf->room->floor->name ?? '?' }}
                            <i class="bi bi-chevron-right text-muted" style="font-size:.65rem"></i>
                            {{ $shelf->room->name ?? '?' }}
                        </small>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark">{{ $shelf->face }} / {{ $shelf->ear }} / {{ $shelf->shelf }}</span>
                    </td>
                    <td style="min-width:140px;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="deposit-occupancy-bar flex-grow-1">
                                <div class="deposit-occupancy-fill bg-{{ $color }}" style="width:{{ $pct }}%"></div>
                            </div>
                            <small class="text-{{ $color }} fw-semibold">{{ $pct }}%</small>
                        </div>
                        <small class="text-muted">{{ $occ }}/{{ $cap }}</small>
                    </td>
                    <td><small>{{ $shelf->volumetry_ml ?? 0 }}</small></td>
                    <td><small class="text-muted">{{ Str::limit($shelf->observation, 40) }}</small></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('shelves.show', $shelf) }}" class="btn btn-outline-primary" title="Voir"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('shelves.edit', $shelf) }}" class="btn btn-outline-warning" title="Modifier"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('shelves.destroy', $shelf) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Supprimer cette étagère ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm" title="Supprimer"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="deposit-empty">
                            <i class="bi bi-bookshelf d-block"></i>
                            <h6>Aucune étagère</h6>
                            <a href="{{ route('shelves.create') }}" class="btn btn-primary btn-sm mt-2">
                                <i class="bi bi-plus-circle me-1"></i>Créer une étagère
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($shelves, 'hasPages') && $shelves->hasPages())
        <div class="d-flex justify-content-center mt-3">{{ $shelves->links() }}</div>
    @endif
</div>

@push('scripts')
<script>
document.getElementById('searchInput')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#shelvesTable tbody tr').forEach(r => {
        r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
@endpush
@endsection
