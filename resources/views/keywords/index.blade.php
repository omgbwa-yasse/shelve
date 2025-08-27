@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-tags"></i> Gestion des mots-clés
                    </h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addKeywordModal">
                        <i class="fas fa-plus"></i> Nouveau mot-clé
                    </button>
                </div>
                <div class="card-body">
                    <!-- Statistiques -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="card-title mb-0">Total mots-clés</h6>
                                            <h3 class="mb-0" id="total-keywords">-</h3>
                                        </div>
                                        <i class="fas fa-tags fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="card-title mb-0">Utilisés</h6>
                                            <h3 class="mb-0" id="used-keywords">-</h3>
                                        </div>
                                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="card-title mb-0">Inutilisés</h6>
                                            <h3 class="mb-0" id="unused-keywords">-</h3>
                                        </div>
                                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="card-title mb-0">Utilisations</h6>
                                            <h3 class="mb-0" id="total-usage">-</h3>
                                        </div>
                                        <i class="fas fa-chart-bar fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres et recherche -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="search-keywords" placeholder="Rechercher des mots-clés...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filter-usage">
                                <option value="">Tous les mots-clés</option>
                                <option value="used">Utilisés uniquement</option>
                                <option value="unused">Inutilisés uniquement</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="sort-by">
                                <option value="name">Trier par nom</option>
                                <option value="usage">Trier par utilisation</option>
                                <option value="created">Trier par date de création</option>
                            </select>
                        </div>
                    </div>

                    <!-- Table des mots-clés -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th>Records</th>
                                    <th>Slip Records</th>
                                    <th>Total</th>
                                    <th>Créé le</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="keywords-table-body">
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="spinner-border" role="status">
                                            <span class="visually-hidden">Chargement...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'ajout de mot-clé -->
<div class="modal fade" id="addKeywordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Nouveau mot-clé
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="add-keyword-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="keyword-name" class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="keyword-name" name="name" required maxlength="100">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="keyword-description" class="form-label">Description</label>
                        <textarea class="form-control" id="keyword-description" name="description" rows="3" maxlength="1000"></textarea>
                        <div class="form-text">Optionnel - Description du mot-clé</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal d'édition de mot-clé -->
