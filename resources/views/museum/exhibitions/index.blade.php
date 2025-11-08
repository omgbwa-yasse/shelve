@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-easel"></i> {{ __('Gestion des expositions') }}</h1>
        <a href="{{ route('museum.exhibitions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> {{ __('Nouvelle exposition') }}
        </a>
    </div>

    <!-- Tabs pour filtrer les expositions -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" href="#">
                <i class="bi bi-play-circle"></i> {{ __('En cours') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">
                <i class="bi bi-calendar-plus"></i> {{ __('À venir') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">
                <i class="bi bi-archive"></i> {{ __('Passées') }}
            </a>
        </li>
    </ul>

    <!-- Liste des expositions -->
    <div class="row g-4">
        {{-- @forelse($exhibitions as $exhibition) --}}
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Exposition exemple') }}</h5>
                    <small class="text-muted">01/01/2025 - 31/03/2025</small>
                </div>
                <img src="https://via.placeholder.com/400x250" class="card-img-top" alt="Exhibition">
                <div class="card-body">
                    <p class="card-text">{{ __('Description de l\'exposition...') }}</p>
                    <div class="mb-2">
                        <strong>{{ __('Lieu:') }}</strong> Salle principale
                    </div>
                    <div class="mb-2">
                        <strong>{{ __('Pièces exposées:') }}</strong> 0
                    </div>
                    <div class="mb-2">
                        <strong>{{ __('Visiteurs:') }}</strong> 0
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-success">{{ __('En cours') }}</span>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="#" class="btn btn-sm btn-info">
                            <i class="bi bi-eye"></i> {{ __('Détails') }}
                        </a>
                        <div>
                            <a href="#" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- @empty --}}
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle"></i> {{ __('Aucune exposition trouvée') }}
            </div>
        </div>
        {{-- @endforelse --}}
    </div>

    <!-- Calendrier des expositions -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-calendar-event"></i> {{ __('Calendrier des expositions') }}
            </h5>
        </div>
        <div class="card-body">
            <div id="exhibitions-calendar"></div>
            <p class="text-center text-muted">{{ __('Calendrier à implémenter') }}</p>
        </div>
    </div>
</div>
@endsection
