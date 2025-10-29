@extends('opac.layouts.app')

@section('title', __('Events'))

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="opac-search-hero mb-4">
        <div class="container">
            <h1 class="display-5 fw-bold mb-3">
                <i class="fas fa-calendar me-3"></i>
                {{ __('Events & Activities') }}
            </h1>
            <p class="lead">{{ __('Discover upcoming events, workshops, and activities.') }}</p>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <a href="{{ route('opac.events.index', ['filter' => 'upcoming']) }}"
                   class="btn {{ $filter === 'upcoming' ? 'btn-primary' : 'btn-outline-primary' }} btn-sm">
                    {{ __('Upcoming') }}
                </a>
                <a href="{{ route('opac.events.index', ['filter' => 'today']) }}"
                   class="btn {{ $filter === 'today' ? 'btn-primary' : 'btn-outline-primary' }} btn-sm">
                    {{ __('Today') }}
                </a>
                <a href="{{ route('opac.events.index', ['filter' => 'this_week']) }}"
                   class="btn {{ $filter === 'this_week' ? 'btn-primary' : 'btn-outline-primary' }} btn-sm">
                    {{ __('This Week') }}
                </a>
                <a href="{{ route('opac.events.index', ['filter' => 'this_month']) }}"
                   class="btn {{ $filter === 'this_month' ? 'btn-primary' : 'btn-outline-primary' }} btn-sm">
                    {{ __('This Month') }}
                </a>
                <a href="{{ route('opac.events.index', ['filter' => 'past']) }}"
                   class="btn {{ $filter === 'past' ? 'btn-primary' : 'btn-outline-primary' }} btn-sm">
                    {{ __('Past Events') }}
                </a>
            </div>

            <form method="GET" action="{{ route('opac.events.index') }}" class="d-flex gap-2">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="form-control" placeholder="{{ __('Search events...') }}">

                <select name="category" class="form-select" style="min-width: 150px;">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                            {{ ucfirst($category) }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-search"></i>
                </button>

                @if(request()->hasAny(['search', 'category']))
                    <a href="{{ route('opac.events.index', ['filter' => $filter]) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>
        </div>

        <div class="col-lg-4 text-end">
            <span class="text-muted">{{ $events->total() }} {{ __('events found') }}</span>
        </div>
    </div>

    <!-- Events List -->
    @if($events->count() > 0)
        <div class="row">
            @foreach($events as $event)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="opac-card h-100">
                        @if($event->image)
                            <img src="{{ asset('storage/' . $event->image) }}"
                                 class="card-img-top"
                                 alt="{{ $event->title }}"
                                 style="height: 200px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                 style="height: 200px;">
                                <i class="fas fa-calendar text-muted" style="font-size: 3rem;"></i>
                            </div>
                        @endif

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <a href="{{ route('opac.events.show', $event->id) }}"
                                   class="text-decoration-none text-dark">
                                    {{ $event->name }}
                                </a>
                            </h5>

                            <p class="card-text text-muted">{{ Str::limit($event->description, 100) }}</p>

                            <div class="mb-2">
                                <small class="text-muted d-block">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y') }}
                                </small>

                                @if($event->location)
                                    <small class="text-muted d-block">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        {{ $event->location }}
                                    </small>
                                @endif
                            </div>

                            <div class="mt-auto">
                                <a href="{{ route('opac.events.show', $event->id) }}"
                                   class="btn btn-sm btn-outline-primary w-100">
                                    {{ __('View Details') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $events->links() }}
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-calendar text-muted mb-3" style="font-size: 4rem;"></i>
            <h4 class="text-muted">{{ __('No Events Found') }}</h4>
            <p class="text-muted">
                @if(request()->hasAny(['search', 'category']) || $filter !== 'upcoming')
                    {{ __('No events match your current filters.') }}
                    <a href="{{ route('opac.events.index') }}" class="text-decoration-none">
                        {{ __('View all upcoming events') }}
                    </a>
                @else
                    {{ __('There are no upcoming events at this time.') }}
                @endif
            </p>
        </div>
    @endif
</div>
@endsection
