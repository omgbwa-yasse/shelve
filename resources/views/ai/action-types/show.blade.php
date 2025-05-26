@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle text-primary me-2"></i>
                            {{ __('action_type_details') }}: {{ $aiActionType->display_name }}
                        </h5>
                        <div>
                            <a href="{{ route('ai.action-types.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                                <i class="bi bi-arrow-left me-1"></i> {{ __('back_to_list') }}
                            </a>
                            @if(!$aiActionType->is_system)
                            <a href="{{ route('ai.action-types.edit', $aiActionType) }}" class="btn btn-primary btn-sm">
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
                                        <div class="fw-medium">{{ $aiActionType->name }}</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-muted">{{ __('display_name') }}</label>
                                        <div class="fw-medium">{{ $aiActionType->display_name }}</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-muted">{{ __('description') }}</label>
                                        <div>{{ $aiActionType->description ?: __('no_description') }}</div>
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
                                                $aiActionType->category === 'text' ? 'primary' :
                                                ($aiActionType->category === 'image' ? 'success' :
                                                ($aiActionType->category === 'code' ? 'warning' : 'info'))
                                            }}">
                                                {{ $aiActionType->category }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-muted">{{ __('status') }}</label>
                                        <div>
                                            <span class="badge bg-{{ $aiActionType->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ $aiActionType->status }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-muted">{{ __('type') }}</label>
                                        <div>
                                            @if($aiActionType->is_system)
                                            <span class="badge bg-info">{{ __('system_type') }}</span>
                                            @else
                                            <span class="badge bg-secondary">{{ __('custom_type') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Statistiques -->
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">{{ __('statistics') }}</h6>

                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label class="form-label text-muted">{{ __('total_actions') }}</label>
                                            <div class="h4 mb-0">{{ number_format($aiActionType->actions->count()) }}</div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label class="form-label text-muted">{{ __('prompt_templates') }}</label>
                                            <div class="h4 mb-0">{{ number_format($aiActionType->promptTemplates->count()) }}</div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label class="form-label text-muted">{{ __('integrations') }}</label>
                                            <div class="h4 mb-0">{{ number_format($aiActionType->integrations->count()) }}</div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label class="form-label text-muted">{{ __('training_data') }}</label>
                                            <div class="h4 mb-0">{{ number_format($aiActionType->trainingData->count()) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Champs et règles -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">{{ __('fields_and_rules') }}</h6>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label text-muted">{{ __('required_fields') }}</label>
                                                <pre class="bg-white p-3 rounded border" style="max-height: 200px; overflow-y: auto;">{{ json_encode($aiActionType->required_fields, JSON_PRETTY_PRINT) ?: '{}' }}</pre>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label text-muted">{{ __('optional_fields') }}</label>
                                                <pre class="bg-white p-3 rounded border" style="max-height: 200px; overflow-y: auto;">{{ json_encode($aiActionType->optional_fields, JSON_PRETTY_PRINT) ?: '{}' }}</pre>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label text-muted">{{ __('validation_rules') }}</label>
                                                <pre class="bg-white p-3 rounded border" style="max-height: 200px; overflow-y: auto;">{{ json_encode($aiActionType->validation_rules, JSON_PRETTY_PRINT) ?: '{}' }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions récentes -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">{{ __('recent_actions') }}</h6>

                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>{{ __('id') }}</th>
                                                    <th>{{ __('target') }}</th>
                                                    <th>{{ __('status') }}</th>
                                                    <th>{{ __('created_at') }}</th>
                                                    <th>{{ __('actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($aiActionType->actions()->latest()->take(5)->get() as $action)
                                                <tr>
                                                    <td>{{ $action->id }}</td>
                                                    <td>
                                                        <span class="badge bg-secondary">{{ $action->target_type }}</span>
                                                        #{{ $action->target_id }}
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
                                                            {{ __('no_recent_actions') }}
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
    font-size: 0.875rem;
    margin: 0;
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
