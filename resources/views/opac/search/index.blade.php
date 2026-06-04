@extends('opac.layouts.app')

@section('title', __('Search') . ' - OPAC')

@push('styles')
<style>
    /* Hero — editorial archive masthead */
    .opac-hero {
        position: relative;
        padding: 5rem 0 4.5rem;
        background:
            radial-gradient(ellipse 80% 60% at 50% -10%, rgba(156, 66, 33, 0.10), transparent 70%),
            var(--opac-paper);
        border-bottom: 1px solid var(--opac-border-color);
        overflow: hidden;
    }
    /* decorative oversized serif ampersand / catalog flourish */
    .opac-hero::after {
        content: "\201C";
        position: absolute;
        right: 4%;
        top: -2.5rem;
        font-family: var(--opac-serif);
        font-size: 22rem;
        line-height: 1;
        color: rgba(156, 66, 33, 0.06);
        pointer-events: none;
        user-select: none;
    }
    .opac-hero-title {
        font-size: clamp(2.4rem, 5vw, 3.6rem);
        font-weight: 600;
        color: var(--opac-dark);
        margin-bottom: 0.75rem;
    }
    .opac-hero-title em {
        font-style: italic;
        color: var(--opac-primary);
    }
    .opac-hero-lead {
        font-size: 1.12rem;
        color: var(--opac-text-secondary);
        max-width: 34rem;
    }

    .opac-search-icon {
        position: absolute;
        left: 1.25rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--opac-text-muted);
        font-size: 1.05rem;
        z-index: 5;
    }
    .hero-search-shell {
        background: #fff;
        border: 1px solid var(--opac-border-color);
        border-radius: var(--opac-border-radius);
        padding: 0.4rem;
        box-shadow: var(--opac-shadow);
        display: flex;
        gap: 0.4rem;
    }
    .hero-search-shell .opac-search-input {
        border: none;
        box-shadow: none;
        background: transparent;
    }
    .hero-search-shell .opac-search-input:focus { box-shadow: none; }

    .quick-tile {
        display: block;
        height: 100%;
        background: #fff;
        border: 1px solid var(--opac-border-color);
        border-radius: var(--opac-border-radius);
        padding: 1.75rem 1.5rem;
        text-decoration: none;
        transition: border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }
    .quick-tile:hover {
        border-color: var(--opac-primary);
        transform: translateY(-3px);
        box-shadow: var(--opac-shadow);
    }
    .quick-tile-icon {
        width: 52px; height: 52px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 10px;
        background: var(--opac-paper-deep);
        color: var(--opac-primary);
        font-size: 1.35rem;
        margin-bottom: 1rem;
    }
    .quick-tile h5 { font-size: 1.15rem; margin-bottom: 0.35rem; }

    @media (max-width: 768px) {
        .opac-hero { padding: 3rem 0; }
        .opac-hero::after { display: none; }
        .hero-search-shell { flex-direction: column; }
    }
</style>
@endpush

