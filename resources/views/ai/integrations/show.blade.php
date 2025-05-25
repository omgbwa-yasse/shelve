@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $aiIntegration->module_name }} - {{ $aiIntegration->event_name }}</h5>
                    <div>
                        <a href="{{ route('ai.integrations.index') }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i> {{ __('back_to_list') }}
                        </a>
                        <a href="{{ route('ai.integrations.edit', $aiIntegration) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-pencil"></i> {{ __('edit') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('basic_information') }}</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4">{{ __('module_name') }}</dt>
                                        <dd class="col-sm-8">{{ __($aiIntegration->module_name) }}</dd>

                                        <dt class="col-sm-4">{{ __('event_name') }}</dt>
                                        <dd class="col-sm-8">{{ $aiIntegration->event_name }}</dd>

                                        <dt class="col-sm-4">{{ __('hook_type') }}</dt>
                                        <dd class="col-sm-8">
                                            <span class="badge bg-{{ $aiIntegration->hook_type === 'before' ? 'info' : 'success' }}">
                                                {{ __($aiIntegration->hook_type) }}
                                            </span>
                                        </dd>

                                        <dt class="col-sm-4">{{ __('status') }}</dt>
                                        <dd class="col-sm-8">
                                            <span class="badge bg-{{ $aiIntegration->is_active ? 'success' : 'danger' }}">
                                                {{ $aiIntegration->is_active ? __('active') : __('inactive') }}
                                            </span>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('ai_configuration') }}</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4">{{ __('action_type') }}</dt>
                                        <dd class="col-sm-8">
                                            @if($aiIntegration->actionType)
                                                <span class="badge bg-primary">
                                                    {{ $aiIntegration->actionType->display_name }}
                                                </span>
                                            @else
                                                <span class="text-muted">{{ __('not_set') }}</span>
                                            @endif
                                        </dd>

                                        <dt class="col-sm-4">{{ __('prompt_template') }}</dt>
                                        <dd class="col-sm-8">
                                            @if($aiIntegration->promptTemplate)
                                                <a href="{{ route('ai.prompt-templates.show', $aiIntegration->promptTemplate) }}" class="text-decoration-none">
                                                    {{ $aiIntegration->promptTemplate->name }}
                                                </a>
                                            @else
                                                <span class="text-muted">{{ __('not_set') }}</span>
                                            @endif
                                        </dd>

                                        <dt class="col-sm-4">{{ __('created_at') }}</dt>
                                        <dd class="col-sm-8">{{ $aiIntegration->created_at->format('Y-m-d H:i:s') }}</dd>

                                        <dt class="col-sm-4">{{ __('updated_at') }}</dt>
                                        <dd class="col-sm-8">{{ $aiIntegration->updated_at->format('Y-m-d H:i:s') }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('description') }}</h6>
                                </div>
                                <div class="card-body">
                                    @if($aiIntegration->description)
                                        <p class="mb-0">{{ $aiIntegration->description }}</p>
                                    @else
                                        <p class="text-muted mb-0">{{ __('no_description') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('configuration') }}</h6>
                                </div>
                                <div class="card-body">
                                    @if($aiIntegration->configuration)
                                        <pre class="mb-0"><code>{{ json_encode($aiIntegration->configuration, JSON_PRETTY_PRINT) }}</code></pre>
                                    @else
                                        <p class="text-muted mb-0">{{ __('no_configuration') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">{{ __('recent_actions') }}</h6>
                        </div>
                        <div class="card-body">
                            @if($aiIntegration->actions->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>{{ __('id') }}</th>
                                                <th>{{ __('action_type') }}</th>
                                                <th>{{ __('status') }}</th>
                                                <th>{{ __('created_at') }}</th>
                                                <th>{{ __('actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($aiIntegration->actions->take(5) as $action)
                                                <tr>
                                                    <td>{{ $action->id }}</td>
                                                    <td>
                                                        <span class="badge bg-primary">
                                                            {{ $action->actionType->display_name }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $action->status === 'completed' ? 'success' : ($action->status === 'failed' ? 'danger' : 'warning') }}">
                                                            {{ __($action->status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $action->created_at->format('Y-m-d H:i:s') }}</td>
                                                    <td>
                                                        <a href="{{ route('ai.actions.show', $action) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted mb-0">{{ __('no_actions_found') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

dt {
    font-weight: 600;
    color: #6c757d;
}

pre {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.25rem;
    margin: 0;
}

code {
    color: #212529;
}

.table th {
    font-weight: 600;
    color: #6c757d;
}

.badge {
    font-weight: 500;
}
</style>
@endpush
