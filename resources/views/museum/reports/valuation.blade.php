@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-cash-stack"></i> {{ __('Rapport Valorisation') }}</h1>
        <a href="{{ route('museum.reports.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-muted">{{ __('Valeur estimée de la collection') }}</p>
        </div>
    </div>
</div>
@endsection
