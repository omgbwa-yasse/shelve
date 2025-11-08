@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-exclamation-triangle text-danger"></i> {{ __('Rapport Prêts en retard') }}</h1>
        <a href="{{ route('library.reports.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-muted">{{ __('Liste des prêts en retard et amendes') }}</p>
        </div>
    </div>
</div>
@endsection
