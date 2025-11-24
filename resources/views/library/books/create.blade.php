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
            <form method="POST" action="{{ route('library.books.store') }}" id="bookForm">
                @csrf

                {{-- ZONE 1 - Titres --}}
                <h5 class="border-bottom pb-2 mb-3"><i class="bi bi-card-heading"></i> Zone 1 - Titres et mentions de responsabilité</h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="title" class="form-label">{{ __('Titre principal') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="subtitle" class="form-label">{{ __('Sous-titre / Complément de titre') }}</label>
                        <input type="text" class="form-control @error('subtitle') is-invalid @enderror"
                               id="subtitle" name="subtitle" value="{{ old('subtitle') }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="titre_parallele" class="form-label">{{ __('Titre parallèle') }}</label>
                        <input type="text" class="form-control" id="titre_parallele" name="titre_parallele" value="{{ old('titre_parallele') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="titre_cle" class="form-label">{{ __('Titre clé') }}</label>
                        <input type="text" class="form-control" id="titre_cle" name="titre_cle" value="{{ old('titre_cle') }}">
                    </div>
                </div>

                {{-- Auteurs / Responsabilités --}}
                <div class="mb-3">
                    <label class="form-label">{{ __('Auteurs et responsabilités') }}</label>
                    <div id="authors-container">
                        <div class="author-item mb-2">
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <select name="authors[0][id]" class="form-select author-select" data-index="0">
                                            <option value="">{{ __('Sélectionner un auteur') }}</option>
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary" onclick="openAuthorModal(0)">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select name="authors[0][responsibility_type]" class="form-select">
                                        <option value="">Type de responsabilité</option>
                                        <option value="author">Auteur principal</option>
                                        <option value="co-author">Co-auteur</option>
                                        <option value="editor">Éditeur scientifique</option>
                                        <option value="translator">Traducteur</option>
                                        <option value="illustrator">Illustrateur</option>
                                        <option value="preface">Préfacier</option>
                                        <option value="contributor">Contributeur</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="authors[0][function]" class="form-control" placeholder="Fonction spécifique">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-success add-author" title="Ajouter un auteur">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ZONE 2 - Édition --}}
                <h5 class="border-bottom pb-2 mb-3 mt-4"><i class="bi bi-journal"></i> Zone 2 - Édition</h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="mention_edition" class="form-label">{{ __('Mention d\'édition') }}</label>
                        <input type="text" class="form-control" id="mention_edition" name="mention_edition"
                               value="{{ old('mention_edition') }}" placeholder="Ex: 2e édition revue et augmentée">
                    </div>
                    <div class="col-md-6">
                        <label for="mention_resp_edition" class="form-label">{{ __('Mention de responsabilité d\'édition') }}</label>
                        <input type="text" class="form-control" id="mention_resp_edition" name="mention_resp_edition" value="{{ old('mention_resp_edition') }}">
                    </div>
                </div>

                {{-- ZONE 4 - Publication --}}
                <h5 class="border-bottom pb-2 mb-3 mt-4"><i class="bi bi-building"></i> Zone 4 - Adresse bibliographique</h5>

                {{-- Éditeurs --}}
                <div class="mb-3">
                    <label class="form-label">{{ __('Éditeurs') }}</label>
                    <div id="publishers-container">
                        <div class="publisher-item mb-2">
                            <div class="row g-2">
                                <div class="col-md-10">
                                    <div class="input-group">
                                        <select name="publishers[]" class="form-select publisher-select" data-index="0">
                                            <option value="">{{ __('Sélectionner un éditeur') }}</option>
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary" onclick="openPublisherModal(0)">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-success add-publisher w-100" title="Ajouter un éditeur">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Lieux de publication --}}
                <div class="mb-3">
                    <label class="form-label">{{ __('Lieux de publication') }}</label>
                    <div id="places-container">
                        <div class="place-item mb-2">
                            <div class="row g-2">
                                <div class="col-md-10">
                                    <input type="text" name="publisher_places[0][place]" class="form-control" placeholder="Ville, Pays">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-success add-place w-100" title="Ajouter un lieu">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Collections --}}
                <div class="mb-3">
                    <label class="form-label">{{ __('Collections') }}</label>
                    <div id="collections-container">
                        <div class="collection-item mb-2">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <select name="collections[0][id]" class="form-select collection-select" data-index="0">
                                            <option value="">{{ __('Sélectionner une collection') }}</option>
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary" onclick="openCollectionModal(0)">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="collections[0][collection_number]" class="form-control" placeholder="N° dans la collection">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-success add-collection w-100" title="Ajouter une collection">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="publication_year" class="form-label">{{ __('Année de publication') }}</label>
                        <input type="number" class="form-control" id="publication_year" name="publication_year"
                               value="{{ old('publication_year') }}" min="1000" max="{{ date('Y') + 5 }}">
                    </div>
                    <div class="col-md-4">
                        <label for="date_publication" class="form-label">{{ __('Date de publication complète') }}</label>
                        <input type="text" class="form-control" id="date_publication" name="date_publication" value="{{ old('date_publication') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="date_copyright" class="form-label">{{ __('Date de copyright') }}</label>
                        <input type="text" class="form-control" id="date_copyright" name="date_copyright" value="{{ old('date_copyright') }}">
                    </div>
                </div>

                {{-- ZONE 5 - Collation --}}
                <h5 class="border-bottom pb-2 mb-3 mt-4"><i class="bi bi-file-earmark-text"></i> Zone 5 - Collation (Description physique)</h5>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="pages" class="form-label">{{ __('Pages') }}</label>
                        <input type="number" class="form-control" id="pages" name="pages" value="{{ old('pages') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="importance_materielle" class="form-label">{{ __('Importance matérielle') }}</label>
                        <input type="text" class="form-control" id="importance_materielle" name="importance_materielle"
                               value="{{ old('importance_materielle') }}" placeholder="Ex: 1 vol. (XVIII-324 p.)">
                    </div>
                    <div class="col-md-3">
                        <label for="format_dimensions" class="form-label">{{ __('Format / Dimensions') }}</label>
                        <input type="text" class="form-control" id="format_dimensions" name="format_dimensions"
                               value="{{ old('format_dimensions') }}" placeholder="Ex: 24 cm">
                    </div>
                    <div class="col-md-3">
                        <label for="materiel_accompagnement" class="form-label">{{ __('Matériel d\'accompagnement') }}</label>
                        <input type="text" class="form-control" id="materiel_accompagnement" name="materiel_accompagnement" value="{{ old('materiel_accompagnement') }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="language_id" class="form-label">{{ __('Langue') }}</label>
                        <select name="language_id" id="language_id" class="form-select">
                            <option value="">{{ __('Sélectionner') }}</option>
                            @foreach($languages as $language)
                                <option value="{{ $language->id }}" {{ old('language_id') == $language->id ? 'selected' : '' }}>
                                    {{ $language->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="format_id" class="form-label">{{ __('Format') }}</label>
                        <select name="format_id" id="format_id" class="form-select">
                            <option value="">{{ __('Sélectionner') }}</option>
                            @foreach($formats as $format)
                                <option value="{{ $format->id }}" {{ old('format_id') == $format->id ? 'selected' : '' }}>
                                    {{ $format->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="binding_id" class="form-label">{{ __('Reliure') }}</label>
                        <select name="binding_id" id="binding_id" class="form-select">
                            <option value="">{{ __('Sélectionner') }}</option>
                            @foreach($bindings as $binding)
                                <option value="{{ $binding->id }}" {{ old('binding_id') == $binding->id ? 'selected' : '' }}>
                                    {{ $binding->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Classifications --}}
                <h5 class="border-bottom pb-2 mb-3 mt-4"><i class="bi bi-tags"></i> Classifications et indexation</h5>

                <div class="mb-3">
                    <label class="form-label">{{ __('Classification') }}</label>
                    <div class="input-group">
                        <select name="classification_id" class="form-select classification-select" data-index="0">
                            <option value="">{{ __('Sélectionner une classification') }}</option>
                        </select>
                        <button type="button" class="btn btn-outline-secondary" onclick="openClassificationModal(0)">
                            <i class="bi bi-three-dots"></i>
                        </button>
                    </div>
                </div>

                {{-- Termes de thésaurus --}}
                <div class="mb-3">
                    <label class="form-label">{{ __('Termes de thésaurus') }}</label>
                    <div id="thesaurus-container">
                        <div class="thesaurus-item mb-2">
                            <div class="row g-2">
                                <div class="col-md-10">
                                    <div class="input-group">
                                        <select name="thesaurus_terms[0][id]" class="form-select thesaurus-select" data-index="0">
                                            <option value="">{{ __('Sélectionner un terme') }}</option>
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary" onclick="openThesaurusModal(0)">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-success add-thesaurus w-100" title="Ajouter un terme">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ZONE 7 - Notes --}}
                <h5 class="border-bottom pb-2 mb-3 mt-4"><i class="bi bi-card-text"></i> Zone 7 - Notes</h5>

                <div class="mb-3">
                    <label for="description" class="form-label">{{ __('Résumé / Description') }}</label>
                    <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="notes_contenu" class="form-label">{{ __('Notes de contenu') }}</label>
                        <textarea class="form-control" id="notes_contenu" name="notes_contenu" rows="2">{{ old('notes_contenu') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="notes_bibliographie" class="form-label">{{ __('Notes bibliographie') }}</label>
                        <textarea class="form-control" id="notes_bibliographie" name="notes_bibliographie" rows="2">{{ old('notes_bibliographie') }}</textarea>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="notes_public_destine" class="form-label">{{ __('Public destiné') }}</label>
                        <textarea class="form-control" id="notes_public_destine" name="notes_public_destine" rows="2">{{ old('notes_public_destine') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="notes_generales" class="form-label">{{ __('Notes générales') }}</label>
                        <textarea class="form-control" id="notes_generales" name="notes_generales" rows="2">{{ old('notes_generales') }}</textarea>
                    </div>
                </div>

                {{-- ZONE 8 - Numéros --}}
                <h5 class="border-bottom pb-2 mb-3 mt-4"><i class="bi bi-upc-scan"></i> Zone 8 - Numéros normalisés</h5>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="isbn" class="form-label">{{ __('ISBN') }}</label>
                        <input type="text" class="form-control" id="isbn" name="isbn" value="{{ old('isbn') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="issn" class="form-label">{{ __('ISSN') }}</label>
                        <input type="text" class="form-control" id="issn" name="issn" value="{{ old('issn') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="ean" class="form-label">{{ __('EAN') }}</label>
                        <input type="text" class="form-control" id="ean" name="ean" value="{{ old('ean') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="numero_editeur" class="form-label">{{ __('N° éditeur') }}</label>
                        <input type="text" class="form-control" id="numero_editeur" name="numero_editeur" value="{{ old('numero_editeur') }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="prix" class="form-label">{{ __('Prix') }}</label>
                        <input type="text" class="form-control" id="prix" name="prix" value="{{ old('prix') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="disponibilite" class="form-label">{{ __('Disponibilité') }}</label>
                        <input type="text" class="form-control" id="disponibilite" name="disponibilite" value="{{ old('disponibilite') }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('library.books.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> {{ __('Annuler') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> {{ __('Enregistrer le livre') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modales de sélection --}}
@include('library.partials.selection-modal', [
    'id' => 'publisherModal',
    'title' => 'Sélectionner un éditeur',
    'type' => 'publishers',
    'searchRoute' => route('library.api.publishers.search'),
    'storeRoute' => route('library.api.publishers.store')
])

@include('library.partials.selection-modal', [
    'id' => 'collectionModal',
    'title' => 'Sélectionner une collection',
    'type' => 'series',
    'searchRoute' => route('library.api.series.search'),
    'storeRoute' => route('library.api.series.store')
])

@include('library.partials.selection-modal', [
    'id' => 'authorModal',
    'title' => 'Sélectionner un auteur',
    'type' => 'authors',
    'searchRoute' => route('library.api.authors.search'),
    'storeRoute' => route('library.api.authors.store')
])

@include('library.partials.selection-modal', [
    'id' => 'classificationModal',
    'title' => 'Sélectionner une classification',
    'type' => 'classifications',
    'searchRoute' => route('library.api.classifications.search'),
    'storeRoute' => route('library.api.classifications.store')
])

@include('library.partials.selection-modal', [
    'id' => 'thesaurusModal',
    'title' => 'Sélectionner un terme de thésaurus',
    'type' => 'thesaurus',
    'searchRoute' => route('library.api.thesaurus.search'),
    'storeRoute' => route('library.api.thesaurus.store')
])

@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }
    .repeater-item {
        position: relative;
    }
    .btn-remove-item {
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Configuration Select2 pour l'autocomplétion AJAX
    function initSelect2(selector, routeName, placeholder) {
        $(selector).select2({
            theme: 'bootstrap-5',
            placeholder: placeholder,
            allowClear: true,
            tags: true, // Permet la saisie libre
            ajax: {
                url: routeName,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        page: params.page || 1,
                        limit: 4 // Limiter à 4 résultats
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results.slice(0, 4), // S'assurer de max 4 résultats
                        pagination: {
                            more: false // Pas de pagination pour 4 résultats
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 3 // Déclencher AJAX après 3 caractères
        });
    }

    // Initialisation des Select2
    initSelect2('.publisher-select', '{{ route("library.api.publishers.search") }}', 'Rechercher un éditeur...');
    initSelect2('.collection-select', '{{ route("library.api.series.search") }}', 'Rechercher une collection...');
    initSelect2('.author-select', '{{ route("library.api.authors.search") }}', 'Rechercher un auteur...');
    initSelect2('.classification-select', '{{ route("library.api.classifications.search") }}', 'Rechercher une classification...');
    initSelect2('.thesaurus-select', '{{ route("library.api.thesaurus.search") }}', 'Rechercher un terme...');

    // Fonctions pour ouvrir les modales
    let currentModalTarget = null;

    window.openPublisherModal = function(index) {
        currentModalTarget = { type: 'publisher', index: index };
        window.initSelectionModal_publisherModal(function(item) {
            const $select = $(`.publisher-select[data-index="${index}"]`);
            const option = new Option(item.text, item.id, true, true);
            $select.append(option).trigger('change');
        });
    };

    window.openCollectionModal = function(index) {
        currentModalTarget = { type: 'collection', index: index };
        window.initSelectionModal_collectionModal(function(item) {
            const $select = $(`.collection-select[data-index="${index}"]`);
            const option = new Option(item.text, item.id, true, true);
            $select.append(option).trigger('change');
        });
    };

    window.openAuthorModal = function(index) {
        currentModalTarget = { type: 'author', index: index };
        window.initSelectionModal_authorModal(function(item) {
            const $select = $(`.author-select[data-index="${index}"]`);
            const option = new Option(item.text, item.id, true, true);
            $select.append(option).trigger('change');
        });
    };

    window.openClassificationModal = function(index) {
        currentModalTarget = { type: 'classification', index: index };
        window.initSelectionModal_classificationModal(function(item) {
            const $select = $('.classification-select');
            const option = new Option(item.text, item.id, true, true);
            $select.append(option).trigger('change');
        });
    };

    window.openThesaurusModal = function(index) {
        currentModalTarget = { type: 'thesaurus', index: index };
        window.initSelectionModal_thesaurusModal(function(item) {
            const $select = $(`.thesaurus-select[data-index="${index}"]`);
            const option = new Option(item.text, item.id, true, true);
            $select.append(option).trigger('change');
        });
    };

    // Multiplication des champs - Éditeurs
    let publisherIndex = 1;
    $(document).on('click', '.add-publisher', function() {
        const template = `
            <div class="publisher-item mb-2">
                <div class="row g-2">
                    <div class="col-md-10">
                        <div class="input-group">
                            <select name="publishers[]" class="form-select publisher-select" data-index="${publisherIndex}">
                                <option value="">{{ __('Sélectionner un éditeur') }}</option>
                            </select>
                            <button type="button" class="btn btn-outline-secondary" onclick="openPublisherModal(${publisherIndex})">
                                <i class="bi bi-three-dots"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-publisher w-100" title="Supprimer">
                            <i class="bi bi-dash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#publishers-container').append(template);
        initSelect2(`.publisher-select[data-index="${publisherIndex}"]`, '{{ route("library.api.publishers.search") }}', 'Rechercher un éditeur...');
        publisherIndex++;
    });

    $(document).on('click', '.remove-publisher', function() {
        $(this).closest('.publisher-item').remove();
    });

    // Multiplication des champs - Auteurs
    let authorIndex = 1;
    $(document).on('click', '.add-author', function() {
        const template = `
            <div class="author-item mb-2">
                <div class="row g-2">
                    <div class="col-md-5">
                        <div class="input-group">
                            <select name="authors[${authorIndex}][id]" class="form-select author-select" data-index="${authorIndex}">
                                <option value="">{{ __('Sélectionner un auteur') }}</option>
                            </select>
                            <button type="button" class="btn btn-outline-secondary" onclick="openAuthorModal(${authorIndex})">
                                <i class="bi bi-three-dots"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="authors[${authorIndex}][responsibility_type]" class="form-select">
                            <option value="">Type de responsabilité</option>
                            <option value="author">Auteur principal</option>
                            <option value="co-author">Co-auteur</option>
                            <option value="editor">Éditeur scientifique</option>
                            <option value="translator">Traducteur</option>
                            <option value="illustrator">Illustrateur</option>
                            <option value="preface">Préfacier</option>
                            <option value="contributor">Contributeur</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="authors[${authorIndex}][function]" class="form-control" placeholder="Fonction spécifique">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger remove-author" title="Supprimer">
                            <i class="bi bi-dash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#authors-container').append(template);
        initSelect2(`.author-select[data-index="${authorIndex}"]`, '{{ route("library.api.authors.search") }}', 'Rechercher un auteur...');
        authorIndex++;
    });

    $(document).on('click', '.remove-author', function() {
        $(this).closest('.author-item').remove();
    });

    // Multiplication des champs - Collections
    let collectionIndex = 1;
    $(document).on('click', '.add-collection', function() {
        const template = `
            <div class="collection-item mb-2">
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="input-group">
                            <select name="collections[${collectionIndex}][id]" class="form-select collection-select" data-index="${collectionIndex}">
                                <option value="">{{ __('Sélectionner une collection') }}</option>
                            </select>
                            <button type="button" class="btn btn-outline-secondary" onclick="openCollectionModal(${collectionIndex})">
                                <i class="bi bi-three-dots"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="collections[${collectionIndex}][collection_number]" class="form-control" placeholder="N° dans la collection">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-collection w-100" title="Supprimer">
                            <i class="bi bi-dash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#collections-container').append(template);
        initSelect2(`.collection-select[data-index="${collectionIndex}"]`, '{{ route("library.api.series.search") }}', 'Rechercher une collection...');
        collectionIndex++;
    });

    $(document).on('click', '.remove-collection', function() {
        $(this).closest('.collection-item').remove();
    });

    // Multiplication des champs - Termes de thésaurus
    let thesaurusIndex = 1;
    $(document).on('click', '.add-thesaurus', function() {
        const template = `
            <div class="thesaurus-item mb-2">
                <div class="row g-2">
                    <div class="col-md-10">
                        <div class="input-group">
                            <select name="thesaurus_terms[${thesaurusIndex}][id]" class="form-select thesaurus-select" data-index="${thesaurusIndex}">
                                <option value="">{{ __('Sélectionner un terme') }}</option>
                            </select>
                            <button type="button" class="btn btn-outline-secondary" onclick="openThesaurusModal(${thesaurusIndex})">
                                <i class="bi bi-three-dots"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-thesaurus w-100" title="Supprimer">
                            <i class="bi bi-dash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#thesaurus-container').append(template);
        initSelect2(`.thesaurus-select[data-index="${thesaurusIndex}"]`, '{{ route("library.api.thesaurus.search") }}', 'Rechercher un terme...');
        thesaurusIndex++;
    });

    $(document).on('click', '.remove-thesaurus', function() {
        $(this).closest('.thesaurus-item').remove();
    });

    // Multiplication des champs - Lieux de publication
    let placeIndex = 1;
    $(document).on('click', '.add-place', function() {
        const template = `
            <div class="place-item mb-2">
                <div class="row g-2">
                    <div class="col-md-10">
                        <input type="text" name="publisher_places[${placeIndex}][place]" class="form-control" placeholder="Ville, Pays">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-place w-100" title="Supprimer">
                            <i class="bi bi-dash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#places-container').append(template);
        placeIndex++;
    });

    $(document).on('click', '.remove-place', function() {
        $(this).closest('.place-item').remove();
    });
});
</script>
@endpush
