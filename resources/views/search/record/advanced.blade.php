@extends('layouts.app')

@section('styles')
<style>
    /* Assurer que tous les éléments dans les accordéons sont cliquables */
    .accordion-collapse,
    .accordion-body,
    .list-group-item-action {
        pointer-events: auto !important;
    }

    /* S'assurer que les éléments interactifs sont toujours cliquables */
    .accordion-item * {
        pointer-events: auto !important;
    }

    /* Rendre visible l'intérieur des accordéons même quand ils sont ouverts */
    .accordion-collapse.show {
        visibility: visible !important;
        opacity: 1 !important;
        height: auto !important;
        overflow: visible !important;
        display: block !important;
    }

    /* Corriger l'affichage des listes à l'intérieur des accordéons */
    .list-group-flush {
        pointer-events: auto !important;
        visibility: visible !important;
        display: block !important;
    }

    /* Garantir que tous les liens dans les accordéons sont visibles et cliquables */
    .list-group-item-action {
        visibility: visible !important;
        display: flex !important;
        opacity: 1 !important;
        position: relative !important;
        z-index: 100 !important; /* Assurer un z-index élevé pour éviter que d'autres éléments passent au-dessus */
    }

    /* Désactiver les animations qui peuvent causer des problèmes */
    .accordion-collapse {
        transition: none !important;
    }

    /* Forcer l'affichage de tous les éléments à l'intérieur des accordéons */
    #fieldsAccordion .list-group,
    #fieldsAccordion .list-group-item {
        display: block !important;
        opacity: 1 !important;
        height: auto !important;
    }
