@extends('opac.layouts.app')

@section('title', __('Search') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <!-- Hero Section -->
            <div class="text-center mb-5">
                <h1 class="display-5 mb-4">{{ __('Search Our Collections') }}</h1>
                <p class="lead text-muted">{{ __('Find documents, records, and resources in our digital archive') }}</p>
            </div>

            <!-- Quick Search Form -->
            <div class="opac-card mb-4">
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('opac.search.results') }}" id="quickSearchForm">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input type="text"
                                           name="q"
                                           class="form-control border-0"
                                           placeholder="{{ __('Search for documents, titles, authors...') }}"
                                           value="{{ request('q') }}"
                                           autocomplete="off"
                                           id="quickSearchInput">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn opac-search-btn w-100">
                                    <i class="fas fa-search me-2"></i>{{ __('Search') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Advanced Search -->
            <div class="opac-card mb-4">
                <div class="opac-card-header">
                    <i class="fas fa-cogs me-2"></i>{{ __('Advanced Search') }}
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('opac.search.results') }}" id="advancedSearchForm">
                        <div class="row g-3">
                            <!-- Title -->
                            <div class="col-md-6">
                                <label for="title" class="form-label">{{ __('Title') }}</label>
                                <input type="text"
                                       class="form-control"
                                       id="title"
                                       name="title"
                                       value="{{ request('title') }}"
                                       placeholder="{{ __('Document title...') }}">
                            </div>

                            <!-- Author -->
                            <div class="col-md-6">
                                <label for="author" class="form-label">{{ __('Author') }}</label>
                                <input type="text"
                                       class="form-control"
                                       id="author"
                                       name="author"
                                       value="{{ request('author') }}"
                                       placeholder="{{ __('Author name...') }}">
                            </div>

                            <!-- Subject -->
                            <div class="col-md-6">
                                <label for="subject" class="form-label">{{ __('Subject') }}</label>
                                <input type="text"
                                       class="form-control"
                                       id="subject"
                                       name="subject"
                                       value="{{ request('subject') }}"
                                       placeholder="{{ __('Subject or topic...') }}">
                            </div>

                            <!-- ISBN -->
                            <div class="col-md-6">
                                <label for="isbn" class="form-label">{{ __('ISBN') }}</label>
                                <input type="text"
                                       class="form-control"
                                       id="isbn"
                                       name="isbn"
                                       value="{{ request('isbn') }}"
                                       placeholder="{{ __('ISBN number...') }}">
                            </div>

                            <!-- Date Range -->
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Date Range') }}</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="date"
                                               class="form-control"
                                               name="date_from"
                                               value="{{ request('date_from') }}"
                                               title="{{ __('From date') }}">
                                    </div>
                                    <div class="col-6">
                                        <input type="date"
                                               class="form-control"
                                               name="date_to"
                                               value="{{ request('date_to') }}"
                                               title="{{ __('To date') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Language -->
                            <div class="col-md-6">
                                <label for="language" class="form-label">{{ __('Language') }}</label>
                                <select class="form-select" id="language" name="language">
                                    <option value="">{{ __('Any language') }}</option>
                                    <option value="fr" {{ request('language') == 'fr' ? 'selected' : '' }}>{{ __('French') }}</option>
                                    <option value="en" {{ request('language') == 'en' ? 'selected' : '' }}>{{ __('English') }}</option>
                                    <option value="es" {{ request('language') == 'es' ? 'selected' : '' }}>{{ __('Spanish') }}</option>
                                    <option value="de" {{ request('language') == 'de' ? 'selected' : '' }}>{{ __('German') }}</option>
                                </select>
                            </div>

                            <!-- Document Type -->
                            <div class="col-md-6">
                                <label for="type" class="form-label">{{ __('Document Type') }}</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="">{{ __('Any type') }}</option>
                                    <option value="book" {{ request('type') == 'book' ? 'selected' : '' }}>{{ __('Book') }}</option>
                                    <option value="article" {{ request('type') == 'article' ? 'selected' : '' }}>{{ __('Article') }}</option>
                                    <option value="report" {{ request('type') == 'report' ? 'selected' : '' }}>{{ __('Report') }}</option>
                                    <option value="thesis" {{ request('type') == 'thesis' ? 'selected' : '' }}>{{ __('Thesis') }}</option>
                                    <option value="multimedia" {{ request('type') == 'multimedia' ? 'selected' : '' }}>{{ __('Multimedia') }}</option>
                                </select>
                            </div>

                            <!-- Category -->
                            <div class="col-md-6">
                                <label for="category" class="form-label">{{ __('Category') }}</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">{{ __('Any category') }}</option>
                                    <!-- Categories would be loaded dynamically -->
                                </select>
                            </div>

                            <!-- Sort Order -->
                            <div class="col-12">
                                <label for="sort" class="form-label">{{ __('Sort Results By') }}</label>
                                <select class="form-select" id="sort" name="sort">
                                    <option value="relevance" {{ request('sort') == 'relevance' ? 'selected' : '' }}>{{ __('Relevance') }}</option>
                                    <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>{{ __('Title (A-Z)') }}</option>
                                    <option value="author" {{ request('sort') == 'author' ? 'selected' : '' }}>{{ __('Author (A-Z)') }}</option>
                                    <option value="date_desc" {{ request('sort') == 'date_desc' ? 'selected' : '' }}>{{ __('Date (Newest First)') }}</option>
                                    <option value="date_asc" {{ request('sort') == 'date_asc' ? 'selected' : '' }}>{{ __('Date (Oldest First)') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <button type="submit" class="btn opac-search-btn w-100">
                                    <i class="fas fa-search me-2"></i>{{ __('Advanced Search') }}
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="clearAdvancedForm()">
                                    <i class="fas fa-eraser me-2"></i>{{ __('Clear Form') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

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

            <!-- Quick Access -->
            <div class="row mt-5">
                <div class="col-md-4 mb-3">
                    <div class="opac-card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-books fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">{{ __('Browse Records') }}</h5>
                            <p class="card-text">{{ __('Explore our complete collection') }}</p>
                            <a href="{{ route('opac.records.index') }}" class="btn btn-primary">
                                {{ __('Browse Now') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="opac-card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-newspaper fa-3x text-success mb-3"></i>
                            <h5 class="card-title">{{ __('Latest News') }}</h5>
                            <p class="card-text">{{ __('Stay updated with library news') }}</p>
                            <a href="{{ route('opac.news.index') }}" class="btn btn-success">
                                {{ __('Read News') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="opac-card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-question-circle fa-3x text-info mb-3"></i>
                            <h5 class="card-title">{{ __('Need Help?') }}</h5>
                            <p class="card-text">{{ __('Get assistance with your search') }}</p>
                            <a href="{{ route('opac.feedback.create') }}" class="btn btn-info">
                                {{ __('Contact Us') }}
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
