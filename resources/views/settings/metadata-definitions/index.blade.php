@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">Définitions de métadonnées</h1>
            <p class="text-muted">Gérez les champs de métadonnées disponibles pour les dossiers et documents</p>
        </div>
        <a href="{{ route('settings.metadata-definitions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Ajouter une définition
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
                        <th>Type</th>
                        <th style="width: 100px">Statut</th>
                        <th style="width: 150px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($definitions as $definition)
                        <tr>
                            <td>
                                <code class="bg-light px-2 py-1 rounded">{{ $definition->code }}</code>
                            </td>
                            <td>
                                <strong>{{ $definition->name }}</strong>
                                @if($definition->description)
                                    <br>
                                    <small class="text-muted">{{ $definition->description }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $definition->data_type }}</span>
                            </td>
                            <td>
                                @if($definition->active)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-secondary">Inactif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('settings.metadata-definitions.edit', $definition) }}"
                                       class="btn btn-outline-secondary"
                                       title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('settings.metadata-definitions.destroy', $definition) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette définition ?');">
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
                                <p class="text-muted mb-0">Aucune définition de métadonnée trouvée</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($definitions->hasPages())
            <div class="card-footer bg-light">
                {{ $definitions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
