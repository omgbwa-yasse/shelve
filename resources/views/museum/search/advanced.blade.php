@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-funnel"></i> {{ __('Recherche avancée') }}</h1>
        <a href="{{ route('museum.search.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Recherche simple') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('museum.search.advanced') }}">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">{{ __('Nom') }}</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ request('name') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="code" class="form-label">{{ __('Code') }}</label>
                        <input type="text" class="form-control" id="code" name="code" value="{{ request('code') }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="category" class="form-label">{{ __('Catégorie') }}</label>
                        <input type="text" class="form-control" id="category" name="category" value="{{ request('category') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="material" class="form-label">{{ __('Matériau') }}</label>
                        <input type="text" class="form-control" id="material" name="material" value="{{ request('material') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="origin" class="form-label">{{ __('Origine') }}</label>
                        <input type="text" class="form-control" id="origin" name="origin" value="{{ request('origin') }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="conservation_state" class="form-label">{{ __('État de conservation') }}</label>
                        <select class="form-select" id="conservation_state" name="conservation_state">
                            <option value="">{{ __('Tous') }}</option>
                            <option value="excellent">{{ __('Excellent') }}</option>
                            <option value="good">{{ __('Bon') }}</option>
                            <option value="fair">{{ __('Moyen') }}</option>
                            <option value="poor">{{ __('Mauvais') }}</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="location" class="form-label">{{ __('Localisation') }}</label>
                        <input type="text" class="form-control" id="location" name="location" value="{{ request('location') }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('museum.search.index') }}" class="btn btn-secondary">
                        {{ __('Annuler') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> {{ __('Rechercher') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
