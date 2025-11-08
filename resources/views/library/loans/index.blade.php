@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-arrow-left-right"></i> {{ __('Gestion des prêts') }}</h1>
        <a href="{{ route('library.loans.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> {{ __('Nouveau prêt') }}
        </a>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Prêts en cours') }}</h5>
                    <p class="card-text display-4">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Retards') }}</h5>
                    <p class="card-text display-4">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Retours du jour') }}</h5>
                    <p class="card-text display-4">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Prêts du mois') }}</h5>
                    <p class="card-text display-4">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('library.loans.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">{{ __('Recherche') }}</label>
                    <input type="text" id="search" name="search" class="form-control"
                           placeholder="{{ __('Lecteur, ouvrage...') }}"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">{{ __('Statut') }}</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">{{ __('Tous') }}</option>
                        <option value="active">{{ __('En cours') }}</option>
                        <option value="overdue">{{ __('En retard') }}</option>
                        <option value="returned">{{ __('Retourné') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_from" class="form-label">{{ __('Du') }}</label>
                    <input type="date" id="date_from" name="date_from" class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> {{ __('Filtrer') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des prêts -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('library.loans.index') }}">
                        {{ __('En cours') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('library.loans.overdue') }}">
                        {{ __('Retards') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('library.loans.history') }}">
                        {{ __('Historique') }}
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('N° Prêt') }}</th>
                            <th>{{ __('Lecteur') }}</th>
                            <th>{{ __('Ouvrage') }}</th>
                            <th>{{ __('Date prêt') }}</th>
                            <th>{{ __('Date retour prévue') }}</th>
                            <th>{{ __('Statut') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                {{ __('Aucun prêt trouvé') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
