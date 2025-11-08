@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-file-earmark-bar-graph"></i> {{ __('Rapports de la bibliothèque') }}</h1>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-book"></i> {{ __('Rapport Collection') }}</h5>
                    <p class="card-text">{{ __('Vue d\'ensemble de la collection') }}</p>
                    <a href="{{ route('library.reports.collection') }}" class="btn btn-primary">
                        {{ __('Voir le rapport') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-calendar-check"></i> {{ __('Rapport Prêts') }}</h5>
                    <p class="card-text">{{ __('Statistiques des prêts') }}</p>
                    <a href="{{ route('library.reports.loans') }}" class="btn btn-primary">
                        {{ __('Voir le rapport') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-clipboard-data"></i> {{ __('Rapport Inventaire') }}</h5>
                    <p class="card-text">{{ __('État de l\'inventaire') }}</p>
                    <a href="{{ route('library.reports.inventory') }}" class="btn btn-primary">
                        {{ __('Voir le rapport') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-people"></i> {{ __('Rapport Lecteurs') }}</h5>
                    <p class="card-text">{{ __('Statistiques des lecteurs') }}</p>
                    <a href="{{ route('library.reports.readers') }}" class="btn btn-primary">
                        {{ __('Voir le rapport') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-exclamation-triangle"></i> {{ __('Rapport Retards') }}</h5>
                    <p class="card-text">{{ __('Prêts en retard et amendes') }}</p>
                    <a href="{{ route('library.reports.overdue') }}" class="btn btn-danger">
                        {{ __('Voir le rapport') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
