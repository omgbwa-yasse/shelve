@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col">
                <h4 class="mb-3">{{ __('create_description') }}</h4>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('records.store') }}" method="POST">
            @csrf
            @if (!empty($record))
                <input type="hidden" name="parent_id" value="{{$record->id}}">
            @endif

            <div class="row">
                <!-- Left Column -->
                <div class="col-md-8">
                    <!-- Main Information Card -->
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white py-2">
                            <h5 class="card-title mb-0 small">{{ __('identification') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-2">
                                    <label class="form-label small">{{ __('level') }}</label>
                                    <select name="level_id" class="form-select form-select-sm" required>
                                        @foreach ($levels as $level)
                                            <option value="{{ $level->id }}">{{ $level->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">{{ __('support') }}</label>
                                    <select name="support_id" class="form-select form-select-sm" required>
                                        @foreach ($supports as $support)
                                            <option value="{{ $support->id }}">{{ $support->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">{{ __('code') }}</label>
                                    <input type="text" name="code" class="form-control form-control-sm" required maxlength="10">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small">{{ __('status') }}</label>
                                    <select name="status_id" class="form-select form-select-sm" required>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-12">
                                    <label class="form-label small">{{ __('name') }}</label>
                                    <textarea name="name" class="form-control form-control-sm" rows="2" required></textarea>
                                </div>
                            </div>

                            <div class="row mt-2 g-2">
                                <div class="col-md-4">
                                    <label class="form-label small">{{ __('date_start') }}</label>
                                    <input type="text" name="date_start" class="form-control form-control-sm" maxlength="10">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">{{ __('date_end') }}</label>
                                    <input type="text" name="date_end" class="form-control form-control-sm" maxlength="10">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">{{ __('date_exact') }}</label>
                                    <input type="date" name="date_exact" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row mt-2 g-2">
                                <div class="col-md-3">
                                    <label class="form-label small">{{ __('width') }}</label>
                                    <input type="number" name="width" class="form-control form-control-sm" step="0.01">
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label small">{{ __('width_description') }}</label>
                                    <input type="text" name="width_description" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Context Card -->
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white py-2">
                            <h5 class="card-title mb-0 small">{{ __('context') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label small">{{ __('producers') }}</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control" id="selected-authors-display" readonly>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#authorModal">
                                            {{ __('select') }}
                                        </button>
                                    </div>
                                    <input type="hidden" name="author_ids[]" id="author-ids">
                                </div>
                            </div>

                            <div class="mt-2">
                                <label class="form-label small">{{ __('biographical_history') }}</label>
                                <textarea name="biographical_history" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-4">
                    <!-- Indexing Card -->
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white py-2">
                            <h5 class="card-title mb-0 small">{{ __('indexing') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <label class="form-label small">{{ __('thesaurus') }}</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" id="selected-terms-display" readonly>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#termModal">
                                        {{ __('select') }}
                                    </button>
                                </div>
                                <input type="hidden" name="term_ids[]" id="term-ids">
                            </div>

                            <div class="mb-2">
                                <label class="form-label small">{{ __('activities') }}</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" id="selected-activity-display" readonly>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#activityModal">
                                        {{ __('select') }}
                                    </button>
                                </div>
                                <input type="hidden" name="activity_id" id="activity-id">
                            </div>
                        </div>
                    </div>

                    <!-- Notes Card -->
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white py-2">
                            <h5 class="card-title mb-0 small">{{ __('notes') }}</h5>
                        </div>
                        <div class="card-body">
                            <textarea name="note" class="form-control form-control-sm" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">{{ __('create') }}</button>
                    <a href="{{ route('records.index') }}" class="btn btn-secondary">{{ __('cancel') }}</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Modals -->
    @include('records.partials.author_modal')
    @include('records.partials.term_modal')
    @include('records.partials.activity_modal')

    <style>
        .form-label { margin-bottom: 0.2rem; }
        .card-body { padding: 1rem; }
        .form-control-sm, .form-select-sm { padding: 0.25rem 0.5rem; }
        .input-group-sm > .form-control { padding: 0.25rem 0.5rem; }
        .btn-sm { padding: 0.25rem 0.5rem; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fonction pour filtrer les éléments d'une liste
            function filterList(searchInput, listItems) {
                const filter = searchInput.value.toLowerCase();
                listItems.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(filter) ? '' : 'none';
                });
            }

            // Configuration des modals
            const modals = [
                {
                    modalId: 'authorModal',
                    searchId: 'author-search',
                    listId: 'author-list',
                    displayId: 'selected-authors-display',
                    hiddenInputId: 'author-ids',
                    saveButtonId: 'save-authors',
                    multiSelect: true
                },
                {
                    modalId: 'termModal',
                    searchId: 'term-search',
                    listId: 'term-list',
                    displayId: 'selected-terms-display',
                    hiddenInputId: 'term-ids',
                    saveButtonId: 'save-terms',
                    multiSelect: true
                },
                {
                    modalId: 'activityModal',
                    searchId: 'activity-search',
                    listId: 'activity-list',
                    displayId: 'selected-activity-display',
                    hiddenInputId: 'activity-id',
                    saveButtonId: 'save-activity',
                    multiSelect: false
                }
            ];

            // Initialiser chaque modal
            modals.forEach(config => {
                const modal = document.getElementById(config.modalId);
                const search = document.getElementById(config.searchId);
                const list = document.getElementById(config.listId);
                const saveButton = document.getElementById(config.saveButtonId);
                const displayInput = document.getElementById(config.displayId);
                const hiddenInput = document.getElementById(config.hiddenInputId);

                if (!modal || !search || !list || !saveButton || !displayInput || !hiddenInput) return;

                const items = list.querySelectorAll('.list-group-item');

                // Recherche
                search.addEventListener('input', () => filterList(search, items));

                // Sélection des items
                items.forEach(item => {
                    item.addEventListener('click', (e) => {
                        e.preventDefault();
                        if (config.multiSelect) {
                            item.classList.toggle('active');
                        } else {
                            items.forEach(i => i.classList.remove('active'));
                            item.classList.add('active');
                        }
                    });
                });

                // Sauvegarde
                saveButton.addEventListener('click', () => {
                    const selectedItems = list.querySelectorAll('.list-group-item.active');
                    const selectedNames = Array.from(selectedItems).map(item => item.textContent.trim());
                    const selectedIds = Array.from(selectedItems).map(item => item.dataset.id);

                    displayInput.value = selectedNames.join('; ');
                    hiddenInput.value = selectedIds.join(',');

                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    modalInstance.hide();
                });
            });
        });
    </script>
@endsection
