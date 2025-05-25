@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('edit_training_data') }}</h5>
                    <div>
                        <a href="{{ route('ai.training-data.show', $aiTrainingData) }}" class="btn btn-sm btn-outline-primary me-2">
                            <i class="bi bi-eye"></i> {{ __('view') }}
                        </a>
                        <a href="{{ route('ai.training-data.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> {{ __('back_to_list') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('ai.training-data.update', $aiTrainingData) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Informations de base -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">{{ __('basic_information') }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="action_type_id" class="form-label required">{{ __('action_type') }}</label>
                                        <select class="form-select @error('action_type_id') is-invalid @enderror"
                                                id="action_type_id" name="action_type_id" required>
                                            <option value="">{{ __('select_action_type') }}</option>
                                            @foreach($actionTypes as $actionType)
                                                <option value="{{ $actionType->id }}"
                                                    {{ (old('action_type_id', $aiTrainingData->action_type_id) == $actionType->id) ? 'selected' : '' }}>
                                                    {{ $actionType->display_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('action_type_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ __('validation_status') }}</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                   id="is_validated" name="is_validated"
                                                   value="1" {{ old('is_validated', $aiTrainingData->is_validated) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_validated">
                                                {{ __('validated') }}
                                            </label>
                                        </div>
                                        @if($aiTrainingData->validator)
                                            <small class="text-muted d-block mt-1">
                                                {{ __('validated_by') }}: {{ $aiTrainingData->validator->name }}
                                                ({{ $aiTrainingData->updated_at->format('Y-m-d H:i:s') }})
                                            </small>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            {{ __('created_by') }}: {{ $aiTrainingData->creator->name }}
                                            ({{ $aiTrainingData->created_at->format('Y-m-d H:i:s') }})
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Données d'entraînement -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">{{ __('training_data') }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="input" class="form-label required">{{ __('input') }}</label>
                                    <textarea class="form-control @error('input') is-invalid @enderror"
                                              id="input" name="input" rows="4" required>{{ old('input', $aiTrainingData->input) }}</textarea>
                                    @error('input')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">{{ __('input_help_text') }}</div>
                                </div>

                                <div class="mb-3">
                                    <label for="expected_output" class="form-label required">{{ __('expected_output') }}</label>
                                    <textarea class="form-control @error('expected_output') is-invalid @enderror"
                                              id="expected_output" name="expected_output" rows="4" required>{{ old('expected_output', $aiTrainingData->expected_output) }}</textarea>
                                    @error('expected_output')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">{{ __('expected_output_help_text') }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('ai.training-data.index') }}" class="btn btn-secondary">
                                {{ __('cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> {{ __('save_changes') }}
                            </button>
                        </div>
                    </form>
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
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.form-label.required:after {
    content: " *";
    color: #dc3545;
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
}

.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

textarea.form-control {
    font-family: monospace;
    font-size: 0.875rem;
}

.text-muted {
    font-size: 0.875rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation côté client
    const form = document.querySelector('form');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Ajustement automatique de la hauteur des textareas
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        // Déclencher l'ajustement initial
        textarea.dispatchEvent(new Event('input'));
    });
});
</script>
@endpush
