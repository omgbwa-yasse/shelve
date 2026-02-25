@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">Gestion des types de dossiers numériques</h1>
            <p class="text-muted">Définissez et gérez les types de dossiers disponibles dans votre organisation</p>
        </div>
        <a href="{{ route('settings.folder-types.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Ajouter un type
        </a>
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

    <div class="card">
        <div class="card-header bg-light">
            <form class="row g-2" method="GET" action="{{ route('settings.folder-types.index') }}">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">-- Tous les statuts --</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-search"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px">Code</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th style="width: 100px">Statut</th>
                        <th style="width: 200px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($folderTypes as $type)
                        <tr>
                            <td>
                                <span class="badge bg-secondary">{{ $type->code }}</span>
                            </td>
                            <td>
                                <strong>{{ $type->name }}</strong>
                                @if($type->icon)
                                    <i class="{{ $type->icon }} ms-2"></i>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $type->description ? Str::limit($type->description, 50) : '-' }}</small>
                            </td>
                            <td>
                                @if($type->is_active)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-secondary">Inactif</span>
                                @endif
                                @if($type->is_system)
                                    <span class="badge bg-info ms-1">Système</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('settings.folder-types.show', $type) }}" class="btn btn-outline-primary"
                                       title="Voir les détails">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('settings.folder-types.edit', $type) }}" class="btn btn-outline-secondary"
                                       title="Modifier" @if($type->is_system) disabled @endif>
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="{{ route('settings.folder-types.metadata-profiles.index', $type) }}"
                                       class="btn btn-outline-info" title="Gérer les métadonnées">
                                        <i class="bi bi-gear"></i>
                                    </a>
                                    @if(!$type->is_system)
                                        <form action="{{ route('settings.folder-types.destroy', $type) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce type ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <p class="text-muted mb-0">Aucun type de dossier trouvé</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($folderTypes->hasPages())
            <div class="card-footer bg-light">
                {{ $folderTypes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
