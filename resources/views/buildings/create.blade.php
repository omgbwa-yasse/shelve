@extends('layouts.app')

@push('styles')
<style>
/* Design System Harmonisé */
:root {
    --primary-color: #2563eb;
    --secondary-color: #64748b;
    --success-color: #059669;
    --warning-color: #d97706;
    --danger-color: #dc2626;
    --info-color: #0891b2;
    --light-bg: #f8fafc;
    --border-color: #e2e8f0;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
    --radius: 0.5rem;
    --transition: all 0.2s ease-in-out;
}

/* Layout optimisé */
.compact-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 1rem;
}

/* Header harmonisé */
.page-header {
    background: linear-gradient(135deg, var(--light-bg) 0%, #ffffff 100%);
    border-radius: var(--radius);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
}

.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.page-subtitle {
    color: var(--secondary-color);
    margin: 0.5rem 0 0 0;
    font-size: 0.95rem;
}

/* Breadcrumb moderne */
.modern-breadcrumb {
    background: #ffffff;
    border-radius: var(--radius);
    padding: 0.75rem 1rem;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    margin-bottom: 1.5rem;
}

.breadcrumb-item {
    color: var(--secondary-color);
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition);
}

.breadcrumb-item:hover {
    color: var(--primary-color);
}

.breadcrumb-item.active {
    color: #1e293b;
    font-weight: 600;
}

/* Form card */
.form-card {
    background: #ffffff;
    border-radius: var(--radius);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.form-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
    color: white;
    padding: 1.5rem;
    text-align: center;
}

.form-header i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.form-body {
    padding: 2rem;
}

/* Form groups */
.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-control, .form-select {
    border: 2px solid var(--border-color);
    border-radius: var(--radius);
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: var(--transition);
    background: #ffffff;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
    outline: none;
}

.form-control.is-invalid {
    border-color: var(--danger-color);
}

.invalid-feedback {
    color: var(--danger-color);
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

/* Form actions */
.form-actions {
    background: #f8fafc;
    border-top: 1px solid var(--border-color);
    padding: 1.5rem;
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

/* Boutons harmonisés */
.btn-modern {
    border-radius: var(--radius);
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    transition: var(--transition);
    border: 1px solid transparent;
    font-size: 0.95rem;
}

.btn-modern:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        padding: 1rem;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .form-body {
        padding: 1.5rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in-up {
    animation: fadeInUp 0.3s ease-out;
}

/* Help text */
.help-text {
    font-size: 0.875rem;
    color: var(--secondary-color);
    margin-top: 0.25rem;
}

/* Required indicator */
.required {
    color: var(--danger-color);
    margin-left: 0.25rem;
}
</style>
@endpush

@section('content')
<div class="compact-container">
    <!-- Header moderne -->
    <div class="page-header">
        <div class="text-center">
            <h1 class="page-title">
                <i class="bi bi-plus-circle text-primary"></i>
                {{ __('Créer un nouveau bâtiment') }}
            </h1>
            <p class="page-subtitle">Ajoutez un nouveau bâtiment à votre infrastructure</p>
        </div>
    </div>

    <!-- Breadcrumb moderne -->
    <nav class="modern-breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="{{ route('buildings.index') }}" class="breadcrumb-item">
                    <i class="bi bi-building text-primary me-1"></i>{{ __('Dépôts') }}
                </a>
            </li>
            <li class="breadcrumb-item active">
                <i class="bi bi-plus-circle me-1"></i>{{ __('Nouveau bâtiment') }}
            </li>
        </ol>
    </nav>

    <!-- Formulaire -->
    <div class="form-card fade-in-up">
        <div class="form-header">
            <i class="bi bi-building"></i>
            <h3 class="mb-0">{{ __('Informations du bâtiment') }}</h3>
        </div>
        
        <form action="{{ route('buildings.store') }}" method="POST" id="buildingForm">
            @csrf
            <div class="form-body">
                <div class="row g-3">
                    <!-- Nom -->
                    <div class="col-12">
                        <div class="form-group">
                            <label for="name" class="form-label">
                                <i class="bi bi-tag text-primary"></i>
                                {{ __('Nom') }}<span class="required">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   placeholder="Ex: Bâtiment A - Archives Centrales"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="help-text">{{ __('Nom descriptif du bâtiment') }}</div>
                        </div>
                    </div>

                    <!-- Visibilité -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="visibility" class="form-label">
                                <i class="bi bi-eye text-info"></i>
                                {{ __('Visibilité') }}<span class="required">*</span>
                            </label>
                            <select class="form-select @error('visibility') is-invalid @enderror" 
                                    id="visibility" 
                                    name="visibility" 
                                    required>
                                <option value="">{{ __('Sélectionner la visibilité') }}</option>
                                @foreach($visibilityOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('visibility') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('visibility')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="help-text">{{ __('Contrôle l\'accès au bâtiment') }}</div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <div class="form-group">
                            <label for="description" class="form-label">
                                <i class="bi bi-file-text text-secondary"></i>
                                {{ __('Description') }}
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4"
                                      placeholder="Description détaillée du bâtiment (optionnel)">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="help-text">{{ __('Description optionnelle du bâtiment') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <a href="{{ route('buildings.index') }}" class="btn btn-outline-secondary btn-modern">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Annuler') }}
                </a>
                <button type="submit" class="btn btn-primary btn-modern">
                    <i class="bi bi-check-circle me-2"></i>{{ __('Créer le bâtiment') }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeFormValidation();
    initializeAnimations();
});

function initializeFormValidation() {
    const form = document.getElementById('buildingForm');
    const requiredFields = form.querySelectorAll('[required]');

    requiredFields.forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
        });

        field.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
        });
    });

    form.addEventListener('submit', function(e) {
        let isValid = true;
        requiredFields.forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
            // Scroll to first error
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    function validateField(field) {
        const value = field.value.trim();
        const isValid = value.length > 0;

        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
        }

        return isValid;
    }
}

function initializeAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });

    document.querySelectorAll('.fade-in-up').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        observer.observe(el);
    });
}
</script>
@endpush
@endsection
