@extends('layouts.opac')

@section('title', __('Advanced Search'))

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">
                    <i class="bi bi-search text-primary"></i>
                    {{ __('Advanced Search') }}
                </h1>
                <a href="{{ route('opac.records.index') }}" class="btn btn-outline-secondary">
                    {{ __('Simple Search') }}
                </a>
            </div>

            <!-- Search Form -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('opac.search.results') }}" method="POST">
                        @csrf

                        <!-- Basic Search -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="q" class="form-label">{{ __('Keywords') }}</label>
                                <input type="text"
                                       class="form-control @error('q') is-invalid @enderror"
                                       id="q"
                                       name="q"
                                       value="{{ old('q') }}"
                                       placeholder="{{ __('Enter keywords...') }}">
                                @error('q')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="title" class="form-label">{{ __('Title') }}</label>
                                <input type="text"
                                       class="form-control @error('title') is-invalid @enderror"
                                       id="title"
                                       name="title"
                                       value="{{ old('title') }}"
                                       placeholder="{{ __('Search by title...') }}">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Author and Subject -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="author" class="form-label">{{ __('Author') }}</label>
                                <input type="text"
                                       class="form-control @error('author') is-invalid @enderror"
                                       id="author"
                                       name="author"
                                       value="{{ old('author') }}"
                                       placeholder="{{ __('Search by author...') }}">
                                @error('author')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="subject" class="form-label">{{ __('Subject') }}</label>
                                <input type="text"
                                       class="form-control @error('subject') is-invalid @enderror"
                                       id="subject"
                                       name="subject"
                                       value="{{ old('subject') }}"
                                       placeholder="{{ __('Search by subject...') }}">
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- ISBN and Language -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="isbn" class="form-label">{{ __('ISBN/ISSN') }}</label>
                                <input type="text"
                                       class="form-control @error('isbn') is-invalid @enderror"
                                       id="isbn"
                                       name="isbn"
                                       value="{{ old('isbn') }}"
                                       placeholder="{{ __('Enter ISBN or ISSN...') }}">
                                @error('isbn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="language" class="form-label">{{ __('Language') }}</label>
                                <select class="form-select @error('language') is-invalid @enderror"
                                        id="language"
                                        name="language">
                                    <option value="">{{ __('All Languages') }}</option>
                                    <option value="en" {{ old('language') === 'en' ? 'selected' : '' }}>{{ __('English') }}</option>
                                    <option value="fr" {{ old('language') === 'fr' ? 'selected' : '' }}>{{ __('French') }}</option>
                                    <option value="es" {{ old('language') === 'es' ? 'selected' : '' }}>{{ __('Spanish') }}</option>
                                    <option value="de" {{ old('language') === 'de' ? 'selected' : '' }}>{{ __('German') }}</option>
                                    <option value="other">{{ __('Other') }}</option>
                                </select>
                                @error('language')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date_from" class="form-label">{{ __('Publication Date From') }}</label>
                                <input type="date"
                                       class="form-control @error('date_from') is-invalid @enderror"
                                       id="date_from"
                                       name="date_from"
                                       value="{{ old('date_from') }}">
                                @error('date_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="date_to" class="form-label">{{ __('Publication Date To') }}</label>
                                <input type="date"
                                       class="form-control @error('date_to') is-invalid @enderror"
                                       id="date_to"
                                       name="date_to"
                                       value="{{ old('date_to') }}">
                                @error('date_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Type and Category -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="type" class="form-label">{{ __('Document Type') }}</label>
                                <select class="form-select @error('type') is-invalid @enderror"
                                        id="type"
                                        name="type">
                                    <option value="">{{ __('All Types') }}</option>
                                    <option value="book">{{ __('Book') }}</option>
                                    <option value="article">{{ __('Article') }}</option>
                                    <option value="thesis">{{ __('Thesis') }}</option>
                                    <option value="report">{{ __('Report') }}</option>
                                    <option value="map">{{ __('Map') }}</option>
                                    <option value="multimedia">{{ __('Multimedia') }}</option>
                                    <option value="other">{{ __('Other') }}</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="category" class="form-label">{{ __('Category') }}</label>
                                <select class="form-select @error('category') is-invalid @enderror"
                                        id="category"
                                        name="category">
                                    <option value="">{{ __('All Categories') }}</option>
                                    <option value="science">{{ __('Science') }}</option>
                                    <option value="literature">{{ __('Literature') }}</option>
                                    <option value="history">{{ __('History') }}</option>
                                    <option value="technology">{{ __('Technology') }}</option>
                                    <option value="arts">{{ __('Arts') }}</option>
                                    <option value="education">{{ __('Education') }}</option>
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Sort Options -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="sort" class="form-label">{{ __('Sort Results By') }}</label>
                                <select class="form-select" id="sort" name="sort">
                                    <option value="relevance">{{ __('Relevance') }}</option>
                                    <option value="title">{{ __('Title A-Z') }}</option>
                                    <option value="author">{{ __('Author A-Z') }}</option>
                                    <option value="date_desc">{{ __('Newest First') }}</option>
                                    <option value="date_asc">{{ __('Oldest First') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg me-3">
                                    <i class="bi bi-search"></i>
                                    {{ __('Search') }}
                                </button>
                                <button type="reset" class="btn btn-outline-secondary btn-lg">
                                    {{ __('Clear Form') }}
                                </button>

                                @auth('public')
                                <button type="button" class="btn btn-outline-info btn-lg ms-3" id="saveSearchBtn">
                                    <i class="bi bi-bookmark"></i>
                                    {{ __('Save Search') }}
                                </button>
                                @endauth
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @auth('public')
            <!-- Search History -->
            @if($searchHistory && $searchHistory->count() > 0)
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history text-info"></i>
                        {{ __('Recent Searches') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($searchHistory->take(5) as $search)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $search->query }}</strong>
                                <small class="text-muted d-block">{{ $search->created_at->diffForHumans() }}</small>
                            </div>
                            <button class="btn btn-sm btn-outline-primary repeat-search"
                                    data-search="{{ htmlspecialchars(json_encode($search->filters)) }}">
                                {{ __('Repeat') }}
                            </button>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('opac.search.history') }}" class="btn btn-sm btn-outline-info">
                            {{ __('View All Search History') }}
                        </a>
                    </div>
                </div>
            </div>
            @endif
            @endauth
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize autocomplete for text fields
    $('#title, #author, #subject').autocomplete({
        source: "{{ route('opac.search.suggestions') }}",
        minLength: 2
    });

    // Save search functionality
    $('#saveSearchBtn').click(function() {
        // Implementation for saving searches
        alert('{{ __("Save search functionality will be implemented here") }}');
    });

    // Repeat search functionality
    $('.repeat-search').click(function() {
        const searchData = JSON.parse($(this).data('search'));
        // Populate form with saved search data
        Object.keys(searchData).forEach(key => {
            $(`[name="${key}"]`).val(searchData[key]);
        });
    });
});
</script>
@endpush
