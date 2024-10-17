@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="">
            <div class="">
                <h2 class="mb-0">Créer un contenant d'archives</h2>
            </div>
            <div class="card-body">
                <form action="{{ route('containers.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="code" class="form-label">Code</label>
                        <input type="text" class="form-control" id="code" name="code" required>
                    </div>
                    <div class="mb-3">
                        <label for="shelve_id" class="form-label">Shelf</label>
                        <div class="select-with-search">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control search-input" placeholder="Search shelf...">
                            </div>
                            <select class="form-select mt-2" id="shelve_id" name="shelve_id" required>
                                <option value="">Select a shelf</option>
                                @foreach ($shelves as $shelf)
                                    <option value="{{ $shelf->id }}">{{ $shelf->code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="status_id" class="form-label">Status</label>
                        <div class="select-with-search">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control search-input" placeholder="Search status...">
                            </div>
                            <select class="form-select mt-2" id="status_id" name="status_id" required>
                                <option value="">Select a status</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="property_id" class="form-label">Property</label>
                        <div class="select-with-search">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control search-input" placeholder="Search property...">
                            </div>
                            <select class="form-select mt-2" id="property_id" name="property_id" required>
                                <option value="">Select a property</option>
                                @foreach ($properties as $property)
                                    <option value="{{ $property->id }}">{{ $property->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Create Container</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .card {
            border-radius: 0;
        }
        .card-header {
            border-bottom: none;
        }
        .form-label {
            font-weight: 600;
        }
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
            const selectWithSearch = document.querySelectorAll('.select-with-search');

            selectWithSearch.forEach(container => {
                const searchInput = container.querySelector('.search-input');
                const select = container.querySelector('select');
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
