@extends('layouts.app')

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
                                            <a href="#" class="list-group-item list-group-item-action" data-field="content" data-name-field="Contenu">
                                                <i class="bi bi-file-text me-2"></i>{{ __('content') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dates -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDates">
                                        {{ __('dates') }}
                                    </button>
                                </h2>
                                <div id="collapseDates" class="accordion-collapse collapse" data-bs-parent="#fieldsAccordion">
                                    <div class="accordion-body p-0">
                                        <div class="list-group list-group-flush">
                                            <a href="#" class="list-group-item list-group-item-action" data-field="return_date" data-name-field="Date retour prévue">
                                                <i class="bi bi-calendar me-2"></i>{{ __('return_date') }}
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action" data-field="return_effective" data-name-field="Date retour effective">
                                                <i class="bi bi-calendar-check me-2"></i>{{ __('return_effective') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Relations -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRelations">
                                        {{ __('relations') }}
                                    </button>
                                </h2>
                                <div id="collapseRelations" class="accordion-collapse collapse" data-bs-parent="#fieldsAccordion">
                                    <div class="accordion-body p-0">
                                        <div class="list-group list-group-flush">
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
{{--                                            <a href="#" class="list-group-item list-group-item-action" data-field="record" data-name-field="Archive">--}}
{{--                                                <i class="bi bi-archive me-2"></i>{{ __('record') }}--}}
{{--                                            </a>--}}
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
                        <form id="advanced-search-form" method="POST" action="{{ route('search.communications.advanced') }}">
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
        document.addEventListener('DOMContentLoaded', function() {
            // Données de l'application
            const data = @json($data ?? []);

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
                return_date: 'date',
                return_effective: 'date',
                operator: 'select',
                operator_organisation: 'select',
                user: 'select',
                user_organisation: 'select',
                status: 'select',
                record: 'select'
            };

            // Données pour les champs select
            const selectFieldsData = {
                operator: data.operators,
                operator_organisation: data.organisations,
                user: data.users,
                user_organisation: data.organisations,
                status: data.statuses,
            };

            // Éléments du DOM
            const searchCriteriaContainer = document.getElementById('search-criteria-container');
            const searchCriteriaTemplate = document.getElementById('search-criteria-template');
            const noCriteriaMessage = document.getElementById('no-criteria-message');
            const clearSearchBtn = document.getElementById('clear-search-btn');
            const saveSearchBtn = document.getElementById('save-search-btn');

            // Gestionnaire de clic sur les champs
            document.querySelectorAll('[data-field]').forEach(field => {
                field.addEventListener('click', (e) => {
                    e.preventDefault();
                    const fieldName = field.getAttribute('data-field');
                    const fieldLabel = field.getAttribute('data-name-field');
                    addSearchCriteria(fieldName, fieldLabel);
                    noCriteriaMessage.style.display = 'none';
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

                    // Ajout des options
                    const items = selectFieldsData[field] || [];
                    items.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.name;
                        selectElement.appendChild(option);
                    });

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
                    criteria.push({
                        field: row.querySelector('.field-name').value,
                        operator: row.querySelector('.field-operator').value,
                        value: row.querySelector('.field-value').value
                    });
                });

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
