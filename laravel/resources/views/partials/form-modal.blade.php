{{-- Form Modal for Creating/Editing Elements --}}
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog {{ $size ?? 'modal-lg' }} modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-{{ $headerColor ?? 'info' }} text-white">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    <i class="{{ $icon ?? 'bi bi-plus-circle' }}"></i> {{ $title }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="{{ $modalId }}_form" method="{{ $method ?? 'POST' }}" action="{{ $action ?? '#' }}" 
                  @if(isset($enctype)) enctype="{{ $enctype }}" @endif>
                @if(in_array(strtoupper($method ?? 'POST'), ['PUT', 'PATCH', 'DELETE']))
                    @method($method)
                @endif
                @csrf
                <div class="modal-body">
                    @if(isset($description))
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> {{ $description }}
                        </div>
                    @endif

                    <!-- Form Fields -->
                    <div class="row">
                        @foreach($fields as $field)
                            <div class="col-{{ $field['width'] ?? '12' }} mb-3">
                                @switch($field['type'])
                                    @case('text')
                                    @case('email')
                                    @case('password')
                                    @case('url')
                                    @case('tel')
                                    @case('number')
                                    @case('date')
                                    @case('datetime-local')
                                    @case('time')
                                        <label for="{{ $field['name'] }}" class="form-label">
                                            {{ $field['label'] }}
                                            @if($field['required'] ?? false)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        <input type="{{ $field['type'] }}" 
                                               class="form-control @error($field['name']) is-invalid @enderror" 
                                               id="{{ $field['name'] }}" 
                                               name="{{ $field['name'] }}"
                                               value="{{ old($field['name'], $field['value'] ?? '') }}"
                                               placeholder="{{ $field['placeholder'] ?? '' }}"
                                               @if($field['required'] ?? false) required @endif
                                               @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
                                               @if(isset($field['max'])) max="{{ $field['max'] }}" @endif
                                               @if(isset($field['step'])) step="{{ $field['step'] }}" @endif>
                                        @break

                                    @case('textarea')
                                        <label for="{{ $field['name'] }}" class="form-label">
                                            {{ $field['label'] }}
                                            @if($field['required'] ?? false)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        <textarea class="form-control @error($field['name']) is-invalid @enderror" 
                                                  id="{{ $field['name'] }}" 
                                                  name="{{ $field['name'] }}"
                                                  rows="{{ $field['rows'] ?? 3 }}"
                                                  placeholder="{{ $field['placeholder'] ?? '' }}"
                                                  @if($field['required'] ?? false) required @endif>{{ old($field['name'], $field['value'] ?? '') }}</textarea>
                                        @break

                                    @case('select')
                                        <label for="{{ $field['name'] }}" class="form-label">
                                            {{ $field['label'] }}
                                            @if($field['required'] ?? false)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        <select class="form-select @error($field['name']) is-invalid @enderror" 
                                                id="{{ $field['name'] }}" 
                                                name="{{ $field['name'] }}"
                                                @if($field['required'] ?? false) required @endif
                                                @if($field['multiple'] ?? false) multiple @endif>
                                            @if(!($field['multiple'] ?? false))
                                                <option value="">{{ $field['placeholder'] ?? 'Sélectionner une option...' }}</option>
                                            @endif
                                            @foreach($field['options'] as $value => $label)
                                                <option value="{{ $value }}" 
                                                    @if(old($field['name'], $field['value'] ?? '') == $value) selected @endif>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @break

                                    @case('checkbox')
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   class="form-check-input @error($field['name']) is-invalid @enderror" 
                                                   id="{{ $field['name'] }}" 
                                                   name="{{ $field['name'] }}"
                                                   value="{{ $field['value'] ?? '1' }}"
                                                   @if(old($field['name'], $field['checked'] ?? false)) checked @endif>
                                            <label class="form-check-label" for="{{ $field['name'] }}">
                                                {{ $field['label'] }}
                                                @if($field['required'] ?? false)
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>
                                        </div>
                                        @break

                                    @case('radio')
                                        <fieldset>
                                            <legend class="form-label">
                                                {{ $field['label'] }}
                                                @if($field['required'] ?? false)
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </legend>
                                            @foreach($field['options'] as $value => $label)
                                                <div class="form-check">
                                                    <input type="radio" 
                                                           class="form-check-input @error($field['name']) is-invalid @enderror" 
                                                           id="{{ $field['name'] }}_{{ $value }}" 
                                                           name="{{ $field['name'] }}"
                                                           value="{{ $value }}"
                                                           @if(old($field['name'], $field['value'] ?? '') == $value) checked @endif
                                                           @if($field['required'] ?? false) required @endif>
                                                    <label class="form-check-label" for="{{ $field['name'] }}_{{ $value }}">
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </fieldset>
                                        @break

                                    @case('file')
                                        <label for="{{ $field['name'] }}" class="form-label">
                                            {{ $field['label'] }}
                                            @if($field['required'] ?? false)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        <input type="file" 
                                               class="form-control @error($field['name']) is-invalid @enderror" 
                                               id="{{ $field['name'] }}" 
                                               name="{{ $field['name'] }}"
                                               @if($field['required'] ?? false) required @endif
                                               @if(isset($field['accept'])) accept="{{ $field['accept'] }}" @endif
                                               @if($field['multiple'] ?? false) multiple @endif>
                                        @if(isset($field['help']))
                                            <div class="form-text">{{ $field['help'] }}</div>
                                        @endif
                                        @break

                                    @case('hidden')
                                        <input type="hidden" 
                                               name="{{ $field['name'] }}"
                                               value="{{ $field['value'] ?? '' }}">
                                        @break

                                    @case('custom')
                                        @if(isset($field['html']))
                                            {!! $field['html'] !!}
                                        @endif
                                        @break
                                @endswitch

                                @if(isset($field['help']) && $field['type'] !== 'file')
                                    <div class="form-text">{{ $field['help'] }}</div>
                                @endif

                                @error($field['name'])
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    @if(isset($additionalContent))
                        <div class="mt-4">
                            {!! $additionalContent !!}
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <div class="d-flex justify-content-between w-100 align-items-center">
                        <div>
                            @if(isset($leftActions))
                                @foreach($leftActions as $action)
                                    <button type="button" class="btn btn-{{ $action['type'] ?? 'outline-secondary' }}" 
                                            @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif>
                                        <i class="{{ $action['icon'] }}"></i> {{ $action['label'] }}
                                    </button>
                                @endforeach
                            @endif
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                                <i class="bi bi-x-lg"></i> {{ __('Annuler') }}
                            </button>
                            <button type="submit" class="btn btn-{{ $submitColor ?? 'primary' }}" id="{{ $modalId }}_submit">
                                <i class="{{ $submitIcon ?? 'bi bi-check-lg' }}"></i> {{ $submitLabel ?? 'Enregistrer' }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('{{ $modalId }}');
    const form = document.getElementById('{{ $modalId }}_form');
    const submitBtn = document.getElementById('{{ $modalId }}_submit');

    // Form validation
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> {{ $loadingText ?? "Enregistrement..." }}';

        // Remove previous validation states
        form.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.invalid-feedback').forEach(el => {
            el.remove();
        });

        // Perform client-side validation
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = 'Ce champ est requis.';
                field.parentNode.appendChild(feedback);
                isValid = false;
            }
        });

        // Email validation
        const emailFields = form.querySelectorAll('input[type="email"]');
        emailFields.forEach(field => {
            if (field.value && !isValidEmail(field.value)) {
                field.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = 'Veuillez entrer une adresse email valide.';
                field.parentNode.appendChild(feedback);
                isValid = false;
            }
        });

        if (isValid) {
            @if(isset($onSubmit))
                // Custom submit handler
                {{ $onSubmit }}
            @else
                // Default form submission
                fetch(form.action, {
                    method: form.method,
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => {
                    if (response.ok) {
                        // Success
                        bootstrap.Modal.getInstance(modal).hide();
                        @if(isset($onSuccess))
                            {{ $onSuccess }}
                        @else
                            location.reload();
                        @endif
                    } else {
                        return response.json().then(data => {
                            throw data;
                        });
                    }
                })
                .catch(error => {
                    console.error('Form submission error:', error);
                    if (error.errors) {
                        // Laravel validation errors
                        Object.keys(error.errors).forEach(field => {
                            const fieldElement = form.querySelector(`[name="${field}"]`);
                            if (fieldElement) {
                                fieldElement.classList.add('is-invalid');
                                const feedback = document.createElement('div');
                                feedback.className = 'invalid-feedback';
                                feedback.textContent = error.errors[field][0];
                                fieldElement.parentNode.appendChild(feedback);
                            }
                        });
                    } else {
                        alert('Une erreur est survenue lors de l\'enregistrement.');
                    }
                })
                .finally(() => {
                    // Restore button state
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            @endif
        } else {
            // Restore button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });

    // Reset form on modal show
    modal.addEventListener('show.bs.modal', function() {
        form.reset();
        form.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.invalid-feedback').forEach(el => {
            el.remove();
        });
    });

    // Email validation helper
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // Auto-resize textareas
    form.querySelectorAll('textarea').forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });

    // File input preview (for image files)
    form.querySelectorAll('input[type="file"]').forEach(input => {
        if (input.accept && input.accept.includes('image')) {
            input.addEventListener('change', function() {
                const preview = this.parentNode.querySelector('.file-preview');
                if (preview) {
                    preview.remove();
                }

                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'file-preview mt-2';
                    
                    reader.onload = function(e) {
                        previewDiv.innerHTML = `<img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">`;
                    };
                    
                    reader.readAsDataURL(this.files[0]);
                    this.parentNode.appendChild(previewDiv);
                }
            });
        }
    });
});
</script>

