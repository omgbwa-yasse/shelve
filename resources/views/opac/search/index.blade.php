@extends('opac.layouts.app')

@section('title', __('Search') . ' - OPAC')

@push('styles')
<style>
    /* Hero Search Specific Styles */
    .opac-search-icon {
        position: absolute;
        left: 1.25rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        font-size: 1.1rem;
        z-index: 5;
    }

    .opac-search-input {
        width: 100%;
        padding: 1rem 1rem 1rem 3.25rem;
        border: 2px solid #e0e0e0;
        border-radius: var(--opac-border-radius);
        font-size: 1.05rem;
        transition: all 0.3s ease;
        background: white;
    }

    .opac-search-input:focus {
        outline: none;
        border-color: var(--opac-primary);
        box-shadow: 0 0 0 0.25rem rgba(0, 74, 153, 0.15);
    }

    .opac-search-btn {
        padding: 1rem 2.5rem;
        background: var(--opac-accent);
        color: white;
        border: none;
        border-radius: var(--opac-border-radius);
        font-weight: 600;
        font-size: 1.05rem;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .opac-search-btn:hover {
        background: #e55a2a;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
    }

    /* Advanced Search Styling */
    .form-label.fw-semibold {
        color: var(--opac-text-primary);
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--opac-primary);
        box-shadow: 0 0 0 0.2rem rgba(0, 74, 153, 0.15);
    }

    /* Quick Access Cards */
    .opac-card.text-center .card-body {
        padding: 2rem 1.5rem;
    }

    .opac-card.text-center .fa-3x {
        opacity: 0.9;
    }

    .opac-card.text-center:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .opac-card.text-center:hover .fa-3x {
        transform: scale(1.1);
        transition: transform 0.3s ease;
    }

    /* Search Tips */
    .text-warning {
        color: #ffc107 !important;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .opac-search-btn {
            padding: 1rem 1.5rem;
            font-size: 1rem;
        }

        .opac-search-input {
            font-size: 1rem;
        }

        .display-5 {
            font-size: 2rem;
        }

        .lead {
            font-size: 1rem;
        }
    }
</style>
@endpush

