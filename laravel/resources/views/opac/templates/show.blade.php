@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $template->name }}</h5>
                    <div>
                        <a href="{{ route('public.opac-templates.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> {{ __('Back') }}
                        </a>
                        <a href="{{ route('public.opac-templates.preview', $template) }}" class="btn btn-info" target="_blank">
                            <i class="bi bi-display"></i> {{ __('Preview') }}
                        </a>
                        <a href="{{ route('public.opac-templates.edit', $template) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> {{ __('Edit') }}
                        </a>
                        <form action="{{ route('public.opac-templates.duplicate', $template) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-files"></i> {{ __('Duplicate') }}
                            </button>
                        </form>
                        <a href="{{ route('public.opac-templates.export', $template) }}" class="btn btn-primary">
                            <i class="bi bi-download"></i> {{ __('Export') }}
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h6 class="text-muted">{{ __('Description') }}</h6>
                            <p>{{ $template->description ?? __('No description available') }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted">{{ __('Details') }}</h6>
                            <ul class="list-unstyled">
                                <li><strong>{{ __('Type') }}:</strong> {{ $template->type }}</li>
                                <li><strong>{{ __('Status') }}:</strong>
                                    @if($template->status === 'active')
                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                    @endif
                                </li>
                                <li><strong>{{ __('Created') }}:</strong> {{ $template->created_at->format('d/m/Y H:i') }}</li>
                                <li><strong>{{ __('Updated') }}:</strong> {{ $template->updated_at->format('d/m/Y H:i') }}</li>
                            </ul>
                        </div>
                    </div>

                    @if($template->preview_image)
                        <div class="mb-4">
                            <h6 class="text-muted">{{ __('Preview Image') }}</h6>
                            <img src="{{ asset('storage/' . $template->preview_image) }}" alt="{{ $template->name }}" class="img-fluid rounded" style="max-height: 400px;">
                        </div>
                    @endif

                    <div class="mb-4">
                        <h6 class="text-muted">{{ __('Template Content') }}</h6>
                        <div class="border rounded p-3 bg-light">
                            <pre><code>{{ $template->content }}</code></pre>
                        </div>
                    </div>

                    @if($template->custom_css)
                        <div class="mb-4">
                            <h6 class="text-muted">{{ __('Custom CSS') }}</h6>
                            <div class="border rounded p-3 bg-light">
                                <pre><code>{{ $template->custom_css }}</code></pre>
                            </div>
                        </div>
                    @endif

                    @if($template->custom_js)
                        <div class="mb-4">
                            <h6 class="text-muted">{{ __('Custom JavaScript') }}</h6>
                            <div class="border rounded p-3 bg-light">
                                <pre><code>{{ $template->custom_js }}</code></pre>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
