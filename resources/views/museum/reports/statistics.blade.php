@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-bar-chart"></i> {{ __('Statistiques du musée') }}</h1>
        <a href="{{ route('museum.reports.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
        </a>
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
                    <h5 class="card-title">{{ __('Collections') }}</h5>
                    <p class="card-text display-4">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Expositions') }}</h5>
                    <p class="card-text display-4">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Valeur totale') }}</h5>
                    <p class="card-text display-4">0 €</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-pie-chart"></i> {{ __('Répartition par catégorie') }}</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">{{ __('Graphique à venir') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-bar-chart-line"></i> {{ __('État de conservation') }}</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">{{ __('Graphique à venir') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
