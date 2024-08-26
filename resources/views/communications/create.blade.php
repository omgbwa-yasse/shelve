@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Communication</h1>
        <form action="{{ route('transactions.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" class="form-control" id="code" name="code" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="operator_id" class="form-label">Operator</label>
                    <div class="select-with-search">
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control search-input" placeholder="Search operator...">
                        </div>
                        <select class="form-select" id="operator_id" name="operator_id" required>
                            <option value="" disabled selected>Enter the operator</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="operator_organisation_id" class="form-label">Operator organisation</label>
                    <div class="select-with-search">
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control search-input" placeholder="Search operator organisation...">
                        </div>
                        <select class="form-select" id="operator_organisation_id" name="operator_organisation_id" required>
                            <option value="" disabled selected>Enter the operator organisation</option>
                            @foreach ($organisations as $organisation)
                                <option value="{{ $organisation->id }}" {{ $organisation->user_id == $organisation->id ? 'selected' : '' }}>{{ $organisation->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="user_id" class="form-label">User</label>
                    <div class="select-with-search">
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control search-input" placeholder="Search user...">
                        </div>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="" disabled selected>Enter the user</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="user_organisation_id" class="form-label">User organisation</label>
                    <div class="select-with-search">
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control search-input" placeholder="Search user organisation...">
                        </div>
                        <select class="form-select" id="user_organisation_id" name="user_organisation_id" required>
                            <option value="" disabled selected>Enter the user organisation</option>
                            @foreach ($organisations as $organisation)
                                <option value="{{ $organisation->id }}" {{ $organisation->user_id == $organisation->id ? 'selected' : '' }}>{{ $organisation->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="return_date" class="form-label">Return Date</label>
                    <input type="datetime-local" class="form-control" id="return_date" name="return_date" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="return_effective" class="form-label">Return Effective</label>
                    <input type="date" class="form-control" id="return_effective" name="return_effective">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="status_id" class="form-label">Status</label>
                    <div class="select-with-search">
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control search-input" placeholder="Search status...">
                        </div>
                        <select class="form-select" id="status_id" name="status_id" required>
                            <option value="" disabled selected>Enter the status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Create</button>
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
