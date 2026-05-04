@extends('opac.layouts.app')

@section('title', __('Search History') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1">{{ __('Search History') }}</h1>
            <p class="text-muted mb-0">
                {{ $totalSearches }} {{ __('total searches') }} &bull;
                {{ $recentSearches }} {{ __('in the last 7 days') }}
            </p>
        </div>
        @if($totalSearches > 0)
            <button class="btn btn-outline-danger btn-sm" id="clearHistoryBtn">
                <i class="fas fa-trash me-2"></i>{{ __('Clear History') }}
            </button>
        @endif
    </div>

    <div class="row g-4">
        {{-- History list --}}
        <div class="col-md-8">
            <div class="opac-card">
                @if($searchHistory->count())
                    <div class="list-group list-group-flush" id="historyList">
                        @foreach($searchHistory as $entry)
                            <div class="list-group-item d-flex align-items-center justify-content-between gap-2" data-id="{{ $entry->id }}">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="fas fa-search text-muted"></i>
                                    <div>
                                        <a href="{{ route('opac.search.results', ['q' => $entry->search_term]) }}"
                                           class="fw-semibold text-decoration-none text-dark">
                                            {{ $entry->search_term }}
                                        </a>
                                        <div class="small text-muted">
                                            {{ $entry->results_count }} {{ __('results') }}
                                            &bull; {{ $entry->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary delete-search"
                                        data-id="{{ $entry->id }}" title="{{ __('Delete') }}">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <div class="p-3 d-flex justify-content-center">
                        {{ $searchHistory->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-history fa-4x text-muted mb-3 d-block opacity-25"></i>
                        <h5>{{ __('No search history') }}</h5>
                        <p class="text-muted mb-4">{{ __('Your search history will appear here after you search the catalog.') }}</p>
                        <a href="{{ route('opac.search') }}" class="btn btn-opac-primary">
                            <i class="fas fa-search me-2"></i>{{ __('Search Now') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Popular terms sidebar --}}
        @if($popularTerms->count())
            <div class="col-md-4">
                <div class="opac-card">
                    <div class="opac-card-header"><i class="fas fa-fire"></i> {{ __('Most Searched') }}</div>
                    <div class="card-body opac-card-body p-0">
                        @foreach($popularTerms as $term)
                            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                                <a href="{{ route('opac.search.results', ['q' => $term->search_term]) }}"
                                   class="text-decoration-none text-dark small fw-semibold">
                                    {{ $term->search_term }}
                                </a>
                                <span class="badge bg-light text-muted border">{{ $term->count }}×</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// Delete single entry
document.querySelectorAll('.delete-search').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;
        fetch(`/opac/search/history/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        }).then(r => r.json()).then(data => {
            if (data.success) {
                this.closest('[data-id]').remove();
            }
        });
    });
});

// Clear all
document.getElementById('clearHistoryBtn')?.addEventListener('click', function () {
    if (!confirm('{{ __("Clear all your search history?") }}')) return;
    fetch('/opac/search/history/clear', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    }).then(r => r.json()).then(data => {
        if (data.success) location.reload();
    });
});
</script>
@endpush
