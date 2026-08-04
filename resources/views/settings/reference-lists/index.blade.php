@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">Domaines de valeurs</h1>
            <p class="text-muted">Référentiels de valeurs (alignés sur DomaineValeurs IntelliGID) utilisés par les métadonnées et les types de notices</p>
        </div>
        <a href="{{ route('settings.reference-lists.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Créer un domaine
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
                        <th style="width: 110px">Valeurs</th>
                        <th style="width: 100px">Statut</th>
                        <th style="width: 150px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lists as $list)
                        <tr>
                            <td><code class="bg-light px-2 py-1 rounded">{{ $list->code }}</code></td>
                            <td>
                                <strong>{{ $list->name }}</strong>
                                @if($list->description)
                                    <br>
                                    <small class="text-muted">{{ $list->description }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $list->values_count }} valeur(s)</span>
                            </td>
                            <td>
                                @if($list->active)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-secondary">Inactif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('settings.reference-lists.show', $list) }}" class="btn btn-outline-primary" title="Voir les valeurs">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('settings.reference-lists.edit', $list) }}" class="btn btn-outline-secondary" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('settings.reference-lists.destroy', $list) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Supprimer ce domaine de valeurs ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <p class="text-muted mb-0">Aucun domaine de valeurs trouvé</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($lists->hasPages())
            <div class="card-footer bg-light">{{ $lists->links() }}</div>
        @endif
    </div>
</div>
@endsection
