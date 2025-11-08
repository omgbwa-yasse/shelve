@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-funnel"></i> {{ __('Recherche avancée') }}</h1>
        <a href="{{ route('library.search.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Recherche simple') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('library.search.advanced') }}">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="title" class="form-label">{{ __('Titre') }}</label>
                        <input type="text" class="form-control" id="title" name="title" value="{{ request('title') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="author" class="form-label">{{ __('Auteur') }}</label>
                        <input type="text" class="form-control" id="author" name="author" value="{{ request('author') }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="isbn" class="form-label">{{ __('ISBN') }}</label>
                        <input type="text" class="form-control" id="isbn" name="isbn" value="{{ request('isbn') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="publisher" class="form-label">{{ __('Éditeur') }}</label>
                        <input type="text" class="form-control" id="publisher" name="publisher" value="{{ request('publisher') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="year" class="form-label">{{ __('Année de publication') }}</label>
                        <input type="number" class="form-control" id="year" name="year" value="{{ request('year') }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="category" class="form-label">{{ __('Catégorie') }}</label>
                        <input type="text" class="form-control" id="category" name="category" value="{{ request('category') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="language" class="form-label">{{ __('Langue') }}</label>
                        <input type="text" class="form-control" id="language" name="language" value="{{ request('language') }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('library.search.index') }}" class="btn btn-secondary">
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