@section('content')
<!-- Hero Search Section -->
<div class="opac-hero">
    <div class="container position-relative">
        <div class="row">
            <div class="col-lg-10 col-xl-9">
                <div class="mb-4 opac-reveal opac-reveal-1">
                    <span class="opac-eyebrow mb-3">{{ __('Online Public Access Catalog') }}</span>
                    <h1 class="opac-hero-title mt-3">{{ __('Search our') }} <em>{{ __('collections') }}</em></h1>
                    <p class="opac-hero-lead">{{ __('Explore thousands of documents, books, and digital resources') }}</p>
                </div>

                <!-- Quick Search Form -->
                <form method="GET" action="{{ route('opac.search.results') }}" id="quickSearchForm" class="opac-reveal opac-reveal-2">
                    <div class="hero-search-shell">
                        <div class="flex-grow-1 position-relative d-flex align-items-center">
                            <i class="fas fa-search opac-search-icon"></i>
                            <input type="text"
                                   name="q"
                                   class="opac-search-input"
                                   placeholder="{{ __('Search by title, author, subject, ISBN...') }}"
                                   value="{{ request('q') }}"
                                   autocomplete="off"
                                   id="quickSearchInput">
                        </div>
                        <button type="submit" class="opac-search-btn">
                            <i class="fas fa-search"></i>
                            {{ __('Search') }}
                        </button>
                    </div>
                    <div class="mt-3 d-flex flex-wrap gap-2 align-items-center opac-reveal opac-reveal-3">
                        <span class="small text-muted me-1">{{ __('Browse Catalog') }}:</span>
                        <a href="{{ route('opac.records.index') }}" class="btn btn-sm btn-opac-outline">{{ __('Browse Records') }}</a>
                        <a href="{{ route('opac.digital.folders.index') }}" class="btn btn-sm btn-opac-outline">{{ __('Digital Archives') }}</a>
                        <a href="{{ route('opac.news.index') }}" class="btn btn-sm btn-opac-outline">{{ __('Latest News') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-10 col-xl-9 mx-auto">

            <!-- Search Tips -->
            <div class="opac-card">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>{{ __('Search Tips') }}
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <strong>{{ __('Quotes:') }}</strong> {{ __('Use quotes for exact phrases') }}
                                    <br><small class="text-muted">{{ __('Example: "annual report 2024"') }}</small>
                                </li>
                                <li class="mb-2">
                                    <strong>{{ __('Wildcards:') }}</strong> {{ __('Use * for partial words') }}
                                    <br><small class="text-muted">{{ __('Example: climat* finds climate, climatic, etc.') }}</small>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <strong>{{ __('Boolean:') }}</strong> {{ __('Use AND, OR, NOT') }}
                                    <br><small class="text-muted">{{ __('Example: climate AND change') }}</small>
                                </li>
                                <li class="mb-2">
                                    <strong>{{ __('Fields:') }}</strong> {{ __('Combine multiple search fields') }}
                                    <br><small class="text-muted">{{ __('Use advanced search for better precision') }}</small>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search History for Authenticated Users -->
            @auth('public')
            @if(isset($searchHistory) && $searchHistory->isNotEmpty())
            <div class="opac-card mt-4">
                <div class="opac-card-header">
                    <i class="fas fa-history me-2"></i>{{ __('Recent Searches') }}
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($searchHistory->take(6) as $search)
                        <div class="col-md-6 col-lg-4 mb-2">
                            <a href="#" class="text-decoration-none">
                                <small class="text-muted d-block">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $search->created_at->diffForHumans() }}
                                </small>
                                <div class="fw-medium">{{ Str::limit($search->search_term, 30) }}</div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('opac.search.history') }}" class="btn btn-sm btn-outline-primary">
                            {{ __('View All Search History') }}
                        </a>
                    </div>
                </div>
            </div>
            @endif
            @endauth

            <!-- Latest News and Events Section -->
            <div class="row mt-5">
                <!-- Latest News -->
                <div class="col-lg-8 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="h4 mb-0"><i class="fas fa-newspaper me-2 text-primary"></i>{{ __('Latest News') }}</h3>
                        <a href="{{ route('opac.news.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View All News') }}</a>
                    </div>

                    @if($latestNews->isNotEmpty())
                        <div class="row">
                            @foreach($latestNews as $news)
                                <div class="col-md-6 mb-4">
                                    <div class="opac-card h-100">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <span class="badge bg-opac-light text-opac-primary border-opac">
                                                    {{ $news->published_at ? $news->published_at->format('d M Y') : $news->created_at->format('d M Y') }}
                                                </span>
                                            </div>
                                            <h5 class="h6 mb-2">
                                                <a href="{{ route('opac.news.show', $news->id) }}" class="text-decoration-none text-dark fw-bold">
                                                    {{ Str::limit($news->title, 60) }}
                                                </a>
                                            </h5>
                                            <p class="small text-muted mb-0">
                                                {{ Str::limit(strip_tags($news->content), 100) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="opac-card-simple text-center py-4 bg-white border-opac">
                            <p class="text-muted mb-0">{{ __('No recent news articles.') }}</p>
                        </div>
                    @endif
                </div>

                <!-- Upcoming Events -->
                <div class="col-lg-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="h4 mb-0"><i class="fas fa-calendar-alt me-2 text-primary"></i>{{ __('Upcoming Events') }}</h3>
                        <a href="{{ route('opac.events.index') }}" class="btn btn-sm btn-outline-primary">{{ __('All Events') }}</a>
                    </div>

                    @if($upcomingEvents->isNotEmpty())
                        @foreach($upcomingEvents as $event)
                            <div class="opac-card-simple mb-3 bg-white border-opac hover-shadow-sm transition-all d-flex gap-3">
                                <div class="text-center bg-opac-light p-2 rounded d-flex flex-column justify-content-center" style="min-width: 65px; height: 65px;">
                                    <span class="small fw-bold text-uppercase text-opac-primary">{{ $event->start_date->format('M') }}</span>
                                    <span class="h5 mb-0 fw-bold">{{ $event->start_date->format('d') }}</span>
                                </div>
                                <div class="overflow-hidden">
                                    <h6 class="mb-1 text-truncate">
                                        <a href="{{ route('opac.events.show', $event->id) }}" class="text-decoration-none text-dark fw-bold">
                                            {{ $event->title }}
                                        </a>
                                    </h6>
                                    <p class="small text-muted mb-0">
                                        <i class="fas fa-clock me-1 opacity-75"></i> {{ $event->start_date->format('H:i') }}
                                        @if($event->location)
                                            <span class="mx-1">•</span>
                                            <i class="fas fa-map-marker-alt me-1 opacity-75"></i> {{ Str::limit($event->location, 20) }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="opac-card-simple text-center py-4 bg-white border-opac">
                            <p class="text-muted mb-0">{{ __('No upcoming events scheduled.') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Browse & Services Quick Access -->
            <div class="mb-2"><span class="opac-eyebrow">{{ __('Discover') }}</span></div>
            <div class="row g-3 mt-1 mb-5">
                <div class="col-md-4">
                    <a href="{{ route('opac.records.index') }}" class="quick-tile">
                        <span class="quick-tile-icon"><i class="fas fa-book-reader"></i></span>
                        <h5>{{ __('Browse Records') }}</h5>
                        <p class="small text-muted mb-0">{{ __('Explore our physical and digital collections through the catalog.') }}</p>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('opac.digital.folders.index') }}" class="quick-tile">
                        <span class="quick-tile-icon"><i class="fas fa-folder-open"></i></span>
                        <h5>{{ __('Digital Archives') }}</h5>
                        <p class="small text-muted mb-0">{{ __('Directly access digitized documents, manuscripts, and reports.') }}</p>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('opac.feedback.create') }}" class="quick-tile">
                        <span class="quick-tile-icon"><i class="fas fa-question-circle"></i></span>
                        <h5>{{ __('Need Help?') }}</h5>
                        <p class="small text-muted mb-0">{{ __('Have a question? Get in touch with our expert library staff.') }}</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus on search input
    const searchInput = document.getElementById('quickSearchInput');
    if (searchInput) {
        searchInput.focus();
    }

    // Search suggestions (autocomplete)
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const term = this.value.trim();
                if (term.length >= 2) {
                    fetchSuggestions(term);
                }
            }, 300);
        });
    }
});

function fetchSuggestions(term) {
    fetch(`{{ route('opac.search.suggestions') }}?term=${encodeURIComponent(term)}`)
        .then(response => response.json())
        .then(data => {
            // Handle suggestions display
            // This would require additional UI for suggestions dropdown
        })
        .catch(error => {
            console.error('Error fetching suggestions:', error);
        });
}

function clearAdvancedForm() {
    const form = document.getElementById('advancedSearchForm');
    form.reset();
    // Clear any selected values
    const selects = form.querySelectorAll('select');
    selects.forEach(select => {
        select.selectedIndex = 0;
    });
}
</script>
@endpush
