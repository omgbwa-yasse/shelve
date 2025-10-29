@extends('opac.layouts.app')

@section('title', __('Search Records'))

@section('content')
<div class="container py-4">
    <!-- Search Header -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('opac.records.search') }}" method="GET" id="searchForm">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label for="q" class="form-label">{{ __('Search in our catalog') }}</label>
                        <div class="input-group input-group-lg">
                            <input type="text"
                                   class="form-control"
                                   id="q"
                                   name="q"
                                   value="{{ request('q') }}"
                                   placeholder="{{ __('Enter title, author, ISBN, or keywords...') }}"
                                   autocomplete="off">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i>
                                {{ __('Search') }}
                            </button>
                        </div>
                        <!-- Autocomplete suggestions -->
                        <div id="suggestions" class="position-absolute w-100 bg-white border border-top-0 shadow-sm d-none" style="z-index: 1000;">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <button type="button"
                                class="btn btn-outline-secondary w-100"
                                data-bs-toggle="collapse"
                                data-bs-target="#advancedSearch">
                            <i class="bi bi-funnel"></i>
                            {{ __('Advanced Search') }}
                        </button>
                    </div>
                </div>

                <!-- Advanced Search Options -->
                <div class="collapse mt-3" id="advancedSearch">
                    <div class="card card-body bg-light">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="type" class="form-label">{{ __('Document Type') }}</label>
                                <select class="form-select" name="type" id="type">
                                    <option value="">{{ __('All Types') }}</option>
                                    <option value="book" {{ request('type') == 'book' ? 'selected' : '' }}>{{ __('Books') }}</option>
                                    <option value="periodical" {{ request('type') == 'periodical' ? 'selected' : '' }}>{{ __('Periodicals') }}</option>
                                    <option value="multimedia" {{ request('type') == 'multimedia' ? 'selected' : '' }}>{{ __('Multimedia') }}</option>
                                    <option value="manuscript" {{ request('type') == 'manuscript' ? 'selected' : '' }}>{{ __('Manuscripts') }}</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="author" class="form-label">{{ __('Author') }}</label>
                                <input type="text" class="form-control" name="author"
                                       value="{{ request('author') }}"
                                       placeholder="{{ __('Author name...') }}">
                            </div>

                            <div class="col-md-3">
                                <label for="year_from" class="form-label">{{ __('Year From') }}</label>
                                <input type="number" class="form-control" name="year_from"
                                       value="{{ request('year_from') }}"
                                       min="1000" max="{{ date('Y') }}"
                                       placeholder="{{ __('e.g., 2000') }}">
                            </div>

                            <div class="col-md-3">
                                <label for="year_to" class="form-label">{{ __('Year To') }}</label>
                                <input type="number" class="form-control" name="year_to"
                                       value="{{ request('year_to') }}"
                                       min="1000" max="{{ date('Y') }}"
                                       placeholder="{{ __('e.g., 2023') }}">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="subject" class="form-label">{{ __('Subject') }}</label>
                                <input type="text" class="form-control" name="subject"
                                       value="{{ request('subject') }}"
                                       placeholder="{{ __('Subject or topic...') }}">
                            </div>

                            <div class="col-md-6">
                                <label for="language" class="form-label">{{ __('Language') }}</label>
                                <select class="form-select" name="language">
                                    <option value="">{{ __('All Languages') }}</option>
                                    <option value="fr" {{ request('language') == 'fr' ? 'selected' : '' }}>{{ __('French') }}</option>
                                    <option value="en" {{ request('language') == 'en' ? 'selected' : '' }}>{{ __('English') }}</option>
                                    <option value="es" {{ request('language') == 'es' ? 'selected' : '' }}>{{ __('Spanish') }}</option>
                                    <option value="de" {{ request('language') == 'de' ? 'selected' : '' }}>{{ __('German') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Search Results -->
    @if(isset($records))
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">{{ __('Search Results') }}</h4>
                <p class="text-muted mb-0">
                    {{ __(':count results found', ['count' => $records->total()]) }}
                    @if(request('q'))
                        {{ __('for') }} "<strong>{{ request('q') }}</strong>"
                    @endif
                </p>
            </div>

            <div class="d-flex gap-2">
                <select class="form-select form-select-sm" onchange="window.location.href=this.value" style="width: auto;">
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'relevance']) }}"
                            {{ request('sort', 'relevance') == 'relevance' ? 'selected' : '' }}>
                        {{ __('Sort by Relevance') }}
                    </option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'title']) }}"
                            {{ request('sort') == 'title' ? 'selected' : '' }}>
                        {{ __('Sort by Title') }}
                    </option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'author']) }}"
                            {{ request('sort') == 'author' ? 'selected' : '' }}>
                        {{ __('Sort by Author') }}
                    </option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'date']) }}"
                            {{ request('sort') == 'date' ? 'selected' : '' }}>
                        {{ __('Sort by Date') }}
                    </option>
                </select>

                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="view" id="listView"
                           {{ request('view', 'list') == 'list' ? 'checked' : '' }}>
                    <label class="btn btn-outline-secondary btn-sm" for="listView">
                        <i class="bi bi-list-ul"></i>
                    </label>

                    <input type="radio" class="btn-check" name="view" id="gridView"
                           {{ request('view') == 'grid' ? 'checked' : '' }}>
                    <label class="btn btn-outline-secondary btn-sm" for="gridView">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </label>
                </div>
            </div>
        </div>

        @if($records->count() > 0)
            <div id="recordsList" class="{{ request('view') == 'grid' ? 'row' : '' }}">
                @foreach($records as $record)
                    @if(request('view') == 'grid')
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                @if($record->cover_image)
                                    <img src="{{ asset('storage/' . $record->cover_image) }}"
                                         class="card-img-top" style="height: 200px; object-fit: cover;"
                                         alt="{{ $record->title }}">
                                @endif
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title">
                                        <a href="{{ route('opac.records.show', $record) }}"
                                           class="text-decoration-none">
                                            {{ Str::limit($record->title, 50) }}
                                        </a>
                                    </h6>
                                    @if($record->authors)
                                        <p class="card-text text-muted small">
                                            {{ __('by') }} {{ Str::limit($record->authors, 40) }}
                                        </p>
                                    @endif
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">{{ $record->publication_year }}</small>
                                            <span class="badge {{ $record->availability ? 'bg-success' : 'bg-warning' }}">
                                                {{ $record->availability ? __('Available') : __('On Loan') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card shadow-sm mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2">
                                        @if($record->cover_image)
                                            <img src="{{ asset('storage/' . $record->cover_image) }}"
                                                 class="img-fluid rounded"
                                                 alt="{{ $record->title }}"
                                                 style="max-height: 120px;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                 style="height: 120px; width: 80px;">
                                                <i class="bi bi-book text-muted display-6"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-8">
                                        <h5 class="card-title mb-2">
                                            <a href="{{ route('opac.records.show', $record) }}"
                                               class="text-decoration-none">
                                                {{ $record->title }}
                                            </a>
                                        </h5>
                                        @if($record->authors)
                                            <p class="text-muted mb-1">
                                                <strong>{{ __('Author(s):') }}</strong> {{ $record->authors }}
                                            </p>
                                        @endif
                                        @if($record->publication_year)
                                            <p class="text-muted mb-1">
                                                <strong>{{ __('Year:') }}</strong> {{ $record->publication_year }}
                                            </p>
                                        @endif
                                        @if($record->publisher_name)
                                            <p class="text-muted mb-1">
                                                <strong>{{ __('Publisher:') }}</strong> {{ $record->publisher_name }}
                                            </p>
                                        @endif
                                        @if($record->description)
                                            <p class="card-text">
                                                {{ Str::limit($record->description, 200) }}
                                            </p>
                                        @endif
                                        <div class="d-flex flex-wrap gap-1">
                                            @if($record->subjects)
                                                @foreach(explode(',', $record->subjects) as $subject)
                                                    <span class="badge bg-secondary">{{ trim($subject) }}</span>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <span class="badge {{ $record->availability ? 'bg-success' : 'bg-warning' }} mb-2">
                                            {{ $record->availability ? __('Available') : __('On Loan') }}
                                        </span>
                                        <br>
                                        <a href="{{ route('opac.records.show', $record) }}"
                                           class="btn btn-primary btn-sm">
                                            {{ __('View Details') }}
                                        </a>
                                        @if($record->availability && auth('public')->check())
                                            <br><br>
                                            <a href="{{ route('opac.reservations.create', ['record' => $record->id]) }}"
                                               class="btn btn-success btn-sm">
                                                {{ __('Reserve') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $records->appends(request()->query())->links() }}
            </div>
        @else
            <!-- No Results -->
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-search display-1 text-muted mb-3"></i>
                    <h4 class="text-muted">{{ __('No Records Found') }}</h4>
                    <p class="text-muted mb-4">
                        {{ __('We couldn\'t find any records matching your search criteria.') }}
                    </p>

                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <h6>{{ __('Try:') }}</h6>
                            <ul class="list-unstyled text-muted">
                                <li>• {{ __('Using different keywords') }}</li>
                                <li>• {{ __('Checking your spelling') }}</li>
                                <li>• {{ __('Using fewer search terms') }}</li>
                                <li>• {{ __('Browsing by category') }}</li>
                            </ul>

                            <div class="mt-4">
                                <a href="{{ route('opac.document-requests.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i>
                                    {{ __('Request This Document') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Autocomplete functionality
    const searchInput = document.getElementById('q');
    const suggestionsDiv = document.getElementById('suggestions');
    let timeoutId;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(timeoutId);
            const query = this.value.trim();

            if (query.length >= 2) {
                timeoutId = setTimeout(() => {
                    fetch(`{{ route('opac.records.autocomplete') }}?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            showSuggestions(data);
                        })
                        .catch(error => {
                            console.error('Autocomplete error:', error);
                        });
                }, 300);
            } else {
                hideSuggestions();
            }
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
                hideSuggestions();
            }
        });
    }

    function showSuggestions(suggestions) {
        if (suggestions.length === 0) {
            hideSuggestions();
            return;
        }

        let html = '';
        suggestions.forEach(suggestion => {
            html += `
                <div class="suggestion-item p-2 border-bottom"
                     style="cursor: pointer;"
                     onclick="selectSuggestion('${suggestion}')">
                    ${suggestion}
                </div>
            `;
        });

        suggestionsDiv.innerHTML = html;
        suggestionsDiv.classList.remove('d-none');
    }

    function hideSuggestions() {
        suggestionsDiv.classList.add('d-none');
    }

    // View toggle functionality
    const listViewBtn = document.getElementById('listView');
    const gridViewBtn = document.getElementById('gridView');
    const recordsList = document.getElementById('recordsList');

    if (listViewBtn && gridViewBtn) {
        listViewBtn.addEventListener('change', function() {
            if (this.checked) {
                updateView('list');
            }
        });

        gridViewBtn.addEventListener('change', function() {
            if (this.checked) {
                updateView('grid');
            }
        });
    }

    function updateView(view) {
        const url = new URL(window.location);
        url.searchParams.set('view', view);
        window.location.href = url.toString();
    }
});

function selectSuggestion(suggestion) {
    document.getElementById('q').value = suggestion;
    document.getElementById('suggestions').classList.add('d-none');
    document.getElementById('searchForm').submit();
}
</script>
@endpush

@push('styles')
<style>
.suggestion-item:hover {
    background-color: #f8f9fa;
}

.card-img-top {
    transition: transform 0.2s;
}

.card:hover .card-img-top {
    transform: scale(1.05);
}

.badge {
    font-size: 0.75em;
}

.btn-check:checked + .btn {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}
</style>
@endpush
