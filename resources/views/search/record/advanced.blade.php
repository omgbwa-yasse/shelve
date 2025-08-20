@extends('layouts.app')

@section('styles')
<style>
    /* Styles simplifiés et optimisés */
    .list-group-item-action {
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .search-criteria-row {
        transition: all 0.3s ease;
    }

    .saved-search-item {
        transition: background-color 0.2s ease;
    }

    .field-icon {
        width: 18px;
        text-align: center;
        margin-right: 0.5rem;
    }

    .criteria-appear {
        animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Styles pour les sections de champs */
    .field-section h6 {
        font-size: 0.95rem;
        color: #495057;
    }

    .field-section .list-group-item-action {
        border: none;
        border-radius: 4px;
        margin-bottom: 2px;
    }

    .list-group-flush {
        padding-left: 0.5rem;
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
                        <h5 class="mb-0">{{ __('search.available_fields') }}</h5>
                    </div>
                    <div class="card-body">
                        <!-- Section: Description -->
                        <div class="field-section mb-3">
                            <h6 class="border-bottom pb-2 mb-2">{{ __('search.description') }}</h6>
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action px-2 py-1" data-field="code" data-name-field="{{ __('search.code') }}">
                                    <i class="bi bi-hash field-icon"></i> {{ __('search.code') }}
                                </a>
                                <a href="#" class="list-group-item list-group-item-action px-2 py-1" data-field="name" data-name-field="{{ __('search.name') }}">
                                    <i class="bi bi-type field-icon"></i> {{ __('search.name') }}
                                </a>
                                <a href="#" class="list-group-item list-group-item-action px-2 py-1" data-field="author" data-name-field="{{ __('search.author') }}">
                                    <i class="bi bi-person field-icon"></i> {{ __('search.author') }}
                                </a>
                                <a href="#" class="list-group-item list-group-item-action px-2 py-1" data-field="content" data-name-field="{{ __('search.content') }}">
                                    <i class="bi bi-file-text field-icon"></i> {{ __('search.content') }}
                                </a>
                                <a href="#" class="list-group-item list-group-item-action px-2 py-1" data-field="attachment" data-name-field="{{ __('Attachments') }}">
                                    <i class="bi bi-paperclip field-icon"></i> {{ __('Attachments') }}
                                </a>
                                <a href="#" class="list-group-item list-group-item-action px-2 py-1" data-field="attachment_content" data-name-field="{{ __('Attachment content') }}">
                                    <i class="bi bi-paperclip field-icon"></i> {{ __('Attachment content') }}
                                </a>
                                <a href="#" class="list-group-item list-group-item-action px-2 py-1" data-field="date_start" data-name-field="{{ __('search.start_date') }}">
                                    <i class="bi bi-calendar field-icon"></i> {{ __('search.start_date') }}
                                </a>
                                <a href="#" class="list-group-item list-group-item-action px-2 py-1" data-field="date_end" data-name-field="{{ __('search.end_date') }}">
                                    <i class="bi bi-calendar field-icon"></i> {{ __('search.end_date') }}
                                </a>
                                <a href="#" class="list-group-item list-group-item-action px-2 py-1" data-field="date_exact" data-name-field="{{ __('search.exact_date') }}">
                                    <i class="bi bi-calendar field-icon"></i> {{ __('search.exact_date') }}
                                </a>
                                <a href="#" class="list-group-item list-group-item-action px-2 py-1" data-field="status" data-name-field="{{ __('search.status') }}">
                                    <i class="bi bi-flag field-icon"></i> {{ __('search.status') }}
                                </a>
                                <a href="#" class="list-group-item list-group-item-action px-2 py-1" data-field="date_creation" data-name-field="{{ __('search.creation_date') }}">
                                    <i class="bi bi-calendar-plus field-icon"></i> {{ __('search.creation_date') }}
                                </a>
                            </div>
                        </div>

                        <!-- Section: Cycle de vie -->
                        <div class="field-section mb-3">
                            <h6 class="border-bottom pb-2 mb-2">{{ __('search.lifecycle') }}</h6>
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action px-2 py-1" data-field="dua" data-name-field="{{ __('search.communication_delay') }}">
                                    <i class="bi bi-clock-history field-icon"></i> {{ __('search.communication_delay') }}
                                </a>
                                <a href="#" class="list-group-item list-group-item-action px-2 py-1" data-field="dul" data-name-field="{{ __('search.legal_delay') }}">
                                    <i class="bi bi-clock field-icon"></i> {{ __('search.legal_delay') }}
                                </a>
                            </div>
                        </div>

                        <!-- Section: Localisation -->
                        <div class="field-section mb-3">
                            <h6 class="border-bottom pb-2 mb-2">{{ __('search.location') }}</h6>
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action px-2 py-1" data-field="container" data-name-field="{{ __('search.archive_box') }}">
                                    <i class="bi bi-archive field-icon"></i> {{ __('search.archive_box') }}
                                </a>
                                <a href="#" class="list-group-item list-group-item-action px-2 py-1" data-field="shelf" data-name-field="{{ __('search.shelf') }}">
                                    <i class="bi bi-bookshelf field-icon"></i> {{ __('search.shelf') }}
                                </a>
                                <a href="#" class="list-group-item list-group-item-action px-2 py-1" data-field="room" data-name-field="{{ __('search.storage') }}">
                                    <i class="bi bi-building field-icon"></i> {{ __('search.storage') }}
                                </a>
                            </div>
                        </div>

                        <!-- Section: Indexation -->
                        <div class="field-section mb-3">
                            <h6 class="border-bottom pb-2 mb-2">{{ __('search.indexation') }}</h6>
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action px-2 py-1" data-field="term" data-name-field="{{ __('search.term') }}">
                                    <i class="bi bi-tags field-icon"></i> {{ __('search.term') }}
                                </a>
                                <a href="#" class="list-group-item list-group-item-action px-2 py-1" data-field="activity" data-name-field="{{ __('search.activity') }}">
                                    <i class="bi bi-gear field-icon"></i> {{ __('search.activity') }}
                                </a>
                            </div>
                        </div>

                        <!-- Section: Autres -->
                        <div class="field-section">
                            <h6 class="border-bottom pb-2 mb-2">{{ __('search.others') }}</h6>
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action px-2 py-1" data-field="creator" data-name-field="{{ __('search.creator') }}">
                                    <i class="bi bi-person-plus field-icon"></i> {{ __('search.creator') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Zone de recherche -->
            <div class="col-md-9">
                <div class="card shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ __('search.search_criteria') }}</h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="clear-search-btn">
                                <i class="bi bi-x-circle me-1"></i> {{ __('search.clear') }}
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="save-search-btn">
                                <i class="bi bi-bookmark me-1"></i> {{ __('search.save_search') }}
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="advanced-search-form" method="POST" action="{{ route('records.advanced') }}">
                            @csrf
                            <div id="search-criteria-container">
                                <div class="alert alert-info" id="no-criteria-message">
                                    <i class="bi bi-info-circle me-2"></i> {{ __('search.click_fields_to_add_criteria') }}
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i> {{ __('search.search') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Recherches sauvegardées -->
                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ __('search.saved_searches') }}</h5>
                        <span class="badge bg-secondary" id="saved-searches-count">0</span>
                    </div>
                    <div class="card-body" id="saved-searches-container">
                        <div class="text-muted" id="no-saved-searches">{{ __('search.no_saved_searches') }}</div>
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
                    <div class="flex-grow-1 me-2 field-value-container">
                        <!-- Input ajouté dynamiquement -->
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-criteria-btn">
                        <i class="bi bi-trash me-1"></i> {{ __('search.remove_criteria') }}
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- Template pour une recherche sauvegardée -->
    <template id="saved-search-template">
        <div class="saved-search-item d-flex justify-content-between align-items-center border-bottom py-2">
            <div>
                <div class="fw-bold mb-1 saved-search-name"><!-- Sera rempli dynamiquement --></div>
                <small class="text-muted saved-search-date"><!-- Sera rempli dynamiquement --></small>
            </div>
            <div class="btn-group">
                <button class="btn btn-sm btn-outline-primary load-search" title="{{ __('search.load_search') }}">
                    <i class="bi bi-arrow-clockwise me-1"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger delete-search" title="{{ __('search.delete_search') }}">
                    <i class="bi bi-trash me-1"></i>
                </button>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Module principal de recherche avancée
            const AdvancedSearch = (function() {
                // Configuration privée
                const config = {
                    operatorsByType: {
                        text: ['{{ __("search.begins_with") }}', '{{ __("search.contains") }}', '{{ __("search.does_not_contain") }}', '{{ __("search.is_exactly") }}'],
                        date: ['{{ __("search.equals") }}', '{{ __("search.greater_than") }}', '{{ __("search.less_than") }}', '{{ __("search.greater_than_or_equal") }}', '{{ __("search.less_than_or_equal") }}', '{{ __("search.between") }}'],
                        select: ['{{ __("search.with") }}', '{{ __("search.without") }}']
                    },
                    fieldTypes: {
                        code: 'text',
                        name: 'text',
                        content: 'text',
                            attachment: 'text',
                            attachment_content: 'text',
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
                    }
                };

                // Éléments du DOM
                const elements = {
                    container: document.getElementById('search-criteria-container'),
                    template: document.getElementById('search-criteria-template'),
                    noMessage: document.getElementById('no-criteria-message'),
                    clearBtn: document.getElementById('clear-search-btn'),
                    saveBtn: document.getElementById('save-search-btn'),
                    form: document.getElementById('advanced-search-form'),
                    savedContainer: document.getElementById('saved-searches-container'),
                    savedTemplate: document.getElementById('saved-search-template'),
                    savedCount: document.getElementById('saved-searches-count'),
                    noSavedMsg: document.getElementById('no-saved-searches')
                };

                // Données de l'application
                const data = @json($data ?? []);
                const selectFieldsData = {
                    room: data.rooms || [],
                    shelf: data.shelve || [],
                    activity: data.activities || [],
                    term: data.terms || [],
                    author: data.authors || [],
                    creator: data.creators || [],
                    container: data.containers || [],
                    status: data.statues || []
                };

                // Initialisation des événements
                function init() {
                    // Gestion des champs à ajouter
                    document.addEventListener('click', function(e) {
                        const fieldElement = e.target.closest('[data-field]');
                        if (fieldElement) {
                            e.preventDefault();
                            const fieldName = fieldElement.getAttribute('data-field');
                            const fieldLabel = fieldElement.getAttribute('data-name-field');
                            if (fieldName && fieldLabel) {
                                addSearchCriteria(fieldName, fieldLabel);
                                elements.noMessage.style.display = 'none';
                            }
                        }
                    });

                    // Suppression d'un critère
                    elements.container.addEventListener('click', function(e) {
                        if (e.target.closest('.remove-criteria-btn')) {
                            removeCriteria(e.target.closest('.search-criteria-row'));
                        }
                    });

                    // Effacer tous les critères
                    elements.clearBtn.addEventListener('click', clearAllCriteria);

                    // Sauvegarder la recherche
                    elements.saveBtn.addEventListener('click', saveSearch);

                    // Gérer les événements des recherches sauvegardées
                    elements.savedContainer.addEventListener('click', function(e) {
                        const loadBtn = e.target.closest('.load-search');
                        const deleteBtn = e.target.closest('.delete-search');

                        if (loadBtn) {
                            const index = parseInt(loadBtn.dataset.index);
                            const savedSearches = getSavedSearches();
                            if (savedSearches[index]) {
                                loadSavedSearch(savedSearches[index].criteria);
                            }
                        }

                        if (deleteBtn) {
                            const index = parseInt(deleteBtn.dataset.index);
                            if (confirm('{{ __("search.confirm_delete_search") }}')) {
                                deleteSavedSearch(index);
                            }
                        }
                    });

                    // Charger les recherches sauvegardées
                    updateSavedSearchesList();
                }

                // Ajouter un critère de recherche
                function addSearchCriteria(field, label) {
                    const criteriaClone = document.importNode(elements.template.content, true);
                    const criteriaRow = criteriaClone.querySelector('.search-criteria-row');

                    // Définir les valeurs de base
                    criteriaClone.querySelector('.field-name').value = field;
                    criteriaClone.querySelector('.field-label').textContent = label;

                    const operatorSelect = criteriaClone.querySelector('.field-operator');
                    const valueContainer = criteriaClone.querySelector('.field-value-container');

                    // Configurer les opérateurs selon le type de champ
                    const fieldType = config.fieldTypes[field] || 'text';
                    const operators = config.operatorsByType[fieldType];

                    // Vider et remplir le select d'opérateurs
                    operatorSelect.innerHTML = '';
                    operators.forEach(op => {
                        const option = document.createElement('option');
                        option.value = op;
                        option.textContent = op;
                        operatorSelect.appendChild(option);
                    });

                    // Créer le champ de valeur approprié
                    let valueInput;

                    if (fieldType === 'date') {
                        valueInput = document.createElement('input');
                        valueInput.type = 'date';
                        valueInput.classList.add('form-control', 'form-control-sm');
                        valueInput.name = 'value[]';                    }
                    else if (fieldType === 'select') {
                        valueInput = document.createElement('select');
                        valueInput.classList.add('form-select', 'form-select-sm');
                        valueInput.name = 'value[]';

                        // Option par défaut
                        const defaultOption = document.createElement('option');
                        defaultOption.value = '';
                        defaultOption.textContent = '-- {{ __("search.select_option") }} --';
                        valueInput.appendChild(defaultOption);

                        // Ajouter les options disponibles
                        const items = selectFieldsData[field] || [];
                        if (Array.isArray(items) && items.length > 0) {
                            items.forEach(item => {
                                if (item && typeof item === 'object') {
                                    const option = document.createElement('option');
                                    option.value = item.id || item.value || '';
                                    option.textContent = getItemDisplayValue(item);
                                    valueInput.appendChild(option);
                                }
                            });
                        }

                        // Fonction d'aide pour extraire la valeur d'affichage
                        function getItemDisplayValue(item) {
                            return item.name || item.title || item.code || item.label || String(item);
                        }
                    }
                    else {
                        valueInput = document.createElement('input');
                        valueInput.type = 'text';
                        valueInput.classList.add('form-control', 'form-control-sm');
                        valueInput.name = 'value[]';
                    }

                    valueContainer.appendChild(valueInput);

                    // Ajouter au DOM avec animation
                    criteriaRow.classList.add('criteria-appear');
                    elements.container.appendChild(criteriaClone);
                }

                // Supprimer un critère spécifique
                function removeCriteria(criteriaRow) {
                    criteriaRow.style.opacity = '0';
                    criteriaRow.style.transform = 'translateY(-10px)';

                    setTimeout(() => {
                        criteriaRow.remove();
                        if (!elements.container.querySelector('.search-criteria-row')) {
                            elements.noMessage.style.display = 'block';
                        }
                    }, 300);
                }

                // Effacer tous les critères
                function clearAllCriteria() {
                    const criteriaRows = elements.container.querySelectorAll('.search-criteria-row');

                    criteriaRows.forEach((row, index) => {
                        setTimeout(() => {
                            row.style.opacity = '0';
                            row.style.transform = 'translateY(-10px)';

                            setTimeout(() => row.remove(), 300);
                        }, index * 50); // Effet en cascade
                    });

                    setTimeout(() => {
                        elements.noMessage.style.display = 'block';
                    }, criteriaRows.length * 50 + 300);
                }

                // Collecter tous les critères actuels
                function collectSearchCriteria() {
                    const criteria = [];
                    const rows = elements.container.querySelectorAll('.search-criteria-row');

                    rows.forEach(row => {
                        const field = row.querySelector('.field-name').value;
                        const operator = row.querySelector('.field-operator').value;
                        const valueInput = row.querySelector('input[name="value[]"], select[name="value[]"]');

                        if (valueInput && valueInput.value.trim()) {
                            criteria.push({
                                field,
                                operator,
                                value: valueInput.value,
                                fieldLabel: row.querySelector('.field-label').textContent
                            });
                        }
                    });

                    return criteria;
                }

                // Sauvegarder une recherche
                function saveSearch() {
                    const criteria = collectSearchCriteria();

                    if (criteria.length === 0) {
                        Toast.show('warning', '{{ __("search.add_criteria_first") }}');
                        return;
                    }

                    const searchName = prompt('{{ __("search.search_name_prompt") }}', '');
                    if (searchName && searchName.trim()) {
                        const savedSearches = getSavedSearches();
                        savedSearches.push({
                            name: searchName.trim(),
                            criteria,
                            date: new Date().toISOString()
                        });

                        localStorage.setItem('savedSearches', JSON.stringify(savedSearches));
                        updateSavedSearchesList();
                        Toast.show('success', '{{ __("search.search_saved_successfully") }}');
                    }
                }

                // Obtenir les recherches sauvegardées
                function getSavedSearches() {
                    return JSON.parse(localStorage.getItem('savedSearches') || '[]');
                }

                // Supprimer une recherche sauvegardée
                function deleteSavedSearch(index) {
                    const savedSearches = getSavedSearches();
                    savedSearches.splice(index, 1);
                    localStorage.setItem('savedSearches', JSON.stringify(savedSearches));
                    updateSavedSearchesList();
                    Toast.show('success', '{{ __("search.search_deleted") }}');
                }

                // Mettre à jour la liste des recherches sauvegardées
                function updateSavedSearchesList() {
                    const savedSearches = getSavedSearches();

                    // Mettre à jour le compteur
                    elements.savedCount.textContent = savedSearches.length;

                    if (savedSearches.length === 0) {
                        elements.noSavedMsg.style.display = 'block';
                        elements.savedContainer.querySelectorAll('.saved-search-item').forEach(item => item.remove());
                        return;
                    }

                    elements.noSavedMsg.style.display = 'none';
                    elements.savedContainer.querySelectorAll('.saved-search-item').forEach(item => item.remove());

                    // Ajouter chaque recherche sauvegardée
                    savedSearches.forEach((search, index) => {
                        const searchElement = document.importNode(elements.savedTemplate.content, true);

                        const nameElem = searchElement.querySelector('.saved-search-name');
                        nameElem.textContent = Utils.escapeHtml(search.name);
                        nameElem.setAttribute('aria-label', `{{ __("search.search_named") }} ${Utils.escapeHtml(search.name)}`);

                        searchElement.querySelector('.saved-search-date').textContent = new Date(search.date).toLocaleDateString();

                        const loadBtn = searchElement.querySelector('.load-search');
                        const deleteBtn = searchElement.querySelector('.delete-search');

                        loadBtn.dataset.index = index;
                        deleteBtn.dataset.index = index;

                        elements.savedContainer.appendChild(searchElement);
                    });
                }

                // Charger une recherche sauvegardée
                function loadSavedSearch(criteria) {
                    clearAllCriteria();

                    // Attendre la fin de l'animation d'effacement
                    setTimeout(() => {
                        criteria.forEach(criterion => {
                            // Trouver le champ correspondant
                            const field = document.querySelector(`[data-field="${criterion.field}"]`);

                            if (field) {
                                const fieldName = field.getAttribute('data-field');
                                const fieldLabel = field.getAttribute('data-name-field');

                                addSearchCriteria(fieldName, fieldLabel);

                                // Attendre que l'élément soit ajouté au DOM
                                setTimeout(() => {
                                    const lastRow = elements.container.querySelector('.search-criteria-row:last-child');

                                    if (lastRow) {
                                        const operatorSelect = lastRow.querySelector('.field-operator');
                                        const valueInput = lastRow.querySelector('input[name="value[]"], select[name="value[]"]');

                                        if (operatorSelect) operatorSelect.value = criterion.operator;
                                        if (valueInput) valueInput.value = criterion.value;
                                    }
                                }, 50);
                            }
                        });
                    }, 350);
                }

                // API publique
                return {
                    init
                };
            })();

            // Utilitaires
            const Utils = {
                escapeHtml: function(text) {
                    const map = {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    };
                    return String(text).replace(/[&<>"']/g, m => map[m]);
                }
            };

            // Module Toast pour les notifications
            const Toast = (function() {
                function createContainer() {
                    let container = document.getElementById('toast-container');
                    if (!container) {
                        container = document.createElement('div');
                        container.id = 'toast-container';
                        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                        container.style.zIndex = '1055';
                        document.body.appendChild(container);
                    }
                    return container;
                }

                function show(type, message) {
                    const container = createContainer();
                    const toast = document.createElement('div');
                    toast.className = `toast align-items-center text-white bg-${type} border-0`;
                    toast.setAttribute('role', 'alert');
                    toast.setAttribute('aria-live', 'assertive');
                    toast.setAttribute('aria-atomic', 'true');

                    toast.innerHTML = `
                        <div class="d-flex">
                            <div class="toast-body">
                                ${Utils.escapeHtml(message)}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto"
                                    data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    `;

                    container.appendChild(toast);

                    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
                        const bsToast = new bootstrap.Toast(toast, {
                            autohide: true,
                            delay: 3000
                        });
                        bsToast.show();
                        toast.addEventListener('hidden.bs.toast', () => toast.remove());
                    } else {
                        setTimeout(() => toast.remove(), 3000);
                    }
                }

                return {
                    show
                };
            })();

            // Démarrer l'application
            AdvancedSearch.init();
        });
    </script>
@endpush
