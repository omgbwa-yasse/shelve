@extends('opac.layouts.app')

@section('title', __('Pages'))

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="opac-search-hero mb-4">
        <div class="container">
            <h1 class="display-5 fw-bold mb-3">
                <i class="fas fa-file-alt me-3"></i>
                {{ __('Information Pages') }}
            </h1>
            <p class="lead">{{ __('Browse our information pages and resources.') }}</p>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <form method="GET" action="{{ route('opac.pages.index') }}" class="d-flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="form-control" placeholder="{{ __('Search pages...') }}">

                <select name="category" class="form-select" style="min-width: 150px;">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                            {{ ucfirst($category) }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-search"></i>
                </button>

                @if(request()->hasAny(['search', 'category']))
                    <a href="{{ route('opac.pages.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>
        </div>

        <div class="col-lg-4 text-end">
            <span class="text-muted">{{ $pages->total() }} {{ __('pages found') }}</span>
        </div>
    </div>

    <!-- Pages Grid -->
    @if($pages->count() > 0)
        <div class="row">
            @foreach($pages as $page)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="opac-card h-100">
                        <div class="card-body d-flex flex-column">
                            @if($page->status)
                                <span class="opac-badge mb-2 align-self-start">{{ ucfirst($page->status) }}</span>
                            @endif

                            <h5 class="card-title">
                                <a href="{{ route('opac.pages.show', $page->id) }}"
                                   class="text-decoration-none text-dark">
                                    {{ $page->title }}
                                </a>
                            </h5>

                            @if($page->meta_description)
                                <p class="card-text text-muted">{{ Str::limit($page->meta_description, 120) }}</p>
                            @else
                                <p class="card-text text-muted">{{ Str::limit(strip_tags($page->content), 120) }}</p>
                            @endif

                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $page->updated_at->format('M d, Y') }}
                                    </small>

                                    <a href="{{ route('opac.pages.show', $page->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        {{ __('Read More') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $pages->links() }}
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-file-alt text-muted mb-3" style="font-size: 4rem;"></i>
            <h4 class="text-muted">{{ __('No Pages Found') }}</h4>
            <p class="text-muted">
                @if(request()->hasAny(['search', 'category']))
                    {{ __('No pages match your current filters.') }}
                    <a href="{{ route('opac.pages.index') }}" class="text-decoration-none">
                        {{ __('Clear filters') }}
                    </a>
                @else
                    {{ __('There are no published pages at this time.') }}
                @endif
            </p>
        </div>
    @endif
</div>
@endsection
