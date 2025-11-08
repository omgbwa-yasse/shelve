@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-shield-check"></i> {{ __('Rapport Conservation') }}</h1>
        <a href="{{ route('museum.reports.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-muted">{{ __('État de conservation des artefacts') }}</p>
        </div>
    </div>
</div>
@endsection
