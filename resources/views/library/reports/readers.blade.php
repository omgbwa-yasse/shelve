@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-people"></i> {{ __('Rapport Lecteurs') }}</h1>
        <a href="{{ route('library.reports.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-muted">{{ __('Statistiques et informations sur les lecteurs') }}</p>
        </div>
    </div>
</div>
@endsection
