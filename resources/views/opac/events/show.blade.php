@extends('opac.layouts.app')

@section('title', $event->name . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('opac.search') }}">{{ __('Home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('opac.events.index') }}">{{ __('Events') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $event->name }}</li>
                </ol>
            </nav>

            <!-- Event Content -->
            <article class="opac-card">
                <div class="card-body">
                    <!-- Event Header -->
                    <header class="mb-4">
                        <!-- Event Date & Status -->
                        <div class="mb-3 d-flex align-items-center flex-wrap gap-2">
                            <span class="badge bg-primary fs-6">
                                {{ \Carbon\Carbon::parse($event->start_date)->format('d F Y') }}
                            </span>
                            @if($event->start_date != $event->end_date)
                                <span class="badge bg-secondary">
                                    {{ __('to') }} {{ \Carbon\Carbon::parse($event->end_date)->format('d F Y') }}
                                </span>
                            @endif
                            @php
                                $now = now();
                                $startDate = \Carbon\Carbon::parse($event->start_date);
                                $endDate = \Carbon\Carbon::parse($event->end_date);
                            @endphp
                            @if($startDate > $now)
                                <span class="badge bg-success">{{ __('Upcoming') }}</span>
                            @elseif($startDate <= $now && $endDate >= $now)
                                <span class="badge bg-warning">{{ __('Ongoing') }}</span>
                            @else
                                <span class="badge bg-dark">{{ __('Past') }}</span>
                            @endif
                        </div>

                        <h1 class="h2 mb-3">{{ $event->name }}</h1>

                        <!-- Event Details -->
                        <div class="row mb-3">
                            @if($event->location)
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="fas fa-map-marker-alt me-2"></i>
                                        <span>{{ $event->location }}</span>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-6">
                                <div class="d-flex align-items-center text-muted">
                                    <i class="fas fa-clock me-2"></i>
                                    <span>
                                        {{ \Carbon\Carbon::parse($event->start_date)->format('H:i') }}
                                        @if($event->end_date && \Carbon\Carbon::parse($event->start_date)->format('Y-m-d') == \Carbon\Carbon::parse($event->end_date)->format('Y-m-d'))
                                            - {{ \Carbon\Carbon::parse($event->end_date)->format('H:i') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if($event->max_participants)
                            <div class="mb-3">
                                <div class="d-flex align-items-center text-muted">
                                    <i class="fas fa-users me-2"></i>
                                    <span>
                                        {{ __('Max participants') }}: {{ $event->max_participants }}
                                        @php
                                            $registrations = $event->registrations()->count();
                                        @endphp
                                        @if($registrations > 0)
                                            ({{ $registrations }} {{ __('registered') }})
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endif
                    </header>

                    <!-- Event Description -->
                    @if($event->description)
                        <div class="opac-event-content mb-4">
                            {!! $event->description !!}
                        </div>
                    @endif

                    <!-- Registration Section -->
                    @if($startDate > $now && $event->max_participants)
                        <div class="registration-section bg-light p-4 rounded mb-4">
                            <h5 class="mb-3">{{ __('Registration') }}</h5>
                            @php
                                $registrations = $event->registrations()->count();
                                $spotsLeft = $event->max_participants - $registrations;
                            @endphp

                            @if($spotsLeft > 0)
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    {{ $spotsLeft }} {{ __('spots available') }}
                                </div>
                                <p class="text-muted mb-3">
                                    {{ __('This event requires registration. Please contact the library to reserve your spot.') }}
                                </p>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#contactModal">
                                        <i class="fas fa-user-plus me-2"></i>{{ __('Register') }}
                                    </button>
                                    <a href="{{ route('opac.feedback.create') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-envelope me-2"></i>{{ __('Contact Us') }}
                                    </a>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    {{ __('Event is full') }}
                                </div>
                                <p class="text-muted">
                                    {{ __('This event has reached its maximum capacity. Contact us to be added to the waiting list.') }}
                                </p>
                            @endif
                        </div>
                    @endif

                    <!-- Event Footer -->
                    <footer class="mt-5 pt-4 border-top">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-2"></i>
                                    {{ __('Last updated') }}: {{ $event->updated_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="{{ route('opac.events.index') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    {{ __('Back to Events') }}
                                </a>
                            </div>
                        </div>
                    </footer>
                </div>
            </article>

            <!-- Related Events -->
            @if($relatedEvents->count() > 0)
                <div class="mt-4">
                    <h5 class="mb-3">{{ __('Other Upcoming Events') }}</h5>
                    <div class="row">
                        @foreach($relatedEvents as $relatedEvent)
                            <div class="col-md-4 mb-3">
                                <div class="card card-sm">
                                    <div class="card-body">
                                        <div class="mb-2">
                                            <small class="badge bg-light text-dark">
                                                {{ \Carbon\Carbon::parse($relatedEvent->start_date)->format('d/m/Y') }}
                                            </small>
                                        </div>
                                        <h6 class="card-title">
                                            <a href="{{ route('opac.events.show', $relatedEvent->id) }}"
                                               class="text-decoration-none">
                                                {{ $relatedEvent->name }}
                                            </a>
                                        </h6>
                                        @if($relatedEvent->location)
                                            <p class="card-text small text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ $relatedEvent->location }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Event Registration') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('To register for this event, please contact the library:') }}</p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-phone me-2"></i> +33 1 23 45 67 89</li>
                    <li><i class="fas fa-envelope me-2"></i> contact@bibliotheque.fr</li>
                    <li><i class="fas fa-map-marker-alt me-2"></i> Visit us in person</li>
                </ul>
                <p class="text-muted small">
                    {{ __('Please mention the event name when contacting us.') }}
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <a href="{{ route('opac.feedback.create') }}" class="btn btn-primary">{{ __('Send Message') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.opac-event-content {
    line-height: 1.7;
}

.opac-event-content h1,
.opac-event-content h2,
.opac-event-content h3,
.opac-event-content h4,
.opac-event-content h5,
.opac-event-content h6 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: #333;
}

.opac-event-content p {
    margin-bottom: 1rem;
}

.opac-event-content ul,
.opac-event-content ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.registration-section {
    border: 2px solid #e9ecef;
}

.card-sm {
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.card-sm:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}
</style>
@endpush