</style>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <!-- Sidebar des champs -->
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">{{ __('available_fields') }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="accordion" id="fieldsAccordion">
                            <!-- Description -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDescription">
                                        {{ __('description') }}
                                    </button>
                                </h2>
                                <div id="collapseDescription" class="accordion-collapse collapse show" data-bs-parent="#fieldsAccordion">
                                    <div class="accordion-body p-0">
                                        <div class="list-group list-group-flush" id="description-fields">
                                            <a href="#" class="list-group-item list-group-item-action" data-field="code" data-name-field="Code">
                                                <i class="bi bi-hash me-2"></i>{{ __('code') }}
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action" data-field="name" data-name-field="Intitulé">
                                                <i class="bi bi-type me-2"></i>{{ __('name') }}
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action" data-field="author" data-name-field="Producteur">
                                                <i class="bi bi-person me-2"></i>{{ __('author') }}
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action" data-field="content" data-name-field="Contenu">
                                                <i class="bi bi-file-text me-2"></i>{{ __('content') }}
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action" data-field="date_start" data-name-field="Date début">
                                                <i class="bi bi-calendar me-2"></i>{{ __('start_date') }}
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action" data-field="date_end" data-name-field="Date fin">
                                                <i class="bi bi-calendar me-2"></i>{{ __('end_date') }}
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action" data-field="date_exact" data-name-field="Date exacte">
                                                <i class="bi bi-calendar me-2"></i>{{ __('exact_date') }}
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action" data-field="status" data-name-field="Statut">
                                                <i class="bi bi-flag me-2"></i>{{ __('status') }}
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action" data-field="date_creation" data-name-field="Date création">
                                                <i class="bi bi-calendar-plus me-2"></i>{{ __('creation_date') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cycle de vie -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLifecycle">
                                        {{ __('lifecycle') }}
                                    </button>
                                </h2>
                                <div id="collapseLifecycle" class="accordion-collapse collapse" data-bs-parent="#fieldsAccordion">
                                    <div class="accordion-body p-0">
                                        <div class="list-group list-group-flush">
                                            <a href="#" class="list-group-item list-group-item-action" data-field="dua" data-name-field="Délai communicabilité">
                                                <i class="bi bi-clock-history me-2"></i>{{ __('communication_delay') }}
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action" data-field="dul" data-name-field="Délai légal">
                                                <i class="bi bi-clock me-2"></i>{{ __('legal_delay') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Localisation -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLocation">
                                        {{ __('location') }}
                                    </button>
                                </h2>
                                <div id="collapseLocation" class="accordion-collapse collapse" data-bs-parent="#fieldsAccordion">
                                    <div class="accordion-body p-0">
                                        <div class="list-group list-group-flush">
                                            <a href="#" class="list-group-item list-group-item-action" data-field="container" data-name-field="Boite/chrono">
                                                <i class="bi bi-archive me-2"></i>{{ __('archive_box') }}
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action" data-field="shelf" data-name-field="Etagère">
                                                <i class="bi bi-bookshelf me-2"></i>{{ __('shelf') }}
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action" data-field="room" data-name-field="Dépôt">
                                                <i class="bi bi-building me-2"></i>{{ __('storage') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Indexation -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseIndexation">
                                        {{ __('indexation') }}
                                    </button>
                                </h2>
                                <div id="collapseIndexation" class="accordion-collapse collapse" data-bs-parent="#fieldsAccordion">
                                    <div class="accordion-body p-0">
                                        <div class="list-group list-group-flush">
                                            <a href="#" class="list-group-item list-group-item-action" data-field="term" data-name-field="Terme (thésaurus)">
                                                <i class="bi bi-tags me-2"></i>{{ __('term') }}
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action" data-field="activity" data-name-field="Activité">
                                                <i class="bi bi-gear me-2"></i>{{ __('activity') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Autres -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOthers">
                                        {{ __('others') }}
                                    </button>
                                </h2>
                                <div id="collapseOthers" class="accordion-collapse collapse" data-bs-parent="#fieldsAccordion">
                                    <div class="accordion-body p-0">
                                        <div class="list-group list-group-flush">
                                            <a href="#" class="list-group-item list-group-item-action" data-field="creator" data-name-field="Créateur">
                                                <i class="bi bi-person-plus me-2"></i>{{ __('creator') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Zone de recherche -->
            <div class="col-md-9">
                <div class="card shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ __('search_criteria') }}</h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="clear-search-btn">
                                <i class="bi bi-x-circle me-1"></i>{{ __('clear') }}
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="save-search-btn">
                                <i class="bi bi-bookmark me-1"></i>{{ __('save_search') }}
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="advanced-search-form" method="POST" action="{{ route('records.advanced') }}">
                            @csrf
                            <div id="search-criteria-container">
                                <div class="alert alert-info" id="no-criteria-message">
                                    <i class="bi bi-info-circle me-2"></i>{{ __('click_fields_to_add_criteria') }}
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i>{{ __('search') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Recherches sauvegardées -->
                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">{{ __('saved_searches') }}</h5>
                    </div>
                    <div class="card-body" id="saved-searches-container">
                        <!-- Les recherches sauvegardées seront ajoutées ici dynamiquement -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Template de critère -->
    <template id="search-criteria-template">
        <div class="search-criteria-row card mb-2">
            <div class="card-body p-2">
                <div class="d-flex align-items-center">
                    <input type="hidden" name="field[]" class="field-name">
                    <div class="me-2 field-label-container">
                        <span class="badge bg-secondary field-label"></span>
                    </div>
                    <select class="form-select form-select-sm me-2 field-operator" style="width: auto;" name="operator[]">
                        <!-- Options ajoutées dynamiquement -->
                    </select>
                    <div class="flex-grow-1 me-2">
                        <input type="text" class="form-control form-control-sm field-value" name="value[]">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-criteria-btn">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </template>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {            // Fonction complète pour corriger les problèmes d'interactivité des accordéons
            const fixAccordionInteractivity = () => {
                // Traiter tous les accordéons
                document.querySelectorAll('.accordion-collapse').forEach(collapse => {
                    // Forcer l'élément accordéon à être interactif
                    collapse.style.pointerEvents = 'auto';
                    collapse.style.visibility = 'visible';
                    collapse.style.overflow = 'visible';
                    collapse.style.height = 'auto';
                    collapse.style.display = 'block';
                    collapse.style.opacity = '1';
                    collapse.style.position = 'relative';
                    collapse.style.zIndex = '5';

                    // Traiter les contenus internes
                    collapse.querySelectorAll('.accordion-body').forEach(body => {
                        body.style.pointerEvents = 'auto';
                        body.style.visibility = 'visible';
                        body.style.display = 'block';
                    });

                    // Traiter les listes à l'intérieur
                    collapse.querySelectorAll('.list-group, .list-group-flush').forEach(list => {
                        list.style.pointerEvents = 'auto';
                        list.style.visibility = 'visible';
                        list.style.display = 'block';
                        list.style.opacity = '1';
                    });

                    // Traiter tous les éléments cliquables
                    collapse.querySelectorAll('.list-group-item, .list-group-item-action, a, button, input, select').forEach(element => {
                        element.style.pointerEvents = 'auto';
                        element.style.visibility = 'visible';
                        element.style.opacity = '1';
                        element.style.position = 'relative';
                        element.style.zIndex = '10';
                        element.style.display = element.tagName.toLowerCase() === 'a' ? 'flex' : '';
                    });

                    // S'assurer que tous les champs avec un attribut data-field sont particulièrement visibles
                    collapse.querySelectorAll('[data-field]').forEach(field => {
                        field.style.pointerEvents = 'auto';
                        field.style.visibility = 'visible';
                        field.style.opacity = '1';
                        field.style.position = 'relative';
                        field.style.zIndex = '20'; // Z-index plus élevé pour ces éléments critiques
                    });
                });
            };

            // Appliquer les correctifs immédiatement
            fixAccordionInteractivity();

            // Forcer tous les accordéons ouverts par défaut pour éviter les problèmes
            document.querySelectorAll('.accordion-collapse').forEach(collapse => {
                collapse.classList.add('show');
            });

            // Mettre en place une surveillance continue pour garantir l'interactivité
            const observer = new MutationObserver(() => {
                fixAccordionInteractivity();
            });

            // Observer les changements dans les accordéons
            document.querySelectorAll('.accordion').forEach(accordion => {
                observer.observe(accordion, { subtree: true, attributes: true, childList: true });
            });

            // Ajouter des écouteurs d'événements pour corriger après chaque clic
            document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(button => {
                button.addEventListener('click', () => {
                    // Appliquer immédiatement
                    fixAccordionInteractivity();

                    // Puis réappliquer après un délai pour gérer les animations
                    setTimeout(fixAccordionInteractivity, 50);
                    setTimeout(fixAccordionInteractivity, 300);
                });
            });

            // Corriger également après le chargement complet
            window.addEventListener('load', fixAccordionInteractivity);

            // Données de l'application
            const data = @json($data ?? []);
            console.log('Données chargées:', data); // Pour déboguer

            // Vérifiez que les données existent pour chaque select
            console.log('Rooms:', data.rooms);
            console.log('Shelves:', data.shelve);
            // Éléments du DOM
            const searchCriteriaContainer = document.getElementById('search-criteria-container');
            const searchCriteriaTemplate = document.getElementById('search-criteria-template');
            const noCriteriaMessage = document.getElementById('no-criteria-message');
            const clearSearchBtn = document.getElementById('clear-search-btn');
            const saveSearchBtn = document.getElementById('save-search-btn');

            // Configuration des opérateurs par type de champ
            const operatorConfig = {
                text: ['commence par', 'contient', 'ne contient pas'],
                date: ['=', '>', '<'],
                select: ['avec', 'sauf']
            };

            // Types de champs
            const fieldTypes = {
                code: 'text',
                name: 'text',
                content: 'text',
                date_start: 'date',
                date_end: 'date',
                date_exact: 'date',
                date_creation: 'date',
                dua: 'date',
                dul: 'date',
                room: 'select',
                shelf: 'select',
                activity: 'select',
                term: 'select',
                author: 'select',
                creator: 'select',
                container: 'select',
                status: 'select'
            };

            // Mapping des champs select avec leurs données
            const selectFieldsData = {
                room: data.rooms,
                shelf: data.shelve,
                activity: data.activities,
                term: data.terms,
                author: data.authors,
                creator: data.creators,
                container: data.containers,
                status: data.statues
            };            // Fonction pour rendre un élément complètement cliquable
            const makeFullyInteractive = (element) => {
                element.style.pointerEvents = 'auto';
                element.style.visibility = 'visible';
                element.style.opacity = '1';
                element.style.position = 'relative';
                element.style.zIndex = '50'; // Z-index très élevé
                element.style.cursor = 'pointer'; // Assurer que le curseur indique un élément cliquable
            };

            // Gestionnaire de clic sur les champs avec amélioration de l'interactivité
            document.querySelectorAll('[data-field]').forEach(field => {
                // Application des styles essentiels pour rendre le champ cliquable
                makeFullyInteractive(field);

                // S'assurer que tous les parents sont également cliquables
                let parent = field.parentElement;
                while (parent && parent !== document.body) {
                    parent.style.pointerEvents = 'auto';
                    parent = parent.parentElement;
                }

                // Attacher plusieurs écouteurs d'événements pour maximiser les chances de capture
                ['click', 'mousedown', 'touchstart'].forEach(eventType => {
                    field.addEventListener(eventType, (e) => {
                        // Bloquer la propagation et le comportement par défaut
                        e.preventDefault();
                        e.stopPropagation();

                        console.log('Champ cliqué:', field.getAttribute('data-field')); // Pour le débogage

                        const fieldName = field.getAttribute('data-field');
                        const fieldLabel = field.getAttribute('data-name-field');
                        addSearchCriteria(fieldName, fieldLabel);
                        noCriteriaMessage.style.display = 'none';
                    }, true); // Use capture phase
                });
            });

            // Fonction d'ajout de critère
            function addSearchCriteria(field, label) {
                const criteriaClone = document.importNode(searchCriteriaTemplate.content, true);

                // Configuration des éléments de base
                criteriaClone.querySelector('.field-name').value = field;
                criteriaClone.querySelector('.field-label').textContent = label;

                const operatorSelect = criteriaClone.querySelector('.field-operator');
                const valueInput = criteriaClone.querySelector('.field-value');

                // Configuration des opérateurs
                const fieldType = fieldTypes[field];
                const operators = operatorConfig[fieldType];
                operators.forEach(op => {
                    const option = document.createElement('option');
                    option.value = op;
                    option.textContent = op;
                    operatorSelect.appendChild(option);
                });

                // Configuration du champ de valeur
                if (fieldType === 'date') {
                    valueInput.type = 'date';
                } else if (fieldType === 'select') {
                    const selectElement = document.createElement('select');
                    selectElement.classList.add('form-select', 'form-select-sm');
                    selectElement.name = 'value[]';

                    // Ajout d'une option par défaut
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = '-- Sélectionner --';
                    selectElement.appendChild(defaultOption);

                    // Vérification et ajout des options
                    const items = selectFieldsData[field] || [];
                    if (Array.isArray(items) && items.length > 0) {
                        items.forEach(item => {
                            if (item && typeof item === 'object') {
                                const option = document.createElement('option');
                                option.value = item.id;
                                // Gestion plus robuste du texte de l'option
                                option.textContent = item.name || item.title || item.code ||
                                    (typeof item.toString === 'function' ? item.toString() : '');
                                selectElement.appendChild(option);
                            }
                        });
                    } else {
                        console.warn(`Aucune donnée trouvée pour le champ ${field}`);
                    }

                    valueInput.replaceWith(selectElement);
                }

                // Ajout des classes pour l'animation
                const criteriaRow = criteriaClone.querySelector('.search-criteria-row');
                criteriaRow.style.opacity = '0';
                searchCriteriaContainer.appendChild(criteriaClone);

                // Animation d'apparition
                requestAnimationFrame(() => {
                    criteriaRow.style.transition = 'opacity 0.3s ease-in-out';
                    criteriaRow.style.opacity = '1';
                });
            }

            // Suppression d'un critère
            searchCriteriaContainer.addEventListener('click', function(e) {
                if (e.target.closest('.remove-criteria-btn')) {
                    const criteriaRow = e.target.closest('.search-criteria-row');
                    criteriaRow.style.opacity = '0';
                    setTimeout(() => {
                        criteriaRow.remove();
                        if (searchCriteriaContainer.querySelectorAll('.search-criteria-row').length === 0) {
                            noCriteriaMessage.style.display = 'block';
                        }
                    }, 300);
                }
            });

            // Effacer tous les critères
            clearSearchBtn.addEventListener('click', function() {
                const criteriaRows = searchCriteriaContainer.querySelectorAll('.search-criteria-row');
                criteriaRows.forEach(row => {
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                });
                setTimeout(() => {
                    noCriteriaMessage.style.display = 'block';
                }, 300);
            });

            // Sauvegarder la recherche
            saveSearchBtn.addEventListener('click', function() {
                const searchCriteria = collectSearchCriteria();
                if (searchCriteria.length === 0) {
                    showToast('warning', 'Veuillez ajouter au moins un critère de recherche');
                    return;
                }

                // Demander le nom de la recherche
                const searchName = prompt('Nom de la recherche :', '');
                if (searchName) {
                    const savedSearches = JSON.parse(localStorage.getItem('savedSearches') || '[]');
                    savedSearches.push({
                        name: searchName,
                        criteria: searchCriteria,
                        date: new Date().toISOString()
                    });
                    localStorage.setItem('savedSearches', JSON.stringify(savedSearches));
                    updateSavedSearchesList();
                    showToast('success', 'Recherche sauvegardée avec succès');
                }
            });

            // Collecter les critères de recherche
            function collectSearchCriteria() {
                const criteria = [];
                const rows = searchCriteriaContainer.querySelectorAll('.search-criteria-row');

                rows.forEach(row => {
                    criteria.push({
                        field: row.querySelector('.field-name').value,
                        operator: row.querySelector('.field-operator').value,
                        value: row.querySelector('.field-value').value
                    });
                });

                return criteria;
            }

            // Mettre à jour la liste des recherches sauvegardées
            function updateSavedSearchesList() {
                const container = document.getElementById('saved-searches-container');
                const savedSearches = JSON.parse(localStorage.getItem('savedSearches') || '[]');

                if (savedSearches.length === 0) {
                    container.innerHTML = '<div class="text-muted">Aucune recherche sauvegardée</div>';
                    return;
                }

                const searchesHTML = savedSearches.map((search, index) => `
            <div class="saved-search-item d-flex justify-content-between align-items-center border-bottom py-2">
                <div>
                    <h6 class="mb-1">${search.name}</h6>
                    <small class="text-muted">${new Date(search.date).toLocaleDateString()}</small>
                </div>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary load-search" data-index="${index}">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete-search" data-index="${index}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');

                container.innerHTML = searchesHTML;

                // Gestionnaires d'événements pour les recherches sauvegardées
                container.querySelectorAll('.load-search').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const index = this.dataset.index;
                        loadSavedSearch(savedSearches[index].criteria);
                    });
                });

                container.querySelectorAll('.delete-search').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const index = this.dataset.index;
                        savedSearches.splice(index, 1);
                        localStorage.setItem('savedSearches', JSON.stringify(savedSearches));
                        updateSavedSearchesList();
                        showToast('success', 'Recherche supprimée');
                    });
                });
            }

            // Charger une recherche sauvegardée
            function loadSavedSearch(criteria) {
                clearSearchBtn.click();
                setTimeout(() => {
                    criteria.forEach(criterion => {
                        const field = document.querySelector(`[data-field="${criterion.field}"]`);
                        if (field) {
                            const fieldName = field.getAttribute('data-field');
                            const fieldLabel = field.getAttribute('data-name-field');
                            addSearchCriteria(fieldName, fieldLabel);
                            const lastRow = searchCriteriaContainer.lastElementChild;
                            lastRow.querySelector('.field-operator').value = criterion.operator;
                            const valueInput = lastRow.querySelector('.field-value');
                            if (valueInput) {
                                valueInput.value = criterion.value;
                            }
                        }
                    });
                }, 300);
            }

            // Afficher un toast
            function showToast(type, message) {
                const toastContainer = document.getElementById('toast-container') || createToastContainer();
                const toast = document.createElement('div');
                toast.className = `toast align-items-center text-white bg-${type} border-0`;
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');

                toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

                toastContainer.appendChild(toast);
                const bsToast = new bootstrap.Toast(toast, { autohide: true, delay: 3000 });
                bsToast.show();

                toast.addEventListener('hidden.bs.toast', () => toast.remove());
            }

            // Créer le conteneur de toast
            function createToastContainer() {
                const container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                document.body.appendChild(container);
                return container;
            }

            // Initialiser les recherches sauvegardées au chargement
            updateSavedSearchesList();
        });
    </script>
@endpush
