@extends('opac.layouts.app')

@section('title', __('News & Updates'))

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="opac-search-hero mb-4">
        <div class="container">
            <h1 class="display-5 fw-bold mb-3">
                <i class="fas fa-newspaper me-3"></i>
                {{ __('News & Updates') }}
            </h1>
            <p class="lead">{{ __('Stay informed with the latest news and announcements from our archive.') }}</p>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <form method="GET" action="{{ route('opac.news.index') }}" class="d-flex gap-2">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="form-control"
                       placeholder="{{ __('Search news...') }}">

                <select name="category" class="form-select" style="min-width: 150px;">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}"
                                {{ request('category') === $category ? 'selected' : '' }}>
                            {{ ucfirst($category) }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-search"></i>
                </button>

                @if(request()->hasAny(['search', 'category']))
                    <a href="{{ route('opac.news.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>
        </div>

        <div class="col-lg-4 text-end">
            <span class="text-muted">
                {{ $news->count() }} {{ __('articles found') }}
            </span>
        </div>
    </div>

    <!-- News Grid -->
    @if($news && $news->count() > 0)
        <div class="row">
            @foreach($news as $article)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="opac-card h-100">
                        @if($article->featured_image)
                            <img src="{{ asset('storage/' . $article->featured_image) }}"
                                 class="card-img-top"
                                 alt="{{ $article->title }}"
                                 style="height: 200px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                 style="height: 200px;">
                                <i class="fas fa-newspaper text-muted" style="font-size: 3rem;"></i>
                            </div>
                        @endif

                        <div class="card-body d-flex flex-column">
                            @if($article->category)
                                <span class="opac-badge mb-2 align-self-start">{{ ucfirst($article->category) }}</span>
                            @endif

                            <h5 class="card-title">
                                <a href="{{ route('opac.news.show', $article->id) }}"
                                   class="text-decoration-none text-dark">
                                    {{ $article->title }}
                                </a>
                            </h5>

                            @if($article->excerpt)
                                <p class="card-text text-muted">{{ Str::limit($article->excerpt, 120) }}</p>
                            @else
                                <p class="card-text text-muted">{{ Str::limit(strip_tags($article->content), 120) }}</p>
                            @endif

                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ $article->published_at->format('M d, Y') }}
                                    </small>

                                    <a href="{{ route('opac.news.show', $article->id) }}"
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
        @if(method_exists($news, 'links'))
            <div class="d-flex justify-content-center">
                {{ $news->links() }}
            </div>
        @endif
    @else
        <div class="text-center py-5">
            <i class="fas fa-newspaper text-muted mb-3" style="font-size: 4rem;"></i>
            <h4 class="text-muted">{{ __('No News Found') }}</h4>
            <p class="text-muted">
                {{ __('The news system is not yet configured. Please contact the administrator.') }}
            </p>
        </div>
    @endif
</div>
@endsection
