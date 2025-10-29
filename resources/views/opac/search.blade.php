@extends('opac.layouts.app')

@section('title', __('Search') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-md-3">
            <!-- Sidebar avec filtres -->
            <div class="opac-card">
                <div class="opac-card-header">
                    <i class="fas fa-filter me-2"></i>{{ __('Search Filters') }}
                </div>
                <div class="card-body">
                    <form id="searchForm" method="GET" action="{{ route('opac.search') }}">
                        <!-- Recherche textuelle -->
                        <div class="mb-3">
                            <label for="q" class="form-label">{{ __('Search Terms') }}</label>
                            <input type="text" class="form-control" id="q" name="q"
                                   value="{{ request('q') }}"
                                   placeholder="{{ __('Keywords, title, content...') }}">
                        </div>

                        <!-- Filtre par activité -->
                        <div class="mb-3">
                            <label for="activity_id" class="form-label">{{ __('Category') }}</label>
                            <select class="form-select" id="activity_id" name="activity_id">
                                <option value="">{{ __('All Categories') }}</option>
                                @foreach($activities as $activity)
                                    <option value="{{ $activity->id }}"
                                            {{ request('activity_id') == $activity->id ? 'selected' : '' }}>
                                        {{ $activity->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filtre par dates -->
                        <div class="mb-3">
                            <label class="form-label">{{ __('Date Range') }}</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="date" class="form-control form-control-sm"
                                           name="date_from" value="{{ request('date_from') }}"
                                           placeholder="{{ __('From') }}">
                                </div>
                                <div class="col-6">
                                    <input type="date" class="form-control form-control-sm"
                                           name="date_to" value="{{ request('date_to') }}"
                                           placeholder="{{ __('To') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Filtre par auteur -->
                        <div class="mb-3">
                            <label for="author" class="form-label">{{ __('Author') }}</label>
                            <input type="text" class="form-control" id="author" name="author"
                                   value="{{ request('author') }}"
                                   placeholder="{{ __('Author name') }}">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>{{ __('Search') }}
                            </button>
                            <a href="{{ route('opac.search') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-2"></i>{{ __('Clear Filters') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Résultats -->
            @if(isset($results) && request()->hasAny(['q', 'activity_id', 'date_from', 'date_to', 'author']))
                <div class="mb-4">
                    <h2>{{ __('Search Results') }}</h2>
                    <p class="text-muted">
                        {{ $total }} {{ __('document(s) found') }}
                        @if(request('q'))
                            {{ __('for') }} "<strong>{{ request('q') }}</strong>"
                        @endif
                    </p>
                </div>

                @if($results->count() > 0)
                    <!-- Liste des résultats -->
                    @foreach($results as $record)
                        <div class="opac-card mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-9">
                                        <h5 class="card-title">
                                            <a href="{{ route('opac.show', $record->id) }}" class="text-decoration-none">
                                                {{ $record->name }}
                                            </a>
                                        </h5>

                                        <div class="mb-2">
                                            <span class="opac-badge">{{ $record->activity->name ?? __('No Category') }}</span>
                                            @if($record->date_exact)
                                                <span class="opac-badge ms-2">
                                                    <i class="fas fa-calendar me-1"></i>{{ $record->date_exact->format('Y-m-d') }}
                                                </span>
                                            @endif
                                        </div>

                                        @if($record->authors->isNotEmpty())
                                            <p class="text-muted small mb-2">
                                                <i class="fas fa-user me-1"></i>
                                                {{ $record->authors->pluck('name')->implode(', ') }}
                                            </p>
                                        @endif

                                        <p class="card-text">
                                            {{ Str::limit($record->content ?: $record->description, 200) }}
                                        </p>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <a href="{{ route('opac.show', $record->id) }}" class="btn btn-outline-primary">
                                            {{ __('View Details') }}
                                        </a>
                                        @if($record->attachments->count() > 0)
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-paperclip me-1"></i>
                                                    {{ $record->attachments->count() }} {{ __('attachment(s)') }}
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $results->appends(request()->query())->links() }}
                    </div>
                @else
                    <!-- Aucun résultat -->
                    <div class="opac-card text-center">
                        <div class="card-body py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h4>{{ __('No documents found') }}</h4>
                            <p class="text-muted">{{ __('Try adjusting your search criteria or browse by category') }}</p>
                            <a href="{{ route('opac.browse') }}" class="btn btn-primary">
                                {{ __('Browse Collections') }}
                            </a>
                        </div>
                    </div>
                @endif
            @else
                <!-- Formulaire de recherche principal -->
                <div class="opac-card">
                    <div class="opac-card-header">
                        <i class="fas fa-search me-2"></i>{{ __('Advanced Search') }}
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">{{ __('Use the form below or the filters on the left to search our collections') }}</p>

                        <form method="GET" action="{{ route('opac.search') }}">
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="text" class="form-control form-control-lg"
                                           name="q" placeholder="{{ __('Search documents, authors, keywords...') }}"
                                           value="{{ request('q') }}">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-search me-2"></i>{{ __('Search') }}
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="mt-4">
                            <h6>{{ __('Search Tips:') }}</h6>
                            <ul class="small text-muted">
                                <li>{{ __('Use quotes for exact phrases: "annual report"') }}</li>
                                <li>{{ __('Use wildcards: report* finds report, reports, reporting') }}</li>
                                <li>{{ __('Combine filters for more precise results') }}</li>
                            </ul>
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
    // Auto-submit form when filters change
    const form = document.getElementById('searchForm');
    const selects = form.querySelectorAll('select');
    const dateInputs = form.querySelectorAll('input[type="date"]');

    selects.forEach(select => {
        select.addEventListener('change', function() {
            form.submit();
        });
    });

    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            form.submit();
        });
    });
});
</script>
@endpush
