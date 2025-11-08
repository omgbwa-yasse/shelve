@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-people"></i> {{ __('Gestion des lecteurs') }}</h1>
        <a href="{{ route('library.readers.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> {{ __('Nouveau lecteur') }}
        </a>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Lecteurs actifs') }}</h5>
                    <p class="card-text display-4">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Nouvelles inscriptions (mois)') }}</h5>
                    <p class="card-text display-4">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Cartes à renouveler') }}</h5>
                    <p class="card-text display-4">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres de recherche -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('library.readers.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">{{ __('Recherche') }}</label>
                    <input type="text" id="search" name="search" class="form-control"
                           placeholder="{{ __('Nom, prénom, email, n° carte...') }}"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">{{ __('Statut') }}</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">{{ __('Tous') }}</option>
                        <option value="active">{{ __('Actif') }}</option>
                        <option value="inactive">{{ __('Inactif') }}</option>
                        <option value="expired">{{ __('Carte expirée') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="category" class="form-label">{{ __('Catégorie') }}</label>
                    <select id="category" name="category" class="form-select">
                        <option value="">{{ __('Toutes') }}</option>
                        <option value="student">{{ __('Étudiant') }}</option>
                        <option value="teacher">{{ __('Enseignant') }}</option>
                        <option value="staff">{{ __('Personnel') }}</option>
                        <option value="external">{{ __('Externe') }}</option>
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

    <!-- Liste des lecteurs -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('N° Carte') }}</th>
                            <th>{{ __('Nom complet') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Téléphone') }}</th>
                            <th>{{ __('Catégorie') }}</th>
                            <th>{{ __('Statut') }}</th>
                            <th>{{ __('Expiration') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                {{ __('Aucun lecteur trouvé') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
