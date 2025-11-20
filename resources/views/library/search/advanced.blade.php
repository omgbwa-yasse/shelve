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
                        <label for="publisher_id" class="form-label">{{ __('Éditeur') }}</label>
                        <select class="form-select" id="publisher_id" name="publisher_id">
                            <option value="">{{ __('Tous les éditeurs') }}</option>
                            @foreach($publishers as $id => $name)
                                <option value="{{ $id }}" {{ request('publisher_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="year_from" class="form-label">{{ __('Année (De)') }}</label>
                        <input type="number" class="form-control" id="year_from" name="year_from" value="{{ request('year_from') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="year_to" class="form-label">{{ __('Année (À)') }}</label>
                        <input type="number" class="form-control" id="year_to" name="year_to" value="{{ request('year_to') }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="dewey" class="form-label">{{ __('Cote (Dewey)') }}</label>
                        <select class="form-select" id="dewey" name="dewey">
                            <option value="">{{ __('Toutes les cotes') }}</option>
                            @foreach($categories as $dewey)
                                <option value="{{ $dewey }}" {{ request('dewey') == $dewey ? 'selected' : '' }}>{{ $dewey }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="language_id" class="form-label">{{ __('Langue') }}</label>
                        <select class="form-select" id="language_id" name="language_id">
                            <option value="">{{ __('Toutes les langues') }}</option>
                            @foreach($languages as $id => $name)
                                <option value="{{ $id }}" {{ request('language_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
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
