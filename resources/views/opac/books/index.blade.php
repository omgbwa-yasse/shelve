@extends('opac.layouts.app')

@section('title', __('Browse Books') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <div class="mb-4">
                <h1 class="h3 mb-2">{{ __('Library Books') }}</h1>
                <p class="text-muted">{{ __('Browse our library collection') }}</p>
            </div>

            <div class="opac-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('opac.books.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label for="q" class="form-label">{{ __('Search Books') }}</label>
                            <input type="text" class="form-control" id="q" name="q" value="{{ request('q') }}" placeholder="{{ __('Search by title, author, ISBN...') }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn opac-search-btn w-100">
                                <i class="fas fa-search me-2"></i>{{ __('Search') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                @forelse($books as $book)
                    <div class="col-md-3 mb-4">
                        <div class="card h-100 opac-card">
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 250px;">
                                <i class="fas fa-book fa-4x text-muted"></i>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title text-truncate" title="{{ $book->title }}">{{ $book->title }}</h5>
                                <p class="card-text text-muted small mb-2">
                                    {{ $book->authors_string }}
                                </p>
                                <p class="card-text small">
                                    <span class="text-muted">{{ $book->publication_year }}</span>
                                    @if($book->publisher)
                                        <span class="text-muted"> • {{ $book->publisher->name }}</span>
                                    @endif
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    @if($book->is_available)
                                        <span class="badge bg-success">{{ __('Available') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('Unavailable') }}</span>
                                    @endif
                                    <a href="{{ route('opac.books.show', $book->id) }}" class="btn btn-sm btn-outline-primary">{{ __('View') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <p class="text-muted">{{ __('No books found.') }}</p>
                    </div>
                @endforelse
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $books->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
