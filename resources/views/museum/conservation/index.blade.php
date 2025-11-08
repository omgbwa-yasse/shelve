@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-shield-check"></i> {{ __('Rapports de conservation') }}</h1>
        <a href="{{ route('museum.conservation.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> {{ __('Nouveau rapport') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('Artefact') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('État') }}</th>
                            <th>{{ __('Auteur') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                {{ __('Aucun rapport trouvé') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
