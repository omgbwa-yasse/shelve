@extends('opac.layouts.app')

@section('title', $article->title . ' - OPAC')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-10 col-xl-8 mx-auto">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-5">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('opac.search') }}">{{ __('Home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('opac.news.index') }}">{{ __('News') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $article->title }}</li>
                </ol>
            </nav>

            <!-- Article Content -->
            <article class="opac-card shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <!-- Article Header -->
                    <header class="mb-5">
                        <div class="mb-4">
                            <span class="badge bg-primary fs-6 px-3 py-2">
                                {{ $article->published_at ? $article->published_at->format('d F Y') : $article->created_at->format('d F Y') }}
                            </span>
                        </div>

                        <h1 class="display-6 mb-4 fw-bold lh-base">{{ $article->title }}</h1>

                        @if($article->summary)
                            <p class="lead text-muted mb-4 fs-5 lh-base">{{ $article->summary }}</p>
                        @endif

                        <div class="d-flex flex-wrap align-items-center text-muted">
                            @if($article->author)
                                <div class="me-4 mb-2">
                                    <i class="fas fa-user me-2"></i>
                                    <span class="fw-medium">{{ $article->author->name }}</span>
                                </div>
                            @endif
                            <div class="mb-2">
                                <i class="fas fa-calendar-alt me-2"></i>
                                {{ __('Published') }}: {{ $article->published_at ? $article->published_at->format('d/m/Y') : $article->created_at->format('d/m/Y') }}
                            </div>
                        </div>
                    </header>

                    <!-- Article Content -->
                    <div class="opac-article-content mb-5">
                        {!! $article->content !!}
                    </div>

                    <!-- Article Footer -->
                    <footer class="mt-5 pt-4 border-top">
                        <div class="row align-items-center g-3">
                            <div class="col-md-6">
                                <small class="text-muted d-flex align-items-center">
                                    <i class="fas fa-info-circle me-2"></i>
                                    {{ __('Last updated') }}: {{ $article->updated_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <a href="{{ route('opac.news.index') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    {{ __('Back to News') }}
                                </a>
                            </div>
                        </div>
                    </footer>
                </div>
            </article>

            <!-- Related Articles -->
            <div class="mt-5 pt-4">
                <h5 class="mb-4 fw-semibold">{{ __('Other News') }}</h5>
                <div class="row g-4">
                    @php
                        $relatedNews = \App\Models\PublicNews::where('is_published', true)
                                                           ->where('id', '!=', $article->id)
                                                           ->orderBy('published_at', 'desc')
                                                           ->limit(3)
                                                           ->get();
                    @endphp

                    @foreach($relatedNews as $relatedArticle)
                        <div class="col-md-4">
                            <div class="card card-sm h-100">
                                <div class="card-body p-4">
                                    <div class="mb-3">
                                        <small class="badge bg-light text-dark px-2 py-1">
                                            {{ $relatedArticle->published_at ? $relatedArticle->published_at->format('d/m/Y') : $relatedArticle->created_at->format('d/m/Y') }}
                                        </small>
                                    </div>
                                    <h6 class="card-title mb-3 lh-base">
                                        <a href="{{ route('opac.news.show', $relatedArticle->id) }}"
                                           class="text-decoration-none text-dark fw-medium">
                                            {{ $relatedArticle->title }}
                                        </a>
                                    </h6>
                                    @if($relatedArticle->summary)
                                        <p class="card-text text-muted mb-0 lh-base">
                                            {{ Str::limit($relatedArticle->summary, 100) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if($relatedNews->count() === 0)
                        <div class="col-12">
                            <p class="text-muted text-center">
                                {{ __('No other news available at the moment.') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.opac-article-content {
    line-height: 1.8;
    font-size: 1.1rem;
}

.opac-article-content h1,
.opac-article-content h2,
.opac-article-content h3,
.opac-article-content h4,
.opac-article-content h5,
.opac-article-content h6 {
    margin-top: 2.5rem;
    margin-bottom: 1.25rem;
    color: #2c3e50;
    font-weight: 600;
}

.opac-article-content h1:first-child,
.opac-article-content h2:first-child,
.opac-article-content h3:first-child {
    margin-top: 0;
}

.opac-article-content p {
    margin-bottom: 1.5rem;
    text-align: justify;
}

.opac-article-content img {
    max-width: 100%;
    height: auto;
    margin: 2rem 0;
    border-radius: 0.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.1);
}

.opac-article-content blockquote {
    border-left: 4px solid #007bff;
    padding: 1.5rem;
    margin: 2rem 0;
    font-style: italic;
    background-color: #f8f9fa;
    border-radius: 0.5rem;
    font-size: 1.05rem;
}

.opac-article-content ul,
.opac-article-content ol {
    margin-bottom: 1.5rem;
    padding-left: 2rem;
}

.opac-article-content li {
    margin-bottom: 0.5rem;
}

.opac-article-content strong {
    font-weight: 600;
    color: #2c3e50;
}

.opac-card {
    border: none;
    border-radius: 0.75rem;
}

.card-sm {
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    border-radius: 0.5rem;
}

.card-sm:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    transform: translateY(-2px);
    border-color: #007bff;
}

.breadcrumb {
    background: none;
    padding: 0;
    margin: 0;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: #6c757d;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .opac-article-content {
        font-size: 1rem;
    }

    .display-6 {
        font-size: 1.75rem !important;
    }

    .lead {
        font-size: 1.1rem !important;
    }
}
</style>
@endpush
