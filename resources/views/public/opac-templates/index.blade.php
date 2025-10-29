@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Templates OPAC</h1>
            <p class="text-muted">Gérez les templates d'affichage du catalogue public depuis le portail</p>
        </div>
        <a href="{{ route('public.opac-templates.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouveau Template
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('public.opac-templates.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Rechercher</label>
                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}"
                           placeholder="Nom ou description...">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Statut</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="sort" class="form-label">Trier par</label>
                    <select class="form-select" id="sort" name="sort">
                        <option value="name" {{ request('sort', 'name') === 'name' ? 'selected' : '' }}>Nom</option>
                        <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Date de création</option>
                        <option value="updated_at" {{ request('sort') === 'updated_at' ? 'selected' : '' }}>Dernière modification</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">Filtrer</button>
                    <a href="{{ route('public.opac-templates.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des templates -->
    <div class="row">
        @forelse($templates as $template)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $template->name }}</h5>
                        <span class="badge bg-{{ $template->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($template->status) }}
                        </span>
                    </div>

                    <div class="card-body">
                        <p class="card-text text-muted">{{ $template->description }}</p>

                        <div class="template-preview mb-3">
                            <div class="border rounded p-2" style="height: 150px; overflow: hidden; background: #f8f9fa;">
                                <div class="template-thumbnail">
                                    <!-- Aperçu miniature du template -->
                                    <div style="background: {{ $template->variables['primary_color'] ?? '#007bff' }}; height: 30px; width: 100%; margin-bottom: 5px; border-radius: 3px;"></div>
                                    <div class="d-flex gap-1 mb-2">
                                        <div style="background: {{ $template->variables['secondary_color'] ?? '#6c757d' }}; height: 15px; width: 60%; border-radius: 2px;"></div>
                                        <div style="background: {{ $template->variables['accent_color'] ?? '#28a745' }}; height: 15px; width: 30%; border-radius: 2px;"></div>
                                    </div>
                                    <div class="d-flex flex-column gap-1">
                                        <div style="background: #dee2e6; height: 8px; width: 80%; border-radius: 1px;"></div>
                                        <div style="background: #dee2e6; height: 8px; width: 60%; border-radius: 1px;"></div>
                                        <div style="background: #dee2e6; height: 8px; width: 90%; border-radius: 1px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between text-muted small mb-3">
                            <span>Créé: {{ $template->created_at->format('d/m/Y') }}</span>
                            <span>Modifié: {{ $template->updated_at->format('d/m/Y') }}</span>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="btn-group w-100">
                            <a href="{{ route('public.opac-templates.show', $template) }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-eye"></i> Voir
                            </a>
                            <a href="{{ route('public.opac-templates.preview', $template) }}" class="btn btn-outline-success btn-sm" target="_blank">
                                <i class="fas fa-external-link-alt"></i> Aperçu
                            </a>
                            <a href="{{ route('public.opac-templates.edit', $template) }}" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <div class="btn-group">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <form method="POST" action="{{ route('public.opac-templates.duplicate', $template) }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <i class="fas fa-copy"></i> Dupliquer
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('public.opac-templates.export', $template) }}">
                                            <i class="fas fa-download"></i> Exporter
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('public.opac-templates.destroy', $template) }}"
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce template ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-palette fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Aucun template trouvé</h4>
                    <p class="text-muted">Commencez par créer votre premier template OPAC.</p>
                    <a href="{{ route('public.opac-templates.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Créer un template
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($templates->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $templates->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<style>
.template-thumbnail {
    transform: scale(0.8);
    transform-origin: top left;
}
</style>
@endsection
