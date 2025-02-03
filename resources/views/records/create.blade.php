@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('create_description') }} (light) </h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('records.store') }}" method="POST" class="mb-4">
        @csrf
        @if (!empty($record))
            <input type="hidden" name="parent_id" value="{{$record->id}}">
        @endif

        <!-- Identification Section -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">{{ __('identification') }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="level_id" class="form-label">{{ __('level') }}</label>
                        <select name="level_id" id="level_id" class="form-select" required>
                            @foreach ($levels as $level)
                                <option value="{{ $level->id }}">{{ $level->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="support_id" class="form-label">{{ __('support') }}</label>
                        <select name="support_id" id="support_id" class="form-select" required>
                            @foreach ($supports as $support)
                                <option value="{{ $support->id }}">{{ $support->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="code" class="form-label">{{ __('code') }}</label>
                        <input type="text" name="code" id="code" class="form-control" required maxlength="10">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">{{ __('name') }}</label>
                    <textarea name="name" id="name" class="form-control" required></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="date_start" class="form-label">{{ __('date_start') }}</label>
                        <input type="text" name="date_start" id="date_start" class="form-control" maxlength="10">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="date_end" class="form-label">{{ __('date_end') }}</label>
                        <input type="text" name="date_end" id="date_end" class="form-control" maxlength="10">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="date_exact" class="form-label">{{ __('date_exact') }}</label>
                        <input type="date" name="date_exact" id="date_exact" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label for="width" class="form-label">{{ __('width') }}</label>
                        <input type="number" name="width" id="width" class="form-control" step="0.01" min="0" max="9999999999.99">
                    </div>
                    <div class="col-md-10 mb-3">
                        <label for="width_description" class="form-label">{{ __('width_description') }}</label>
                        <input type="text" name="width_description" id="width_description" class="form-control" maxlength="100">
                    </div>
                </div>
            </div>
        </div>

        <!-- Context Section -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">{{ __('context') }}</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="author" class="form-label">{{ __('producers') }}</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="selected-authors-display" readonly>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#authorModal">
                            {{ __('select') }}
                        </button>
                    </div>
                    <input type="hidden" name="author_ids[]" id="author-ids">
                </div>

                <div class="mb-3">
                    <label for="biographical_history" class="form-label">{{ __('biographical_history') }}</label>
                    <textarea name="biographical_history" id="biographical_history" class="form-control"></textarea>
                </div>

                <div class="mb-3">
                    <label for="archival_history" class="form-label">{{ __('archival_history') }}</label>
                    <textarea name="archival_history" id="archival_history" class="form-control"></textarea>
                </div>

                <div class="mb-3">
                    <label for="acquisition_source" class="form-label">{{ __('acquisition_source') }}</label>
                    <textarea name="acquisition_source" id="acquisition_source" class="form-control"></textarea>
                </div>
            </div>
        </div>

        <!-- Notes Section -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">{{ __('notes') }}</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="note" class="form-label">{{ __('note') }}</label>
                    <textarea name="note" id="note" class="form-control"></textarea>
                </div>
            </div>
        </div>

        <!-- Status Section -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">{{ __('status') }}</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="status_id" class="form-label">{{ __('status') }}</label>
                    <select name="status_id" id="status_id" class="form-select" required>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Indexation Section -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">{{ __('indexing') }}</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="term_id" class="form-label">{{ __('thesaurus') }}</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="selected-terms-display" readonly>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#termModal">
                            {{ __('select') }}
                        </button>
                    </div>
                    <input type="hidden" name="term_ids[]" id="term-ids">
                </div>

                <div class="mb-3">
                    <label for="activity_id" class="form-label">{{ __('activities') }}</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="selected-activity-display" readonly>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#activityModal">
                            {{ __('select') }}
                        </button>
                    </div>
                    <input type="hidden" name="activity_id" id="activity-id">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">{{ __('create') }}</button>
    </form>

    <!-- Modals -->
    @include('records.partials.author_modal')
    @include('records.partials.term_modal')
    @include('records.partials.activity_modal')
</div>

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

    // Configuration des modals
    modals.forEach(config => {
        const modal = document.getElementById(config.modalId);
        const search = document.getElementById(config.searchId);
        const list = document.getElementById(config.listId);
        const items = list.querySelectorAll('.list-group-item');
        const saveButton = document.getElementById(config.saveButtonId);
        const displayInput = document.getElementById(config.displayId);
        const hiddenInput = document.getElementById(config.hiddenInputId);

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
            bootstrap.Modal.getInstance(modal).hide();
        });
    });
});
</script>
@endsection
