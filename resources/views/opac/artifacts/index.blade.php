@extends('opac.layouts.app')

@section('title', __('Browse Artifacts') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <div class="mb-4">
                <h1 class="h3 mb-2">{{ __('Museum Artifacts') }}</h1>
                <p class="text-muted">{{ __('Explore our museum collection') }}</p>
            </div>

            <div class="opac-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('opac.artifacts.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label for="q" class="form-label">{{ __('Search Artifacts') }}</label>
                            <input type="text" class="form-control" id="q" name="q" value="{{ request('q') }}" placeholder="{{ __('Search by name, description...') }}">
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
                @forelse($artifacts as $artifact)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 opac-card">
                            @if($artifact->attachments->where('type', 'image')->first())
                                <img src="{{ Storage::url($artifact->attachments->where('type', 'image')->first()->path) }}" class="card-img-top" alt="{{ $artifact->name }}" style="height: 200px; object-fit: cover;">
                            @else
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-cube fa-3x text-muted"></i>
                                </div>
                            @endif
                            <div class="card-body">
                                <h5 class="card-title">{{ $artifact->name }}</h5>
                                <p class="card-text text-muted small">{{ Str::limit($artifact->description, 100) }}</p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="badge bg-secondary">{{ $artifact->category }}</span>
                                    <a href="{{ route('opac.artifacts.show', $artifact->id) }}" class="btn btn-sm btn-outline-primary">{{ __('View Details') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <p class="text-muted">{{ __('No artifacts found.') }}</p>
                    </div>
                @endforelse
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $artifacts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
