<!-- Modal pour l'association des termes du thésaurus -->
<div class="modal fade" id="termModal" tabindex="-1" aria-labelledby="termModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termModalLabel">
                    <i class="fas fa-tags"></i> Associer des termes du thésaurus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Filtre par schéma -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="schemeSelect" class="form-label">Schéma thésaurus</label>
                        <select class="form-select" id="schemeSelect">
                            <option value="">Tous les schémas</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="termSearch" class="form-label">Rechercher un terme</label>
                        <input type="text" class="form-control" id="termSearch" placeholder="Tapez pour rechercher...">
                    </div>
                </div>

                <!-- Zone de résultats -->
                <div class="row">
                    <div class="col-md-6">
                        <h6>Termes disponibles</h6>
                        <div id="availableTerms" class="border p-3" style="height: 300px; overflow-y: auto;">
                            <div class="text-center text-muted">
                                <i class="fas fa-spinner fa-spin"></i> Chargement...
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Termes sélectionnés</h6>
                        <div id="selectedTerms" class="border p-3" style="height: 300px; overflow-y: auto;">
                            <div class="text-center text-muted">
                                Aucun terme sélectionné
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    <nav aria-label="Navigation des termes">
                        <ul class="pagination justify-content-center" id="termsPagination">
                            <!-- Pagination générée dynamiquement -->
                        </ul>
                    </nav>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="saveTerms">
                    <i class="fas fa-save"></i> Sauvegarder
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Script pour la gestion des termes -->
<script>
let selectedTermsData = [];
let availableTermsData = [];
let currentPage = 1;
let totalPages = 1;

// Initialisation du modal
document.addEventListener('DOMContentLoaded', function() {
    const termModal = document.getElementById('termModal');
    const schemeSelect = document.getElementById('schemeSelect');
    const termSearch = document.getElementById('termSearch');
    
    // Charger les schémas au chargement
    loadSchemes();
    
    // Event listeners
    termModal.addEventListener('shown.bs.modal', function() {
        loadTerms();
    });
    
    schemeSelect.addEventListener('change', function() {
        currentPage = 1;
        loadTerms();
    });
    
    termSearch.addEventListener('input', debounce(function() {
        currentPage = 1;
        loadTerms();
    }, 300));
    
    document.getElementById('saveTerms').addEventListener('click', saveSelectedTerms);
});

