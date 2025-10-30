@extends('opac.layouts.app')

@section('title', __('Browse Records') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="mb-4">
                <h1 class="h3 mb-2">{{ __('Browse Records') }}</h1>
                <p class="text-muted">{{ __('Explore our complete collection of documents and resources') }}</p>
            </div>

            <!-- Search Bar -->
            <div class="opac-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('opac.records.search') }}" class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label for="q" class="form-label">{{ __('Quick Search') }}</label>
                            <input type="text"
                                   class="form-control"
                                   id="q"
                                   name="q"
                                   value="{{ request('q') }}"
                                   placeholder="{{ __('Search records...') }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn opac-search-btn w-100">
                                <i class="fas fa-search me-2"></i>{{ __('Search') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Records placeholder -->
            <div class="opac-card text-center py-5">
                <div class="card-body">
                    <i class="fas fa-books fa-4x text-muted mb-4"></i>
                    <h4>{{ __('Records Browser') }}</h4>
                    <p class="text-muted mb-4">
                        {{ __('This feature is being prepared and will be available soon.') }}
                    </p>
                    <p class="text-muted mb-4">
                        {{ __('In the meantime, you can use the search functionality to find specific documents.') }}
                    </p>
                    <a href="{{ route('opac.search') }}" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>{{ __('Go to Search') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
