@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('edit_integration') }}</h5>
                    <a href="{{ route('ai.integrations.show', $aiIntegration) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> {{ __('back_to_details') }}
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('ai.integrations.update', $aiIntegration) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h6 class="mb-0">{{ __('basic_information') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('module_name') }}</label>
                                            <select name="module_name" class="form-select @error('module_name') is-invalid @enderror" required>
                                                <option value="">{{ __('select_module') }}</option>
                                                <option value="records" {{ old('module_name', $aiIntegration->module_name) === 'records' ? 'selected' : '' }}>{{ __('records') }}</option>
                                                <option value="slip" {{ old('module_name', $aiIntegration->module_name) === 'slip' ? 'selected' : '' }}>{{ __('slip') }}</option>
                                                <option value="communication" {{ old('module_name', $aiIntegration->module_name) === 'communication' ? 'selected' : '' }}>{{ __('communication') }}</option>
                                                <option value="mail" {{ old('module_name', $aiIntegration->module_name) === 'mail' ? 'selected' : '' }}>{{ __('mail') }}</option>
                                            </select>
                                            @error('module_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">{{ __('event_name') }}</label>
                                            <input type="text" name="event_name" class="form-control @error('event_name') is-invalid @enderror"
                                                value="{{ old('event_name', $aiIntegration->event_name) }}" required>
                                            @error('event_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">{{ __('hook_type') }}</label>
                                            <select name="hook_type" class="form-select @error('hook_type') is-invalid @enderror" required>
                                                <option value="before" {{ old('hook_type', $aiIntegration->hook_type) === 'before' ? 'selected' : '' }}>{{ __('before') }}</option>
                                                <option value="after" {{ old('hook_type', $aiIntegration->hook_type) === 'after' ? 'selected' : '' }}>{{ __('after') }}</option>
                                            </select>
                                            @error('hook_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">{{ __('status') }}</label>
                                            <div class="form-check form-switch">
                                                <input type="checkbox" name="is_active" class="form-check-input" value="1"
                                                    {{ old('is_active', $aiIntegration->is_active) ? 'checked' : '' }}>
                                                <label class="form-check-label">{{ __('active') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h6 class="mb-0">{{ __('ai_configuration') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('action_type') }}</label>
                                            <select name="action_type_id" class="form-select @error('action_type_id') is-invalid @enderror" required>
                                                <option value="">{{ __('select_action_type') }}</option>
                                                @foreach($actionTypes as $actionType)
                                                    <option value="{{ $actionType->id }}"
                                                        {{ old('action_type_id', $aiIntegration->action_type_id) == $actionType->id ? 'selected' : '' }}>
                                                        {{ $actionType->display_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('action_type_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">{{ __('prompt_template') }}</label>
                                            <select name="ai_prompt_template_id" class="form-select @error('ai_prompt_template_id') is-invalid @enderror">
                                                <option value="">{{ __('select_prompt_template') }}</option>
                                                @foreach($promptTemplates as $template)
                                                    <option value="{{ $template->id }}"
                                                        {{ old('ai_prompt_template_id', $aiIntegration->ai_prompt_template_id) == $template->id ? 'selected' : '' }}>
                                                        {{ $template->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('ai_prompt_template_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">{{ __('description') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                                rows="3">{{ old('description', $aiIntegration->description) }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">{{ __('configuration') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <textarea name="configuration" class="form-control @error('configuration') is-invalid @enderror"
                                                rows="3" placeholder="{{ __('json_configuration') }}">{{ old('configuration', $aiIntegration->configuration ? json_encode($aiIntegration->configuration, JSON_PRETTY_PRINT) : '') }}</textarea>
                                            @error('configuration')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="{{ route('ai.integrations.show', $aiIntegration) }}" class="btn btn-secondary me-2">
                                {{ __('cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ __('save_changes') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation JSON en temps réel pour le champ configuration
    const configTextarea = document.querySelector('textarea[name="configuration"]');
    configTextarea.addEventListener('input', function() {
        try {
            if (this.value) {
                JSON.parse(this.value);
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid', 'is-invalid');
            }
        } catch (e) {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.form-switch .form-check-input {
    width: 2.5em;
    height: 1.25em;
}

textarea[name="configuration"] {
    font-family: monospace;
}

.is-valid {
    border-color: #198754 !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right calc(0.375em + 0.1875rem) center !important;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem) !important;
}
</style>
@endpush
