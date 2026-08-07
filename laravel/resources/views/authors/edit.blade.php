@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-dark">
                            <i class="fas fa-edit me-2 text-primary"></i>
                            {{ __('edit_author') }}
                        </h4>
                        <a href="{{ route('mail-author.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i>{{ __('back') }}
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('mail-author.update', $author) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type_id" class="form-label fw-medium">
                                        <i class="fas fa-tag me-1 text-muted"></i>{{ __('type') }}
                                    </label>
                                    <select id="type_id" name="type_id" class="form-select" required>
                                        <option value="">{{ __('select_type') }}</option>
                                        @foreach ($authorTypes as $type)
                                            <option value="{{ $type->id }}" {{ $author->type_id == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label fw-medium">
                                        <i class="fas fa-user me-1 text-muted"></i>{{ __('name') }}
                                    </label>
                                    <input type="text" id="name" name="name" class="form-control" 
                                           data-field="name" value="{{ $author->name }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="parallel_name" class="form-label fw-medium">
                                        <i class="fas fa-user-tag me-1 text-muted"></i>{{ __('parallel_name') }}
                                    </label>
                                    <input type="text" id="parallel_name" name="parallel_name" class="form-control" 
                                           data-field="parallel_name" value="{{ $author->parallel_name }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="other_name" class="form-label fw-medium">
                                        <i class="fas fa-user-plus me-1 text-muted"></i>{{ __('other_name') }}
                                    </label>
                                    <input type="text" id="other_name" name="other_name" class="form-control" 
                                           data-field="other_name" value="{{ $author->other_name }}">
                                </div>

                                <div class="mb-3">
                                    <label for="lifespan" class="form-label fw-medium">
                                        <i class="fas fa-calendar me-1 text-muted"></i>{{ __('lifespan') }}
                                    </label>
                                    <input type="text" id="lifespan" name="lifespan" class="form-control" 
                                           value="{{ $author->lifespan }}" placeholder="ex: 1920-1990">
                                </div>

                                <div class="mb-3">
                                    <label for="locations" class="form-label fw-medium">
                                        <i class="fas fa-map-marker-alt me-1 text-muted"></i>{{ __('residence') }}
                                    </label>
                                    <input type="text" id="locations" name="locations" class="form-control" 
                                           data-field="locations" value="{{ $author->locations }}">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="parent_id" class="form-label fw-medium">
                                <i class="fas fa-sitemap me-1 text-muted"></i>{{ __('parent_entity') }}
                            </label>
                            <select id="parent_id" name="parent_id" class="form-select">
                                <option value="">{{ __('select_parent') }}</option>
                                @foreach ($parents as $parent)
                                    <option value="{{ $parent->id }}" {{ $author->parent_id == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('mail-author.index') }}" class="btn btn-outline-secondary">
                                {{ __('cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>{{ __('update_author') }}
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
.suggestions-list {
    position: absolute;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    max-height: 200px;
    overflow-y: auto;
    z-index: 1050;
    list-style: none;
    padding: 0;
    margin: 0;
    min-width: 200px;
}

.suggestions-list li {
    padding: 0.5rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid #f8f9fa;
    transition: background-color 0.2s ease;
}

.suggestions-list li:hover {
    background-color: #f8f9fa;
}

.suggestions-list li:last-child {
    border-bottom: none;
}

.form-select, .form-control {
    border-radius: 0.375rem;
    border: 1px solid #dee2e6;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-select:focus, .form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.card {
    border-radius: 0.75rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const authorInputs = document.querySelectorAll('input[data-field]');
    const suggestionsList = document.createElement('ul');
    suggestionsList.classList.add('suggestions-list');
    suggestionsList.style.display = 'none';

    document.body.appendChild(suggestionsList);

    authorInputs.forEach(input => {
        input.addEventListener('input', () => {
            const searchTerm = input.value.toLowerCase();
            const field = input.dataset.field;

            if (searchTerm.length < 2) {
                suggestionsList.style.display = 'none';
                return;
            }

            // Simuler des suggestions basées sur le champ
            const suggestions = generateSuggestions(field, searchTerm);
            
            if (suggestions.length > 0) {
                displaySuggestions(suggestions, input);
            } else {
                suggestionsList.style.display = 'none';
            }
        });

        input.addEventListener('blur', () => {
            setTimeout(() => {
                suggestionsList.style.display = 'none';
            }, 200);
        });
    });

    function generateSuggestions(field, searchTerm) {
        // Suggestions basées sur le type de champ
        const fieldSuggestions = {
            'name': ['Jean Dupont', 'Marie Martin', 'Pierre Durand', 'Sophie Bernard'],
            'parallel_name': ['John Smith', 'Mary Johnson', 'Peter Brown', 'Sarah Wilson'],
            'locations': ['Paris, France', 'Montréal, Canada', 'Lyon, France', 'Québec, Canada'],
            'lifespan': ['1920-1990', '1945-2010', '1900-1985', '1950-2020']
        };

        return fieldSuggestions[field]?.filter(suggestion => 
            suggestion.toLowerCase().includes(searchTerm)
        ) || [];
    }

    function displaySuggestions(suggestions, input) {
        suggestionsList.innerHTML = '';
        
        const rect = input.getBoundingClientRect();
        suggestionsList.style.top = `${rect.bottom + window.scrollY}px`;
        suggestionsList.style.left = `${rect.left + window.scrollX}px`;
        suggestionsList.style.display = 'block';

        suggestions.forEach(suggestion => {
            const suggestionItem = document.createElement('li');
            suggestionItem.textContent = suggestion;
            suggestionItem.addEventListener('click', () => {
                input.value = suggestion;
                suggestionsList.style.display = 'none';
            });
            suggestionsList.appendChild(suggestionItem);
        });
    }
});
</script>
@endpush


@endsection