<div class="modal fade" id="editKeywordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Modifier le mot-clé
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="edit-keyword-form">
                <input type="hidden" id="edit-keyword-id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-keyword-name" class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-keyword-name" name="name" required maxlength="100">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit-keyword-description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit-keyword-description" name="description" rows="3" maxlength="1000"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let keywords = [];
    let filteredKeywords = [];

    // Charger les mots-clés
    loadKeywords();

    // Event listeners
    document.getElementById('search-keywords').addEventListener('input', filterKeywords);
    document.getElementById('filter-usage').addEventListener('change', filterKeywords);
    document.getElementById('sort-by').addEventListener('change', filterKeywords);

    document.getElementById('add-keyword-form').addEventListener('submit', handleAddKeyword);
    document.getElementById('edit-keyword-form').addEventListener('submit', handleEditKeyword);

    async function loadKeywords() {
        try {
            const response = await fetch('/keywords', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                }
            });

            if (response.ok) {
                keywords = await response.json();
                filteredKeywords = [...keywords];
                updateStatistics();
                renderKeywords();
            } else {
                throw new Error('Erreur lors du chargement des mots-clés');
            }
        } catch (error) {
            console.error('Error loading keywords:', error);
            showAlert('Erreur lors du chargement des mots-clés', 'danger');
        }
    }

    function updateStatistics() {
        const total = keywords.length;
        const used = keywords.filter(k => k.total_usage > 0).length;
        const unused = total - used;
        const totalUsage = keywords.reduce((sum, k) => sum + k.total_usage, 0);

        document.getElementById('total-keywords').textContent = total;
        document.getElementById('used-keywords').textContent = used;
        document.getElementById('unused-keywords').textContent = unused;
        document.getElementById('total-usage').textContent = totalUsage;
    }

    function filterKeywords() {
        const searchTerm = document.getElementById('search-keywords').value.toLowerCase();
        const usageFilter = document.getElementById('filter-usage').value;
        const sortBy = document.getElementById('sort-by').value;

        filteredKeywords = keywords.filter(keyword => {
            const matchesSearch = keyword.name.toLowerCase().includes(searchTerm) ||
                                (keyword.description && keyword.description.toLowerCase().includes(searchTerm));

            const matchesUsage = usageFilter === '' ||
                               (usageFilter === 'used' && keyword.total_usage > 0) ||
                               (usageFilter === 'unused' && keyword.total_usage === 0);

            return matchesSearch && matchesUsage;
        });

        // Tri
        filteredKeywords.sort((a, b) => {
            switch (sortBy) {
                case 'usage':
                    return b.total_usage - a.total_usage;
                case 'created':
                    return new Date(b.created_at) - new Date(a.created_at);
                case 'name':
                default:
                    return a.name.localeCompare(b.name);
            }
        });

        renderKeywords();
    }

    function renderKeywords() {
        const tbody = document.getElementById('keywords-table-body');

        if (filteredKeywords.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        <i class="fas fa-info-circle"></i> Aucun mot-clé trouvé
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = filteredKeywords.map(keyword => `
            <tr>
                <td>
                    <strong>${escapeHtml(keyword.name)}</strong>
                </td>
                <td>
                    ${keyword.description ? escapeHtml(keyword.description) : '<em class="text-muted">Aucune description</em>'}
                </td>
                <td>
                    <span class="badge bg-primary">${keyword.records_count}</span>
                </td>
                <td>
                    <span class="badge bg-info">${keyword.slip_records_count}</span>
                </td>
                <td>
                    <span class="badge bg-${keyword.total_usage > 0 ? 'success' : 'secondary'}">${keyword.total_usage}</span>
                </td>
                <td>
                    <small class="text-muted">${formatDate(keyword.created_at)}</small>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="editKeyword(${keyword.id})" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteKeyword(${keyword.id})" title="Supprimer" ${keyword.total_usage > 0 ? 'disabled' : ''}>
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    async function handleAddKeyword(e) {
        e.preventDefault();

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('/keywords', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                const newKeyword = await response.json();
                keywords.push({...newKeyword, records_count: 0, slip_records_count: 0, total_usage: 0});
                filterKeywords();
                updateStatistics();

                const modal = bootstrap.Modal.getInstance(document.getElementById('addKeywordModal'));
                modal.hide();
                e.target.reset();

                showAlert('Mot-clé créé avec succès', 'success');
            } else {
                const errorData = await response.json();
                showValidationErrors(e.target, errorData.errors);
            }
        } catch (error) {
            console.error('Error creating keyword:', error);
            showAlert('Erreur lors de la création du mot-clé', 'danger');
        }
    }

    async function handleEditKeyword(e) {
        e.preventDefault();

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        const keywordId = data.id;

        try {
            const response = await fetch(`/keywords/${keywordId}`, {
                method: 'PUT',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                const updatedKeyword = await response.json();
                const index = keywords.findIndex(k => k.id == keywordId);
                if (index !== -1) {
                    keywords[index] = {...keywords[index], ...updatedKeyword};
                    filterKeywords();
                }

                const modal = bootstrap.Modal.getInstance(document.getElementById('editKeywordModal'));
                modal.hide();

                showAlert('Mot-clé mis à jour avec succès', 'success');
            } else {
                const errorData = await response.json();
                showValidationErrors(e.target, errorData.errors);
            }
        } catch (error) {
            console.error('Error updating keyword:', error);
            showAlert('Erreur lors de la mise à jour du mot-clé', 'danger');
        }
    }

    window.editKeyword = function(id) {
        const keyword = keywords.find(k => k.id === id);
        if (!keyword) return;

        document.getElementById('edit-keyword-id').value = keyword.id;
        document.getElementById('edit-keyword-name').value = keyword.name;
        document.getElementById('edit-keyword-description').value = keyword.description || '';

        const modal = new bootstrap.Modal(document.getElementById('editKeywordModal'));
        modal.show();
    };

    window.deleteKeyword = async function(id) {
        const keyword = keywords.find(k => k.id === id);
        if (!keyword) return;

        if (keyword.total_usage > 0) {
            showAlert('Ce mot-clé est utilisé et ne peut pas être supprimé', 'warning');
            return;
        }

        if (!confirm(`Êtes-vous sûr de vouloir supprimer le mot-clé "${keyword.name}" ?`)) {
            return;
        }

        try {
            const response = await fetch(`/keywords/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                keywords = keywords.filter(k => k.id !== id);
                filterKeywords();
                updateStatistics();
                showAlert('Mot-clé supprimé avec succès', 'success');
            } else {
                const errorData = await response.json();
                showAlert(errorData.error || 'Erreur lors de la suppression', 'danger');
            }
        } catch (error) {
            console.error('Error deleting keyword:', error);
            showAlert('Erreur lors de la suppression du mot-clé', 'danger');
        }
    };

    function showAlert(message, type) {
        const alertContainer = document.querySelector('.container-fluid');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        alertContainer.insertBefore(alert, alertContainer.firstChild);

        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }

    function showValidationErrors(form, errors) {
        // Nettoyer les erreurs précédentes
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

        // Afficher les nouvelles erreurs
        Object.keys(errors).forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            const feedback = input?.nextElementSibling;

            if (input && feedback) {
                input.classList.add('is-invalid');
                feedback.textContent = errors[field][0];
            }
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }
});
</script>
@endpush
