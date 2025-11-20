@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-book"></i> {{ __('Nouveau livre') }}</h1>
        <a href="{{ route('library.books.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('library.books.store') }}">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="title" class="form-label">{{ __('Titre') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="subtitle" class="form-label">{{ __('Sous-titre') }}</label>
                        <input type="text" class="form-control @error('subtitle') is-invalid @enderror"
                               id="subtitle" name="subtitle" value="{{ old('subtitle') }}">
                        @error('subtitle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="isbn" class="form-label">{{ __('ISBN') }}</label>
                        <input type="text" class="form-control @error('isbn') is-invalid @enderror"
                               id="isbn" name="isbn" value="{{ old('isbn') }}">
                        @error('isbn')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="publication_year" class="form-label">{{ __('Année de publication') }}</label>
                        <input type="number" class="form-control @error('publication_year') is-invalid @enderror"
                               id="publication_year" name="publication_year" value="{{ old('publication_year') }}" min="1000" max="{{ date('Y') + 5 }}">
                        @error('publication_year')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="edition" class="form-label">{{ __('Édition') }}</label>
                        <input type="text" class="form-control @error('edition') is-invalid @enderror"
                               id="edition" name="edition" value="{{ old('edition') }}">
                        @error('edition')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="publishers" class="form-label">{{ __('Éditeurs') }}</label>
                        <div class="input-group">
                            <select name="publishers[]" id="publishers" class="form-select @error('publishers') is-invalid @enderror" multiple>
                                @foreach($publishers as $publisher)
                                    <option value="{{ $publisher->id }}" {{ in_array($publisher->id, old('publishers', [])) ? 'selected' : '' }}>
                                        {{ $publisher->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-secondary" onclick="openSelectionModal('publishers')">
                                <i class="bi bi-three-dots"></i>
                            </button>
                        </div>
                        @error('publishers')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="series_id" class="form-label">{{ __('Collection') }}</label>
                        <div class="input-group">
                            <select name="series_id" id="series_id" class="form-select @error('series_id') is-invalid @enderror">
                                <option value="">{{ __('Sélectionner une collection') }}</option>
                                @foreach($series as $s)
                                    <option value="{{ $s->id }}" {{ old('series_id') == $s->id ? 'selected' : '' }}>
                                        {{ $s->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-secondary" onclick="openSelectionModal('series')">
                                <i class="bi bi-three-dots"></i>
                            </button>
                        </div>
                        @error('series_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="language_id" class="form-label">{{ __('Langue') }}</label>
                        <select name="language_id" id="language_id" class="form-select @error('language_id') is-invalid @enderror">
                            <option value="">{{ __('Sélectionner') }}</option>
                            @foreach($languages as $language)
                                <option value="{{ $language->id }}" {{ old('language_id') == $language->id ? 'selected' : '' }}>
                                    {{ $language->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="format_id" class="form-label">{{ __('Format') }}</label>
                        <select name="format_id" id="format_id" class="form-select @error('format_id') is-invalid @enderror">
                            <option value="">{{ __('Sélectionner') }}</option>
                            @foreach($formats as $format)
                                <option value="{{ $format->id }}" {{ old('format_id') == $format->id ? 'selected' : '' }}>
                                    {{ $format->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="binding_id" class="form-label">{{ __('Reliure') }}</label>
                        <select name="binding_id" id="binding_id" class="form-select @error('binding_id') is-invalid @enderror">
                            <option value="">{{ __('Sélectionner') }}</option>
                            @foreach($bindings as $binding)
                                <option value="{{ $binding->id }}" {{ old('binding_id') == $binding->id ? 'selected' : '' }}>
                                    {{ $binding->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="pages" class="form-label">{{ __('Pages') }}</label>
                        <input type="number" class="form-control @error('pages') is-invalid @enderror"
                               id="pages" name="pages" value="{{ old('pages') }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="dewey" class="form-label">{{ __('Classification Dewey') }}</label>
                        <input type="text" class="form-control @error('dewey') is-invalid @enderror"
                               id="dewey" name="dewey" value="{{ old('dewey') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="dimensions" class="form-label">{{ __('Dimensions') }}</label>
                        <input type="text" class="form-control @error('dimensions') is-invalid @enderror"
                               id="dimensions" name="dimensions" value="{{ old('dimensions') }}" placeholder="Ex: 15x23 cm">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">{{ __('Description / Résumé') }}</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="4">{{ old('description') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">{{ __('Notes internes') }}</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror"
                              id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> {{ __('Enregistrer le livre') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #ced4da;
        min-height: 38px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Configuration commune pour Select2 AJAX
        function getSelect2Config(type, placeholder) {
            return {
                placeholder: placeholder,
                allowClear: true,
                ajax: {
                    url: '{{ route("library.search.autocomplete") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term,
                            type: type
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                },
                minimumInputLength: 3
            };
        }

        // Initialisation des champs Select2
        $('#publishers').select2(getSelect2Config('publishers', '{{ __("Rechercher un éditeur...") }}'));
        $('#language_id').select2(getSelect2Config('languages', '{{ __("Rechercher une langue...") }}'));
        $('#format_id').select2(getSelect2Config('formats', '{{ __("Rechercher un format...") }}'));
        $('#binding_id').select2(getSelect2Config('bindings', '{{ __("Rechercher une reliure...") }}'));
        $('#series_id').select2(getSelect2Config('series', '{{ __("Rechercher une collection...") }}'));
    });

    // Modal functions
    let currentModalType = null;

    function openSelectionModal(type) {
        currentModalType = type;
        const modalTitle = type === 'publishers' ? 'Sélectionner un éditeur' : 'Sélectionner une collection';

        $('#selectionModalLabel').text(modalTitle);
        $('#modalItemsList').html('<div class="text-center"><i class="bi bi-hourglass-split"></i> Chargement...</div>');
        $('#selectionModal').modal('show');

        // Load data
        $.get('{{ route("library.books.modal.data") }}', { type: type })
            .done(function(data) {
                renderModalItems(data, type);
            })
            .fail(function() {
                $('#modalItemsList').html('<div class="alert alert-danger">Erreur de chargement</div>');
            });
    }

    function renderModalItems(data, type) {
        let html = '';

        for (const [letter, items] of Object.entries(data)) {
            html += `<div class="letter-group mb-3">
                        <h5 class="text-primary border-bottom">${letter}</h5>
                        <div class="list-group">`;

            items.forEach(item => {
                const displayName = type === 'series' && item.publisher
                    ? `${item.name} (${item.publisher.name})`
                    : item.name;
                html += `<a href="#" class="list-group-item list-group-item-action" onclick="selectModalItem(${item.id}, '${item.name.replace(/'/g, "\\'")}'); return false;">
                            ${displayName}
                         </a>`;
            });

            html += `</div></div>`;
        }

        $('#modalItemsList').html(html);
    }

    function selectModalItem(id, name) {
        const selectId = currentModalType === 'publishers' ? '#publishers' : '#series_id';

        // Check if option exists
        if ($(selectId + ' option[value="' + id + '"]').length === 0) {
            // Add new option
            const newOption = new Option(name, id, true, true);
            $(selectId).append(newOption);
        }

        // Select the option
        if (currentModalType === 'publishers') {
            let selected = $(selectId).val() || [];
            if (!selected.includes(id.toString())) {
                selected.push(id.toString());
                $(selectId).val(selected);
            }
        } else {
            $(selectId).val(id);
        }

        $(selectId).trigger('change');
        $('#selectionModal').modal('hide');
    }

    function showCreateForm() {
        $('#itemsList').hide();
        $('#createForm').show();
        $('#newItemName').val('').focus();
    }

    function cancelCreate() {
        $('#createForm').hide();
        $('#itemsList').show();
    }

    function saveNewItem() {
        const name = $('#newItemName').val().trim();

        if (!name) {
            alert('Le nom est requis');
            return;
        }

        $.post('{{ route("library.books.modal.store") }}', {
            _token: '{{ csrf_token() }}',
            type: currentModalType,
            name: name
        })
        .done(function(data) {
            // Add to select
            const selectId = currentModalType === 'publishers' ? '#publishers' : '#series_id';
            const newOption = new Option(data.text, data.id, true, true);
            $(selectId).append(newOption).trigger('change');

            // Close modal
            $('#selectionModal').modal('hide');
            cancelCreate();
        })
        .fail(function(xhr) {
            const error = xhr.responseJSON?.error || 'Erreur lors de la création';
            alert(error);
        });
    }
</script>

<!-- Modal -->
<div class="modal fade" id="selectionModal" tabindex="-1" aria-labelledby="selectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="selectionModalLabel">Sélection</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="itemsList">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <input type="text" id="modalSearch" class="form-control me-2" placeholder="Rechercher...">
                        <button type="button" class="btn btn-primary" onclick="showCreateForm()">
                            <i class="bi bi-plus-circle"></i> Nouveau
                        </button>
                    </div>
                    <div id="modalItemsList" style="max-height: 400px; overflow-y: auto;"></div>
                </div>

                <div id="createForm" style="display: none;">
                    <div class="mb-3">
                        <label for="newItemName" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="newItemName" placeholder="Entrez le nom...">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" onclick="saveNewItem()">
                            <i class="bi bi-save"></i> Enregistrer
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="cancelCreate()">
                            Annuler
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush
