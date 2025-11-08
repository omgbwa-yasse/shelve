@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-person-lines-fill"></i> {{ __('Gestion des auteurs') }}</h1>
        <a href="{{ route('library.authors.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> {{ __('Nouvel auteur') }}
        </a>
    </div>

    <!-- Filtres de recherche -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('library.authors.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">{{ __('Recherche') }}</label>
                    <input type="text" id="search" name="search" class="form-control"
                           placeholder="{{ __('Nom, prénom, nationalité...') }}"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label for="nationality" class="form-label">{{ __('Nationalité') }}</label>
                    <input type="text" id="nationality" name="nationality" class="form-control"
                           value="{{ request('nationality') }}">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">{{ __('Statut') }}</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">{{ __('Tous') }}</option>
                        <option value="alive">{{ __('Vivant') }}</option>
                        <option value="deceased">{{ __('Décédé') }}</option>
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

    <!-- Liste des auteurs -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('Nom complet') }}</th>
                            <th>{{ __('Date de naissance') }}</th>
                            <th>{{ __('Date de décès') }}</th>
                            <th>{{ __('Nationalité') }}</th>
                            <th>{{ __('Nombre d\'ouvrages') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                {{ __('Aucun auteur trouvé') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
