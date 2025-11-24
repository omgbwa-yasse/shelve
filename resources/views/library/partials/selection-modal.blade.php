{{--
    Modal de sélection réutilisable pour éditeurs, collections, auteurs, classifications
    @param string $id - ID du modal
    @param string $title - Titre du modal
    @param string $type - Type d'entité (publishers, series, authors, classifications)
    @param string $searchRoute - Route pour la recherche AJAX
    @param string $storeRoute - Route pour la création
--}}
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Onglets -->
                <ul class="nav nav-tabs mb-3" id="{{ $id }}Tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="{{ $id }}-search-tab" data-bs-toggle="tab"
                                data-bs-target="#{{ $id }}-search" type="button" role="tab">
                            <i class="bi bi-search"></i> Rechercher
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="{{ $id }}-create-tab" data-bs-toggle="tab"
                                data-bs-target="#{{ $id }}-create" type="button" role="tab">
                            <i class="bi bi-plus-circle"></i> Créer nouveau
                        </button>
                    </li>
                </ul>

                <!-- Contenu des onglets -->
                <div class="tab-content" id="{{ $id }}TabsContent">
                    <!-- Onglet Recherche -->
                    <div class="tab-pane fade show active" id="{{ $id }}-search" role="tabpanel">
                        <!-- Barre de recherche et filtres -->
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="{{ $id }}-search-input"
                                       placeholder="Rechercher...">
                            </div>
                            <div class="col-md-4" id="{{ $id }}-filters">
                                <!-- Filtres spécifiques selon le type -->
                            </div>
                        </div>

                        <!-- Tri alphabétique -->
                        <div class="mb-3">
                            <div class="btn-group btn-group-sm alphabet-nav" role="group">
                                <button type="button" class="btn btn-outline-secondary alphabet-btn" data-letter="ALL">Tout</button>
                                @foreach(range('A', 'Z') as $letter)
                                    <button type="button" class="btn btn-outline-secondary alphabet-btn" data-letter="{{ $letter }}">{{ $letter }}</button>
                                @endforeach
                                <button type="button" class="btn btn-outline-secondary alphabet-btn" data-letter="#">#</button>
                            </div>
                        </div>

                        <!-- Résultats -->
                        <div id="{{ $id }}-results" style="max-height: 400px; overflow-y: auto;">
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1"></i>
                                <p>Effectuez une recherche ou sélectionnez une lettre</p>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div id="{{ $id }}-pagination" class="mt-3 d-none">
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <!-- Pagination dynamique -->
                                </ul>
                            </nav>
                        </div>
                    </div>

                    <!-- Onglet Création -->
                    <div class="tab-pane fade" id="{{ $id }}-create" role="tabpanel">
                        <form id="{{ $id }}-create-form">
                            @csrf
                            <div id="{{ $id }}-create-fields">
                                <!-- Champs de formulaire spécifiques selon le type -->
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Enregistrer et sélectionner
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.alphabet-nav .alphabet-btn {
    min-width: 32px;
    padding: 4px 8px;
    font-size: 0.875rem;
}
.alphabet-nav .alphabet-btn.active {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
}
.result-item {
    cursor: pointer;
    transition: background-color 0.2s;
}
.result-item:hover {
    background-color: #f8f9fa;
}
.result-group {
    margin-bottom: 1.5rem;
}
.result-group-letter {
    position: sticky;
    top: 0;
    background: white;
    z-index: 1;
    padding: 0.5rem 0;
    border-bottom: 2px solid #0d6efd;
    margin-bottom: 0.5rem;
}
</style>

