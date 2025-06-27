@extends('layouts.app')

@push('styles')
<style>
    /* Stabiliser les boutons pour éviter qu'ils apparaissent/disparaissent */
    #clear-search-btn, #save-search-btn, #search-btn {
        position: relative;
        opacity: 1 !important;
        visibility: visible !important;
        transition: background-color 0.2s ease, border-color 0.2s ease !important;
        display: inline-block !important;
    }

    /* Empêcher les flickering des boutons */
    .btn {
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
        transform: translateZ(0);
        -webkit-transform: translateZ(0);
    }

    /* Stabiliser les onglets de l'accordéon */
    .accordion-button {
        position: relative !important;
        pointer-events: auto !important;
    }

    .accordion-button:not(.collapsed) {
        color: #0c63e4 !important;
        background-color: #e7f1ff !important;
        box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.125) !important;
    }

    /* Empêcher la fermeture automatique des onglets */
    .accordion-collapse.show {
        display: block !important;
    }

    .accordion-collapse.collapsing {
        position: relative;
        height: 0;
        overflow: hidden;
        transition: height 0.35s ease;
    }

    /* Forcer l'ouverture du premier onglet */
    #collapseDescription {
        display: block !important;
    }

    #collapseDescription.show {
        display: block !important;
    }

    /* Forcer l'ouverture de tous les onglets */
    #collapseDates,
    #collapseRelations {
        display: block !important;
    }

    #collapseDates.show,
    #collapseRelations.show {
        display: block !important;
    }

    /* Forcer l'affichage de tous les contenus d'accordéon */
    .accordion-collapse {
        display: block !important;
        height: auto !important;
    }

    .accordion-body {
        display: block !important;
        visibility: visible !important;
    }

    .list-group-flush {
        display: block !important;
    }

    .list-group-item {
        display: block !important;
        visibility: visible !important;
    }

    /* Éviter les animations indésirables */
    .search-criteria-row {
        transition: opacity 0.2s ease !important;
    }    /* Stabiliser les éléments dynamiques */
    .list-group-item {
        transition: background-color 0.15s ease-in-out !important;
        cursor: pointer !important;
    }

    .list-group-item:hover {
        background-color: #f8f9fa !important;
        border-color: #dee2e6 !important;
    }

    .list-group-item-action {
        cursor: pointer !important;
    }

    .list-group-item-action:hover {
        background-color: #e9ecef !important;
    }

    /* Empêcher les problèmes de z-index */
    .card {
        position: relative;
        z-index: 1;
    }

    .accordion {
        z-index: 2;
    }

    .accordion-body {
        z-index: 3;
    }
