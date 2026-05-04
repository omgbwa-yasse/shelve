@extends('opac.layouts.app')

@section('title', __('Quick Actions') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="mb-4 d-flex align-items-center gap-3">
        <a href="{{ route('opac.dashboard') }}" class="btn btn-sm btn-opac-outline">
            <i class="fas fa-arrow-left me-1"></i>{{ __('Dashboard') }}
        </a>
        <h1 class="h3 mb-0">{{ __('Quick Actions') }}</h1>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <a href="{{ route('opac.search') }}" class="opac-card text-decoration-none d-block p-4 text-center">
                <i class="fas fa-search fa-3x text-primary mb-3"></i>
                <h5 class="mb-1">{{ __('Search Catalog') }}</h5>
                <p class="text-muted small mb-0">{{ __('Search all records, documents and folders') }}</p>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('opac.document-requests.create') }}" class="opac-card text-decoration-none d-block p-4 text-center">
                <i class="fas fa-file-plus fa-3x text-success mb-3"></i>
                <h5 class="mb-1">{{ __('Request a Document') }}</h5>
                <p class="text-muted small mb-0">{{ __('Submit a new document request') }}</p>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('opac.feedback.create') }}" class="opac-card text-decoration-none d-block p-4 text-center">
                <i class="fas fa-comment-alt fa-3x text-warning mb-3"></i>
                <h5 class="mb-1">{{ __('Send Feedback') }}</h5>
                <p class="text-muted small mb-0">{{ __('Share a suggestion, question or complaint') }}</p>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('opac.records.index') }}" class="opac-card text-decoration-none d-block p-4 text-center">
                <i class="fas fa-archive fa-3x text-info mb-3"></i>
                <h5 class="mb-1">{{ __('Browse Records') }}</h5>
                <p class="text-muted small mb-0">{{ __('Explore the full catalog') }}</p>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('opac.profile') }}" class="opac-card text-decoration-none d-block p-4 text-center">
                <i class="fas fa-user-edit fa-3x text-secondary mb-3"></i>
                <h5 class="mb-1">{{ __('Edit Profile') }}</h5>
                <p class="text-muted small mb-0">{{ __('Update your personal information') }}</p>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('opac.document-requests.index') }}" class="opac-card text-decoration-none d-block p-4 text-center">
                <i class="fas fa-list-alt fa-3x text-danger mb-3"></i>
                <h5 class="mb-1">{{ __('My Requests') }}</h5>
                <p class="text-muted small mb-0">{{ __('Track your document requests') }}</p>
            </a>
        </div>
    </div>
</div>
@endsection
