@extends('opac.layouts.app')

@section('title', __('Browse Records') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="mb-4">
                <h1 class="h3 mb-2">{{ __('Browse Records') }}</h1>
                <p class="text-muted">{{ __('Explore our complete collection of documents and resources') }}</p>
            </div>

            <!-- Search Bar -->
            <div class="opac-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('opac.records.search') }}" class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label for="q" class="form-label">{{ __('Quick Search') }}</label>
                            <input type="text"
                                   class="form-control"
                                   id="q"
                                   name="q"
                                   value="{{ request('q') }}"
                                   placeholder="{{ __('Search records...') }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn opac-search-btn w-100">
                                <i class="fas fa-search me-2"></i>{{ __('Search') }}
                            </button>
                        </div>
                    </form>
                    <!-- Search Options -->
                    <div class="mt-3 text-center">
                        <small class="opacity-75">
                            <a href="#advancedSearch" class="text-decoration-none fw-semibold" data-bs-toggle="collapse">
                                <i class="fas fa-sliders-h me-1"></i>{{ __('Advanced Search Options') }}
                            </a>
                        </small>
                    </div>
                </div>
            </div>

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
                                <!-- Resource Type -->
                                <div class="col-12">
                                    <label for="type" class="form-label fw-semibold">{{ __('Resource Type') }}</label>
                                    <select class="form-select" id="type" name="type" onchange="updateAdvancedSearchFields()">
                                        <option value="">{{ __('All types') }}</option>
                                        <option value="book" {{ request('type') == 'book' ? 'selected' : '' }}>{{ __('Book') }}</option>
                                        <option value="artifact" {{ request('type') == 'artifact' ? 'selected' : '' }}>{{ __('Artifact') }}</option>
                                        <option value="archive" {{ request('type') == 'archive' ? 'selected' : '' }}>{{ __('Digital Folder') }}</option>
                                        <option value="document" {{ request('type') == 'document' ? 'selected' : '' }}>{{ __('Digital Document') }}</option>
                                    </select>
                                </div>

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
                                    <label for="author" class="form-label fw-semibold" id="label-author">{{ __('Author / Creator') }}</label>
                                    <input type="text"
                                           class="form-control"
                                           id="author"
                                           name="author"
                                           value="{{ request('author') }}"
                                           placeholder="{{ __('Enter author name...') }}">
                                </div>

                                <!-- Subject / Keywords -->
                                <div class="col-md-6">
                                    <label for="subject" class="form-label fw-semibold" id="label-subject">{{ __('Subject / Keywords') }}</label>
                                    <input type="text"
                                           class="form-control"
                                           id="subject"
                                           name="subject"
                                           value="{{ request('subject') }}"
                                           placeholder="{{ __('Enter subject or keywords...') }}">
                                </div>

                                <!-- ISBN / Identifier -->
                                <div class="col-md-6" id="field-isbn">
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
                                <div class="col-md-6" id="field-language">
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

            <!-- Records placeholder -->
            <div class="opac-card text-center py-5">
                <div class="card-body">
                    <i class="fas fa-books fa-4x text-muted mb-4"></i>
                    <h4>{{ __('Records Browser') }}</h4>
                    <p class="text-muted mb-4">
                        {{ __('This feature is being prepared and will be available soon.') }}
                    </p>
                    <p class="text-muted mb-4">
                        {{ __('In the meantime, you can use the search functionality to find specific documents.') }}
                    </p>
                    <a href="{{ route('opac.search') }}" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>{{ __('Go to Search') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function clearAdvancedForm() {
    const form = document.getElementById('advancedSearchForm');
    form.reset();
    // Clear any selected values
    const selects = form.querySelectorAll('select');
    selects.forEach(select => {
        select.selectedIndex = 0;
    });
    updateAdvancedSearchFields(); // Reset visibility
}

function updateAdvancedSearchFields() {
    const type = document.getElementById('type').value;
    const isbnField = document.getElementById('field-isbn');
    const languageField = document.getElementById('field-language');
    const authorLabel = document.getElementById('label-author');
    const subjectLabel = document.getElementById('label-subject');

    // Default visibility
    isbnField.style.display = 'block';
    languageField.style.display = 'block';
    authorLabel.innerText = "{{ __('Author / Creator') }}";
    subjectLabel.innerText = "{{ __('Subject / Keywords') }}";

    if (type === 'book') {
        // Books have everything
        authorLabel.innerText = "{{ __('Author') }}";
        subjectLabel.innerText = "{{ __('Subject') }}";
    } else if (type === 'artifact') {
        isbnField.style.display = 'none';
        languageField.style.display = 'none';
        authorLabel.innerText = "{{ __('Creator') }}";
        subjectLabel.innerText = "{{ __('Category / Material') }}";
    } else if (type === 'archive' || type === 'document') {
        isbnField.style.display = 'none';
        languageField.style.display = 'none';
        authorLabel.innerText = "{{ __('Creator') }}";
        subjectLabel.innerText = "{{ __('Keywords') }}";
    }
}

// Run on load
document.addEventListener('DOMContentLoaded', function() {
    updateAdvancedSearchFields();
});
</script>
@endpush
