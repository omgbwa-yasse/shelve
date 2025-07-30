@extends('layouts.app')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-md-8">
                <h4 class="mb-3">{{ __('create_description') }}</h4>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('records.create.full') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-file-earmark-text me-1"></i>
                    Afficher une fiche complète
                </a>
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

        <form action="{{ route('records.store') }}" method="POST" id="recordForm">
            @csrf
            @if (!empty($record))
                <input type="hidden" name="parent_id" value="{{$record->id}}">
            @endif
            <div class="accordion" id="formAccordion">
                <!-- Identification Panel - Open by default (contains required fields) -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="identificationHeader">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#identificationCollapse" aria-expanded="true" aria-controls="identificationCollapse">
                            {{ __('identification') }} *
                        </button>
                    </h2>
                    <div id="identificationCollapse" class="accordion-collapse collapse show" aria-labelledby="identificationHeader" data-bs-parent="">
                        <div class="accordion-body">

                            <div class="row g-2">
                                <div class="col-md-2">
                                    <label class="form-label small">{{ __('level') }} *</label>
                                    <select name="level_id" class="form-select form-select-sm" required>
                                        @foreach ($levels as $level)
                                            <option value="{{ $level->id }}" {{ old('level_id') == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">{{ __('support') }} *</label>
                                    <select name="support_id" class="form-select form-select-sm" required>
                                        @foreach ($supports as $support)
                                            <option value="{{ $support->id }}" {{ old('support_id') == $support->id ? 'selected' : '' }}>{{ $support->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">{{ __('code') }} *</label>
                                    <input type="text" name="code" class="form-control form-control-sm" required maxlength="10" value="{{ old('code') }}">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small">{{ __('status') }} *</label>
                                    <select name="status_id" class="form-select form-select-sm" required>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->id }}" {{ old('status_id') == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-12">
                                    <label class="form-label small">{{ __('name') }} *</label>
                                    <textarea name="name" class="form-control form-control-sm" rows="2" required>{{ old('name') }}</textarea>
                                </div>
                            </div>

                            <div class="row mt-2 g-2">
                                <div class="col-md-4">
                                    <label class="form-label small">{{ __('date_start') }}</label>
                                    <input type="text" name="date_start" class="form-control form-control-sm" maxlength="10" value="{{ old('date_start') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">{{ __('date_end') }}</label>
                                    <input type="text" name="date_end" class="form-control form-control-sm" maxlength="10" value="{{ old('date_end') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">{{ __('date_exact') }}</label>
                                    <input type="date" name="date_exact" class="form-control form-control-sm" value="{{ old('date_exact') }}">
                                </div>
                            </div>

                            <div class="row mt-2 g-2">
                                <div class="col-md-3">
                                    <label class="form-label small">{{ __('width') }}</label>
                                    <input type="number" name="width" class="form-control form-control-sm" step="0.01" value="{{ old('width') }}">
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label small">{{ __('width_description') }}</label>
                                    <input type="text" name="width_description" class="form-control form-control-sm" value="{{ old('width_description') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Context Panel -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="contextHeader">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#contextCollapse" aria-expanded="true" aria-controls="contextCollapse">
                            {{ __('context') }}
                        </button>
                    </h2>
                    <div id="contextCollapse" class="accordion-collapse collapse show" aria-labelledby="contextHeader" data-bs-parent="">
                        <div class="accordion-body">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label small">{{ __('producers') }}</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control" id="selected-authors-display" readonly>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#authorModal">
                                            {{ __('select') }}
                                        </button>
                                    </div>
                                    <input type="hidden" name="author_ids" id="author-ids">
                                </div>
                            </div>

                            <div class="mt-2">
                                <label class="form-label small">{{ __('biographical_history') }}</label>
                                <textarea name="biographical_history" class="form-control form-control-sm" rows="2">{{ old('biographical_history') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Indexing Panel -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="indexingHeader">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#indexingCollapse" aria-expanded="true" aria-controls="indexingCollapse">
                            {{ __('indexing') }} *
                        </button>
                    </h2>
                    <div id="indexingCollapse" class="accordion-collapse collapse show" aria-labelledby="indexingHeader" data-bs-parent="">
                        <div class="accordion-body">

                            <div class="mb-2">
                                <label class="form-label small">{{ __('thesaurus') }}</label>
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

                            <div class="mb-2">
                                <label class="form-label small">{{ __('activities') }} *</label>
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
                </div>

                <!-- Notes Panel -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="notesHeader">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#notesCollapse" aria-expanded="true" aria-controls="notesCollapse">
                            {{ __('notes') }}
                        </button>
                    </h2>
                    <div id="notesCollapse" class="accordion-collapse collapse show" aria-labelledby="notesHeader" data-bs-parent="">
                        <div class="accordion-body">
                            <textarea name="note" class="form-control form-control-sm" rows="3">{{ old('note') }}</textarea>
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

    <!-- Modals - inclus une seule fois -->
    @include('records.partials.author_modal')
    @include('records.partials.activity_modal')

    <style>
        /* Styles pour l'accordéon et les formulaires */
        .accordion-button { padding: 0.75rem 1.25rem; }
        .accordion-button:not(.collapsed) {
            background-color: var(--bs-primary);
            color: white;
        }
        .accordion-button.collapsed {
            background-color: var(--bs-light);
        }
        .accordion-button:hover {
            background-color: var(--bs-primary);
            color: white;
        }
        .accordion-button:focus { box-shadow: none; }
        .form-label { margin-bottom: 0.2rem; }
        .accordion-body { padding: 1rem; }
        .form-control-sm, .form-select-sm { padding: 0.25rem 0.5rem; }
        .input-group-sm > .form-control { padding: 0.25rem 0.5rem; }
        .btn-sm { padding: 0.25rem 0.5rem; }

        /* Assurer que les champs sont toujours visibles - pour tous les panneaux, pas seulement ceux avec .show */
        .accordion-collapse {
            position: static !important;
            visibility: visible !important;
            display: block !important;
            height: auto !important;
            overflow: visible !important;
            pointer-events: auto !important;
            opacity: 1 !important;
        }

        /* Styles pour les contenus des sections accordéon */
        .accordion-item {
            overflow: visible;
            z-index: auto;
            position: relative;
        }

        /* Assurer que les champs de formulaire sont cliquables */
        .accordion-body .form-control,
        .accordion-body .form-select,
        .accordion-body .input-group,
        .accordion-body .btn {
            position: relative;
            z-index: 10;
            pointer-events: auto !important;
        }

        /* Correction pour que toutes les sections accordéon restent ouvertes */
        .collapse {
            display: block !important;
        }

        /* Style spécifique pour les sections accordéon */
        .accordion-button {
            pointer-events: auto;
        }

        /* Réduire l'opacité des panneaux fermés pour indiquer visuellement qu'ils sont fermés tout en les gardant visibles */
        .accordion-collapse:not(.show) {
            opacity: 0.95 !important;
        }

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
    </style>

    <script src="{{ asset('js/records.js') }}"></script>
    <script>        document.addEventListener('DOMContentLoaded', function() {
            // Initialiser le gestionnaire de records avec le thésaurus AJAX
            initRecordsManager();

            // Initialiser la recherche AJAX du thésaurus
            initThesaurusSearch();

            // Configuration spécifique pour les accordéons sur cette page
            initAccordionBehavior();

            // Pré-remplir les champs avec les anciennes valeurs en cas d'erreur
            preloadOldValues();
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
                        const termElement = document.createElement('span');
                        termElement.className = 'selected-term';
                        termElement.dataset.id = termId;
                        termElement.innerHTML = `
                            <span>Terme sélectionné (ID: ${termId})</span>
                            <button type="button" class="remove-term" onclick="this.parentElement.remove(); updateTermIds();">×</button>
                        `;
                        container.appendChild(termElement);
                    });
                    updateTermIds(); // Mettre à jour les champs cachés
                }
            }
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

        function initAccordionBehavior() {
            // Configurer l'accordéon pour permettre plusieurs panneaux ouverts et tous les ouvrir par défaut
            const accordionPanels = document.querySelectorAll('.accordion-collapse');
            accordionPanels.forEach(panel => {
                // Enlever la référence au parent pour permettre d'avoir plusieurs sections ouvertes
                panel.removeAttribute('data-bs-parent');

                // S'assurer que tous les panneaux sont ouverts par défaut
                panel.classList.add('show');

                // Forcer la visibilité des panneaux
                panel.style.display = 'block';
                panel.style.height = 'auto';
                panel.style.visibility = 'visible';
                panel.style.overflow = 'visible';
                panel.style.opacity = '1';
            });

            // S'assurer que tous les boutons accordéon sont dans l'état ouvert
            document.querySelectorAll('.accordion-button').forEach(button => {
                button.classList.remove('collapsed');
                button.setAttribute('aria-expanded', 'true');
            });

            // S'assurer que les boutons accordéon fonctionnent correctement
            document.querySelectorAll('.accordion-button').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-bs-target');
                    const targetPanel = document.querySelector(targetId);

                    // Attendre la fin de l'animation Bootstrap
                    setTimeout(() => {
                        if (targetPanel.classList.contains('show')) {
                            // Assurer la visibilité complète
                            targetPanel.style.display = 'block';
                            targetPanel.style.height = 'auto';
                            targetPanel.style.visibility = 'visible';
                            targetPanel.style.overflow = 'visible';
                            targetPanel.style.opacity = '1';
                        }
                    }, 350); // Délai légèrement supérieur à la transition Bootstrap
                });
            });

            // Fonction pour s'assurer que tous les champs d'une section sont visibles
            function ensureFieldsVisibility() {
                document.querySelectorAll('.accordion-collapse.show').forEach(panel => {
                    // Pour chaque panneau ouvert, s'assurer que ses champs sont visibles
                    panel.querySelectorAll('.form-control, .form-select, .input-group').forEach(field => {
                        field.style.pointerEvents = 'auto';
                        field.style.opacity = '1';
                        field.style.position = 'relative';
                        field.style.zIndex = '10';
                    });
                });
            }

            // Exécuter au chargement pour les sections déjà ouvertes
            ensureFieldsVisibility();

            // Observer les mutations pour détecter les changements de classe .show
            const observer = new MutationObserver(mutations => {
                mutations.forEach(mutation => {
                    if (mutation.type === 'attributes' &&
                       mutation.attributeName === 'class' &&
                       mutation.target.classList.contains('accordion-collapse')) {
                        ensureFieldsVisibility();
                    }
                });
            });

            // Observer tous les panneaux accordéon
            document.querySelectorAll('.accordion-collapse').forEach(panel => {
                observer.observe(panel, { attributes: true });
            });
        }

        // Appel direct depuis initRecordsManager dans records.js
        // La fonction initModals() est maintenant dans records.js
    </script>
@endsection
