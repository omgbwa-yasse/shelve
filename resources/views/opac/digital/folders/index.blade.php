@extends('opac.layouts.app')

@section('title', __('Digital Collections') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <div class="mb-4">
                <h1 class="h3 mb-2">{{ __('Digital Collections') }}</h1>
                <p class="text-muted">{{ __('Explore our digital archives and documents') }}</p>
            </div>

            <div class="opac-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('opac.digital.folders.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label for="q" class="form-label">{{ __('Search Collections') }}</label>
                            <input type="text" class="form-control" id="q" name="q" value="{{ request('q') }}" placeholder="{{ __('Search folders...') }}">
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
                @forelse($folders as $folder)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 opac-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-folder fa-2x text-warning me-3"></i>
                                    <h5 class="card-title mb-0 text-truncate">{{ $folder->name }}</h5>
                                </div>
                                <p class="card-text text-muted small">{{ Str::limit($folder->description, 100) }}</p>
                                <div class="d-flex justify-content-between align-items-center mt-3 text-muted small">
                                    <span><i class="fas fa-file me-1"></i> {{ $folder->documents_count }} {{ __('documents') }}</span>
                                    <span><i class="fas fa-folder-open me-1"></i> {{ $folder->subfolders_count }} {{ __('subfolders') }}</span>
                                </div>
                                <a href="{{ route('opac.digital.folders.show', $folder->id) }}" class="btn btn-sm btn-outline-primary w-100 mt-3">{{ __('Open Folder') }}</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <p class="text-muted">{{ __('No public collections found.') }}</p>
                    </div>
                @endforelse
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $folders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
