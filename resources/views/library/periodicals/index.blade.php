@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-journal-text"></i> {{ __('Périodiques et revues') }}</h1>
        <div>
            <a href="{{ route('library.periodicals.articles') }}" class="btn btn-secondary">
                <i class="bi bi-search"></i> {{ __('Rechercher des articles') }}
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('library.periodicals.index') }}" class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">{{ __('Recherche') }}</label>
                    <input type="text" id="search" name="search" class="form-control"
                           placeholder="{{ __('Titre, ISSN...') }}"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <label for="frequency" class="form-label">{{ __('Périodicité') }}</label>
                    <select id="frequency" name="frequency" class="form-select">
                        <option value="">{{ __('Toutes') }}</option>
                        <option value="daily">{{ __('Quotidien') }}</option>
                        <option value="weekly">{{ __('Hebdomadaire') }}</option>
                        <option value="monthly">{{ __('Mensuel') }}</option>
                        <option value="quarterly">{{ __('Trimestriel') }}</option>
                        <option value="annual">{{ __('Annuel') }}</option>
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

    <!-- Liste des périodiques -->
    <div class="row g-4">
        {{-- @forelse($periodicals as $periodical) --}}
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ __('Revue exemple') }}</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        <strong>{{ __('ISSN:') }}</strong> 1234-5678<br>
                        <strong>{{ __('Éditeur:') }}</strong> Éditeur exemple<br>
                        <strong>{{ __('Périodicité:') }}</strong> Mensuel<br>
                        <strong>{{ __('Numéros disponibles:') }}</strong> 0
                    </p>
                    <p class="card-text text-muted">
                        {{ __('Description de la revue...') }}
                    </p>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="#" class="btn btn-sm btn-info">
                            <i class="bi bi-eye"></i> {{ __('Voir les numéros') }}
                        </a>
                        <a href="#" class="btn btn-sm btn-primary">
                            <i class="bi bi-search"></i> {{ __('Articles') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        {{-- @empty --}}
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle"></i> {{ __('Aucun périodique trouvé') }}
            </div>
        </div>
        {{-- @endforelse --}}
    </div>

    <!-- Section Numéros récents -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-clock-history"></i> {{ __('Derniers numéros reçus') }}
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('Revue') }}</th>
                            <th>{{ __('Numéro') }}</th>
                            <th>{{ __('Date de parution') }}</th>
                            <th>{{ __('Date de réception') }}</th>
                            <th>{{ __('Articles') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                {{ __('Aucun numéro récent') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
