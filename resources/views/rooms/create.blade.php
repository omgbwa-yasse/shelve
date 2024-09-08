@extends('layouts.app')

@section('content')
    <div class="container-fluid p-0">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2 class="mb-0">Create Room</h2>
            </div>
            <div class="card-body">
                <form action="{{ route('rooms.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="code" class="form-label">Code</label>
                        <input type="text" class="form-control" id="code" name="code" required>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="floor_id" class="form-label">Choisir le niveau</label>
                        <div class="select-with-search">
                        <select class="form-select" id="floor_id" name="floor_id" required>
                                @foreach ($floors as $floor)
                                    <option value="{{ $floor->id }}" data-building="{{ $floor->building->name }}">
                                        {{ $floor->name }} ({{ $floor->building->name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="type_id" class="form-label">Type de local</label>
                        <div class="select-with-search">
                            <select class="form-select" id="type_id" name="type_id" required>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">
                                        @if( $type->name == "archives")
                                            Salle d'archives
                                        @elseif($type->name == "producer")
                                            Local tampon (service producteur)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Create Room</button>
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
            const selectWithSearch = document.querySelector('.select-with-search');
            const searchInput = selectWithSearch.querySelector('.search-input');
            const select = selectWithSearch.querySelector('select');
            const options = Array.from(select.options).slice(1); // Exclude the first option

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();

                options.forEach(option => {
                    const optionText = option.textContent.toLowerCase();
                    const buildingName = option.getAttribute('data-building').toLowerCase();
                    if (optionText.includes(searchTerm) || buildingName.includes(searchTerm)) {
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
