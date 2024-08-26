@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h2>Add New Author</h2>
        <form action="{{ route('mail-author.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="type_id" class="form-label">Type</label>
                <select id="type_id" name="type_id" class="form-control" required>
                    <option value="" disabled selected>Enter the type</option>
                    @foreach ($authorTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name" class="form-control" data-field="name" required>
            </div>

            <div class="mb-3">
                <label for="parallel_name" class="form-label">Parallel Name</label>
                <input type="text" id="parallel_name" name="parallel_name" class="form-control" data-field="parallel_name">
            </div>

            <div class="mb-3">
                <label for="other_name" class="form-label">Other Name</label>
                <input type="text" id="other_name" name="other_name" class="form-control" data-field="other_name">
            </div>

            <div class="mb-3">
                <label for="lifespan" class="form-label">Lifespan</label>
                <input type="text" id="lifespan" name="lifespan" class="form-control">
            </div>

            <div class="mb-3">
                <label for="locations" class="form-label">Locations</label>
                <input type="text" id="locations" name="locations" class="form-control" data-field="locations">
            </div>

            <div class="mb-3">
                <label for="parent_id" class="form-label">Parent Author</label>
                <div class="select-with-search">
                    <div class="input-group mb-2">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control search-input" placeholder="Search parent author...">
                    </div>
                    <select id="parent_id" name="parent_id" class="form-control">
                        <option value="" disabled selected>Enter the parent author</option>
                        @foreach ($parents as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }} <i>({{ $parent->authorType->name }})</i></option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Create Author</button>
        </form>
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
                            newNoResultsOption.textContent = 'No results found';
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
