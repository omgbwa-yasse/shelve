@extends('layouts.app')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                                            <option value="{{ $level->id }}">{{ $level->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">{{ __('support') }} *</label>
                                    <select name="support_id" class="form-select form-select-sm" required>
                                        @foreach ($supports as $support)
                                            <option value="{{ $support->id }}">{{ $support->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">{{ __('code') }} *</label>
                                    <input type="text" name="code" class="form-control form-control-sm" required maxlength="10">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small">{{ __('status') }} *</label>
                                    <select name="status_id" class="form-select form-select-sm" required>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-12">
                                    <label class="form-label small">{{ __('name') }} *</label>
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
                                <label class="form-label small">{{ __('thesaurus') }} *</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" id="selected-terms-display" readonly>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#termModal">
                                        {{ __('select') }}
                                    </button>
                                </div>
                                <input type="hidden" name="term_ids[]" id="term-ids" required>
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

    <!-- Modals - inclus une seule fois -->
    @include('records.partials.author_modal')
    @include('records.partials.term_modal')
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
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

            // Fonction pour filtrer les éléments de liste dans les modals
            function filterList(searchInput, listItems) {
                const filter = searchInput.value.toLowerCase();
                listItems.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(filter) ? '' : 'none';
                });
            }

            // Charger les catégories de termes pour le filtre
            function loadTermCategories() {
                const categorySelect = document.getElementById('term-category-filter');
                if (!categorySelect) return;

                fetch('/api/thesaurus/categories', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Vider les options existantes sauf la première
                    while (categorySelect.options.length > 1) {
                        categorySelect.options.remove(1);
                    }

                    // Ajouter les catégories comme options
                    data.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = category.name;
                        categorySelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Erreur lors du chargement des catégories:', error));
            }

            // Fonction pour rechercher des termes via AJAX
            function searchTerms(keyword = '', categoryId = '') {
                const termList = document.getElementById('term-list');
                const loadingIndicator = document.getElementById('term-loading');
                const noResultsMessage = document.getElementById('term-no-results');

                if (!termList || !loadingIndicator || !noResultsMessage) return;

                // Afficher l'indicateur de chargement
                termList.style.display = 'none';
                noResultsMessage.style.display = 'none';
                loadingIndicator.style.display = 'block';

                // Construire l'URL avec les paramètres de recherche
                let searchUrl = `/api/thesaurus/search?keyword=${encodeURIComponent(keyword)}`;
                if (categoryId) {
                    searchUrl += `&category_id=${categoryId}`;
                }

                fetch(searchUrl, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Cacher l'indicateur de chargement
                    loadingIndicator.style.display = 'none';

                    // Vider la liste de résultats
                    termList.innerHTML = '';

                    if (data.length === 0) {
                        // Afficher le message "aucun résultat"
                        noResultsMessage.style.display = 'block';
                    } else {
                        // Afficher la liste des résultats
                        termList.style.display = 'block';                        // Ajouter chaque terme à la liste
                        data.forEach(term => {
                            const item = document.createElement('a');
                            item.href = '#';
                            item.className = 'list-group-item list-group-item-action';
                            item.dataset.id = term.id;
                            if (term.category_id) {
                                item.dataset.category = term.category_id;
                            }
                            // Stocker le nom formaté pour l'affichage
                            item.dataset.formattedName = term.formatted_name || term.name;

                            // Créer un span pour le nom du terme
                            const nameSpan = document.createElement('span');
                            nameSpan.textContent = term.name;

                            // Si une catégorie est disponible, ajouter un span pour celle-ci
                            if (term.category_name) {
                                nameSpan.textContent = term.name;

                                const categorySpan = document.createElement('span');
                                categorySpan.className = 'ms-1 text-muted';
                                categorySpan.textContent = '(' + term.category_name + ')';

                                item.appendChild(nameSpan);
                                item.appendChild(categorySpan);
                            } else {
                                item.appendChild(nameSpan);
                            }

                            termList.appendChild(item);

                            // Ajouter les gestionnaires d'événements pour la sélection
                            item.addEventListener('click', function(e) {
                                e.preventDefault();
                                item.classList.toggle('active');
                            });
                        });
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de la recherche de termes:', error);
                    loadingIndicator.style.display = 'none';
                    noResultsMessage.style.display = 'block';
                    noResultsMessage.textContent = 'Erreur lors de la recherche. Veuillez réessayer.';
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
                    searchId: 'term-search-input',
                    listId: 'term-list',
                    displayId: 'selected-terms-display',
                    hiddenInputId: 'term-ids',
                    saveButtonId: 'save-terms',
                    searchButtonId: 'term-search-button',
                    categoryFilterId: 'term-category-filter',
                    multiSelect: true,
                    required: true,
                    useAjax: true
                },
                {
                    modalId: 'activityModal',
                    searchId: 'activity-search',
                    listId: 'activity-list',
                    displayId: 'selected-activity-display',
                    hiddenInputId: 'activity-id',
                    saveButtonId: 'save-activity',
                    multiSelect: false,
                    required: true
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

                // Configuration spécifique pour le modal de termes avec AJAX
                if (config.useAjax) {
                    // Charger les catégories lors de l'ouverture du modal
                    modal.addEventListener('show.bs.modal', function() {
                        loadTermCategories();
                    });

                    // Configurer la recherche AJAX
                    const searchButton = document.getElementById(config.searchButtonId);
                    const categoryFilter = document.getElementById(config.categoryFilterId);

                    if (searchButton && categoryFilter) {
                        // Recherche lorsqu'on clique sur le bouton
                        searchButton.addEventListener('click', function() {
                            searchTerms(search.value, categoryFilter.value);
                        });

                        // Recherche lorsqu'on appuie sur Entrée dans le champ de recherche
                        search.addEventListener('keypress', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                searchTerms(search.value, categoryFilter.value);
                            }
                        });

                        // Recherche lorsqu'on change de catégorie
                        categoryFilter.addEventListener('change', function() {
                            searchTerms(search.value, categoryFilter.value);
                        });

                        // Recherche initiale avec des termes vides
                        modal.addEventListener('shown.bs.modal', function() {
                            searchTerms('', '');
                        });
                    }
                } else {
                    // Fonctionnalité de recherche standard pour les autres modaux
                    const items = list.querySelectorAll('.list-group-item');
                    search.addEventListener('input', () => filterList(search, items));

                    // Sélection d'éléments pour les modaux standard
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
                }

                // Sauvegarder la sélection (commun à tous les modals)
                saveButton.addEventListener('click', () => {
                    const selectedItems = list.querySelectorAll('.list-group-item.active');
                    // Utiliser le nom formaté stocké dans data-formatted-name s'il existe
                    const selectedNames = Array.from(selectedItems).map(item => {
                        // Utiliser le format leMot(PremiereLeMajusculeCategorie)
                        return item.dataset.formattedName || item.textContent.trim();
                    });
                    const selectedIds = Array.from(selectedItems).map(item => item.dataset.id);

                    displayInput.value = selectedNames.join('; ');
                    if (config.multiSelect) {
                        hiddenInput.value = selectedIds.join(',');
                    } else {
                        hiddenInput.value = selectedIds[0] || '';
                    }

                    // Ajouter une classe de validation si requis
                    if (config.required && hiddenInput.value === '') {
                        displayInput.classList.add('is-invalid');
                    } else {
                        displayInput.classList.remove('is-invalid');
                    }

                    bootstrap.Modal.getInstance(modal).hide();
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

            // Exécuter après chaque clic sur une section accordéon
            document.querySelectorAll('.accordion-button').forEach(button => {
                button.addEventListener('click', function() {
                    // Délai pour laisser Bootstrap traiter les transitions
                    setTimeout(ensureFieldsVisibility, 400);
                });
            });

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
        });
    </script>
@endsection
