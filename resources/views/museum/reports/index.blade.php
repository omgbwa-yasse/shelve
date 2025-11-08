@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-file-earmark-bar-graph"></i> {{ __('Rapports du musée') }}</h1>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-collection"></i> {{ __('Rapport Collection') }}</h5>
                    <p class="card-text">{{ __('Vue d\'ensemble de la collection') }}</p>
                    <a href="{{ route('museum.reports.collection') }}" class="btn btn-primary">
                        {{ __('Voir le rapport') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-shield-check"></i> {{ __('Rapport Conservation') }}</h5>
                    <p class="card-text">{{ __('État de conservation') }}</p>
                    <a href="{{ route('museum.reports.conservation') }}" class="btn btn-primary">
                        {{ __('Voir le rapport') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-easel"></i> {{ __('Rapport Expositions') }}</h5>
                    <p class="card-text">{{ __('Historique des expositions') }}</p>
                    <a href="{{ route('museum.reports.exhibitions') }}" class="btn btn-primary">
                        {{ __('Voir le rapport') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-cash-stack"></i> {{ __('Rapport Valorisation') }}</h5>
                    <p class="card-text">{{ __('Valeur de la collection') }}</p>
                    <a href="{{ route('museum.reports.valuation') }}" class="btn btn-success">
                        {{ __('Voir le rapport') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-bar-chart"></i> {{ __('Statistiques générales') }}</h5>
                    <p class="card-text">{{ __('Statistiques du musée') }}</p>
                    <a href="{{ route('museum.reports.statistics') }}" class="btn btn-info">
                        {{ __('Voir les statistiques') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
