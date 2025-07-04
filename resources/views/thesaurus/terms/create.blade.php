@extends('layouts.app')

@section('title', 'Créer un terme')

@section('content')
    <div class="container-fluid">
        <h1>Créer un nouveau terme</h1>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('terms.store') }}" method="POST">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="preferred_label">Libellé préféré <span class="text-danger">*</span></label>
                                <input type="text" name="preferred_label" id="preferred_label" class="form-control @error('preferred_label') is-invalid @enderror" value="{{ old('preferred_label') }}" required>
                                @error('preferred_label')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="language">Langue <span class="text-danger">*</span></label>
                                <select name="language" id="language" class="form-control @error('language') is-invalid @enderror" required>
                                    @foreach($languages as $code => $name)
                                        <option value="{{ $code }}" {{ old('language') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('language')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">Statut <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                    @foreach($statuses as $code => $name)
                                        <option value="{{ $code }}" {{ old('status') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category">Catégorie / Domaine</label>
                                <input type="text" name="category" id="category" class="form-control @error('category') is-invalid @enderror" value="{{ old('category') }}">
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="notation">Notation / Code</label>
                                <input type="text" name="notation" id="notation" class="form-control @error('notation') is-invalid @enderror" value="{{ old('notation') }}">
                                @error('notation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group pt-4 mt-2">
                                <div class="form-check">
                                    <input type="checkbox" name="is_top_term" id="is_top_term" class="form-check-input" {{ old('is_top_term') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_top_term">Terme de tête</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="definition">Définition</label>
                                <textarea name="definition" id="definition" class="form-control @error('definition') is-invalid @enderror" rows="3">{{ old('definition') }}</textarea>
                                @error('definition')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="scope_note">Note d'application</label>
                                <textarea name="scope_note" id="scope_note" class="form-control @error('scope_note') is-invalid @enderror" rows="3">{{ old('scope_note') }}</textarea>
                                @error('scope_note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="history_note">Note historique</label>
                                <textarea name="history_note" id="history_note" class="form-control @error('history_note') is-invalid @enderror" rows="3">{{ old('history_note') }}</textarea>
                                @error('history_note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="example">Exemple</label>
                                <textarea name="example" id="example" class="form-control @error('example') is-invalid @enderror" rows="3">{{ old('example') }}</textarea>
                                @error('example')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="editorial_note">Note éditoriale</label>
                                <textarea name="editorial_note" id="editorial_note" class="form-control @error('editorial_note') is-invalid @enderror" rows="3">{{ old('editorial_note') }}</textarea>
                                @error('editorial_note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Créer</button>
                        <a href="{{ route('terms.index') }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
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
@endpush
