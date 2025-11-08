@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-palette"></i> {{ __('Collections du musée') }}</h1>
        <div>
            <div class="btn-group" role="group">
                <a href="{{ route('museum.artifacts.index', ['view' => 'gallery']) }}"
                   class="btn btn-outline-primary {{ request('view', 'gallery') === 'gallery' ? 'active' : '' }}">
                    <i class="bi bi-grid-3x3"></i> {{ __('Galerie') }}
                </a>
                <a href="{{ route('museum.artifacts.index', ['view' => 'list']) }}"
                   class="btn btn-outline-primary {{ request('view') === 'list' ? 'active' : '' }}">
                    <i class="bi bi-list-ul"></i> {{ __('Liste') }}
                </a>
            </div>
            <a href="{{ route('museum.artifacts.create') }}" class="btn btn-primary ms-2">
                <i class="bi bi-plus-circle"></i> {{ __('Nouvelle pièce') }}
            </a>
        </div>
    </div>

    <!-- Filtres de recherche -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('museum.artifacts.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">{{ __('Recherche') }}</label>
                    <input type="text" id="search" name="search" class="form-control"
                           placeholder="{{ __('Code, nom, description...') }}"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label for="category" class="form-label">{{ __('Catégorie') }}</label>
                    <select id="category" name="category" class="form-select">
                        <option value="">{{ __('Toutes') }}</option>
                        <option value="painting">{{ __('Peinture') }}</option>
                        <option value="sculpture">{{ __('Sculpture') }}</option>
                        <option value="artifact">{{ __('Artefact') }}</option>
                        <option value="photo">{{ __('Photographie') }}</option>
                        <option value="document">{{ __('Document') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="collection" class="form-label">{{ __('Collection') }}</label>
                    <select id="collection" name="collection_id" class="form-select">
                        <option value="">{{ __('Toutes') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">{{ __('Statut') }}</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">{{ __('Tous') }}</option>
                        <option value="available">{{ __('Disponible') }}</option>
                        <option value="exhibition">{{ __('En exposition') }}</option>
                        <option value="loan">{{ __('En prêt') }}</option>
                        <option value="restoration">{{ __('En restauration') }}</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> {{ __('Rechercher') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(request('view', 'gallery') === 'gallery')
        <!-- Vue Galerie -->
        <div class="row g-4">
            {{-- @forelse($artifacts as $artifact) --}}
            <div class="col-md-3">
                <div class="card h-100">
                    <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Artifact">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('Exemple de pièce') }}</h5>
                        <p class="card-text text-muted small">Code: ART-001</p>
                        <p class="card-text">{{ __('Description courte de la pièce...') }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-success">{{ __('Disponible') }}</span>
                            <div>
                                <a href="#" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- @empty --}}
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> {{ __('Aucune pièce trouvée') }}
                </div>
            </div>
            {{-- @endforelse --}}
        </div>
    @else
        <!-- Vue Liste -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Code') }}</th>
                                <th>{{ __('Nom') }}</th>
                                <th>{{ __('Catégorie') }}</th>
                                <th>{{ __('Collection') }}</th>
                                <th>{{ __('Date acquisition') }}</th>
                                <th>{{ __('Valeur') }}</th>
                                <th>{{ __('Statut') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    {{ __('Aucune pièce trouvée') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
