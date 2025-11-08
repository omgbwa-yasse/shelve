@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-collection"></i> {{ __('Gestion des collections') }}</h1>
        <a href="{{ route('museum.collections.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> {{ __('Nouvelle collection') }}
        </a>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Collections') }}</h5>
                    <p class="card-text display-4">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Total pièces') }}</h5>
                    <p class="card-text display-4">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">{{ __('En exposition') }}</h5>
                    <p class="card-text display-4">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Valorisation totale') }}</h5>
                    <p class="card-text display-6">0 €</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des collections -->
    <div class="row g-4">
        {{-- @forelse($collections as $collection) --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-folder"></i> {{ __('Collection exemple') }}
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text">{{ __('Description de la collection...') }}</p>
                    <div class="row text-center mt-3">
                        <div class="col-4">
                            <strong>0</strong>
                            <div class="text-muted small">{{ __('Pièces') }}</div>
                        </div>
                        <div class="col-4">
                            <strong>0</strong>
                            <div class="text-muted small">{{ __('Expositions') }}</div>
                        </div>
                        <div class="col-4">
                            <strong>0 €</strong>
                            <div class="text-muted small">{{ __('Valeur') }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="#" class="btn btn-sm btn-info">
                            <i class="bi bi-eye"></i> {{ __('Voir') }}
                        </a>
                        <div>
                            <a href="#" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- @empty --}}
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle"></i> {{ __('Aucune collection trouvée') }}
            </div>
        </div>
        {{-- @endforelse --}}
    </div>
</div>
@endsection