@section('content')
<!-- Hero Search Section -->
<div style="background: linear-gradient(135deg, var(--opac-primary) 0%, var(--opac-primary-dark) 100%); color: white; padding: 3.5rem 0 4rem;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="text-center mb-4">
                    <h1 class="display-5 fw-bold mb-3" style="font-family: 'Lora', Georgia, serif;">{{ __('Search Our Collections') }}</h1>
                    <p class="lead opacity-90">{{ __('Explore thousands of documents, books, and digital resources') }}</p>
                </div>

                <!-- Quick Search Form -->
                <form method="GET" action="{{ route('opac.search.results') }}" id="quickSearchForm">
                    <div class="d-flex gap-2">
                        <div class="flex-grow-1 position-relative">
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
                </form>

                <!-- Search Options -->
                <div class="mt-3 text-center">
                    <small class="opacity-75">
                        <a href="#advancedSearch" class="text-white text-decoration-none fw-semibold" data-bs-toggle="collapse">
                            <i class="fas fa-sliders-h me-1"></i>{{ __('Advanced Search Options') }}
                        </a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-10 col-xl-9 mx-auto">

            <!-- Advanced Search (Collapsed) -->
            <div class="collapse mb-4" id="advancedSearch">
                <div class="opac-card">
                    <div class="opac-card-header">
                        <i class="fas fa-cogs"></i>
                        {{ __('Advanced Search') }}
                    </div>
                    <div class="card-body opac-card-body">
                        <form method="GET" action="{{ route('opac.search.results') }}" id="advancedSearchForm">
                            <div class="row g-3">
                                <!-- Title -->
                                <div class="col-md-6">
                                    <label for="title" class="form-label fw-semibold">{{ __('Title') }}</label>
                                    <input type="text"
                                           class="form-control"
                                           id="title"
                                           name="title"
                                           value="{{ request('title') }}"
                                           placeholder="{{ __('Enter document title...') }}">
                                </div>

                                <!-- Author -->
                                <div class="col-md-6">
                                    <label for="author" class="form-label fw-semibold">{{ __('Author / Creator') }}</label>
                                    <input type="text"
                                           class="form-control"
                                           id="author"
                                           name="author"
                                           value="{{ request('author') }}"
                                           placeholder="{{ __('Enter author name...') }}">
                                </div>

                                <!-- Subject / Keywords -->
                                <div class="col-md-6">
                                    <label for="subject" class="form-label fw-semibold">{{ __('Subject / Keywords') }}</label>
                                    <input type="text"
                                           class="form-control"
                                           id="subject"
                                           name="subject"
                                           value="{{ request('subject') }}"
                                           placeholder="{{ __('Enter subject or keywords...') }}">
                                </div>

                                <!-- ISBN / Identifier -->
                                <div class="col-md-6">
                                    <label for="isbn" class="form-label fw-semibold">{{ __('ISBN / Identifier') }}</label>
                                    <input type="text"
                                           class="form-control"
                                           id="isbn"
                                           name="isbn"
                                           value="{{ request('isbn') }}"
                                           placeholder="{{ __('ISBN, ISSN, or other identifier...') }}">
                                </div>

                                <!-- Date Range -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Publication Date Range') }}</label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <input type="date"
                                                   class="form-control"
                                                   name="date_from"
                                                   value="{{ request('date_from') }}"
                                                   placeholder="{{ __('From') }}">
                                        </div>
                                        <div class="col-6">
                                            <input type="date"
                                                   class="form-control"
                                                   name="date_to"
                                                   value="{{ request('date_to') }}"
                                                   placeholder="{{ __('To') }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Language -->
                                <div class="col-md-6">
                                    <label for="language" class="form-label fw-semibold">{{ __('Language') }}</label>
                                    <select class="form-select" id="language" name="language">
                                        <option value="">{{ __('Any language') }}</option>
                                        <option value="fr" {{ request('language') == 'fr' ? 'selected' : '' }}>{{ __('French') }}</option>
                                        <option value="en" {{ request('language') == 'en' ? 'selected' : '' }}>{{ __('English') }}</option>
                                        <option value="es" {{ request('language') == 'es' ? 'selected' : '' }}>{{ __('Spanish') }}</option>
                                        <option value="de" {{ request('language') == 'de' ? 'selected' : '' }}>{{ __('German') }}</option>
                                        <option value="ar" {{ request('language') == 'ar' ? 'selected' : '' }}>{{ __('Arabic') }}</option>
                                        <option value="pt" {{ request('language') == 'pt' ? 'selected' : '' }}>{{ __('Portuguese') }}</option>
                                    </select>
                                </div>

                                <!-- Document Type -->
                                <div class="col-md-6">
                                    <label for="type" class="form-label fw-semibold">{{ __('Resource Type') }}</label>
                                    <select class="form-select" id="type" name="type">
                                        <option value="">{{ __('All types') }}</option>
                                        <option value="book" {{ request('type') == 'book' ? 'selected' : '' }}>{{ __('Book') }}</option>
                                        <option value="artifact" {{ request('type') == 'artifact' ? 'selected' : '' }}>{{ __('Artifact') }}</option>
                                        <option value="archive" {{ request('type') == 'archive' ? 'selected' : '' }}>{{ __('Digital Folder') }}</option>
                                        <option value="document" {{ request('type') == 'document' ? 'selected' : '' }}>{{ __('Digital Document') }}</option>
                                    </select>
                                </div>

                                <!-- Sort By -->
                                <div class="col-md-6">
                                    <label for="sort" class="form-label fw-semibold">{{ __('Sort Results By') }}</label>
                                    <select class="form-select" id="sort" name="sort">
                                        <option value="relevance" {{ request('sort') == 'relevance' ? 'selected' : '' }}>{{ __('Relevance') }}</option>
                                        <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>{{ __('Title (A-Z)') }}</option>
                                        <option value="title_desc" {{ request('sort') == 'title_desc' ? 'selected' : '' }}>{{ __('Title (Z-A)') }}</option>
                                        <option value="author_asc" {{ request('sort') == 'author_asc' ? 'selected' : '' }}>{{ __('Author (A-Z)') }}</option>
                                        <option value="date_desc" {{ request('sort') == 'date_desc' ? 'selected' : '' }}>{{ __('Date (Newest First)') }}</option>
                                        <option value="date_asc" {{ request('sort') == 'date_asc' ? 'selected' : '' }}>{{ __('Date (Oldest First)') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <button type="submit" class="btn btn-opac-primary w-100">
                                        <i class="fas fa-search me-2"></i>{{ __('Search with Filters') }}
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-opac-outline w-100" onclick="clearAdvancedForm()">
                                        <i class="fas fa-undo me-2"></i>{{ __('Clear All') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
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
