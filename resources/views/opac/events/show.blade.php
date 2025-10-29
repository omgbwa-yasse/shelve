@extends('opac.layouts.app')

@section('title', $event->name)

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('opac.index') }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('opac.events.index') }}">{{ __('Events') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $event->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <div class="opac-record-detail">
                <!-- Event Header -->
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h1 class="opac-record-title">{{ $event->name }}</h1>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            @if(\Carbon\Carbon::parse($event->start_date)->isFuture())
                                <span class="badge bg-success">{{ __('Upcoming') }}</span>
                            @elseif(\Carbon\Carbon::parse($event->start_date)->isToday())
                                <span class="badge bg-warning">{{ __('Today') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('Past') }}</span>
                            @endif

                            @if($event->is_online)
                                <span class="badge bg-info">{{ __('Online Event') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Event Details -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="opac-field-label">
                            <i class="fas fa-calendar me-2"></i>
                            {{ __('Start Date') }}
                        </div>
                        <div class="opac-field-value">
                            {{ \Carbon\Carbon::parse($event->start_date)->format('F j, Y') }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="opac-field-label">
                            <i class="fas fa-calendar me-2"></i>
                            {{ __('End Date') }}
                        </div>
                        <div class="opac-field-value">
                            {{ \Carbon\Carbon::parse($event->end_date)->format('F j, Y') }}
                        </div>
                    </div>
                </div>

                @if($event->location || $event->is_online)
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="opac-field-label">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                {{ __('Location') }}
                            </div>
                            <div class="opac-field-value">
                                @if($event->is_online && $event->online_link)
                                    <span class="badge bg-info me-2">{{ __('Online') }}</span>
                                    <a href="{{ $event->online_link }}" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="fas fa-external-link-alt me-1"></i>
                                        {{ __('Join Online') }}
                                    </a>
                                @elseif($event->location)
                                    {{ $event->location }}
                                @else
                                    {{ __('Online Event') }}
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Event Description -->
                @if($event->description)
                    <div class="mb-4">
                        <div class="opac-field-label">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('Description') }}
                        </div>
                        <div class="opac-field-value">
                            <div class="event-description">
                                {!! nl2br(e($event->description)) !!}
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Registration/Action Button -->
                <div class="text-center py-4">
                    @auth('public')
                        @if(\Carbon\Carbon::parse($event->start_date)->isFuture())
                            <button class="btn btn-success btn-lg">
                                <i class="fas fa-calendar-plus me-2"></i>
                                {{ __('Register for Event') }}
                            </button>
                        @endif
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('Please') }}
                            <a href="{{ route('opac.login') }}" class="alert-link">{{ __('login') }}</a>
                            {{ __('to register for this event.') }}
                        </div>
                    @endauth
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Event Information Sidebar -->
            <div class="opac-card">
                <div class="opac-card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('Event Information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-3">
                        <li class="mb-2">
                            <strong>{{ __('Duration') }}:</strong>
                            @if(\Carbon\Carbon::parse($event->start_date)->format('Y-m-d') === \Carbon\Carbon::parse($event->end_date)->format('Y-m-d'))
                                {{ __('Single day event') }}
                            @else
                                {{ \Carbon\Carbon::parse($event->start_date)->diffInDays(\Carbon\Carbon::parse($event->end_date)) + 1 }} {{ __('days') }}
                            @endif
                        </li>
                        @if($event->is_online)
                            <li class="mb-2">
                                <strong>{{ __('Format') }}:</strong> {{ __('Online Event') }}
                            </li>
                        @endif
                    </ul>

                    <div class="d-grid gap-2">
                        <a href="{{ route('opac.events.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>
                            {{ __('Back to Events') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Related Events -->
            @if($relatedEvents->count() > 0)
                <div class="opac-card mt-4">
                    <div class="opac-card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-calendar me-2"></i>
                            {{ __('Other Upcoming Events') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($relatedEvents as $relatedEvent)
                            <div class="mb-3 pb-2 @if(!$loop->last) border-bottom @endif">
                                <h6 class="mb-1">
                                    <a href="{{ route('opac.events.show', $relatedEvent->id) }}"
                                       class="text-decoration-none">
                                        {{ $relatedEvent->name }}
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ \Carbon\Carbon::parse($relatedEvent->start_date)->format('M j, Y') }}
                                </small>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.event-description {
    line-height: 1.8;
}

.badge {
    font-size: 0.8rem;
}
</style>
@endpush
@endsection
