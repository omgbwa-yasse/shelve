@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('OPAC Templates') }}</h5>
                    <a href="{{ route('public.opac-templates.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> {{ __('New Template') }}
                    </a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($templates->count() > 0)
                        <div class="row">
                            @foreach($templates as $template)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 shadow-sm">
                                        @if($template->preview_image)
                                            <img src="{{ asset('storage/' . $template->preview_image) }}" class="card-img-top" alt="{{ $template->name }}" style="height: 200px; object-fit: cover;">
                                        @else
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                <i class="bi bi-palette" style="font-size: 3rem; color: #ccc;"></i>
                                            </div>
                                        @endif
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $template->name }}</h5>
                                            <p class="card-text text-muted small">{{ Str::limit($template->description, 100) }}</p>

                                            @if($template->status === 'active')
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                            @endif
                                        </div>
                                        <div class="card-footer bg-transparent border-top-0">
                                            <div class="d-grid gap-2">
                                                <a href="{{ route('public.opac-templates.show', $template) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> {{ __('View') }}
                                                </a>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('public.opac-templates.preview', $template) }}" class="btn btn-sm btn-outline-info" target="_blank">
                                                        <i class="bi bi-display"></i> {{ __('Preview') }}
                                                    </a>
                                                    <a href="{{ route('public.opac-templates.edit', $template) }}" class="btn btn-sm btn-outline-warning">
                                                        <i class="bi bi-pencil"></i> {{ __('Edit') }}
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="bi bi-clock"></i> {{ __('Updated') }}: {{ $template->updated_at->diffForHumans() }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info text-center" role="alert">
                            <i class="bi bi-info-circle"></i> {{ __('No OPAC templates available') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
