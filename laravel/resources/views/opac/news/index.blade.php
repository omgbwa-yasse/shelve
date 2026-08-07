@extends('opac.layouts.app')

@section('title', __('News') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="mb-4">
                <h1 class="h3 mb-2">{{ __('Library News & Updates') }}</h1>
                <p class="text-muted">{{ __('Stay informed about the latest news, events, and announcements') }}</p>
            </div>

            <!-- Search Bar -->
            @if(request()->filled('search'))
            <div class="alert alert-info">
                <i class="fas fa-search me-2"></i>
                {{ __('Search results for') }}: <strong>{{ request('search') }}</strong>
                <a href="{{ route('opac.news.index') }}" class="btn btn-sm btn-outline-primary ms-2">
                    {{ __('Clear search') }}
                </a>
            </div>
            @endif

            <div class="row mb-4">
                <div class="col-md-8">
                    <form method="GET" action="{{ route('opac.news.index') }}" class="d-flex">
                        <input type="text" class="form-control me-2" name="search"
                               placeholder="{{ __('Search news...') }}"
                               value="{{ request('search') }}">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-4 text-end">
                    <small class="text-muted">
                        {{ $news->total() }} {{ __('article(s) found') }}
                    </small>
                </div>
            </div>

            <!-- News List -->
            @if($news->count() > 0)
                <div class="row">
                    @foreach($news as $article)
                        <div class="col-lg-6 col-xl-4 mb-4">
                            <div class="card h-100 opac-card">
                                <div class="card-body d-flex flex-column">
                                    <div class="mb-3">
                                        <span class="badge bg-primary">
                                            {{ $article->published_at ? $article->published_at->format('d/m/Y') : $article->created_at->format('d/m/Y') }}
                                        </span>
                                    </div>

                                    <h5 class="card-title">
                                        <a href="{{ route('opac.news.show', $article->id) }}" class="text-decoration-none">
                                            {{ $article->title }}
                                        </a>
                                    </h5>

                                    @if($article->summary)
                                        <p class="card-text text-muted small mb-3">
                                            {{ Str::limit($article->summary, 120) }}
                                        </p>
                                    @endif

                                    @if($article->content)
                                        <p class="card-text flex-grow-1">
                                            {{ Str::limit(strip_tags($article->content), 150) }}
                                        </p>
                                    @endif

                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="{{ route('opac.news.show', $article->id) }}"
                                               class="btn btn-primary btn-sm">
                                                {{ __('Read More') }}
                                                <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                            @if($article->author)
                                                <small class="text-muted">
                                                    <i class="fas fa-user me-1"></i>{{ $article->author->name }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($news->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $news->links() }}
                    </div>
                @endif
            @else
                <!-- No News Found -->
                <div class="opac-card text-center py-5">
                    <div class="card-body">
                        @if(request()->filled('search'))
                            <i class="fas fa-search fa-4x text-muted mb-4"></i>
                            <h4>{{ __('No news found') }}</h4>
                            <p class="text-muted mb-4">
                                {{ __('No news articles match your search criteria. Try different keywords.') }}
                            </p>
                            <a href="{{ route('opac.news.index') }}" class="btn btn-primary">
                                {{ __('View All News') }}
                            </a>
                        @else
                            <i class="fas fa-newspaper fa-4x text-muted mb-4"></i>
                            <h4>{{ __('No news available') }}</h4>
                            <p class="text-muted mb-4">
                                {{ __('There are currently no news articles published.') }}
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
