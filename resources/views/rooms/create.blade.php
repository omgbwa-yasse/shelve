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
    background: linear-gradient(135deg, var(--info-color) 0%, #0ea5e9 100%);
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

/* Enhanced select */
.enhanced-select {
    position: relative;
}

.select-search {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: #ffffff;
    border: 2px solid var(--primary-color);
    border-radius: var(--radius);
    padding: 0.75rem 1rem;
    z-index: 10;
    display: none;
}

.select-search:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
}

.select-options {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #ffffff;
    border: 1px solid var(--border-color);
    border-top: none;
    border-radius: 0 0 var(--radius) var(--radius);
    box-shadow: var(--shadow-lg);
    z-index: 20;
    max-height: 200px;
    overflow-y: auto;
    display: none;
}

.select-option {
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: var(--transition);
    border-bottom: 1px solid var(--border-color);
}

.select-option:hover {
    background: var(--light-bg);
}

.select-option.selected {
    background: var(--primary-color);
    color: white;
}

.select-option:last-child {
    border-bottom: none;
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
                <i class="bi bi-plus-circle text-info"></i>
                {{ __('Créer une nouvelle salle') }}
            </h1>
            <p class="page-subtitle">Ajoutez une nouvelle salle à votre infrastructure</p>
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
            <li class="breadcrumb-item">
                <a href="{{ route('rooms.index') }}" class="breadcrumb-item">
                    <i class="bi bi-house-door text-info me-1"></i>{{ __('Salles') }}
                </a>
            </li>
            <li class="breadcrumb-item active">
                <i class="bi bi-plus-circle me-1"></i>{{ __('Nouvelle salle') }}
            </li>
        </ol>
    </nav>

    <!-- Formulaire -->
    <div class="form-card fade-in-up">
        <div class="form-header">
            <i class="bi bi-house-door"></i>
            <h3 class="mb-0">{{ __('Informations de la salle') }}</h3>
        </div>
        
        <form action="{{ route('rooms.store') }}" method="POST" id="roomForm">
            @csrf
            <div class="form-body">
                <div class="row g-3">
                    <!-- Code -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="code" class="form-label">
                                <i class="bi bi-hash text-primary"></i>
                                {{ __('Code') }}<span class="required">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('code') is-invalid @enderror" 
                                   id="code" 
                                   name="code" 
                                   value="{{ old('code') }}"
                                   placeholder="Ex: SALLE-001"
                                   required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="help-text">{{ __('Code unique pour identifier la salle') }}</div>
                        </div>
                    </div>

                    <!-- Nom -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name" class="form-label">
                                <i class="bi bi-tag text-success"></i>
                                {{ __('Nom') }}<span class="required">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   placeholder="Ex: Salle d'archives principale"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="help-text">{{ __('Nom descriptif de la salle') }}</div>
                        </div>
                    </div>

                    <!-- Type de salle -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="type" class="form-label">
                                <i class="bi bi-gear text-warning"></i>
                                {{ __('Type de salle') }}<span class="required">*</span>
                            </label>
                            <select class="form-select @error('type') is-invalid @enderror" 
                                    id="type" 
                                    name="type" 
                                    required>
                                <option value="">{{ __('Sélectionner un type') }}</option>
                                @foreach($typeOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="help-text">{{ __('Définit l\'usage principal de la salle') }}</div>
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
                            <div class="help-text">{{ __('Contrôle l\'accès à la salle') }}</div>
                        </div>
                    </div>

                    <!-- Niveau -->
                    <div class="col-12">
                        <div class="form-group">
                            <label for="floor_id" class="form-label">
                                <i class="bi bi-layers text-danger"></i>
                                {{ __('Niveau') }}<span class="required">*</span>
                            </label>
                            <div class="enhanced-select">
                                <select class="form-select @error('floor_id') is-invalid @enderror" 
                                        id="floor_id" 
                                        name="floor_id" 
                                        required>
                                    <option value="">{{ __('Sélectionner un niveau') }}</option>
                                    @foreach ($floors as $floor)
                                        <option value="{{ $floor->id }}" 
                                                data-building="{{ $floor->building->name }}"
                                                {{ old('floor_id') == $floor->id ? 'selected' : '' }}>
                                            {{ $floor->name }} ({{ $floor->building->name }})
                                        </option>
                                    @endforeach
                                </select>
                                <input type="text" 
                                       class="select-search" 
                                       placeholder="Rechercher un niveau ou bâtiment..."
                                       style="display: none;">
                                <div class="select-options"></div>
                            </div>
                            @error('floor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="help-text">{{ __('Niveau et bâtiment où se trouve la salle') }}</div>
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
                                      placeholder="Description détaillée de la salle (optionnel)">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="help-text">{{ __('Description optionnelle de la salle') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary btn-modern">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Annuler') }}
                </a>
                <button type="submit" class="btn btn-primary btn-modern">
                    <i class="bi bi-check-circle me-2"></i>{{ __('Créer la salle') }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeEnhancedSelect();
    initializeFormValidation();
    initializeAnimations();
});

function initializeEnhancedSelect() {
    const enhancedSelect = document.querySelector('.enhanced-select');
    const select = enhancedSelect.querySelector('select');
    const searchInput = enhancedSelect.querySelector('.select-search');
    const optionsContainer = enhancedSelect.querySelector('.select-options');
    const options = Array.from(select.options).slice(1); // Exclude placeholder

    // Show search on focus
    select.addEventListener('focus', function() {
        searchInput.style.display = 'block';
        searchInput.focus();
        showOptions();
    });

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        filterOptions(searchTerm);
    });

    // Hide search on blur
    searchInput.addEventListener('blur', function() {
        setTimeout(() => {
            searchInput.style.display = 'none';
            optionsContainer.style.display = 'none';
        }, 200);
    });

    function showOptions() {
        optionsContainer.innerHTML = '';
        options.forEach(option => {
            const optionElement = document.createElement('div');
            optionElement.className = 'select-option';
            optionElement.textContent = option.textContent;
            optionElement.dataset.value = option.value;
            
            optionElement.addEventListener('click', function() {
                select.value = this.dataset.value;
                select.dispatchEvent(new Event('change'));
                searchInput.value = '';
                optionsContainer.style.display = 'none';
            });
            
            optionsContainer.appendChild(optionElement);
        });
        optionsContainer.style.display = 'block';
    }

    function filterOptions(searchTerm) {
        const optionElements = optionsContainer.querySelectorAll('.select-option');
        let hasVisibleOptions = false;

        optionElements.forEach(element => {
            const text = element.textContent.toLowerCase();
            const building = element.textContent.match(/\((.*?)\)/)?.[1]?.toLowerCase() || '';
            
            if (text.includes(searchTerm) || building.includes(searchTerm)) {
                element.style.display = 'block';
                hasVisibleOptions = true;
            } else {
                element.style.display = 'none';
            }
        });

        if (!hasVisibleOptions) {
            const noResults = document.createElement('div');
            noResults.className = 'select-option';
            noResults.textContent = 'Aucun résultat trouvé';
            noResults.style.color = 'var(--secondary-color)';
            noResults.style.fontStyle = 'italic';
            optionsContainer.appendChild(noResults);
        }
    }
}

function initializeFormValidation() {
    const form = document.getElementById('roomForm');
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
