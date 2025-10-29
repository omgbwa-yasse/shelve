@extends('opac.layouts.app')

@section('title', 'OPAC - ' . config('app.name'))

@section('content')
<!-- Hero Section -->
<section class="opac-search-hero">
    <div class="container">
        <h1 class="display-4 mb-4">{{ __('Welcome to our Digital Archive') }}</h1>
        <p class="lead mb-5">{{ __('Search and discover our collections online') }}</p>

        <!-- Search Form -->
        <form action="{{ route('opac.search') }}" method="GET" class="opac-search-box">
            <div class="input-group">
                <input type="text" name="q" class="form-control opac-search-input"
                       placeholder="{{ __('Search documents, authors, keywords...') }}"
                       value="{{ request('q') }}">
                <button type="submit" class="btn opac-search-btn">
                    <i class="fas fa-search me-2"></i>{{ __('Search') }}
                </button>
            </div>
        </form>

        <div class="mt-4">
            <a href="{{ route('opac.search') }}" class="text-white-50">
                <i class="fas fa-cogs me-1"></i>{{ __('Advanced Search') }}
            </a>
        </div>
    </div>
</section>

<!-- Statistics Section -->
@if($config->show_statistics && !empty($stats))
<section class="container my-5">
    <div class="opac-stats">
        <div class="row">
            <div class="col-md-4 opac-stat-item">
                <div class="opac-stat-number">{{ number_format($stats['total_records']) }}</div>
                <div class="opac-stat-label">{{ __('Total Documents') }}</div>
            </div>
            <div class="col-md-4 opac-stat-item">
                <div class="opac-stat-number">{{ number_format($stats['recent_records']) }}</div>
                <div class="opac-stat-label">{{ __('Recent Documents') }}</div>
            </div>
            <div class="col-md-4 opac-stat-item">
                <div class="opac-stat-number">{{ number_format($stats['total_activities']) }}</div>
                <div class="opac-stat-label">{{ __('Collections') }}</div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- Recent Documents -->
@if($config->show_recent_records && $recentRecords->isNotEmpty())
<section class="container my-5">
    <h2 class="mb-4">{{ __('Recent Additions') }}</h2>
    <div class="row">
        @foreach($recentRecords as $record)
        <div class="col-md-4 mb-4">
            <div class="opac-card">
                <div class="card-body">
                    <h5 class="card-title">
                        <a href="{{ route('opac.show', $record->id) }}" class="text-decoration-none">
                            {{ Str::limit($record->name, 60) }}
                        </a>
                    </h5>
                    <p class="text-muted small mb-2">
                        <i class="fas fa-folder me-1"></i>{{ $record->activity->name ?? __('No Category') }}
                    </p>
                    @if($record->date_exact)
                    <p class="text-muted small mb-2">
                        <i class="fas fa-calendar me-1"></i>{{ $record->date_exact->format('Y-m-d') }}
                    </p>
                    @endif
                    @if($record->authors->isNotEmpty())
                    <p class="text-muted small mb-3">
                        <i class="fas fa-user me-1"></i>{{ $record->authors->pluck('name')->implode(', ') }}
                    </p>
                    @endif
                    <p class="card-text">{{ Str::limit($record->content ?: $record->description, 100) }}</p>
                    <a href="{{ route('opac.show', $record->id) }}" class="btn btn-sm btn-outline-primary">
                        {{ __('View Details') }}
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="text-center mt-4">
        <a href="{{ route('opac.search') }}" class="btn btn-primary btn-lg">
            {{ __('Browse All Documents') }}
        </a>
    </div>
</section>
@endif

<!-- Quick Access Section -->
<section class="container my-5">
    <h2 class="mb-4">{{ __('Quick Access') }}</h2>
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="opac-card text-center">
                <div class="card-body">
                    <i class="fas fa-search fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">{{ __('Advanced Search') }}</h5>
                    <p class="card-text">{{ __('Use filters to refine your search results') }}</p>
                    <a href="{{ route('opac.search') }}" class="btn btn-primary">
                        {{ __('Start Searching') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="opac-card text-center">
                <div class="card-body">
                    <i class="fas fa-list fa-3x text-success mb-3"></i>
                    <h5 class="card-title">{{ __('Browse by Category') }}</h5>
                    <p class="card-text">{{ __('Explore documents organized by collections') }}</p>
                    <a href="{{ route('opac.browse') }}" class="btn btn-success">
                        {{ __('Browse Collections') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="opac-card text-center">
                <div class="card-body">
                    <i class="fas fa-question-circle fa-3x text-info mb-3"></i>
                    <h5 class="card-title">{{ __('Need Help?') }}</h5>
                    <p class="card-text">{{ __('Learn how to use the search system effectively') }}</p>
                    <a href="{{ route('opac.help') }}" class="btn btn-info">
                        {{ __('Get Help') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus sur le champ de recherche
    const searchInput = document.querySelector('.opac-search-input');
    if (searchInput) {
        searchInput.focus();
    }
});
</script>
@endpush
