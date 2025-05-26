@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-pencil text-primary me-2"></i>
                            {{ __('edit_action_type') }}: {{ $aiActionType->display_name }}
                        </h5>
                        <a href="{{ route('ai.action-types.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> {{ __('back_to_list') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('ai.action-types.update', $aiActionType) }}" method="POST" class="needs-validation" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Informations de base -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-light mb-4">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">{{ __('basic_information') }}</h6>

                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('name') }}</label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                   name="name" value="{{ old('name', $aiActionType->name) }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">{{ __('action_type_name_help') }}</div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('display_name') }}</label>
                                            <input type="text" class="form-control @error('display_name') is-invalid @enderror"
                                                   name="display_name" value="{{ old('display_name', $aiActionType->display_name) }}" required>
                                            @error('display_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">{{ __('description') }}</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror"
                                                      name="description" rows="3">{{ old('description', $aiActionType->description) }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
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
                                            <label class="form-label required">{{ __('category') }}</label>
                                            <select class="form-select @error('category') is-invalid @enderror"
                                                    name="category" required>
                                                <option value="">{{ __('select_category') }}</option>
                                                <option value="text" {{ old('category', $aiActionType->category) == 'text' ? 'selected' : '' }}>
                                                    {{ __('text') }}
                                                </option>
                                                <option value="image" {{ old('category', $aiActionType->category) == 'image' ? 'selected' : '' }}>
                                                    {{ __('image') }}
                                                </option>
                                                <option value="code" {{ old('category', $aiActionType->category) == 'code' ? 'selected' : '' }}>
                                                    {{ __('code') }}
                                                </option>
                                                <option value="data" {{ old('category', $aiActionType->category) == 'data' ? 'selected' : '' }}>
                                                    {{ __('data') }}
                                                </option>
                                            </select>
                                            @error('category')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('status') }}</label>
                                            <select class="form-select @error('status') is-invalid @enderror"
                                                    name="status" required>
                                                <option value="active" {{ old('status', $aiActionType->status) == 'active' ? 'selected' : '' }}>
                                                    {{ __('active') }}
                                                </option>
                                                <option value="inactive" {{ old('status', $aiActionType->status) == 'inactive' ? 'selected' : '' }}>
                                                    {{ __('inactive') }}
                                                </option>
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input @error('is_system') is-invalid @enderror"
                                                       name="is_system" id="isSystem" value="1"
                                                       {{ old('is_system', $aiActionType->is_system) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="isSystem">
                                                    {{ __('system_type') }}
                                                </label>
                                                @error('is_system')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Champs et règles -->
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">{{ __('fields_and_rules') }}</h6>

                                        <div class="mb-3">
                                            <label class="form-label">{{ __('required_fields') }}</label>
                                            <textarea class="form-control @error('required_fields') is-invalid @enderror"
                                                      name="required_fields" rows="3"
                                                      placeholder="{{ __('json_format') }}">{{ old('required_fields', json_encode($aiActionType->required_fields, JSON_PRETTY_PRINT)) }}</textarea>
                                            @error('required_fields')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">{{ __('optional_fields') }}</label>
                                            <textarea class="form-control @error('optional_fields') is-invalid @enderror"
                                                      name="optional_fields" rows="3"
                                                      placeholder="{{ __('json_format') }}">{{ old('optional_fields', json_encode($aiActionType->optional_fields, JSON_PRETTY_PRINT)) }}</textarea>
                                            @error('optional_fields')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">{{ __('validation_rules') }}</label>
                                            <textarea class="form-control @error('validation_rules') is-invalid @enderror"
                                                      name="validation_rules" rows="3"
                                                      placeholder="{{ __('json_format') }}">{{ old('validation_rules', json_encode($aiActionType->validation_rules, JSON_PRETTY_PRINT)) }}</textarea>
                                            @error('validation_rules')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('ai.action-types.index') }}" class="btn btn-light me-2">
                                {{ __('cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> {{ __('save_changes') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.required:after {
    content: " *";
    color: #dc3545;
}

.card {
    border-radius: 0.5rem;
}

.form-control:focus, .form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
}

.bg-light {
    background-color: #f8f9fa !important;
}

textarea {
    font-family: monospace;
    font-size: 0.875rem;
}
</style>
@endpush

@push('scripts')
<script>
// Validation des formulaires Bootstrap
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()

// Validation JSON en temps réel
document.querySelectorAll('textarea[name$="_fields"], textarea[name="validation_rules"]').forEach(function(textarea) {
    textarea.addEventListener('input', function() {
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
@endsection
