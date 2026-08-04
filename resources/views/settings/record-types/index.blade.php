@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">Typologies de notices</h1>
            <p class="text-muted">Catalogue unifié des types de notices (dossiers = conteneurs, documents = pièces)</p>
        </div>
        <a href="{{ route('settings.record-types.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Créer une typologie
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
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 150px">Code</th>
                        <th>Nom</th>
                        <th style="width: 120px">Catégorie</th>
                        <th style="width: 110px">Champs</th>
                        <th style="width: 100px">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($types as $type)
                        <tr class="clickable-row" data-href="{{ route('settings.record-types.edit', $type) }}">
                            <td>
                                <span class="badge bg-secondary">{{ $type->code }}</span>
                            </td>
                            <td>
                                <strong>{{ $type->name }}</strong>
                                @if($type->description)
                                    <br><small class="text-muted">{{ $type->description }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $type->is_container ? 'bg-success' : 'bg-info' }}">
                                    {{ $type->is_container ? 'Conteneur (dossier)' : 'Pièce (document)' }}
                                </span>
                            </td>
                            <td><span class="badge bg-light text-dark">{{ $type->metadata_profiles_count }} champ(s)</span></td>
                            <td>
                                @if($type->is_active)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-secondary">Inactif</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <p class="text-muted mb-0">Aucune typologie trouvée</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($types->hasPages())
            <div class="card-footer bg-light">{{ $types->links() }}</div>
        @endif
    </div>
</div>
@endsection
