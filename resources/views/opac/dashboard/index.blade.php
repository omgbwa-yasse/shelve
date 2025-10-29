@extends('layouts.opac')

@section('title', __('Dashboard'))

@section('content')
<div class="container-fluid py-4">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="card-title mb-2">{{ __('Welcome back, :name!', ['name' => $user->name]) }}</h1>
                            <p class="card-text mb-0">{{ __('Manage your library activities and explore our digital collection.') }}</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <i class="bi bi-person-circle display-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <i class="bi bi-folder text-primary display-6"></i>
                    <h5 class="card-title mt-2">{{ number_format($stats['total_records']) }}</h5>
                    <p class="card-text text-muted">{{ __('Available Records') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <i class="bi bi-calendar-event text-success display-6"></i>
                    <h5 class="card-title mt-2">{{ number_format($stats['upcoming_events']) }}</h5>
                    <p class="card-text text-muted">{{ __('Upcoming Events') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <i class="bi bi-bookmark text-warning display-6"></i>
                    <h5 class="card-title mt-2">{{ number_format($userStats['reservations_count']) }}</h5>
                    <p class="card-text text-muted">{{ __('My Reservations') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <i class="bi bi-file-earmark-text text-info display-6"></i>
                    <h5 class="card-title mt-2">{{ number_format($userStats['requests_count']) }}</h5>
                    <p class="card-text text-muted">{{ __('My Requests') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Quick Actions -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning-charge text-primary"></i>
                        {{ __('Quick Actions') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('opac.search') }}" class="btn btn-outline-primary">
                            <i class="bi bi-search"></i> {{ __('Advanced Search') }}
                        </a>
                        <a href="{{ route('opac.document-requests.create') }}" class="btn btn-outline-success">
                            <i class="bi bi-file-earmark-plus"></i> {{ __('New Document Request') }}
                        </a>
                        <a href="{{ route('opac.feedback.create') }}" class="btn btn-outline-warning">
                            <i class="bi bi-chat-square-dots"></i> {{ __('Send Feedback') }}
                        </a>
                        <a href="{{ route('opac.profile') }}" class="btn btn-outline-info">
                            <i class="bi bi-person-gear"></i> {{ __('Update Profile') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent News -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-newspaper text-success"></i>
                        {{ __('Recent News') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentNews->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentNews as $news)
                            <div class="list-group-item px-0 border-0">
                                <a href="{{ route('opac.news.show', $news->id) }}" class="text-decoration-none">
                                    <h6 class="mb-1">{{ Str::limit($news->title, 50) }}</h6>
                                    <p class="mb-1 text-muted small">{{ Str::limit($news->summary, 80) }}</p>
                                    <small class="text-muted">{{ $news->published_at->diffForHumans() }}</small>
                                </a>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('opac.news.index') }}" class="btn btn-sm btn-outline-success">
                                {{ __('View All News') }}
                            </a>
                        </div>
                    @else
                        <p class="text-muted">{{ __('No recent news available.') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-calendar-event text-info"></i>
                        {{ __('Upcoming Events') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentEvents->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentEvents as $event)
                            <div class="list-group-item px-0 border-0">
                                <a href="{{ route('opac.events.show', $event->id) }}" class="text-decoration-none">
                                    <h6 class="mb-1">{{ Str::limit($event->name, 50) }}</h6>
                                    <p class="mb-1 text-muted small">
                                        <i class="bi bi-calendar3"></i> {{ $event->start_date->format('M j, Y') }}
                                    </p>
                                    @if($event->location)
                                        <p class="mb-1 text-muted small">
                                            <i class="bi bi-geo-alt"></i> {{ $event->location }}
                                        </p>
                                    @endif
                                </a>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('opac.events.index') }}" class="btn btn-sm btn-outline-info">
                                {{ __('View All Events') }}
                            </a>
                        </div>
                    @else
                        <p class="text-muted">{{ __('No upcoming events.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- My Activity Summary -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-activity text-warning"></i>
                        {{ __('My Recent Activity') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>{{ __('Recent Reservations') }}</h6>
                            @if($myReservations->count() > 0)
                                <!-- Display recent reservations -->
                                <p class="text-muted">{{ __('Recent reservation activities will appear here.') }}</p>
                            @else
                                <p class="text-muted">{{ __('No recent reservations.') }}</p>
                            @endif
                            <a href="{{ route('opac.reservations') }}" class="btn btn-sm btn-outline-primary">
                                {{ __('View All Reservations') }}
                            </a>
                        </div>
                        <div class="col-md-6">
                            <h6>{{ __('Recent Requests') }}</h6>
                            @if($myRequests->count() > 0)
                                <!-- Display recent requests -->
                                <p class="text-muted">{{ __('Recent document requests will appear here.') }}</p>
                            @else
                                <p class="text-muted">{{ __('No recent document requests.') }}</p>
                            @endif
                            <a href="{{ route('opac.document-requests.index') }}" class="btn btn-sm btn-outline-success">
                                {{ __('View All Requests') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Dashboard functionality
$(document).ready(function() {
    // Add any dashboard-specific JavaScript here
    console.log('OPAC Dashboard loaded');
});
</script>
@endpush
