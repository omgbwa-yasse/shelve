@extends('opac.layouts.app')

@section('title', $page->title)

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('opac.index') }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('opac.pages.index') }}">{{ __('Pages') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $page->title }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <div class="opac-record-detail">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h1 class="opac-record-title">{{ $page->title }}</h1>
                        @if($page->status)
                            <span class="opac-badge">{{ ucfirst($page->status) }}</span>
                        @endif
                    </div>
                </div>

                <!-- Meta Information -->
                @if($page->meta_description)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ $page->meta_description }}
                    </div>
                @endif

                <!-- Page Content -->
                <div class="page-content">
                    {!! $page->content !!}
                </div>

                <!-- Meta Information -->
                <div class="row mt-4 pt-4 border-top">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            {{ __('Last updated') }}: {{ $page->updated_at->format('F j, Y') }}
                        </small>
                    </div>
                    @if($page->meta_keywords)
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <i class="fas fa-tags me-1"></i>
                                {{ $page->meta_keywords }}
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Navigation Sidebar -->
            <div class="opac-card">
                <div class="opac-card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Page Navigation') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('opac.pages.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>
                            {{ __('Back to Pages') }}
                        </a>

                        <a href="{{ route('opac.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i>
                            {{ __('Home') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="opac-card mt-4">
                <div class="opac-card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-external-link-alt me-2"></i>
                        {{ __('Quick Links') }}
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li><a href="{{ route('opac.search') }}" class="text-decoration-none">{{ __('Advanced Search') }}</a></li>
                        <li><a href="{{ route('opac.browse') }}" class="text-decoration-none">{{ __('Browse Collections') }}</a></li>
                        <li><a href="{{ route('opac.events.index') }}" class="text-decoration-none">{{ __('Events') }}</a></li>
                        <li><a href="{{ route('opac.news.index') }}" class="text-decoration-none">{{ __('News') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.page-content {
    line-height: 1.8;
}

.page-content h1, .page-content h2, .page-content h3,
.page-content h4, .page-content h5, .page-content h6 {
    color: var(--opac-primary);
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.page-content p {
    margin-bottom: 1rem;
}

.page-content ul, .page-content ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.page-content img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 1rem 0;
}

.page-content blockquote {
    border-left: 4px solid var(--opac-secondary);
    padding-left: 1rem;
    margin: 1rem 0;
    font-style: italic;
    color: #666;
}
</style>
@endpush
@endsection
