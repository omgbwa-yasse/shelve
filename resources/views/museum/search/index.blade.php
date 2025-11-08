@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-search"></i> {{ __('Recherche d\'artefacts') }}</h1>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('museum.search') }}">
                @csrf
                <div class="row">
                    <div class="col-md-10">
                        <input type="text" name="query" class="form-control form-control-lg"
                               placeholder="{{ __('Rechercher un artefact...') }}"
                               value="{{ request('query') }}" autofocus>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-search"></i> {{ __('Rechercher') }}
                        </button>
                    </div>
                </div>
            </form>
            <div class="mt-3">
                <a href="{{ route('museum.search.advanced') }}" class="btn btn-link">
                    <i class="bi bi-funnel"></i> {{ __('Recherche avancée') }}
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5><i class="bi bi-list-ul"></i> {{ __('Résultats') }}</h5>
        </div>
        <div class="card-body">
            <p class="text-muted text-center">{{ __('Effectuez une recherche pour voir les résultats') }}</p>
        </div>
    </div>
</div>
@endsection
