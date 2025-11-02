@extends('opac.layouts.app')

@section('title', __('Pages') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="mb-4">
                <h1 class="h3 mb-2">{{ __('Information Pages') }}</h1>
                <p class="text-muted">{{ __('Access important information and documentation') }}</p>
            </div>

            <!-- Search Bar -->
            @if(request()->filled('search'))
            <div class="alert alert-info">
                <i class="fas fa-search me-2"></i>
                {{ __('Search results for') }}: <strong>{{ request('search') }}</strong>
                <a href="{{ route('opac.pages.index') }}" class="btn btn-sm btn-outline-primary ms-2">
                    {{ __('Clear search') }}
                </a>
            </div>
            @endif

            <div class="row mb-4">
                <div class="col-md-8">
                    <form method="GET" action="{{ route('opac.pages.index') }}" class="d-flex">
                        <input type="text" class="form-control me-2" name="search"
                               placeholder="{{ __('Search pages...') }}"
                               value="{{ request('search') }}">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-4 text-end">
                    <small class="text-muted">
                        {{ $pages->total() }} {{ __('page(s) found') }}
                    </small>
                </div>
            </div>

            <!-- Pages List -->
            @if($pages->count() > 0)
                <div class="row">
                    @foreach($pages as $page)
                        <div class="col-lg-6 col-xl-4 mb-4">
                            <div class="card h-100 opac-card">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">
                                        <a href="{{ route('opac.pages.show', $page->id) }}" class="text-decoration-none">
                                            {{ $page->title }}
                                        </a>
                                    </h5>

                                    @if($page->meta_description)
                                        <p class="card-text text-muted small mb-3">
                                            {{ Str::limit($page->meta_description, 120) }}
                                        </p>
                                    @endif

                                    @if($page->content)
                                        <p class="card-text flex-grow-1">
                                            {{ Str::limit(strip_tags($page->content), 150) }}
                                        </p>
                                    @endif

                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="{{ route('opac.pages.show', $page->id) }}"
                                               class="btn btn-primary btn-sm">
                                                {{ __('Read More') }}
                                                <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                            <small class="text-muted">
                                                {{ $page->updated_at->format('d/m/Y') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($pages->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $pages->links() }}
                    </div>
                @endif
            @else
                <!-- No Pages Found -->
                <div class="opac-card text-center py-5">
                    <div class="card-body">
                        @if(request()->filled('search'))
                            <i class="fas fa-search fa-4x text-muted mb-4"></i>
                            <h4>{{ __('No pages found') }}</h4>
                            <p class="text-muted mb-4">
                                {{ __('No pages match your search criteria. Try different keywords.') }}
                            </p>
                            <a href="{{ route('opac.pages.index') }}" class="btn btn-primary">
                                {{ __('View All Pages') }}
                            </a>
                        @else
                            <i class="fas fa-file-alt fa-4x text-muted mb-4"></i>
                            <h4>{{ __('No pages available') }}</h4>
                            <p class="text-muted mb-4">
                                {{ __('There are currently no information pages published.') }}
                            </p>
                            <a href="{{ route('opac.search') }}" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>{{ __('Search Documents') }}
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
