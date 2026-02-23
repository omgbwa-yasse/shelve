@extends('layouts.app')

@include('deposits.partials._deposit-styles')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="deposit-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="deposit-title">
                    <i class="bi bi-building text-primary"></i> {{ __('Gestion des Bâtiments') }}
                </h1>
                <p class="deposit-subtitle">Vue d'ensemble de vos infrastructures</p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <input type="text" class="form-control form-control-sm" id="searchBuilding"
                       placeholder="Rechercher..." style="width: 200px;">
                <a href="{{ route('buildings.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Nouveau
                </a>
            </div>
        </div>
    </div>

    {{-- Breadcrumb --}}
    <nav class="deposit-breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item active"><i class="bi bi-building me-1"></i> Bâtiments</li>
        </ol>
    </nav>

    {{-- Stats compacts --}}
    @php
        $totalBuildings = $buildings->total();
        $publicBuildings = $buildings->where('visibility', 'public')->count();
        $privateBuildings = $buildings->where('visibility', 'private')->count();
        $floorsCount = $buildings->sum(fn($b) => $b->floors->count());
    @endphp
    <div class="deposit-stats">
        <div class="deposit-stat">
            <div class="deposit-stat-value text-primary">{{ $totalBuildings }}</div>
            <div class="deposit-stat-label">Bâtiments</div>
        </div>
        <div class="deposit-stat">
            <div class="deposit-stat-value text-success">{{ $publicBuildings }}</div>
            <div class="deposit-stat-label">Public</div>
        </div>
        <div class="deposit-stat">
            <div class="deposit-stat-value text-danger">{{ $privateBuildings }}</div>
            <div class="deposit-stat-label">Privé</div>
        </div>
        <div class="deposit-stat">
            <div class="deposit-stat-value text-info">{{ $floorsCount }}</div>
            <div class="deposit-stat-label">Étages</div>
        </div>
    </div>

    {{-- Table des bâtiments --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table deposit-table table-hover mb-0" id="buildingTable">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Visibilité</th>
                            <th class="text-center">Étages</th>
                            <th class="text-center">Salles</th>
                            <th>Description</th>
                            <th>Créé le</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($buildings as $building)
                            <tr>
                                <td>
                                    <a href="{{ route('buildings.show', $building->id) }}" class="fw-bold text-decoration-none">
                                        <i class="bi bi-building text-primary me-1"></i>
                                        {{ $building->name ?? 'N/A' }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $building->visibility == 'public' ? 'success' : ($building->visibility == 'private' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($building->visibility ?? 'N/A') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $building->floors->count() }}</span>
                                </td>
                                <td class="text-center">
                                    {{ $building->floors->sum(fn($f) => $f->rooms->count()) }}
                                </td>
                                <td class="text-muted">{{ Str::limit($building->description, 50) }}</td>
                                <td class="text-muted">{{ $building->created_at?->format('d/m/Y') ?? '-' }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('buildings.show', $building->id) }}" class="btn btn-outline-primary" title="Voir">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('buildings.edit', $building->id) }}" class="btn btn-outline-secondary" title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('buildings.destroy', $building->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Supprimer ce bâtiment ?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm" title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="bi bi-building display-4 text-muted d-block mb-2"></i>
                                    <p class="text-muted mb-2">Aucun bâtiment trouvé</p>
                                    <a href="{{ route('buildings.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-circle me-1"></i> Créer un bâtiment
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    @if(method_exists($buildings, 'hasPages') && $buildings->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $buildings->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
document.getElementById('searchBuilding')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#buildingTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
@endpush
@endsection
