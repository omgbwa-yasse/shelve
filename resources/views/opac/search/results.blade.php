@extends('opac.layouts.app')

@section('title', __('Search Results') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-md-3">
            <!-- Search Filters Sidebar -->
            <div class="opac-card mb-4">
                <div class="opac-card-header">
                    <i class="fas fa-filter me-2"></i>{{ __('Refine Search') }}
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('opac.search.results') }}" id="filterForm">
                        <!-- Keep existing search term -->
                        @if(isset($validated['q']))
                            <input type="hidden" name="q" value="{{ $validated['q'] }}">
                        @endif

                        <!-- Quick search -->
                        <div class="mb-3">
                            <label for="filter_q" class="form-label">{{ __('Keywords') }}</label>
                            <input type="text" class="form-control" id="filter_q" name="q"
                                   value="{{ $validated['q'] ?? '' }}"
                                   placeholder="{{ __('Search terms...') }}">
                        </div>

                        <!-- Document Type Filter -->
                        <div class="mb-3">
                            <label for="filter_type" class="form-label">{{ __('Document Type') }}</label>
                            <select class="form-select" id="filter_type" name="type">
                                <option value="">{{ __('All types') }}</option>
                                <option value="book" {{ ($validated['type'] ?? '') == 'book' ? 'selected' : '' }}>{{ __('Book') }}</option>
                                <option value="article" {{ ($validated['type'] ?? '') == 'article' ? 'selected' : '' }}>{{ __('Article') }}</option>
                                <option value="report" {{ ($validated['type'] ?? '') == 'report' ? 'selected' : '' }}>{{ __('Report') }}</option>
                                <option value="thesis" {{ ($validated['type'] ?? '') == 'thesis' ? 'selected' : '' }}>{{ __('Thesis') }}</option>
                            </select>
                        </div>

                        <!-- Language Filter -->
                        <div class="mb-3">
                            <label for="filter_language" class="form-label">{{ __('Language') }}</label>
                            <select class="form-select" id="filter_language" name="language">
                                <option value="">{{ __('All languages') }}</option>
                                <option value="fr" {{ ($validated['language'] ?? '') == 'fr' ? 'selected' : '' }}>{{ __('French') }}</option>
                                <option value="en" {{ ($validated['language'] ?? '') == 'en' ? 'selected' : '' }}>{{ __('English') }}</option>
                                <option value="es" {{ ($validated['language'] ?? '') == 'es' ? 'selected' : '' }}>{{ __('Spanish') }}</option>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div class="mb-3">
                            <label class="form-label">{{ __('Date Range') }}</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="date" class="form-control" name="date_from"
                                           value="{{ $validated['date_from'] ?? '' }}"
                                           title="{{ __('From date') }}">
                                </div>
                                <div class="col-6">
                                    <input type="date" class="form-control" name="date_to"
                                           value="{{ $validated['date_to'] ?? '' }}"
                                           title="{{ __('To date') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Sort Order -->
                        <div class="mb-3">
                            <label for="filter_sort" class="form-label">{{ __('Sort by') }}</label>
                            <select class="form-select" id="filter_sort" name="sort">
                                <option value="relevance" {{ ($validated['sort'] ?? '') == 'relevance' ? 'selected' : '' }}>{{ __('Relevance') }}</option>
                                <option value="title" {{ ($validated['sort'] ?? '') == 'title' ? 'selected' : '' }}>{{ __('Title') }}</option>
                                <option value="date_desc" {{ ($validated['sort'] ?? '') == 'date_desc' ? 'selected' : '' }}>{{ __('Date (New-Old)') }}</option>
                                <option value="date_asc" {{ ($validated['sort'] ?? '') == 'date_asc' ? 'selected' : '' }}>{{ __('Date (Old-New)') }}</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>{{ __('Apply Filters') }}
                            </button>
                            <a href="{{ route('opac.search') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>{{ __('Clear All') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Search Tips -->
            <div class="opac-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>{{ __('Search Tips') }}
                    </h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2">• {{ __('Use quotes for exact phrases') }}</li>
                        <li class="mb-2">• {{ __('Use * for wildcards') }}</li>
                        <li class="mb-2">• {{ __('Combine multiple filters') }}</li>
                        <li class="mb-2">• {{ __('Try different keywords') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Search Summary -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h2 class="h4 mb-2">{{ __('Search Results') }}</h2>
                        @if(isset($validated['q']) && !empty($validated['q']))
                            <p class="text-muted mb-1">
                                {{ __('Results for:') }} <strong>"{{ $validated['q'] }}"</strong>
                            </p>
                        @endif
                        <p class="text-muted">
                            {{ number_format($totalResults) }} {{ __('document(s) found') }}
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('opac.search') }}" class="btn btn-outline-primary">
                            <i class="fas fa-search me-2"></i>{{ __('New Search') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Applied Filters Display -->
            @if(count(array_filter($validated ?? [])) > 0)
            <div class="mb-4">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="small fw-medium">{{ __('Active filters:') }}</span>

                    @if(!empty($validated['q']))
                        <span class="opac-badge">
                            {{ __('Keywords') }}: {{ $validated['q'] }}
                            <a href="{{ request()->fullUrlWithQuery(['q' => null]) }}" class="text-white ms-1">×</a>
                        </span>
                    @endif

                    @if(!empty($validated['type']))
                        <span class="opac-badge">
                            {{ __('Type') }}: {{ ucfirst($validated['type']) }}
                            <a href="{{ request()->fullUrlWithQuery(['type' => null]) }}" class="text-white ms-1">×</a>
                        </span>
                    @endif

                    @if(!empty($validated['language']))
                        <span class="opac-badge">
                            {{ __('Language') }}: {{ strtoupper($validated['language']) }}
                            <a href="{{ request()->fullUrlWithQuery(['language' => null]) }}" class="text-white ms-1">×</a>
                        </span>
                    @endif

                    @if(!empty($validated['date_from']) || !empty($validated['date_to']))
                        <span class="opac-badge">
                            {{ __('Date') }}:
                            {{ $validated['date_from'] ?? __('Any') }} - {{ $validated['date_to'] ?? __('Any') }}
                            <a href="{{ request()->fullUrlWithQuery(['date_from' => null, 'date_to' => null]) }}" class="text-white ms-1">×</a>
                        </span>
                    @endif

                    <a href="{{ route('opac.search') }}" class="btn btn-sm btn-outline-secondary">
                        {{ __('Clear all filters') }}
                    </a>
                </div>
            </div>
            @endif

            <!-- Results -->
            @if($results->count() > 0)
                <!-- Results List -->
                <div class="search-results">
                    @foreach($results as $result)
                    <div class="opac-card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-9">
                                    <!-- Title -->
                                    <h5 class="card-title mb-2">
                                        <a href="{{ route('opac.records.show', $result->id) }}"
                                           class="text-decoration-none text-dark">
                                            {{ $result->title ?? $result->name ?? __('Untitled Document') }}
                                        </a>
                                    </h5>

                                    <!-- Metadata badges -->
                                    <div class="mb-2">
                                        @if(isset($result->type))
                                            <span class="opac-badge me-2">{{ ucfirst($result->type) }}</span>
                                        @endif
                                        @if(isset($result->language))
                                            <span class="opac-badge me-2">{{ strtoupper($result->language) }}</span>
                                        @endif
                                        @if(isset($result->date))
                                            <span class="opac-badge">
                                                <i class="fas fa-calendar me-1"></i>{{ $result->date }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Authors -->
                                    @if(isset($result->authors) && !empty($result->authors))
                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-user me-1"></i>
                                        {{ is_array($result->authors) ? implode(', ', $result->authors) : $result->authors }}
                                    </p>
                                    @endif

                                    <!-- Description/Content -->
                                    @if(isset($result->description) || isset($result->content))
                                    <p class="card-text">
                                        {{ Str::limit($result->description ?? $result->content, 200) }}
                                    </p>
                                    @endif

                                    <!-- Subject/Keywords -->
                                    @if(isset($result->subjects) && !empty($result->subjects))
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-tags me-1"></i>{{ __('Subjects:') }}
                                            @if(is_array($result->subjects))
                                                {{ implode(', ', $result->subjects) }}
                                            @else
                                                {{ $result->subjects }}
                                            @endif
                                        </small>
                                    </div>
                                    @endif
                                </div>
                                <div class="col-md-3 text-end">
                                    <!-- Actions -->
                                    <a href="{{ route('opac.records.show', $result->id) }}"
                                       class="btn btn-primary mb-2 w-100">
                                        <i class="fas fa-eye me-2"></i>{{ __('View Details') }}
                                    </a>

                                    <!-- Additional info -->
                                    @if(isset($result->attachments_count) && $result->attachments_count > 0)
                                    <div class="text-muted small">
                                        <i class="fas fa-paperclip me-1"></i>
                                        {{ $result->attachments_count }} {{ __('attachment(s)') }}
                                    </div>
                                    @endif

                                    @if(isset($result->pages) && $result->pages > 0)
                                    <div class="text-muted small">
                                        <i class="fas fa-file-alt me-1"></i>
                                        {{ $result->pages }} {{ __('page(s)') }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination would go here -->
                <!-- <div class="d-flex justify-content-center mt-4">
                    {{ $results->appends(request()->query())->links() }}
                </div> -->
            @else
                <!-- No Results -->
                <div class="opac-card text-center py-5">
                    <div class="card-body">
                        <i class="fas fa-search fa-4x text-muted mb-4"></i>
                        <h4>{{ __('No documents found') }}</h4>
                        <p class="text-muted mb-4">
                            {{ __('We couldn\'t find any documents matching your search criteria.') }}
                        </p>

                        <div class="row justify-content-center">
                            <div class="col-md-6">
                                <h6 class="mb-3">{{ __('Try:') }}</h6>
                                <ul class="list-unstyled text-start">
                                    <li class="mb-2">• {{ __('Checking your spelling') }}</li>
                                    <li class="mb-2">• {{ __('Using fewer or different keywords') }}</li>
                                    <li class="mb-2">• {{ __('Using broader search terms') }}</li>
                                    <li class="mb-2">• {{ __('Removing some filters') }}</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('opac.search') }}" class="btn btn-primary me-2">
                                <i class="fas fa-search me-2"></i>{{ __('New Search') }}
                            </a>
                            <a href="{{ route('opac.records.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-list me-2"></i>{{ __('Browse All') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit filter form on select changes
    const filterSelects = document.querySelectorAll('#filterForm select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });

    // Auto-submit on date changes with debounce
    const dateInputs = document.querySelectorAll('#filterForm input[type="date"]');
    let dateTimer;
    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            clearTimeout(dateTimer);
            dateTimer = setTimeout(() => {
                document.getElementById('filterForm').submit();
            }, 1000);
        });
    });
});
</script>
@endpush
