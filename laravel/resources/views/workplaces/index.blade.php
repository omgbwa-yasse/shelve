@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Espaces de travail</h2>
        </div>
        <div class="col-md-4 text-end">
            @can('create_workplaces')
            <a href="{{ route('workplaces.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nouveau workspace
            </a>
            @endcan
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('workplaces.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-select">
                            <option value="">Toutes les catégories</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                            <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archivé</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspendu</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">
                            <i class="bi bi-search"></i> Filtrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Workplaces Grid -->
    <div class="row">
        @forelse($workplaces as $workplace)
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="badge bg-{{ $workplace->category->color ?? 'secondary' }}">
                        @if($workplace->category->icon)
                        <i class="bi bi-{{ $workplace->category->icon }}"></i>
                        @endif
                        {{ $workplace->category->name }}
                    </span>
                    <span class="badge bg-{{ $workplace->status == 'active' ? 'success' : 'secondary' }}">
                        {{ $workplace->status }}
                    </span>
                </div>
                <div class="card-body">
                    <h5 class="card-title">
                        <a href="{{ route('workplaces.show', $workplace) }}">{{ $workplace->name }}</a>
                    </h5>
                    <p class="text-muted small">{{ $workplace->code }}</p>
                    <p class="card-text">{{ Str::limit($workplace->description, 100) }}</p>

                    <div class="row text-center mt-3">
                        <div class="col-6">
                            <small class="text-muted">Membres</small>
                            <div class="fw-bold">{{ $workplace->members_count }}/{{ $workplace->max_members ?? '∞' }}</div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Stockage</small>
                            <div class="fw-bold">{{ number_format($workplace->storageUsedMb, 2) }} MB</div>
                        </div>
                    </div>

                    @if($workplace->max_storage_mb)
                    <div class="progress mt-2" style="height: 5px;">
                        <div class="progress-bar" role="progressbar"
                             style="width: {{ $workplace->storagePercentage }}%"
                             aria-valuenow="{{ $workplace->storagePercentage }}"
                             aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    @endif
                </div>
                <div class="card-footer text-muted small">
                    <i class="bi bi-person"></i> {{ $workplace->owner->name }}
                    · <i class="bi bi-calendar"></i> {{ $workplace->created_at->format('d/m/Y') }}
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">
                Aucun espace de travail trouvé.
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $workplaces->links() }}
    </div>
</div>
@endsection
