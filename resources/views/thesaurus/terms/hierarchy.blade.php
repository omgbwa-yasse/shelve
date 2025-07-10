@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">{{ __('Navigation hiérarchique du thésaurus') }}</h1>

            <div class="row">
                <!-- Arbre hiérarchique -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('Arbre hiérarchique') }}</h5>
                            <div class="input-group mt-2">
                                <input type="text" class="form-control form-control-sm" id="tree-search"
                                       placeholder="{{ __('Rechercher dans l\'arbre...') }}">
                                <button class="btn btn-sm btn-outline-secondary" type="button" id="expand-all">
                                    <i class="bi bi-arrows-expand"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" type="button" id="collapse-all">
                                    <i class="bi bi-arrows-collapse"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-2">
                            <div id="tree-container" class="tree-view">
                                <!-- L'arbre sera chargé via AJAX -->
                                <div class="text-center py-4">
                                    <div class="spinner-border" role="status">
                                        <span class="visually-hidden">{{ __('Chargement...') }}</span>
                                    </div>
                                    <p class="mt-2">{{ __('Chargement de l\'arbre...') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Détails du terme sélectionné -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 id="term-title">{{ __('Sélectionnez un terme') }}</h5>
                            <div class="btn-group btn-group-sm" role="group" id="term-actions" style="display: none;">
                                <a href="#" class="btn btn-outline-primary" id="edit-term">
                                    <i class="bi bi-pencil"></i> {{ __('Modifier') }}
                                </a>
                                <button type="button" class="btn btn-outline-success" id="add-child">
                                    <i class="bi bi-plus"></i> {{ __('Ajouter enfant') }}
                                </button>
                                <button type="button" class="btn btn-outline-info" id="show-relations">
                                    <i class="bi bi-diagram-2"></i> {{ __('Relations') }}
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="term-details">
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-arrow-left-circle" style="font-size: 2em;"></i>
                                    <p class="mt-3">{{ __('Sélectionnez un terme dans l\'arbre pour voir ses détails') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Relations du terme -->
                    <div class="card mt-3" id="relations-card" style="display: none;">
                        <div class="card-header">
                            <h6>{{ __('Relations du terme') }}</h6>
                        </div>
                        <div class="card-body">
                            <div id="relations-content">
                                <!-- Les relations seront chargées via AJAX -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour ajouter un terme enfant -->
<div class="modal fade" id="add-child-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Ajouter un terme enfant') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="add-child-form">
                    @csrf
                    <input type="hidden" id="parent-term-id" name="parent_id">
                    <div class="mb-3">
                        <label for="child-preferred-label" class="form-label">{{ __('Libellé préféré') }}</label>
                        <input type="text" class="form-control" id="child-preferred-label" name="preferred_label" required>
                    </div>
                    <div class="mb-3">
                        <label for="child-scope-note" class="form-label">{{ __('Note d\'application') }}</label>
                        <textarea class="form-control" id="child-scope-note" name="scope_note" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="child-language" class="form-label">{{ __('Langue') }}</label>
                        <select class="form-select" id="child-language" name="language">
                            <option value="fr">{{ __('Français') }}</option>
                            <option value="en">{{ __('Anglais') }}</option>
                            <option value="es">{{ __('Espagnol') }}</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                <button type="button" class="btn btn-success" id="save-child-term">{{ __('Créer') }}</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.tree-view {
    max-height: 500px;
    overflow-y: auto;
    overflow-x: auto;
}

.tree-node {
    margin-left: 20px;
    position: relative;
}

.tree-node:before {
    content: '';
    position: absolute;
    left: -20px;
    top: 1em;
    width: 15px;
    height: 1px;
    border-top: 1px solid #ccc;
}

.tree-node:after {
    content: '';
    position: absolute;
    left: -20px;
    top: 0;
    bottom: 50%;
    width: 1px;
    border-left: 1px solid #ccc;
}

.tree-node:last-child:after {
    bottom: auto;
    height: 1em;
}

.tree-item {
    padding: 4px 8px;
    margin: 2px 0;
    cursor: pointer;
    border-radius: 4px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
}

.tree-item:hover {
    background-color: #f8f9fa;
}

.tree-item.selected {
    background-color: #007bff;
    color: white;
}

.tree-toggle {
    width: 16px;
    height: 16px;
    margin-right: 5px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

.tree-toggle.expandable:before {
    content: '▶';
}

.tree-toggle.expanded:before {
    content: '▼';
}

.tree-label {
    flex: 1;
    font-size: 14px;
}

.tree-badge {
    font-size: 10px;
    margin-left: 5px;
}

.term-property {
    margin-bottom: 15px;
}

.term-property-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 5px;
}

.term-property-value {
    background-color: #f8f9fa;
    padding: 8px 12px;
    border-radius: 4px;
    border-left: 3px solid #007bff;
}

.relation-item {
    padding: 8px 12px;
    margin: 5px 0;
    background-color: #f8f9fa;
    border-radius: 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.relation-type {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 11px;
    color: #6c757d;
}

#tree-search {
    font-size: 12px;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let currentTermId = null;
    let treeData = {};

    // Charger l'arbre initial
    loadTree();

    function loadTree() {
        $.ajax({
            url: '{{ route("terms.hierarchy") }}',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    treeData = response.tree;
                    renderTree(treeData);
                } else {
                    $('#tree-container').html(`
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            {{ __('Aucun terme trouvé dans le thésaurus') }}
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                $('#tree-container').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        {{ __('Erreur lors du chargement de l\'arbre') }}
                    </div>
                `);
            }
        });
    }

    function renderTree(tree, container = '#tree-container', level = 0) {
        let html = '';

        if (level === 0) {
            html = '<div class="tree-root">';
        }

        tree.forEach(node => {
            const hasChildren = node.children && node.children.length > 0;
            const expandable = hasChildren ? 'expandable' : '';

            html += `
                <div class="tree-node" data-level="${level}">
                    <div class="tree-item" data-term-id="${node.id}">
                        <span class="tree-toggle ${expandable}"></span>
                        <span class="tree-label">${node.preferred_label}</span>
                        <span class="badge bg-secondary tree-badge">${node.language}</span>
                    </div>
            `;

            if (hasChildren) {
                html += '<div class="tree-children" style="display: none;">';
                html += renderTreeLevel(node.children, level + 1);
                html += '</div>';
            }

            html += '</div>';
        });

        if (level === 0) {
            html += '</div>';
            $(container).html(html);
        }

        return html;
    }

    function renderTreeLevel(children, level) {
        let html = '';
        children.forEach(node => {
            const hasChildren = node.children && node.children.length > 0;
            const expandable = hasChildren ? 'expandable' : '';

            html += `
                <div class="tree-node" data-level="${level}">
                    <div class="tree-item" data-term-id="${node.id}">
                        <span class="tree-toggle ${expandable}"></span>
                        <span class="tree-label">${node.preferred_label}</span>
                        <span class="badge bg-secondary tree-badge">${node.language}</span>
                    </div>
            `;

            if (hasChildren) {
                html += '<div class="tree-children" style="display: none;">';
                html += renderTreeLevel(node.children, level + 1);
                html += '</div>';
            }

            html += '</div>';
        });
        return html;
    }

    // Gérer l'expansion/collapse des nœuds
    $(document).on('click', '.tree-toggle', function(e) {
        e.stopPropagation();
        const toggle = $(this);
        const children = toggle.closest('.tree-node').find('> .tree-children');

        if (toggle.hasClass('expandable')) {
            if (children.is(':visible')) {
                children.slideUp(200);
                toggle.removeClass('expanded');
            } else {
                children.slideDown(200);
                toggle.addClass('expanded');
            }
        }
    });

    // Gérer la sélection d'un terme
    $(document).on('click', '.tree-item', function() {
        const termId = $(this).data('term-id');

        // Désélectionner l'ancien terme
        $('.tree-item').removeClass('selected');
        // Sélectionner le nouveau terme
        $(this).addClass('selected');

        loadTermDetails(termId);
    });

    function loadTermDetails(termId) {
        currentTermId = termId;

        $('#term-details').html(`
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm" role="status"></div>
                <p class="mt-2">{{ __('Chargement des détails...') }}</p>
            </div>
        `);

        $.ajax({
            url: '{{ url("tools/thesaurus/terms") }}/' + termId,
            type: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    const term = response.term;
                    $('#term-title').text(term.preferred_label);
                    $('#term-actions').show();
                    $('#edit-term').attr('href', '{{ url("tools/thesaurus/terms") }}/' + termId + '/edit');

                    let html = `
                        <div class="term-property">
                            <div class="term-property-label">{{ __('Libellé préféré') }}</div>
                            <div class="term-property-value">${term.preferred_label}</div>
                        </div>

                        <div class="term-property">
                            <div class="term-property-label">{{ __('Langue') }}</div>
                            <div class="term-property-value">${term.language}</div>
                        </div>
                    `;

                    if (term.scope_note) {
                        html += `
                            <div class="term-property">
                                <div class="term-property-label">{{ __('Note d\'application') }}</div>
                                <div class="term-property-value">${term.scope_note}</div>
                            </div>
                        `;
                    }

                    if (term.category) {
                        html += `
                            <div class="term-property">
                                <div class="term-property-label">{{ __('Catégorie') }}</div>
                                <div class="term-property-value">${term.category}</div>
                            </div>
                        `;
                    }

                    if (term.status) {
                        const statusClass = term.status === 'approved' ? 'success' :
                                          term.status === 'candidate' ? 'warning' : 'secondary';
                        html += `
                            <div class="term-property">
                                <div class="term-property-label">{{ __('Statut') }}</div>
                                <div class="term-property-value">
                                    <span class="badge bg-${statusClass}">${term.status}</span>
                                </div>
                            </div>
                        `;
                    }

                    $('#term-details').html(html);

                    // Charger les relations
                    loadTermRelations(termId);
                } else {
                    $('#term-details').html(`
                        <div class="alert alert-danger">
                            {{ __('Erreur lors du chargement du terme') }}
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                $('#term-details').html(`
                    <div class="alert alert-danger">
                        {{ __('Erreur lors du chargement du terme') }}
                    </div>
                `);
            }
        });
    }

    function loadTermRelations(termId) {
        $.ajax({
            url: '{{ url("tools/thesaurus/terms") }}/' + termId + '/relations',
            type: 'GET',
            success: function(response) {
                if (response.success && (response.relations.broader.length > 0 ||
                    response.relations.narrower.length > 0 ||
                    response.relations.related.length > 0)) {

                    let html = '';

                    if (response.relations.broader.length > 0) {
                        html += '<div class="mb-3"><h6 class="relation-type">{{ __("Termes génériques") }}</h6>';
                        response.relations.broader.forEach(rel => {
                            html += `<div class="relation-item">${rel.preferred_label}</div>`;
                        });
                        html += '</div>';
                    }

                    if (response.relations.narrower.length > 0) {
                        html += '<div class="mb-3"><h6 class="relation-type">{{ __("Termes spécifiques") }}</h6>';
                        response.relations.narrower.forEach(rel => {
                            html += `<div class="relation-item">${rel.preferred_label}</div>`;
                        });
                        html += '</div>';
                    }

                    if (response.relations.related.length > 0) {
                        html += '<div class="mb-3"><h6 class="relation-type">{{ __("Termes associés") }}</h6>';
                        response.relations.related.forEach(rel => {
                            html += `<div class="relation-item">${rel.preferred_label}</div>`;
                        });
                        html += '</div>';
                    }

                    $('#relations-content').html(html);
                    $('#relations-card').show();
                } else {
                    $('#relations-card').hide();
                }
            },
            error: function(xhr) {
                $('#relations-card').hide();
            }
        });
    }

    // Fonctions pour les boutons d'expansion/collapse
    $('#expand-all').click(function() {
        $('.tree-toggle.expandable').addClass('expanded');
        $('.tree-children').slideDown(200);
    });

    $('#collapse-all').click(function() {
        $('.tree-toggle.expanded').removeClass('expanded');
        $('.tree-children').slideUp(200);
    });

    // Recherche dans l'arbre
    $('#tree-search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();

        if (searchTerm.length === 0) {
            $('.tree-node').show();
            return;
        }

        $('.tree-node').each(function() {
            const label = $(this).find('.tree-label').text().toLowerCase();
            if (label.includes(searchTerm)) {
                $(this).show();
                // Montrer également les parents
                $(this).parents('.tree-node').show();
            } else {
                $(this).hide();
            }
        });
    });

    // Gérer l'ajout d'un terme enfant
    $('#add-child').click(function() {
        if (currentTermId) {
            $('#parent-term-id').val(currentTermId);
            $('#add-child-modal').modal('show');
        }
    });

    $('#save-child-term').click(function() {
        const formData = {
            parent_id: $('#parent-term-id').val(),
            preferred_label: $('#child-preferred-label').val(),
            scope_note: $('#child-scope-note').val(),
            language: $('#child-language').val(),
            _token: '{{ csrf_token() }}'
        };

        $.ajax({
            url: '{{ route("terms.store") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#add-child-modal').modal('hide');
                    $('#add-child-form')[0].reset();
                    // Recharger l'arbre
                    loadTree();
                    // Sélectionner le nouveau terme
                    setTimeout(() => {
                        $(`.tree-item[data-term-id="${response.term.id}"]`).click();
                    }, 500);
                } else {
                    alert('{{ __("Erreur lors de la création du terme") }}');
                }
            },
            error: function(xhr) {
                alert('{{ __("Erreur lors de la création du terme") }}');
            }
        });
    });
});
</script>
@endpush
@endsection
