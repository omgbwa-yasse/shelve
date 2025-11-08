@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-person"></i> {{ __('Détails du lecteur') }}</h1>
        <div>
            <a href="{{ route('library.readers.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Informations du lecteur -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-person-circle"></i> {{ __('Informations personnelles') }}</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">{{ __('Aucun lecteur sélectionné') }}</p>
                </div>
            </div>

            <!-- Historique des prêts -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-clock-history"></i> {{ __('Historique des prêts') }}</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">{{ __('Aucun prêt trouvé') }}</p>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-gear"></i> {{ __('Actions') }}</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="#" class="btn btn-primary">
                        <i class="bi bi-card-text"></i> {{ __('Imprimer la carte') }}
                    </a>
                    <a href="#" class="btn btn-success">
                        <i class="bi bi-book"></i> {{ __('Nouveau prêt') }}
                    </a>
                    <a href="#" class="btn btn-info">
                        <i class="bi bi-pencil"></i> {{ __('Modifier') }}
                    </a>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-bar-chart"></i> {{ __('Statistiques') }}</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">{{ __('Aucune donnée disponible') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
