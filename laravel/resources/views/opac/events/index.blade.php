@extends('opac.layouts.app')

@section('title', __('Events') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="mb-4">
                <h1 class="h3 mb-2">{{ __('Library Events') }}</h1>
                <p class="text-muted">{{ __('Discover upcoming events, workshops, and activities') }}</p>
            </div>

            <!-- Filters and Search -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <form method="GET" action="{{ route('opac.events.index') }}" class="d-flex">
                        <input type="text" class="form-control me-2" name="search"
                               placeholder="{{ __('Search events...') }}"
                               value="{{ request('search') }}">
                        <input type="hidden" name="filter" value="{{ $filter }}">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-6">
                    <div class="btn-group w-100" role="group">
                        <a href="{{ route('opac.events.index', ['filter' => 'upcoming']) }}"
                           class="btn {{ $filter == 'upcoming' ? 'btn-primary' : 'btn-outline-primary' }}">
                            {{ __('Upcoming') }}
                        </a>
                        <a href="{{ route('opac.events.index', ['filter' => 'this_week']) }}"
                           class="btn {{ $filter == 'this_week' ? 'btn-primary' : 'btn-outline-primary' }}">
                            {{ __('This Week') }}
                        </a>
                        <a href="{{ route('opac.events.index', ['filter' => 'this_month']) }}"
                           class="btn {{ $filter == 'this_month' ? 'btn-primary' : 'btn-outline-primary' }}">
                            {{ __('This Month') }}
                        </a>
                        <a href="{{ route('opac.events.index', ['filter' => 'past']) }}"
                           class="btn {{ $filter == 'past' ? 'btn-primary' : 'btn-outline-primary' }}">
                            {{ __('Past') }}
                        </a>
                    </div>
                </div>
            </div>

            @if(request()->filled('search'))
            <div class="alert alert-info">
                <i class="fas fa-search me-2"></i>
                {{ __('Search results for') }}: <strong>{{ request('search') }}</strong>
                <a href="{{ route('opac.events.index', ['filter' => $filter]) }}" class="btn btn-sm btn-outline-primary ms-2">
                    {{ __('Clear search') }}
                </a>
            </div>
            @endif

            <!-- Events List -->
            @if($events->count() > 0)
                <div class="row">
                    @foreach($events as $event)
                        <div class="col-lg-6 col-xl-4 mb-4">
                            <div class="card h-100 opac-card">
                                <div class="card-body d-flex flex-column">
                                    <!-- Event Date Badge -->
                                    <div class="mb-3">
                                        <span class="badge bg-primary">
                                            {{ \Carbon\Carbon::parse($event->start_date)->format('d M Y') }}
                                        </span>
                                        @if($event->start_date != $event->end_date)
                                            <span class="badge bg-secondary">
                                                {{ __('to') }} {{ \Carbon\Carbon::parse($event->end_date)->format('d M') }}
                                            </span>
                                        @endif
                                    </div>

                                    <h5 class="card-title">
                                        <a href="{{ route('opac.events.show', $event->id) }}" class="text-decoration-none">
                                            {{ $event->name }}
                                        </a>
                                    </h5>

                                    @if($event->location)
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            {{ $event->location }}
                                        </p>
                                    @endif

                                    @if($event->description)
                                        <p class="card-text flex-grow-1">
                                            {{ Str::limit(strip_tags($event->description), 120) }}
                                        </p>
                                    @endif

                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="{{ route('opac.events.show', $event->id) }}"
                                               class="btn btn-primary btn-sm">
                                                {{ __('Learn More') }}
                                                <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ \Carbon\Carbon::parse($event->start_date)->format('H:i') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($events->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $events->links() }}
                    </div>
                @endif

                <!-- Summary -->
                <div class="row mt-4">
                    <div class="col-12 text-center">
                        <small class="text-muted">
                            {{ __('Showing') }} {{ $events->count() }} {{ __('of') }} {{ $events->total() }} {{ __('events') }}
                        </small>
                    </div>
                </div>
            @else
                <!-- No Events Found -->
                <div class="opac-card text-center py-5">
                    <div class="card-body">
                        @if(request()->filled('search'))
                            <i class="fas fa-search fa-4x text-muted mb-4"></i>
                            <h4>{{ __('No events found') }}</h4>
                            <p class="text-muted mb-4">
                                {{ __('No events match your search criteria. Try different keywords or time periods.') }}
                            </p>
                            <a href="{{ route('opac.events.index') }}" class="btn btn-primary">
                                {{ __('View All Events') }}
                            </a>
                        @else
                            <i class="fas fa-calendar fa-4x text-muted mb-4"></i>
                            @if($filter == 'upcoming')
                                <h4>{{ __('No upcoming events') }}</h4>
                                <p class="text-muted mb-4">
                                    {{ __('There are currently no upcoming events scheduled. Check back later for updates!') }}
                                </p>
                            @elseif($filter == 'past')
                                <h4>{{ __('No past events') }}</h4>
                                <p class="text-muted mb-4">
                                    {{ __('No past events found in our records.') }}
                                </p>
                            @else
                                <h4>{{ __('No events in this period') }}</h4>
                                <p class="text-muted mb-4">
                                    {{ __('No events are scheduled for the selected time period.') }}
                                </p>
                            @endif
                            <a href="{{ route('opac.search') }}" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>{{ __('Search Documents') }}
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