<script>
$(document).ready(function() {
    const modalId = '{{ $id }}';
    const type = '{{ $type }}';
    const searchRoute = '{{ $searchRoute }}';
    const storeRoute = '{{ $storeRoute }}';

    let currentPage = 1;
    let currentLetter = 'ALL';
    let currentSearch = '';
    let searchTimeout = null;
    let onSelectCallback = null;

    // Fonction pour initialiser le modal (globale)
    window['initSelectionModal_{{ str_replace('-', '_', $id) }}'] = function(callback) {
        onSelectCallback = callback;

        // Réinitialiser
        currentPage = 1;
        currentLetter = 'ALL';
        currentSearch = '';
        $('#' + modalId + '-search-input').val('');
        $('#' + modalId + ' .alphabet-btn').removeClass('active');
        $('#' + modalId + ' .alphabet-btn[data-letter="ALL"]').addClass('active');

        // Afficher le modal
        const modal = new bootstrap.Modal(document.getElementById(modalId));
        modal.show();

        // Charger les données initiales
        loadResults();
    };

    // Recherche avec debounce
    $('#' + modalId + '-search-input').on('input', function() {
        clearTimeout(searchTimeout);
        currentSearch = $(this).val();
        currentPage = 1;

        searchTimeout = setTimeout(() => {
            loadResults();
        }, 300);
    });

    // Navigation alphabétique
    $('#' + modalId + ' .alphabet-btn').on('click', function() {
        const letter = $(this).data('letter');
        currentLetter = letter;
        currentPage = 1;

        $('#' + modalId + ' .alphabet-btn').removeClass('active');
        $(this).addClass('active');

        loadResults();
    });

    // Chargement des résultats
    function loadResults() {
        const $results = $('#' + modalId + '-results');
        $results.html('<div class="text-center py-4"><div class="spinner-border" role="status"></div></div>');

        const params = {
            q: currentSearch,
            page: currentPage,
            letter: currentLetter !== 'ALL' ? currentLetter : ''
        };

        $.get(searchRoute, params)
            .done(function(data) {
                renderResults(data.results);
                renderPagination(data.pagination);
            })
            .fail(function() {
                $results.html('<div class="alert alert-danger">Erreur de chargement</div>');
            });
    }

    // Rendu des résultats
    function renderResults(results) {
        const $results = $('#' + modalId + '-results');

        if (results.length === 0) {
            $results.html('<div class="text-center text-muted py-4"><i class="bi bi-inbox fs-1"></i><p>Aucun résultat</p></div>');
            return;
        }

        // Grouper par lettre
        const grouped = {};
        results.forEach(item => {
            const firstLetter = (item.name || item.text).charAt(0).toUpperCase();
            const letter = /[A-Z]/.test(firstLetter) ? firstLetter : '#';
            if (!grouped[letter]) grouped[letter] = [];
            grouped[letter].push(item);
        });

        let html = '';
        Object.keys(grouped).sort().forEach(letter => {
            html += `<div class="result-group">
                        <h6 class="result-group-letter text-primary">${letter}</h6>
                        <div class="list-group">`;

            grouped[letter].forEach(item => {
                html += renderResultItem(item);
            });

            html += `</div></div>`;
        });

        $results.html(html);

        // Événement de sélection
        $('.result-item').on('click', function(e) {
            e.preventDefault();
            const itemData = $(this).data('item');
            if (onSelectCallback) {
                onSelectCallback(itemData);
            }
            const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
            if (modal) {
                modal.hide();
            }
        });
    }

    // Rendu d'un item selon le type
    function renderResultItem(item) {
        let subtitle = '';
        const itemName = item.name || item.text || item.full_name || 'Sans nom';

        if (type === 'publishers') {
            if (item.city && item.country) {
                subtitle = `<small class="text-muted">${item.city}, ${item.country}</small>`;
            }
        } else if (type === 'series') {
            if (item.publisher_name) {
                subtitle = `<small class="text-muted">${item.publisher_name}</small>`;
            }
        } else if (type === 'authors') {
            if (item.pseudonym) {
                subtitle = `<small class="text-muted">(${item.pseudonym})</small>`;
            }
        } else if (type === 'thesaurus') {
            if (item.notation) {
                subtitle = `<small class="text-muted">[${item.notation}]</small>`;
            }
        }

        return `<a href="#" class="list-group-item list-group-item-action result-item"
                   data-item='${JSON.stringify({id: item.id, text: itemName, name: itemName})}'>
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">${itemName}</h6>
                            ${subtitle}
                        </div>
                        <i class="bi bi-check-circle text-success"></i>
                    </div>
                </a>`;
    }

    // Rendu pagination
    function renderPagination(pagination) {
        // À implémenter si nécessaire
    }

    // Soumission du formulaire de création
    $('#' + modalId + '-create-form').on('submit', function(e) {
        e.preventDefault();

        const formData = $(this).serialize();

        $.post(storeRoute, formData)
            .done(function(data) {
                if (onSelectCallback) {
                    // S'assurer que les données ont le bon format (id + text)
                    const formattedData = {
                        id: data.id,
                        text: data.name || data.text || data.full_name || 'Nouvel élément'
                    };
                    onSelectCallback(formattedData);
                }
                const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
                modal.hide();
                // Réinitialiser le formulaire
                $('#' + modalId + '-create-form')[0].reset();
                // Retourner à l'onglet recherche
                const searchTab = new bootstrap.Tab(document.getElementById(modalId + '-search-tab'));
                searchTab.show();
            })
            .fail(function(xhr) {
                const errors = xhr.responseJSON?.errors || {};
                alert('Erreur lors de la création: ' + Object.values(errors).flat().join(', '));
            });
    });

    // Initialiser les champs de création selon le type
    function initCreateFields() {
        const $fields = $('#' + modalId + '-create-fields');
        let html = '';

        if (type === 'publishers') {
            html = `
                <div class="mb-3">
                    <label class="form-label">Nom de l'éditeur <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ville</label>
                        <input type="text" name="city" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Pays</label>
                        <input type="text" name="country" class="form-control">
                    </div>
                </div>
            `;
        } else if (type === 'series') {
            html = `
                <div class="mb-3">
                    <label class="form-label">Nom de la collection <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Éditeur</label>
                    <select name="publisher_id" class="form-select">
                        <option value="">Sélectionner un éditeur</option>
                    </select>
                </div>
            `;
        } else if (type === 'authors') {
            html = `
                <div class="mb-3">
                    <label class="form-label">Nom complet <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Prénom(s)</label>
                        <input type="text" name="first_name" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nom de famille</label>
                        <input type="text" name="last_name" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Pseudonyme</label>
                    <input type="text" name="pseudonym" class="form-control">
                </div>
            `;
        } else if (type === 'classifications') {
            html = `
                <div class="mb-3">
                    <label class="form-label">Nom de la classification <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
            `;
        } else if (type === 'thesaurus') {
            html = `
                <div class="mb-3">
                    <label class="form-label">Terme <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notation (code)</label>
                    <input type="text" name="notation" class="form-control" placeholder="Ex: 005.1">
                </div>
            `;
        }

        $fields.html(html);
    }

    // Initialiser au chargement du modal
    $(document).on('shown.bs.modal', '#' + modalId, function() {
        initCreateFields();
    });
});
</script>
