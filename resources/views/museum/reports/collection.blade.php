@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-collection"></i> {{ __('Rapport Collection') }}</h1>
        <div>
            <a href="{{ route('museum.reports.collection.export-csv') }}" class="btn btn-success">
                <i class="bi bi-file-earmark-excel"></i> {{ __('Exporter CSV') }}
            </a>
            <a href="{{ route('museum.reports.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-muted">{{ __('Vue d\'ensemble de la collection du musée') }}</p>
        </div>
    </div>
</div>
@endsection
