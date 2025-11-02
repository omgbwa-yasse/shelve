@extends('opac.layouts.app')

@section('title', $page->title . ' - OPAC')

@section('meta')
@if($page->meta_description)
    <meta name="description" content="{{ $page->meta_description }}">
@endif
@endsection

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('opac.search') }}">{{ __('Home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('opac.pages.index') }}">{{ __('Pages') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $page->title }}</li>
                </ol>
            </nav>

            <!-- Page Content -->
            <article class="opac-card">
                <div class="card-body">
                    <!-- Page Header -->
                    <header class="mb-4">
                        <h1 class="h2 mb-3">{{ $page->title }}</h1>

                        @if($page->meta_description)
                            <p class="lead text-muted">{{ $page->meta_description }}</p>
                        @endif

                        <div class="d-flex align-items-center text-muted small">
                            <i class="fas fa-calendar-alt me-2"></i>
                            {{ __('Last updated') }}: {{ $page->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </header>

                    <!-- Page Content -->
                    <div class="opac-page-content">
                        {!! $page->content !!}
                    </div>

                    <!-- Page Footer -->
                    <footer class="mt-5 pt-4 border-top">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-2"></i>
                                    {{ __('Created') }}: {{ $page->created_at->format('d/m/Y') }}
                                </small>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="{{ route('opac.pages.index') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    {{ __('Back to Pages') }}
                                </a>
                            </div>
                        </div>
                    </footer>
                </div>
            </article>

            <!-- Related Pages or Navigation -->
            <div class="mt-4">
                <h5 class="mb-3">{{ __('Other Pages') }}</h5>
                <div class="row">
                    @php
                        $relatedPages = \App\Models\PublicPage::where('is_published', true)
                                                             ->where('id', '!=', $page->id)
                                                             ->limit(3)
                                                             ->get();
                    @endphp

                    @foreach($relatedPages as $relatedPage)
                        <div class="col-md-4 mb-3">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <a href="{{ route('opac.pages.show', $relatedPage->id) }}"
                                           class="text-decoration-none">
                                            {{ $relatedPage->title }}
                                        </a>
                                    </h6>
                                    @if($relatedPage->meta_description)
                                        <p class="card-text small text-muted">
                                            {{ Str::limit($relatedPage->meta_description, 80) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if($relatedPages->count() === 0)
                        <div class="col-12">
                            <p class="text-muted text-center">
                                {{ __('No other pages available at the moment.') }}
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
.opac-page-content {
    line-height: 1.7;
}

.opac-page-content h1,
.opac-page-content h2,
.opac-page-content h3,
.opac-page-content h4,
.opac-page-content h5,
.opac-page-content h6 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: #333;
}

.opac-page-content p {
    margin-bottom: 1rem;
}

.opac-page-content img {
    max-width: 100%;
    height: auto;
    margin: 1rem 0;
}

.opac-page-content blockquote {
    border-left: 4px solid #007bff;
    padding-left: 1rem;
    margin: 1.5rem 0;
    font-style: italic;
    background-color: #f8f9fa;
    padding: 1rem;
}

.opac-page-content ul,
.opac-page-content ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.opac-page-content table {
    width: 100%;
    margin: 1rem 0;
    border-collapse: collapse;
}

.opac-page-content table th,
.opac-page-content table td {
    border: 1px solid #dee2e6;
    padding: 0.5rem;
}

.opac-page-content table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.card-sm {
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.card-sm:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}
</style>
@endpush