<style>
#{{ $modalId }} .modal-header {
    @switch($headerColor ?? 'info')
        @case('primary')
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
            @break
        @case('success')
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
            @break
        @case('warning')
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important;
            @break
        @case('danger')
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
            @break
        @default
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    @endswitch
}

#{{ $modalId }} .form-control:focus,
#{{ $modalId }} .form-select:focus {
    border-color: #{{ $headerColor ?? 'info' === 'info' ? '17a2b8' : ($headerColor === 'primary' ? '007bff' : ($headerColor === 'success' ? '28a745' : ($headerColor === 'warning' ? 'ffc107' : 'dc3545'))) }};
    box-shadow: 0 0 0 0.25rem rgba({{ $headerColor ?? 'info' === 'info' ? '23, 162, 184' : ($headerColor === 'primary' ? '0, 123, 255' : ($headerColor === 'success' ? '40, 167, 69' : ($headerColor === 'warning' ? '255, 193, 7' : '220, 53, 69'))) }}, 0.25);
}

#{{ $modalId }} .btn-{{ $submitColor ?? 'primary' }}:disabled {
    opacity: 0.7;
}

#{{ $modalId }} .invalid-feedback {
    display: block;
}

#{{ $modalId }} textarea {
    resize: vertical;
    min-height: 100px;
}

#{{ $modalId }} .file-preview img {
    border-radius: 0.375rem;
}
</style>