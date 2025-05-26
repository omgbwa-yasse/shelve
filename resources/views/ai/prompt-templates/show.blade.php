@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-file-text text-primary me-2"></i>
                            {{ __('template_details') }}: {{ $promptTemplate->name }}
                        </h5>
                        <div>
                            <a href="{{ route('ai.prompt-templates.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                                <i class="bi bi-arrow-left me-1"></i> {{ __('back_to_list') }}
                            </a>
                            @if(!$promptTemplate->is_system)
                            <a href="{{ route('ai.prompt-templates.edit', $promptTemplate) }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-pencil me-1"></i> {{ __('edit') }}
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Informations de base -->
                        <div class="col-md-6">
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">{{ __('basic_information') }}</h6>

                                    <div class="mb-3">
                                        <label class="form-label text-muted">{{ __('name') }}</label>
                                        <div class="fw-medium">{{ $promptTemplate->name }}</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-muted">{{ __('description') }}</label>
                                        <div>{{ $promptTemplate->description ?: __('no_description') }}</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-muted">{{ __('action_type') }}</label>
                                        <div>
                                            <a href="{{ route('ai.action-types.show', $promptTemplate->actionType) }}" class="text-decoration-none">
                                                {{ $promptTemplate->actionType->display_name }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuration -->
                        <div class="col-md-6">
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">{{ __('configuration') }}</h6>

                                    <div class="mb-3">
                                        <label class="form-label text-muted">{{ __('category') }}</label>
                                        <div>
                                            <span class="badge bg-{{
                                                $promptTemplate->category === 'text' ? 'primary' :
                                                ($promptTemplate->category === 'image' ? 'success' :
                                                ($promptTemplate->category === 'code' ? 'warning' : 'info'))
                                            }}">
                                                {{ $promptTemplate->category }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-muted">{{ __('status') }}</label>
                                        <div>
                                            <span class="badge bg-{{ $promptTemplate->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ $promptTemplate->status }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-muted">{{ __('type') }}</label>
                                        <div>
                                            @if($promptTemplate->is_system)
                                            <span class="badge bg-info">{{ __('system_template') }}</span>
                                            @else
                                            <span class="badge bg-secondary">{{ __('custom_template') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-muted">{{ __('created_at') }}</label>
                                        <div>{{ $promptTemplate->created_at->format('Y-m-d H:i:s') }}</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-muted">{{ __('updated_at') }}</label>
                                        <div>{{ $promptTemplate->updated_at->format('Y-m-d H:i:s') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Template et variables -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">{{ __('template_and_variables') }}</h6>

                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label class="form-label text-muted">{{ __('prompt_template') }}</label>
                                                <pre class="bg-white p-3 rounded border" style="max-height: 300px; overflow-y: auto;">{{ $promptTemplate->template }}</pre>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label text-muted">{{ __('variables') }}</label>
                                                <pre class="bg-white p-3 rounded border" style="max-height: 300px; overflow-y: auto;">{{ json_encode($promptTemplate->variables, JSON_PRETTY_PRINT) ?: '{}' }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Utilisations récentes -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">{{ __('recent_uses') }}</h6>

                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>{{ __('id') }}</th>
                                                    <th>{{ __('action') }}</th>
                                                    <th>{{ __('status') }}</th>
                                                    <th>{{ __('created_at') }}</th>
                                                    <th>{{ __('actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($promptTemplate->actions()->latest()->take(5)->get() as $action)
                                                <tr>
                                                    <td>{{ $action->id }}</td>
                                                    <td>
                                                        <a href="{{ route('ai.actions.show', $action) }}" class="text-decoration-none">
                                                            {{ $action->actionType->display_name }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{
                                                            $action->status === 'pending' ? 'warning' :
                                                            ($action->status === 'accepted' ? 'success' :
                                                            ($action->status === 'rejected' ? 'danger' : 'info'))
                                                        }}">
                                                            {{ $action->status }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $action->created_at->diffForHumans() }}</td>
                                                    <td>
                                                        <a href="{{ route('ai.actions.show', $action) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-4">
                                                        <div class="text-muted">
                                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                            {{ __('no_recent_uses') }}
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.card {
    border-radius: 0.5rem;
}

.bg-light {
    background-color: #f8f9fa !important;
}

pre {
    font-family: monospace;
    font-size: 0.875rem;
    margin: 0;
    white-space: pre-wrap;
}

.badge {
    font-weight: 500;
    padding: 0.5em 0.75em;
}

.table th {
    font-weight: 500;
    font-size: 0.875rem;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}
</style>
@endpush
@endsection
