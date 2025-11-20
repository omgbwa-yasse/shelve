@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <h1 class="h3">
                <i class="bi bi-folder"></i> Dossiers Numériques
            </h1>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filtres et recherche -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('folders.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type_id" class="form-select">
                        <option value="">-- Type --</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" {{ request('type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">-- Statut --</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archivé</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Fermé</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="organisation_id" class="form-select">
                        <option value="">-- Organisation --</option>
                        @foreach($organisations as $org)
                            <option value="{{ $org->id }}" {{ request('organisation_id') == $org->id ? 'selected' : '' }}>
                                {{ $org->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Filtrer
                        </button>
                        <a href="{{ route('folders.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Actions -->
    <div class="mb-3">
        <a href="{{ route('folders.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Nouveau dossier
        </a>
        <a href="{{ route('folders.tree.view') }}" class="btn btn-info">
            <i class="bi bi-diagram-3"></i> Vue arborescente
        </a>
    </div>

    <!-- Liste des dossiers -->
    <div class="card">
        <div class="card-body">
            @if($folders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Nom</th>
                                <th>Type</th>
                                <th>Parent</th>
                                <th>Statut</th>
                                <th>Documents</th>
                                <th>Sous-dossiers</th>
                                <th>Taille</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($folders as $folder)
                                <tr>
                                    <td>
                                        <code>{{ $folder->code }}</code>
                                    </td>
                                    <td>
                                        <a href="{{ route('folders.show', $folder) }}">
                                            <i class="bi bi-folder"></i> {{ $folder->name }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $folder->type->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        @if($folder->parent)
                                            <a href="{{ route('folders.show', $folder->parent) }}" class="text-muted">
                                                {{ $folder->parent->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($folder->status === 'active')
                                            <span class="badge bg-success">Actif</span>
                                        @elseif($folder->status === 'archived')
                                            <span class="badge bg-warning">Archivé</span>
                                        @else
                                            <span class="badge bg-secondary">Fermé</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $folder->documents_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $folder->children_count }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $folder->total_size_human }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('folders.show', $folder) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('folders.edit', $folder) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $folders->withQueryString()->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Aucun dossier trouvé.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
