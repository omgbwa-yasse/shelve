@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-speedometer2"></i> {{ __('Statistiques de la bibliothèque') }}</h1>
    </div>

    <!-- Statistiques générales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Total livres') }}</h5>
                    <p class="card-text display-4">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Prêts actifs') }}</h5>
                    <p class="card-text display-4">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Lecteurs actifs') }}</h5>
                    <p class="card-text display-4">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Retards') }}</h5>
                    <p class="card-text display-4">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-graph-up"></i> {{ __('Prêts par mois (12 derniers mois)') }}</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">{{ __('Graphique à venir') }}</p>
                </div>
            </div>
        </div>
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
    </div>

    <!-- Top livres et auteurs -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-star"></i> {{ __('Livres les plus empruntés') }}</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">{{ __('Aucune donnée disponible') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-person-check"></i> {{ __('Auteurs les plus populaires') }}</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">{{ __('Aucune donnée disponible') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
