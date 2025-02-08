@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Statistiques -->
        <div class="row">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">Publications totales</h6>
                        <h3 class="mb-0">{{ $stats['total_posts'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">Événements actifs</h6>
                        <h3 class="mb-0">{{ $stats['active_events'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">Mes publications</h6>
                        <h3 class="mb-0">{{ $stats['my_posts'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activités récentes et événements à venir -->
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Activités récentes</h5>
                    </div>
                    <div class="card-body">
                        @foreach($stats['recent_activities'] as $activity)
                            <div class="d-flex align-items-center py-2 border-bottom">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $activity->name }}</h6>
                                    <small class="text-muted">
                                        Par {{ $activity->user->name }} • {{ $activity->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <a href="{{ route('bulletin-boards.show', $activity) }}" class="btn btn-sm btn-outline-primary">
                                    Voir
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Événements à venir</h5>
                    </div>
                    <div class="card-body">
                        @foreach($upcomingEvents as $event)
                            <div class="d-flex align-items-center py-2 border-bottom">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $event->name }}</h6>
                                    <small class="text-muted">
                                        {{ $event->start_date->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                                <a href="{{ route('bulletin-boards.events.show', $event) }}" class="btn btn-sm btn-outline-primary">
                                    Détails
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
