@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-dark">
                            <i class="fas fa-user-plus me-2 text-primary"></i>
                            {{ __('add_new_author') }}
                        </h4>
                        <a href="{{ route('mail-author.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i>{{ __('back_to_authors') }}
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('mail-author.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type_id" class="form-label fw-medium">
                                        <i class="fas fa-tag me-1 text-muted"></i>{{ __('entity_type') }}
                                    </label>
                                    <div class="select-with-search">
                                        <div class="input-group mb-2">
                                            <span class="input-group-text">
                                                <i class="fas fa-search"></i>
                                            </span>
                                            <input type="text" class="form-control search-input" 
                                                   placeholder="{{ __('search_type') }}">
                                        </div>
                                        <select id="type_id" name="type_id" class="form-select" required>
                                            <option value="">{{ __('select_type') }}</option>
                                            @foreach ($authorTypes as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label fw-medium">
                                        <i class="fas fa-user me-1 text-muted"></i>{{ __('name') }}
                                    </label>
                                    <input type="text" id="name" name="name" class="form-control" 
                                           data-field="name" required>
                                </div>

                                <div class="mb-3">
                                    <label for="parallel_name" class="form-label fw-medium">
                                        <i class="fas fa-user-tag me-1 text-muted"></i>{{ __('parallel_name') }}
                                    </label>
                                    <input type="text" id="parallel_name" name="parallel_name" class="form-control" 
                                           data-field="parallel_name">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="other_name" class="form-label fw-medium">
                                        <i class="fas fa-user-plus me-1 text-muted"></i>{{ __('other_name') }}
                                    </label>
                                    <input type="text" id="other_name" name="other_name" class="form-control" 
                                           data-field="other_name">
                                </div>

                                <div class="mb-3">
                                    <label for="lifespan" class="form-label fw-medium">
                                        <i class="fas fa-calendar me-1 text-muted"></i>{{ __('lifespan') }}
                                    </label>
                                    <input type="text" id="lifespan" name="lifespan" class="form-control" 
                                           placeholder="ex: 1920-1990">
                                </div>

                                <div class="mb-3">
                                    <label for="locations" class="form-label fw-medium">
                                        <i class="fas fa-map-marker-alt me-1 text-muted"></i>{{ __('residence') }}
                                    </label>
                                    <input type="text" id="locations" name="locations" class="form-control" 
                                           data-field="locations">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="parent_id" class="form-label fw-medium">
                                <i class="fas fa-sitemap me-1 text-muted"></i>{{ __('parent_entity') }}
                            </label>
                            <div class="select-with-search">
                                <div class="input-group mb-2">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control search-input" 
                                           placeholder="{{ __('search_parent_author') }}">
                                </div>
                                <select id="parent_id" name="parent_id" class="form-select">
                                    <option value="">{{ __('select_parent') }}</option>
                                    @foreach ($parents as $parent)
                                        <option value="{{ $parent->id }}">
                                            {{ $parent->name }} <i>({{ $parent->authorType->name }})</i>
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('mail-author.index') }}" class="btn btn-outline-secondary">
                                {{ __('cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>{{ __('save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

    <style>
        .select-with-search {
            position: relative;
        }
        .select-with-search .search-input {
            border-top-right-radius: 0.25rem;
            border-bottom-right-radius: 0.25rem;
        }
        .select-with-search .form-select {
            border-color: #ced4da;
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-color: #ced4da;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectWithSearchElements = document.querySelectorAll('.select-with-search');

            selectWithSearchElements.forEach(selectWithSearch => {
                const searchInput = selectWithSearch.querySelector('.search-input');
                const select = selectWithSearch.querySelector('select');
                const options = Array.from(select.options).slice(1); // Exclude the first option

                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();

                    options.forEach(option => {
                        const optionText = option.textContent.toLowerCase();
                        if (optionText.includes(searchTerm)) {
                            option.style.display = '';
                        } else {
                            option.style.display = 'none';
                        }
                    });

                    // Reset selection and show placeholder option
                    select.selectedIndex = 0;
                    select.options[0].style.display = '';

                    // If no visible options, show a "No results" option
                    const visibleOptions = options.filter(option => option.style.display !== 'none');
                    if (visibleOptions.length === 0) {
                        const noResultsOption = select.querySelector('option[data-no-results]');
                        if (!noResultsOption) {
                            const newNoResultsOption = document.createElement('option');
                            newNoResultsOption.textContent = '{{ __("no_results_found") }}';
                            newNoResultsOption.disabled = true;
                            newNoResultsOption.setAttribute('data-no-results', 'true');
                            select.appendChild(newNoResultsOption);
                        } else {
                            noResultsOption.style.display = '';
                        }
                    } else {
                        const noResultsOption = select.querySelector('option[data-no-results]');
                        if (noResultsOption) {
                            noResultsOption.style.display = 'none';
                        }
                    }
                });

                // Clear search input when select changes
                select.addEventListener('change', function() {
                    searchInput.value = '';
                    options.forEach(option => option.style.display = '');
                    const noResultsOption = select.querySelector('option[data-no-results]');
                    if (noResultsOption) {
                        noResultsOption.style.display = 'none';
                    }
                });
            });
        });
    </script>
@endsection
