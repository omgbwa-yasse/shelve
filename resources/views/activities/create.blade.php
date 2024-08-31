@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="">
            <h1 class="mb-0">Create Activity</h1>
        </div>
        <div class="card-body">
            <form action="{{ route('activities.store') }}" method="POST">
                @csrf
                <div class="form-group mb-4">
                    <label for="code" class="form-label"><i class="bi bi-barcode"></i> Code</label>
                    <input type="text" name="code" id="code" class="form-control form-control-lg" required>
                </div>
                <div class="form-group mb-4">
                    <label for="name" class="form-label"><i class="bi bi-tag"></i> Name</label>
                    <input type="text" name="name" id="name" class="form-control form-control-lg" required>
                </div>
                <div class="form-group mb-4">
                    <label for="observation" class="form-label"><i class="bi bi-chat-left-text"></i> Observation</label>
                    <textarea name="observation" id="observation" class="form-control form-control-lg"></textarea>
                </div>
                <div class="form-group mb-4">
                    <label for="parent_id" class="form-label"><i class="bi bi-diagram-3"></i> Parent ID</label>
                    <div class="select-with-search">
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control search-input" placeholder="Search parent...">
                        </div>
                        <select name="parent_id" id="parent_id" class="form-control form-control-lg">
                            <option value="">None</option>
                            @foreach ($parents as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block">Create</button>
            </form>
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
            const selectWithSearch = document.querySelector('.select-with-search');
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
    </script>
@endsection