// Charger les schémas thésaurus
function loadSchemes() {
    fetch('/api/thesaurus/schemes')
        .then(response => response.json())
        .then(data => {
            const schemeSelect = document.getElementById('schemeSelect');
            schemeSelect.innerHTML = '<option value="">Tous les schémas</option>';
            
            data.forEach(scheme => {
                const option = document.createElement('option');
                option.value = scheme.id;
                option.textContent = scheme.title;
                schemeSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Erreur lors du chargement des schémas:', error);
            showAlert('Erreur lors du chargement des schémas', 'danger');
        });
}

// Charger les termes avec pagination
function loadTerms(page = 1) {
    const schemeId = document.getElementById('schemeSelect').value;
    const search = document.getElementById('termSearch').value;
    
    const params = new URLSearchParams({
        page: page,
        per_page: 20
    });
    
    if (schemeId) params.append('scheme_id', schemeId);
    if (search) params.append('search', search);
    
    fetch(`/api/thesaurus/concepts?${params}`)
        .then(response => response.json())
        .then(data => {
            availableTermsData = data.data;
            currentPage = data.current_page;
            totalPages = data.last_page;
            
            renderAvailableTerms();
            renderPagination();
        })
        .catch(error => {
            console.error('Erreur lors du chargement des termes:', error);
            document.getElementById('availableTerms').innerHTML = 
                '<div class="text-center text-danger">Erreur lors du chargement</div>';
        });
}

// Afficher les termes disponibles
function renderAvailableTerms() {
    const container = document.getElementById('availableTerms');
    
    if (availableTermsData.length === 0) {
        container.innerHTML = '<div class="text-center text-muted">Aucun terme trouvé</div>';
        return;
    }
    
    container.innerHTML = availableTermsData.map(term => {
        const isSelected = selectedTermsData.find(selected => selected.id === term.id);
        return `
            <div class="term-item mb-2 p-2 border rounded ${isSelected ? 'bg-light' : ''}" 
                 data-term-id="${term.id}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${term.pref_label}</strong>
                        ${term.alt_labels ? `<br><small class="text-muted">${term.alt_labels}</small>` : ''}
                        <br><small class="text-info">${term.scheme.title}</small>
                    </div>
                    <button class="btn btn-sm ${isSelected ? 'btn-danger' : 'btn-primary'}" 
                            onclick="${isSelected ? 'removeTerm' : 'addTerm'}(${term.id})">
                        <i class="fas ${isSelected ? 'fa-minus' : 'fa-plus'}"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

// Afficher les termes sélectionnés
function renderSelectedTerms() {
    const container = document.getElementById('selectedTerms');
    
    if (selectedTermsData.length === 0) {
        container.innerHTML = '<div class="text-center text-muted">Aucun terme sélectionné</div>';
        return;
    }
    
    container.innerHTML = selectedTermsData.map(term => `
        <div class="term-item mb-2 p-2 border rounded bg-light" data-term-id="${term.id}">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>${term.pref_label}</strong>
                    ${term.alt_labels ? `<br><small class="text-muted">${term.alt_labels}</small>` : ''}
                    <br><small class="text-info">${term.scheme.title}</small>
                </div>
                <button class="btn btn-sm btn-danger" onclick="removeTerm(${term.id})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `).join('');
}

// Afficher la pagination
function renderPagination() {
    const container = document.getElementById('termsPagination');
    
    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let paginationHTML = '';
    
    // Bouton précédent
    if (currentPage > 1) {
        paginationHTML += `<li class="page-item">
            <a class="page-link" href="#" onclick="loadTerms(${currentPage - 1})">Précédent</a>
        </li>`;
    }
    
    // Numéros de pages
    for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
        paginationHTML += `<li class="page-item ${i === currentPage ? 'active' : ''}">
            <a class="page-link" href="#" onclick="loadTerms(${i})">${i}</a>
        </li>`;
    }
    
    // Bouton suivant
    if (currentPage < totalPages) {
        paginationHTML += `<li class="page-item">
            <a class="page-link" href="#" onclick="loadTerms(${currentPage + 1})">Suivant</a>
        </li>`;
    }
    
    container.innerHTML = paginationHTML;
}

// Ajouter un terme
function addTerm(termId) {
    const term = availableTermsData.find(t => t.id === termId);
    if (term && !selectedTermsData.find(selected => selected.id === termId)) {
        selectedTermsData.push(term);
        renderSelectedTerms();
        renderAvailableTerms();
    }
}

// Supprimer un terme
function removeTerm(termId) {
    selectedTermsData = selectedTermsData.filter(term => term.id !== termId);
    renderSelectedTerms();
    renderAvailableTerms();
}

// Sauvegarder les termes sélectionnés
function saveSelectedTerms() {
    const termIds = selectedTermsData.map(term => term.id);
    
    // Mettre à jour le champ caché du formulaire
    const hiddenInput = document.getElementById('term-ids');
    if (hiddenInput) {
        hiddenInput.value = JSON.stringify(termIds);
    }
    
    // Mettre à jour l'affichage dans le formulaire principal
    updateTermsDisplay();
    
    // Fermer le modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('termModal'));
    modal.hide();
    
    showAlert(`${selectedTermsData.length} terme(s) sélectionné(s)`, 'success');
}

// Mettre à jour l'affichage des termes dans le formulaire
function updateTermsDisplay() {
    const displayContainer = document.getElementById('selected-terms-display');
    if (!displayContainer) return;
    
    if (selectedTermsData.length === 0) {
        displayContainer.value = '';
        displayContainer.placeholder = 'Aucun terme sélectionné';
        return;
    }
    
    const termNames = selectedTermsData.map(term => term.pref_label).join(', ');
    displayContainer.value = termNames;
}

// Fonction utilitaire pour le debouncing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Fonction utilitaire pour afficher les alertes
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Supprimer automatiquement après 5 secondes
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, 5000);
}

// Fonction pour charger les termes déjà associés au record (si en mode édition)
function loadExistingTerms(recordId) {
    if (!recordId) return;
    
    fetch(`/api/records/${recordId}/terms`)
        .then(response => response.json())
        .then(data => {
            selectedTermsData = data;
            renderSelectedTerms();
            updateTermsDisplay();
        })
        .catch(error => {
            console.error('Erreur lors du chargement des termes existants:', error);
        });
}
</script>

<style>
.term-item {
    transition: all 0.2s ease;
}

.term-item:hover {
    background-color: #f8f9fa !important;
}

.pagination .page-link {
    color: #0d6efd;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

#availableTerms, #selectedTerms {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}

.badge .btn-close {
    font-size: 0.65em;
}
</style>