</style>
@endpush

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
                        <div class="simple-sections" id="fieldsContainer">
                            <!-- Description -->
                            <div class="section-item mb-3">
                                <h6 class="fw-bold text-primary">
                                    <i class="bi bi-file-text me-2"></i>{{ __('description') }}
                                </h6>
                                <div class="list-group">
                                    <a href="#" class="list-group-item list-group-item-action" data-field="code" data-name-field="Code">
                                        <i class="bi bi-hash me-2"></i>{{ __('code') }}
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action" data-field="name" data-name-field="Intitulé">
                                        <i class="bi bi-type me-2"></i>{{ __('name') }}
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action" data-field="content" data-name-field="Contenu">
                                        <i class="bi bi-file-text me-2"></i>{{ __('content') }}
                                    </a>
                                </div>
                            </div>

                            <!-- Dates -->
                            <div class="section-item mb-3">
                                <h6 class="fw-bold text-primary">
                                    <i class="bi bi-calendar me-2"></i>{{ __('dates') }}
                                </h6>
                                <div class="list-group">
                                    <a href="#" class="list-group-item list-group-item-action" data-field="return_date" data-name-field="Date retour prévue">
                                        <i class="bi bi-calendar me-2"></i>{{ __('return_date') }}
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action" data-field="return_effective" data-name-field="Date retour effective">
                                        <i class="bi bi-calendar-check me-2"></i>{{ __('return_effective') }}
                                    </a>
                                </div>
                            </div>

                            <!-- Relations -->
                            <div class="section-item mb-3">
                                <h6 class="fw-bold text-primary">
                                    <i class="bi bi-people me-2"></i>{{ __('relations') }}
                                </h6>
                                <div class="list-group">
                                    <a href="#" class="list-group-item list-group-item-action" data-field="status" data-name-field="Statut">
                                        <i class="bi bi-flag me-2"></i>{{ __('status') }}
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action" data-field="operator" data-name-field="Opérateur">
                                        <i class="bi bi-person me-2"></i>{{ __('operator') }}
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action" data-field="operator_organisation" data-name-field="Organisation opérateur">
                                        <i class="bi bi-building me-2"></i>{{ __('operator_organisation') }}
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action" data-field="user" data-name-field="Utilisateur">
                                        <i class="bi bi-person me-2"></i>{{ __('user') }}
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action" data-field="user_organisation" data-name-field="Organisation utilisateur">
                                        <i class="bi bi-building me-2"></i>{{ __('user_organisation') }}
                                    </a>
{{--                                    <a href="#" class="list-group-item list-group-item-action" data-field="record" data-name-field="Archive">--}}
{{--                                        <i class="bi bi-archive me-2"></i>{{ __('record') }}--}}
{{--                                    </a>--}}
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
                        <form id="advanced-search-form" method="POST" action="{{ route('search.communications.advanced') }}">
                            @csrf
                            <div id="search-criteria-container">
                                <div class="alert alert-info" id="no-criteria-message">
                                    <i class="bi bi-info-circle me-2"></i>{{ __('click_fields_to_add_criteria') }}
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-3">
                                <button type="submit" class="btn btn-primary" id="search-btn">
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
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Communications Advanced Search - Page chargée');

            // Données de l'application
            const data = @json($data ?? []);
            console.log('Données reçues du serveur:', data); // Debug

            // Configuration des opérateurs par type de champ
            const operatorConfig = {
                text: ['commence par', 'contient', 'ne contient pas', '='],
                date: ['=', '>', '<', '>=', '<='],
                select: ['=', '!=']
            };

            // Types de champs
            const fieldTypes = {
                code: 'text',
                name: 'text',
                content: 'text',
                return_date: 'date',
                return_effective: 'date',
                operator: 'select',
                operator_organisation: 'select',
                user: 'select',
                user_organisation: 'select',
                status: 'select'
            };

            // Données pour les champs select
            const selectFieldsData = {
                operator: data.operators || [],
                operator_organisation: data.organisations || [],
                user: data.users || [],
                user_organisation: data.organisations || [],
                status: data.statuses || [],
            };

            console.log('Configuration des champs select:', selectFieldsData); // Debug

            // Éléments du DOM
            const searchCriteriaContainer = document.getElementById('search-criteria-container');
            const searchCriteriaTemplate = document.getElementById('search-criteria-template');
            const noCriteriaMessage = document.getElementById('no-criteria-message');
            const clearSearchBtn = document.getElementById('clear-search-btn');
            const saveSearchBtn = document.getElementById('save-search-btn');

            // Gestionnaire de clic sur les champs
            const fieldElements = document.querySelectorAll('[data-field]');
            console.log('Nombre de champs détectés:', fieldElements.length); // Debug

            fieldElements.forEach((field, index) => {
                console.log(`Champ ${index}:`, field.getAttribute('data-field'), field.getAttribute('data-name-field')); // Debug

                field.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const fieldName = field.getAttribute('data-field');
                    const fieldLabel = field.getAttribute('data-name-field');

                    console.log('Clic sur le champ:', fieldName, fieldLabel); // Debug

                    // Ajouter un délai pour éviter les problèmes de double-clic
                    if (field.disabled) return;
                    field.disabled = true;

                    setTimeout(() => {
                        addSearchCriteria(fieldName, fieldLabel);
                        noCriteriaMessage.style.display = 'none';
                        field.disabled = false;
                    }, 100);
                });
            });

            // Fonction d'ajout de critère
            function addSearchCriteria(field, label) {
                console.log(`Ajout du critère: ${field} - ${label}`); // Debug

                const criteriaClone = document.importNode(searchCriteriaTemplate.content, true);

                // Configuration des éléments de base
                const fieldInput = criteriaClone.querySelector('.field-name');
                const fieldLabelSpan = criteriaClone.querySelector('.field-label');

                if (!fieldInput || !fieldLabelSpan) {
                    console.error('Éléments template non trouvés');
                    return;
                }

                fieldInput.value = field;
                fieldLabelSpan.textContent = label;

                const operatorSelect = criteriaClone.querySelector('.field-operator');
                const valueInput = criteriaClone.querySelector('.field-value');

                if (!operatorSelect || !valueInput) {
                    console.error('Éléments select/input non trouvés');
                    return;
                }

                // Configuration des opérateurs
                const fieldType = fieldTypes[field] || 'text';
                console.log(`Type de champ ${field}: ${fieldType}`); // Debug

                const operators = operatorConfig[fieldType] || operatorConfig.text;
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
                    // Créer un nouveau select
                    const selectElement = document.createElement('select');
                    selectElement.classList.add('form-select', 'form-select-sm', 'field-value');
                    selectElement.name = 'value[]';

                    // Ajout d'une option par défaut
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = '-- Sélectionner --';
                    selectElement.appendChild(defaultOption);

                    // Ajout des options selon le type de champ
                    const items = selectFieldsData[field] || [];
                    console.log(`Chargement des options pour ${field}:`, items); // Debug

                    items.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.name;
                        selectElement.appendChild(option);
                    });

                    // Remplacer l'input par le select
                    valueInput.parentNode.insertBefore(selectElement, valueInput);
                    valueInput.remove();
                }

                // Ajout au DOM avec animation stable
                const criteriaRow = criteriaClone.querySelector('.search-criteria-row');

                // Désactiver temporairement les transitions pour éviter les flickering
                criteriaRow.style.transition = 'none';
                criteriaRow.style.opacity = '0';

                searchCriteriaContainer.appendChild(criteriaClone);

                // Réactiver les transitions et animer
                requestAnimationFrame(() => {
                    criteriaRow.style.transition = 'opacity 0.3s ease-in-out';
                    requestAnimationFrame(() => {
                        criteriaRow.style.opacity = '1';
                    });
                });
            }

            // Suppression d'un critère
            searchCriteriaContainer.addEventListener('click', function(e) {
                if (e.target.closest('.remove-criteria-btn')) {
                    e.preventDefault();
                    e.stopPropagation();

                    const criteriaRow = e.target.closest('.search-criteria-row');
                    const button = e.target.closest('.remove-criteria-btn');

                    // Désactiver le bouton pour éviter les double-clics
                    button.disabled = true;

                    criteriaRow.style.transition = 'opacity 0.3s ease-in-out';
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

                const searchName = prompt('Nom de la recherche :', '');
                if (searchName) {
                    const savedSearches = JSON.parse(localStorage.getItem('savedCommunicationSearches') || '[]');
                    savedSearches.push({
                        name: searchName,
                        criteria: searchCriteria,
                        date: new Date().toISOString()
                    });
                    localStorage.setItem('savedCommunicationSearches', JSON.stringify(savedSearches));
                    updateSavedSearchesList();
                    showToast('success', 'Recherche sauvegardée avec succès');
                }
            });

            // Collecter les critères de recherche
            function collectSearchCriteria() {
                const criteria = [];
                const rows = searchCriteriaContainer.querySelectorAll('.search-criteria-row');

                rows.forEach(row => {
                    const fieldInput = row.querySelector('.field-name');
                    const operatorSelect = row.querySelector('.field-operator');
                    const valueElement = row.querySelector('.field-value');

                    if (fieldInput && operatorSelect && valueElement) {
                        criteria.push({
                            field: fieldInput.value,
                            operator: operatorSelect.value,
                            value: valueElement.value
                        });
                    }
                });

                console.log('Critères collectés:', criteria); // Debug
                return criteria;
            }

            // Mise à jour de la liste des recherches sauvegardées
            function updateSavedSearchesList() {
                const container = document.getElementById('saved-searches-container');
                const savedSearches = JSON.parse(localStorage.getItem('savedCommunicationSearches') || '[]');

                if (savedSearches.length === 0) {
                    container.innerHTML = '<div class="text-muted">Aucune recherche sauvegardée</div>';
                    return;
                }

                container.innerHTML = savedSearches.map((search, index) => `
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

                // Gestionnaires d'événements pour les recherches sauvegardées
                attachSavedSearchHandlers();
            }

            // Attacher les gestionnaires aux boutons des recherches sauvegardées
            function attachSavedSearchHandlers() {
                const container = document.getElementById('saved-searches-container');
                const savedSearches = JSON.parse(localStorage.getItem('savedCommunicationSearches') || '[]');

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
                        localStorage.setItem('savedCommunicationSearches', JSON.stringify(savedSearches));
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

                            // Attendre que l'élément soit ajouté au DOM
                            setTimeout(() => {
                                const rows = searchCriteriaContainer.querySelectorAll('.search-criteria-row');
                                const lastRow = rows[rows.length - 1];

                                if (lastRow) {
                                    const operatorSelect = lastRow.querySelector('.field-operator');
                                    const valueInput = lastRow.querySelector('.field-value');

                                    if (operatorSelect) {
                                        operatorSelect.value = criterion.operator;
                                    }
                                    if (valueInput) {
                                        valueInput.value = criterion.value;
                                    }
                                }
                            }, 100);
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

            // Validation du formulaire avant soumission
            const searchForm = document.getElementById('advanced-search-form');
            searchForm.addEventListener('submit', function(e) {
                const criteria = collectSearchCriteria();

                if (criteria.length === 0) {
                    e.preventDefault();
                    showToast('warning', 'Veuillez ajouter au moins un critère de recherche');
                    return false;
                }

                // Vérifier que tous les critères ont des valeurs
                const invalidCriteria = criteria.filter(c => !c.value || c.value.trim() === '');
                if (invalidCriteria.length > 0) {
                    e.preventDefault();
                    showToast('warning', 'Tous les critères doivent avoir une valeur');
                    return false;
                }

                console.log('Soumission du formulaire avec les critères:', criteria); // Debug
                return true;
            });
        });

    </script>
@endpush
