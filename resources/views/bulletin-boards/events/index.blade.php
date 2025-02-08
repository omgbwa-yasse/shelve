<!-- resources/views/bulletin-boards/events/index.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Événements</h2>
                    @can('create', App\Models\BulletinBoard::class)
                        <a href="{{ route('bulletin-boards.create', ['type' => 'event']) }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Nouvel événement
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Filtres d'événements -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('bulletin-boards.events.index') }}" method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Période</label>
                                <select name="period" class="form-select">
                                    <option value="upcoming" {{ request('period') == 'upcoming' ? 'selected' : '' }}>À venir</option>
                                    <option value="past" {{ request('period') == 'past' ? 'selected' : '' }}>Passés</option>
                                    <option value="all" {{ request('period') == 'all' ? 'selected' : '' }}>Tous</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Organisation</label>
                                <select name="organisation" class="form-select">
                                    <option value="">Toutes les organisations</option>
                                    @foreach($organisations as $organisation)
                                        <option value="{{ $organisation->id }}" {{ request('organisation') == $organisation->id ? 'selected' : '' }}>
                                            {{ $organisation->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block w-100">Filtrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des événements -->
        <div class="row">
            @forelse($events as $event)
                <div class="col-md-6 col-xl-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary">Événement</span>
                            <div class="dropdown">
                                <button class="btn btn-link text-dark p-0" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('bulletin-boards.show', $event) }}">
                                            <i class="bi bi-eye"></i> Voir les détails
                                        </a>
                                    </li>
                                    @can('update', $event)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('bulletin-boards.edit', $event) }}">
                                                <i class="bi bi-pencil"></i> Modifier
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">{{ $event->name }}</h5>
                            <div class="mb-3">
                                <div class="text-muted">
                                    <i class="bi bi-calendar3"></i>
                                    {{ $event->start_date->format('d/m/Y H:i') }}
                                    @if($event->end_date)
                                        - {{ $event->end_date->format('d/m/Y H:i') }}
                                    @endif
                                </div>
                                @if($event->location)
                                    <div class="text-muted">
                                        <i class="bi bi-geo-alt"></i> {{ $event->location }}
                                    </div>
                                @endif
                            </div>
                            <p class="card-text">{{ Str::limit($event->description, 150) }}</p>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="bi bi-person"></i> {{ $event->user->name }}
                                </small>
                                <div>
                                    @if($event->start_date->isFuture())
                                        <a href="{{ route('bulletin-boards.events.register', $event) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            Participer
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Aucun événement trouvé
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $events->links() }}
        </div>
    </div>
@endsection
