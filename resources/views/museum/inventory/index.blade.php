@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-clipboard-data"></i> {{ __('Dashboard Inventaire') }}</h1>
    </div>

    <!-- Statistiques générales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Total artefacts') }}</h5>
                    <p class="card-text display-4">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Bon état') }}</h5>
                    <p class="card-text display-4">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">{{ __('État moyen') }}</h5>
                    <p class="card-text display-4">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Mauvais état') }}</h5>
                    <p class="card-text display-4">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-check2-square"></i> {{ __('Récolement') }}</h5>
                </div>
                <div class="card-body">
                    <p>{{ __('Effectuer le récolement des collections') }}</p>
                    <a href="{{ route('museum.inventory.recolement') }}" class="btn btn-primary">
                        <i class="bi bi-clipboard-check"></i> {{ __('Démarrer le récolement') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-file-earmark-text"></i> {{ __('Rapports') }}</h5>
                </div>
                <div class="card-body">
                    <p>{{ __('Générer des rapports d\'inventaire') }}</p>
                    <a href="{{ route('museum.reports.collection') }}" class="btn btn-info">
                        <i class="bi bi-file-earmark-bar-graph"></i> {{ __('Voir les rapports') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
