@extends('layouts.app')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ session('records.back_url', route('records.index')) }}" class="btn btn-outline-secondary btn-sm" title="{{ __('back') }}">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h1 class="mb-0">{{ __('create_description') }}</h1>
            </div>
            <a href="{{ route('records.create') }}" class="btn btn-outline-secondary">
                <i class="fas fa-file-alt me-1"></i>Fiche simplifiée
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
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
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="identification-tab" data-bs-toggle="tab" href="#identification" role="tab" aria-controls="identification" aria-selected="true">{{ __('identification') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="contexte-tab" data-bs-toggle="tab" href="#contexte" role="tab" aria-controls="contexte" aria-selected="false">{{ __('context') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="contenu-tab" data-bs-toggle="tab" href="#contenu" role="tab" aria-controls="contenu" aria-selected="false">{{ __('content') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="condition-tab" data-bs-toggle="tab" href="#condition" role="tab" aria-controls="condition" aria-selected="false">{{ __('access_condition') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="sources-tab" data-bs-toggle="tab" href="#sources" role="tab" aria-controls="sources" aria-selected="false">{{ __('complementary_sources') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="notes-tab" data-bs-toggle="tab" href="#notes" role="tab" aria-controls="notes" aria-selected="false">{{ __('notes') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="controle-tab" data-bs-toggle="tab" href="#controle" role="tab" aria-controls="controle" aria-selected="false">{{ __('description_control') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="indexation-tab" data-bs-toggle="tab" href="#indexation" role="tab" aria-controls="indexation" aria-selected="false">{{ __('indexing') }}</a>
                </li>
            </ul>

            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="identification" role="tabpanel" aria-labelledby="identification-tab">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="level_id" class="form-label">{{ __('level') }}</label>
                            <select name="level_id" id="level_id" class="form-select" required>
                                @foreach ($levels as $level)
                                    <option value="{{ $level->id }}" {{ old('level_id') == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="support_id" class="form-label">{{ __('support') }}</label>
                            <select name="support_id" id="support_id" class="form-select" required>
                                @foreach ($supports as $support)
                                    <option value="{{ $support->id }}" {{ old('support_id') == $support->id ? 'selected' : '' }}>{{ $support->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="code" class="form-label">{{ __('code') }}</label>
                            <input type="text" name="code" id="code" class="form-control" required maxlength="10" value="{{ old('code') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">{{ __('name') }}</label>
                        <textarea name="name" id="name" class="form-control" required>{{ old('name') }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="date_start" class="form-label">{{ __('date_start') }}</label>
                            <input type="text" name="date_start" id="date_start" class="form-control" maxlength="10" value="{{ old('date_start') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="date_end" class="form-label">{{ __('date_end') }}</label>
                            <input type="text" name="date_end" id="date_end" class="form-control" maxlength="10" value="{{ old('date_end') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="date_exact" class="form-label">{{ __('date_exact') }}</label>
                            <input type="date" name="date_exact" id="date_exact" class="form-control" value="{{ old('date_exact') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <label for="width" class="form-label">{{ __('width') }}</label>
                            <input type="number" name="width" id="width" class="form-control" step="0.01" min="0" max="9999999999.99" value="{{ old('width') }}">
                        </div>
                        <div class="col-md-10 mb-3">
                            <label for="width_description" class="form-label">{{ __('width_description') }}</label>
                            <input type="text" name="width_description" id="width_description" class="form-control" maxlength="100" value="{{ old('width_description') }}">
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="contexte" role="tabpanel" aria-labelledby="contexte-tab">
                    <div class="mb-3">
                        <div class="mb-3">
                            <label for="author" class="form-label">{{ __('producers') }} *</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#authorModal">
                                    <i class="bi bi-plus-circle me-1"></i>{{ __('select') }}
                                </button>
                            </div>

                            <!-- Zone d'affichage des auteurs sélectionnés -->
                            <div id="selected-authors-container" class="mt-2">
                                <!-- Les auteurs sélectionnés apparaîtront ici -->
                            </div>



                            <!-- Champs cachés pour stocker les ID des auteurs sélectionnés -->
                            <div id="author-ids-container">
                                <!-- Les champs cachés pour les auteurs sélectionnés apparaîtront ici -->
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="biographical_history" class="form-label">{{ __('biographical_history') }}</label>
                        <textarea name="biographical_history" id="biographical_history" class="form-control">{{ old('biographical_history') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="archival_history" class="form-label">{{ __('archival_history') }}</label>
                        <textarea name="archival_history" id="archival_history" class="form-control">{{ old('archival_history') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="acquisition_source" class="form-label">{{ __('acquisition_source') }}</label>
                        <textarea name="acquisition_source" id="acquisition_source" class="form-control">{{ old('acquisition_source') }}</textarea>
                    </div>
                </div>
                <div class="tab-pane fade" id="contenu" role="tabpanel" aria-labelledby="contenu-tab">
                    <div class="mb-3">
                        <label for="content" class="form-label">{{ __('content') }}</label>
                        <textarea name="content" id="content" class="form-control">{{ old('content') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="appraisal" class="form-label">{{ __('appraisal') }}</label>
                        <textarea name="appraisal" id="appraisal" class="form-control">{{ old('appraisal') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="accrual" class="form-label">{{ __('accrual') }}</label>
                        <textarea name="accrual" id="accrual" class="form-control">{{ old('accrual') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="arrangement" class="form-label">{{ __('arrangement') }}</label>
                        <textarea name="arrangement" id="arrangement" class="form-control">{{ old('arrangement') }}</textarea>
                    </div>
                </div>
                <div class="tab-pane fade" id="condition" role="tabpanel" aria-labelledby="condition-tab">
                    <div class="mb-3">
                        <label for="access_conditions" class="form-label">{{ __('access_conditions') }}</label>
                        <input type="text" name="access_conditions" id="access_conditions" class="form-control" maxlength="50" value="{{ old('access_conditions') }}">
                    </div>
                    <div class="mb-3">
                        <label for="reproduction_conditions" class="form-label">{{ __('reproduction_conditions') }}</label>
                        <input type="text" name="reproduction_conditions" id="reproduction_conditions" class="form-control" maxlength="50" value="{{ old('reproduction_conditions') }}">
                    </div>
                    <div class="mb-3">
                        <label for="language_material" class="form-label">{{ __('language_material') }}</label>
                        <input type="text" name="language_material" id="language_material" class="form-control" maxlength="50" value="{{ old('language_material') }}">
                    </div>
                    <div class="mb-3">
                        <label for="characteristic" class="form-label">{{ __('characteristic') }}</label>
                        <input type="text" name="characteristic" id="characteristic" class="form-control" maxlength="100" value="{{ old('characteristic') }}">
                    </div>
                    <div class="mb-3">
                        <label for="finding_aids" class="form-label">{{ __('finding_aids') }}</label>
                        <input type="text" name="finding_aids" id="finding_aids" class="form-control" maxlength="100" value="{{ old('finding_aids') }}">
                    </div>
                </div>

                <div class="tab-pane fade" id="sources" role="tabpanel" aria-labelledby="sources-tab">
                    <div class="mb-3">
                        <label for="location_original" class="form-label">{{ __('location_original') }}</label>
                        <input type="text" name="location_original" id="location_original" class="form-control" maxlength="100" value="{{ old('location_original') }}">
                    </div>
                    <div class="mb-3">
                        <label for="location_copy" class="form-label">{{ __('location_copy') }}</label>
                        <input type="text" name="location_copy" id="location_copy" class="form-control" maxlength="100" value="{{ old('location_copy') }}">
                    </div>
                    <div class="mb-3">
                        <label for="related_unit" class="form-label">{{ __('related_unit') }}</label>
                        <input type="text" name="related_unit" id="related_unit" class="form-control" maxlength="100" value="{{ old('related_unit') }}">
                    </div>
                    <div class="mb-3">
                        <label for="publication_note" class="form-label">{{ __('publication_note') }}</label>
                        <textarea name="publication_note" id="publication_note" class="form-control">{{ old('publication_note') }}</textarea>
                    </div>
                </div>

                <div class="tab-pane fade" id="notes" role="tabpanel" aria-labelledby="notes-tab">
                    <div class="mb-3">
                        <label for="note" class="form-label">{{ __('note') }}</label>
                        <textarea name="note" id="note" class="form-control">{{ old('note') }}</textarea>
                    </div>
                </div>

                <div class="tab-pane fade" id="controle" role="tabpanel" aria-labelledby="controle-tab">
                    <div class="mb-3">
                        <label for="archivist_note" class="form-label">{{ __('archivist_note') }}</label>
                        <textarea name="archivist_note" id="archivist_note" class="form-control">{{ old('archivist_note') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="rule_convention" class="form-label">{{ __('rule_convention') }}</label>
                        <input type="text" name="rule_convention" id="rule_convention" class="form-control" maxlength="100" value="{{ old('rule_convention') }}">
                    </div>
                    <div class="mb-3">
                        <label for="status_id" class="form-label">{{ __('status') }}</label>
                        <select name="status_id" id="status_id" class="form-select" required>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}" {{ old('status_id') == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="tab-pane fade" id="indexation" role="tabpanel" aria-labelledby="indexation-tab">
                    <div class="mb-3">
                        <label class="form-label">{{ __('thesaurus') }} *</label>
                        <div class="position-relative">
                            <input type="text" class="form-control form-control-sm" id="thesaurus-search" placeholder="Rechercher dans le thésaurus..." autocomplete="off">
                            <div id="thesaurus-suggestions" class="position-absolute w-100 bg-white border border-top-0 shadow-sm" style="z-index: 1000; max-height: 200px; overflow-y: auto; display: none;">
                                <!-- Les suggestions apparaîtront ici -->
                            </div>
                        </div>
                        <small class="text-muted">Tapez au moins 3 caractères pour rechercher. Cliquez sur un terme pour l'ajouter.</small>

                        <!-- Zone d'affichage des termes sélectionnés -->
                        <div id="selected-terms-container" class="mt-2">
                            <!-- Les termes sélectionnés apparaîtront ici -->
                        </div>

                        <!-- Champs cachés pour stocker les ID des termes sélectionnés -->
                        <div id="term-ids-container">
                            <!-- Les champs cachés pour les termes sélectionnés apparaîtront ici -->
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('activities') }} *</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="selected-activity-display" readonly>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#activityModal">
                                {{ __('select') }}
                            </button>
                        </div>
                        <input type="hidden" name="activity_id" id="activity-id" required>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('create') }}</button>
        </form>
    </div>

    <!-- Modals - inclus une seule fois -->
    @include('records.partials.author_modal')
    @include('records.partials.activity_modal')

    <style>
        /* Styles pour les onglets et les formulaires */
        .nav-tabs .nav-link { padding: 0.75rem 1.25rem; }
        .nav-tabs .nav-link.active {
            background-color: var(--bs-primary);
            color: white;
            border-color: var(--bs-primary);
        }
        .nav-tabs .nav-link:hover {
            background-color: var(--bs-primary);
            color: white;
            border-color: var(--bs-primary);
        }
        .form-label { margin-bottom: 0.2rem; }
        .tab-content { padding: 1rem; border: 1px solid #dee2e6; border-top: none; }
        .form-control-sm, .form-select-sm { padding: 0.25rem 0.5rem; }
        .input-group-sm > .form-control { padding: 0.25rem 0.5rem; }
        .btn-sm { padding: 0.25rem 0.5rem; }

        /* Styles pour le thésaurus AJAX */
        .thesaurus-suggestion {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }

        .thesaurus-suggestion:hover {
            background-color: #f8f9fa;
        }

        .thesaurus-suggestion:last-child {
            border-bottom: none;
        }

        .selected-term {
            display: inline-flex;
            align-items: center;
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 0.25rem 0.5rem;
            margin: 0.125rem;
            font-size: 0.875rem;
        }

        .selected-term .remove-term {
            background: none;
            border: none;
            color: #6c757d;
            font-weight: bold;
            margin-left: 0.5rem;
            cursor: pointer;
            padding: 0;
            font-size: 1rem;
            line-height: 1;
        }

        .selected-term .remove-term:hover {
            color: #dc3545;
        }

        #thesaurus-search:focus + #thesaurus-suggestions {
            display: block;
        }

        /* Style pour l'état d'erreur du champ thésaurus */
        #thesaurus-search.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .invalid-feedback {
            display: block !important;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* S'assurer qu'aucun élément ne bloque l'interaction */
        .modal-backdrop.show {
            z-index: 1040;
        }

        .modal {
            z-index: 1050;
        }

        /* S'assurer que les éléments sélectionnés n'interfèrent pas avec les onglets */
        .selected-author, .selected-term {
            position: relative;
            z-index: 1;
        }

        /* Force les onglets à être interactifs */
        .nav-tabs .nav-link {
            z-index: 2;
            position: relative;
        }

        /* S'assurer que le contenu des onglets est accessible */
        .tab-content {
            position: relative;
            z-index: 1;
        }
    </style>

    <script src="{{ asset('js/records.js') }}"></script>
    <script>
        // Variables globales
        let selectedAuthors = [];

        // Fonctions globales pour les auteurs
        window.addAuthorToSelection = function(author) {
            console.log('=== Début addAuthorToSelection (globale) ===');
            console.log('Données auteur reçues:', author);

            const container = document.getElementById('selected-authors-container');
            if (!container) {
                console.error('ERREUR: Conteneur selected-authors-container introuvable');
                return;
            }
            console.log('Conteneur trouvé:', container);

            // Vérifier si l'auteur n'est pas déjà sélectionné
            const existingAuthors = container.querySelectorAll('.selected-author');
            console.log('Auteurs existants:', existingAuthors.length);

            for (let existingAuthor of existingAuthors) {
                if (existingAuthor.dataset.id === author.id.toString()) {
                    console.log('Auteur déjà sélectionné, abandon:', author.id);
                    return;
                }
            }

            // Créer l'élément de l'auteur
            const authorElement = document.createElement('div');
            authorElement.className = 'selected-author d-inline-flex align-items-center bg-primary text-white rounded me-2 mb-2 p-2';
            authorElement.dataset.id = author.id;
            authorElement.style.position = 'relative';
            authorElement.style.zIndex = '1';

            const authorName = author.name || 'Nom inconnu';
            const authorType = (author.authorType && author.authorType.name) ? ` (${author.authorType.name})` : '';

            authorElement.innerHTML = `
                <span>${authorName}${authorType}</span>
                <button type="button" class="btn-close btn-close-white ms-2" style="font-size: 0.8em; position: relative; z-index: 2;" onclick="removeAuthor(this)"></button>
            `;

            console.log('Élément auteur créé:', authorElement);
            container.appendChild(authorElement);
            console.log('Auteur ajouté au DOM. Contenu du conteneur:', container.innerHTML);

            updateAuthorIds();
            console.log('=== Fin addAuthorToSelection (globale) ===');
        };

        window.removeAuthor = function(button) {
            console.log('=== Suppression d\'un auteur ===');
            const authorElement = button.closest('.selected-author');
            if (authorElement) {
                console.log('Élément auteur trouvé, suppression...');
                authorElement.remove();
                updateAuthorIds();
                console.log('Auteur supprimé et IDs mis à jour');
            } else {
                console.error('Élément auteur non trouvé pour la suppression');
            }
        };

        window.updateAuthorIds = function() {
            const container = document.getElementById('selected-authors-container');
            const authors = container.querySelectorAll('.selected-author');
            const authorIdsContainer = document.getElementById('author-ids-container');

            console.log('Mise à jour des IDs auteurs, nombre d\'auteurs:', authors.length);

            // Vider les champs cachés existants
            authorIdsContainer.innerHTML = '';

            // Créer un champ caché pour chaque auteur sélectionné
            authors.forEach(author => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'author_ids[]';
                hiddenInput.value = author.dataset.id;
                authorIdsContainer.appendChild(hiddenInput);
                console.log('Champ caché créé pour auteur ID:', author.dataset.id);
            });
        };

        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOMContentLoaded - Initialisation de la page createFull');

            // Vérifier que Bootstrap est disponible
            if (typeof bootstrap === 'undefined') {
                console.error('Bootstrap n\'est pas disponible !');
            } else {
                console.log('Bootstrap détecté, version:', bootstrap);
            }

            // Vérifier que les éléments nécessaires existent
            const authorsContainer = document.getElementById('selected-authors-container');
            const authorIdsContainer = document.getElementById('author-ids-container');

            console.log('Conteneurs trouvés:', {
                authorsContainer: !!authorsContainer,
                authorIdsContainer: !!authorIdsContainer
            });

            if (authorsContainer) {
                console.log('Conteneur auteurs HTML:', authorsContainer.outerHTML);
            }

            // Initialiser le gestionnaire de records avec le thésaurus AJAX et les modals
            if (typeof initRecordsManager === 'function') {
                initRecordsManager();
                console.log('initRecordsManager appelé');
            } else {
                console.error('initRecordsManager non disponible');
            }

            // Pré-remplir les champs avec les anciennes valeurs en cas d'erreur
            preloadOldValues();

            // Ajouter un gestionnaire de soumission pour déboguer les données
            const form = document.querySelector('form[action*="records.store"]');
            if (form) {
                form.addEventListener('submit', function(e) {
                    console.log('=== DÉBOGAGE SOUMISSION FORMULAIRE ===');

                    // Mettre à jour les IDs auteurs avant soumission
                    updateAuthorIds();

                    // Vérifier les champs cachés d'auteurs
                    const authorInputs = form.querySelectorAll('input[name="author_ids[]"]');
                    console.log('Nombre de champs author_ids[]:', authorInputs.length);

                    const authorValues = [];
                    authorInputs.forEach((input, index) => {
                        console.log(`author_ids[${index}]:`, input.value);
                        authorValues.push(input.value);
                    });

                    console.log('Valeurs des auteurs à envoyer:', authorValues);

                    // Vérifier le conteneur des auteurs sélectionnés
                    const selectedAuthors = document.querySelectorAll('#selected-authors-container .selected-author');
                    console.log('Auteurs sélectionnés dans l\'interface:', selectedAuthors.length);
                    selectedAuthors.forEach((author, index) => {
                        console.log(`Auteur ${index}:`, {
                            id: author.dataset.id,
                            name: author.textContent.trim()
                        });
                    });

                    // Si pas d'auteurs, empêcher la soumission pour déboguer
                    if (authorInputs.length === 0) {
                        console.error('ERREUR: Aucun champ author_ids[] trouvé avant soumission !');
                        console.log('Conteneur author-ids:', document.getElementById('author-ids-container')?.innerHTML);
                    }

                    console.log('=== FIN DÉBOGAGE ===');
                });
            }

            // Ajouter une vérification périodique de l'état de l'interface
            setInterval(function() {
                // Vérifier s'il y a des backdrops modaux qui traînent
                const strayBackdrops = document.querySelectorAll('.modal-backdrop');
                if (strayBackdrops.length > 0 && !document.querySelector('.modal.show')) {
                    console.warn('Nettoyage des backdrops modaux orphelins');
                    strayBackdrops.forEach(backdrop => backdrop.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }

                // S'assurer que les onglets restent interactifs
                const tabs = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
                tabs.forEach(tab => {
                    if (tab.style.pointerEvents === 'none') {
                        tab.style.pointerEvents = 'auto';
                    }
                });
            }, 2000); // Vérification toutes les 2 secondes
        });

        function preloadOldValues() {
            // Ajouter des classes d'erreur aux champs qui ont des erreurs
            @if($errors->any())
                @foreach($errors->keys() as $field)
                    const field_{{ $field }} = document.querySelector('[name="{{ $field }}"]');
                    if (field_{{ $field }}) {
                        field_{{ $field }}.classList.add('is-invalid');

                        // Créer un message d'erreur si il n'existe pas déjà
                        if (!field_{{ $field }}.nextElementSibling || !field_{{ $field }}.nextElementSibling.classList.contains('invalid-feedback')) {
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'invalid-feedback';
                            errorDiv.textContent = @json($errors->first($field));
                            field_{{ $field }}.parentNode.appendChild(errorDiv);
                        }
                    }
                @endforeach
            @endif

            // Pré-remplir les auteurs sélectionnés
            const oldAuthorIds = @json(old('author_ids'));
            if (oldAuthorIds) {
                const authorIdsArray = typeof oldAuthorIds === 'string' ? oldAuthorIds.split(',') : oldAuthorIds;
                if (authorIdsArray.length > 0) {
                    document.getElementById('author-ids').value = authorIdsArray.join(',');
                    // Afficher les noms des auteurs (nécessiterait un appel AJAX ou passer les noms depuis le contrôleur)
                    // Pour l'instant, afficher juste les IDs
                    document.getElementById('selected-authors-display').value = 'Auteurs sélectionnés (IDs: ' + authorIdsArray.join(', ') + ')';
                }
            }

            // Pré-remplir l'activité sélectionnée
            const oldActivityId = @json(old('activity_id'));
            if (oldActivityId) {
                document.getElementById('activity-id').value = oldActivityId;
                // Afficher le nom de l'activité (nécessiterait un appel AJAX ou passer le nom depuis le contrôleur)
                document.getElementById('selected-activity-display').value = 'Activité sélectionnée (ID: ' + oldActivityId + ')';
            }

            // Pré-remplir les termes du thésaurus sélectionnés
            const oldTermIds = @json(old('term_ids'));
            if (oldTermIds && oldTermIds.length > 0) {
                // Afficher les termes sélectionnés (nécessiterait un appel AJAX pour récupérer les noms)
                const container = document.getElementById('selected-terms-container');
                if (container) {
                    oldTermIds.forEach(termId => {
                        const termElement = document.createElement('div');
                        termElement.className = 'selected-term badge bg-primary me-2 mb-2 p-2';
                        termElement.dataset.id = termId;
                        termElement.innerHTML = `
                            <span>Terme sélectionné (ID: ${termId})</span>
                            <button type="button" class="btn-close btn-close-white ms-2" style="font-size: 0.8em;" onclick="removeTerm(this)"></button>
                        `;
                        container.appendChild(termElement);
                    });
                    updateTermIds(); // Mettre à jour les champs cachés
                }
            }

            // Initialiser la recherche AJAX du thésaurus
            initThesaurusSearch();
        }

        // === AJAX Thesaurus Search Implementation ===
        function initThesaurusSearch() {
            let thesaurusTimeout;
            const thesaurusSearchInput = document.getElementById('thesaurus-search');
            const thesaurusSuggestions = document.getElementById('thesaurus-suggestions');

            if (thesaurusSearchInput) {
                thesaurusSearchInput.addEventListener('input', function() {
                    const query = this.value.trim();

                    clearTimeout(thesaurusTimeout);

                    if (query.length < 3) {
                        thesaurusSuggestions.style.display = 'none';
                        return;
                    }

                    thesaurusTimeout = setTimeout(() => {
                        searchThesaurus(query);
                    }, 300);
                });

                // Masquer les suggestions quand on clique ailleurs
                document.addEventListener('click', function(e) {
                    if (!thesaurusSearchInput.contains(e.target) && !thesaurusSuggestions.contains(e.target)) {
                        thesaurusSuggestions.style.display = 'none';
                    }
                });
            }
        }

        function searchThesaurus(query) {
            fetch(`{{ route('records.terms.autocomplete') }}?q=${encodeURIComponent(query)}&limit=10`)
                .then(response => response.json())
                .then(data => {
                    displayThesaurusSuggestions(data);
                })
                .catch(error => {
                    console.error('Erreur lors de la recherche dans le thésaurus:', error);
                    document.getElementById('thesaurus-suggestions').style.display = 'none';
                });
        }

        function displayThesaurusSuggestions(suggestions) {
            const thesaurusSuggestions = document.getElementById('thesaurus-suggestions');
            thesaurusSuggestions.innerHTML = '';

            if (suggestions.length === 0) {
                thesaurusSuggestions.innerHTML = '<div class="p-2 text-muted">Aucun résultat trouvé</div>';
                thesaurusSuggestions.style.display = 'block';
                return;
            }

            suggestions.forEach(suggestion => {
                const div = document.createElement('div');
                div.className = 'p-2 cursor-pointer border-bottom';
                div.style.cursor = 'pointer';
                div.innerHTML = `
                    <div class="fw-bold">${suggestion.text}</div>
                    <small class="text-muted">${suggestion.scheme || 'Thésaurus'}</small>
                `;

                div.addEventListener('click', () => {
                    addTermToSelection(suggestion);
                    document.getElementById('thesaurus-search').value = '';
                    thesaurusSuggestions.style.display = 'none';
                });

                div.addEventListener('mouseover', () => {
                    div.style.backgroundColor = '#f8f9fa';
                });

                div.addEventListener('mouseout', () => {
                    div.style.backgroundColor = '';
                });

                thesaurusSuggestions.appendChild(div);
            });

            thesaurusSuggestions.style.display = 'block';
        }

        function addTermToSelection(term) {
            const container = document.getElementById('selected-terms-container');

            // Vérifier si le terme n'est pas déjà sélectionné
            const existingTerms = container.querySelectorAll('.selected-term');
            for (let existingTerm of existingTerms) {
                if (existingTerm.dataset.id === term.id.toString()) {
                    return; // Terme déjà sélectionné
                }
            }

            // Créer l'élément du terme
            const termElement = document.createElement('div');
            termElement.className = 'selected-term badge bg-primary me-2 mb-2 p-2';
            termElement.dataset.id = term.id;
            termElement.innerHTML = `
                <span>${term.text}</span>
                <button type="button" class="btn-close btn-close-white ms-2" style="font-size: 0.8em;" onclick="removeTerm(this)"></button>
            `;

            container.appendChild(termElement);
            updateTermIds();
        }

        function removeTerm(button) {
            button.closest('.selected-term').remove();
            updateTermIds();
        }

        function updateTermIds() {
            const container = document.getElementById('selected-terms-container');
            const terms = container.querySelectorAll('.selected-term');
            const termIdsContainer = document.getElementById('term-ids-container');

            // Vider les champs cachés existants
            termIdsContainer.innerHTML = '';

            // Créer un champ caché pour chaque terme sélectionné
            terms.forEach(term => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'term_ids[]';
                hiddenInput.value = term.dataset.id;
                termIdsContainer.appendChild(hiddenInput);
            });
        }

        // === Gestion des auteurs ===
        // Les fonctions sont maintenant globales (voir au-dessus)

        // Écouter l'événement authorsSelected du modal
        document.addEventListener('authorsSelected', function(e) {
            console.log('Événement authorsSelected reçu:', e.detail.authors);

            if (!e.detail || !e.detail.authors) {
                console.error('Données d\'auteurs manquantes dans l\'événement');
                return;
            }

            const selectedAuthorsFromModal = e.detail.authors;
            console.log('Nombre d\'auteurs à ajouter:', selectedAuthorsFromModal.length);

            // Utiliser setTimeout pour éviter les conflits avec la fermeture du modal
            setTimeout(() => {
                const container = document.getElementById('selected-authors-container');
                if (!container) {
                    console.error('Conteneur selected-authors-container introuvable');
                    return;
                }

                // NE PAS vider le conteneur - juste ajouter les nouveaux auteurs
                // container.innerHTML = ''; // ← LIGNE SUPPRIMÉE !
                console.log('Ajout des nouveaux auteurs sans vider le conteneur');

                selectedAuthorsFromModal.forEach((author, index) => {
                    console.log(`Traitement auteur ${index + 1}:`, author);
                    addAuthorToSelection(author);
                });

                console.log('Tous les auteurs ont été traités');

                // S'assurer que le focus est libéré
                document.activeElement.blur();

                // Forcer le retour du focus sur le formulaire principal
                const mainForm = document.querySelector('form');
                if (mainForm) {
                    mainForm.focus();
                }

                // Réinitialiser les onglets Bootstrap si nécessaire
                try {
                    const tabElements = document.querySelectorAll('[data-bs-toggle="tab"]');
                    tabElements.forEach(tabElement => {
                        if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                            const tab = new bootstrap.Tab(tabElement);
                            // S'assurer que l'onglet est interactif
                            tabElement.style.pointerEvents = 'auto';
                        }
                    });
                } catch (error) {
                    console.error('Erreur lors de la réinitialisation des onglets:', error);
                }

                // S'assurer qu'aucun backdrop modal ne reste
                const remainingBackdrops = document.querySelectorAll('.modal-backdrop');
                if (remainingBackdrops.length > 0) {
                    console.warn('Backdrops modaux détectés, suppression...');
                    remainingBackdrops.forEach(backdrop => backdrop.remove());
                }
            }, 150);
        });

        // Fonction pour charger les auteurs par leurs IDs (pour les anciennes valeurs)
        function loadAuthorsByIds(authorIds) {
            if (!authorIds || authorIds.length === 0) return;

            fetch(`{{ route('author-handler.list') }}?ids=${authorIds.join(',')}`)
                .then(response => response.json())
                .then(data => {
                    if (data.data && data.data.length > 0) {
                        data.data.forEach(author => {
                            addAuthorToSelection(author);
                        });
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des auteurs:', error);
                });
        }

        // Pré-remplir les auteurs si on a des anciennes valeurs
        const oldAuthorIds = @json(old('author_ids'));
        if (oldAuthorIds) {
            const authorIdsArray = Array.isArray(oldAuthorIds) ? oldAuthorIds :
                (typeof oldAuthorIds === 'string' ? oldAuthorIds.split(',') : [oldAuthorIds]);

            if (authorIdsArray.length > 0) {
                loadAuthorsByIds(authorIdsArray);
            }
        }
    </script>

    <style>
        /* Style pour les auteurs sélectionnés */
        .selected-author {
            display: inline-flex;
            align-items: center;
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 0.25rem 0.5rem;
            margin: 0.125rem;
            font-size: 0.875rem;
        }

        .selected-author .remove-author {
            background: none;
            border: none;
            color: #6c757d;
            font-weight: bold;
            margin-left: 0.5rem;
            cursor: pointer;
            padding: 0;
            font-size: 1rem;
            line-height: 1;
        }

        .selected-author .remove-author:hover {
            color: #dc3545;
        }
    </style>
@endsection

@include('records.partials.quick-nav')
