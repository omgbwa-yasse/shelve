@extends('layouts.app')

@include('deposits.partials._deposit-styles')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="deposit-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="deposit-title"><i class="bi bi-house-door text-info"></i> Salles d'archives</h1>
                <p class="deposit-subtitle">Gestion et organisation de vos salles de dépôt</p>
            </div>
            <div class="d-flex gap-2">
                <input type="text" class="form-control form-control-sm" id="searchInput"
                       placeholder="Rechercher..." style="max-width:220px;">
                <a href="{{ route('rooms.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>Nouvelle
                </a>
            </div>
        </div>
    </div>

    {{-- Breadcrumb --}}
    <nav class="deposit-breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('buildings.index') }}"><i class="bi bi-building me-1"></i>Dépôts</a></li>
            <li class="breadcrumb-item active"><i class="bi bi-house-door me-1"></i>Salles</li>
        </ol>
    </nav>

    {{-- Stats --}}
    <div class="deposit-stats">
        <div class="deposit-stat">
            <span class="deposit-stat-value text-primary">{{ $rooms->count() }}</span>
            <span class="deposit-stat-label">Total</span>
        </div>
        <div class="deposit-stat">
            <span class="deposit-stat-value text-success">{{ $rooms->where('visibility', 'public')->count() }}</span>
            <span class="deposit-stat-label">Visibles</span>
        </div>
        <div class="deposit-stat">
            <span class="deposit-stat-value text-warning">{{ $rooms->sum('shelves_count') }}</span>
            <span class="deposit-stat-label">Étagères</span>
        </div>
        <div class="deposit-stat">
            <span class="deposit-stat-value text-info">{{ $rooms->unique('floor_id')->count() }}</span>
            <span class="deposit-stat-label">Étages</span>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-responsive">
        <table class="deposit-table" id="roomsTable">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Bâtiment / Étage</th>
                    <th>Visibilité</th>
                    <th>Étagères</th>
                    <th>Description</th>
                    <th>Créée le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rooms as $room)
                <tr>
                    <td>
                        <a href="{{ route('rooms.show', $room) }}" class="fw-semibold text-decoration-none">
                            <i class="bi bi-house-door text-info me-1"></i>{{ $room->name ?? 'Sans nom' }}
                        </a>
                    </td>
                    <td>
                        @if($room->floor && $room->floor->building)
                            <small>{{ $room->floor->building->name }}</small>
                            <i class="bi bi-chevron-right text-muted" style="font-size:.65rem"></i>
                            <small>{{ $room->floor->name ?? 'N/A' }}</small>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($room->is_visible)
                            <span class="badge bg-success">Visible</span>
                        @else
                            <span class="badge bg-secondary">Masquée</span>
                        @endif
                    </td>
                    <td><span class="badge bg-warning text-dark">{{ $room->shelves_count ?? 0 }}</span></td>
                    <td><small class="text-muted">{{ Str::limit($room->description, 50) }}</small></td>
                    <td><small>{{ $room->created_at?->format('d/m/Y') ?? '—' }}</small></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('rooms.show', $room) }}" class="btn btn-outline-primary" title="Voir"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('rooms.edit', $room) }}" class="btn btn-outline-warning" title="Modifier"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('rooms.destroy', $room) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Supprimer cette salle ?')">
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
                            <i class="bi bi-house-door d-block"></i>
                            <h6>Aucune salle</h6>
                            <a href="{{ route('rooms.create') }}" class="btn btn-primary btn-sm mt-2">
                                <i class="bi bi-plus-circle me-1"></i>Créer une salle
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('searchInput')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#roomsTable tbody tr').forEach(r => {
        r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
@endpush
@endsection
