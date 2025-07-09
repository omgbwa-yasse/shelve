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
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="termAutocomplete" class="form-label">Rechercher et sélectionner des termes</label>
                        <div class="position-relative">
                            <input type="text" class="form-control" id="termAutocomplete" 
                                   placeholder="Tapez au moins 3 caractères pour rechercher...">
                            <div id="autocompleteResults" class="autocomplete-results position-absolute w-100 bg-white border border-top-0 d-none" 
                                 style="max-height: 200px; overflow-y: auto; z-index: 1000;"></div>
                        </div>
                    </div>
                </div>

                <!-- Termes sélectionnés -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Termes sélectionnés</label>
                        <div id="selectedTermsTags" class="selected-terms-tags p-2 border rounded" 
                             style="min-height: 60px; background-color: #f8f9fa;">
                            <div class="text-muted">Aucun terme sélectionné</div>
                        </div>
                    </div>
                </div>

                <!-- Filtre par schéma (optionnel) -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="schemeFilter" class="form-label">Filtrer par schéma (optionnel)</label>
                        <select class="form-select" id="schemeFilter">
                            <option value="">Tous les schémas</option>
                        </select>
                    </div>
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

<!-- Script pour la gestion des termes avec autocomplétion -->
<script>
let selectedTermsData = [];
let autocompleteTimeout;

// Initialisation du modal
document.addEventListener('DOMContentLoaded', function() {
    const termModal = document.getElementById('termModal');
    const termAutocomplete = document.getElementById('termAutocomplete');
    const autocompleteResults = document.getElementById('autocompleteResults');
    const schemeFilter = document.getElementById('schemeFilter');
    
    // Charger les schémas au chargement
    loadSchemes();
    
    // Event listeners
    termModal.addEventListener('shown.bs.modal', function() {
        termAutocomplete.focus();
    });
    
    // Autocomplétion sur saisie
    termAutocomplete.addEventListener('input', function() {
        const query = this.value.trim();
        
        if (query.length < 3) {
            hideAutocompleteResults();
            return;
        }
        
        // Débounce pour éviter trop de requêtes
        clearTimeout(autocompleteTimeout);
        autocompleteTimeout = setTimeout(() => {
            searchTermsAutocomplete(query);
        }, 300);
    });
    
    // Cacher les résultats quand on clique ailleurs
    document.addEventListener('click', function(e) {
        if (!termAutocomplete.contains(e.target) && !autocompleteResults.contains(e.target)) {
            hideAutocompleteResults();
        }
    });
    
    // Filtrage par schéma
    schemeFilter.addEventListener('change', function() {
        const query = termAutocomplete.value.trim();
        if (query.length >= 3) {
            searchTermsAutocomplete(query);
        }
    });
    
    document.getElementById('saveTerms').addEventListener('click', saveSelectedTerms);
});

// Charger les schémas thésaurus
function loadSchemes() {
    fetch('/api/thesaurus/schemes')
        .then(response => response.json())
        .then(data => {
            const schemeFilter = document.getElementById('schemeFilter');
            schemeFilter.innerHTML = '<option value="">Tous les schémas</option>';
            
            data.forEach(scheme => {
                const option = document.createElement('option');
                option.value = scheme.id;
                option.textContent = scheme.title;
                schemeFilter.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Erreur lors du chargement des schémas:', error);
        });
}

// Recherche d'autocomplétion
function searchTermsAutocomplete(query) {
    const schemeId = document.getElementById('schemeFilter').value;
    
    const params = new URLSearchParams({
        search: query,
        limit: 5
    });
    
    if (schemeId) {
        params.append('scheme_id', schemeId);
    }
    
    fetch(`/api/thesaurus/concepts/autocomplete?${params}`)
        .then(response => response.json())
        .then(data => {
            showAutocompleteResults(data);
        })
        .catch(error => {
            console.error('Erreur lors de la recherche:', error);
            hideAutocompleteResults();
        });
}

// Afficher les résultats d'autocomplétion
function showAutocompleteResults(terms) {
    const resultsContainer = document.getElementById('autocompleteResults');
    
    if (terms.length === 0) {
        resultsContainer.innerHTML = '<div class="p-2 text-muted">Aucun terme trouvé</div>';
        resultsContainer.classList.remove('d-none');
        return;
    }
    
    resultsContainer.innerHTML = terms.map(term => {
        const isSelected = selectedTermsData.find(selected => selected.id === term.id);
        const displayTerm = term.specific_term || term; // Utiliser le terme spécifique si disponible
        
        return `
            <div class="autocomplete-item p-2 border-bottom ${isSelected ? 'bg-light text-muted' : 'cursor-pointer'}" 
                 ${!isSelected ? `onclick="selectTermFromAutocomplete(${JSON.stringify(displayTerm).replace(/"/g, '&quot;')})"` : ''}>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${displayTerm.pref_label}</strong>
                        ${displayTerm.alt_labels ? `<br><small class="text-muted">${displayTerm.alt_labels}</small>` : ''}
                        <br><small class="text-info">${displayTerm.scheme ? displayTerm.scheme.title : ''}</small>
                        ${term.specific_term ? '<br><small class="text-warning">→ Terme spécifique proposé</small>' : ''}
                    </div>
                    <div>
                        ${isSelected ? 
                            '<i class="fas fa-check text-success"></i>' : 
                            '<i class="fas fa-plus text-primary"></i>'
                        }
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    resultsContainer.classList.remove('d-none');
}

// Cacher les résultats d'autocomplétion
function hideAutocompleteResults() {
    document.getElementById('autocompleteResults').classList.add('d-none');
}

// Sélectionner un terme depuis l'autocomplétion
function selectTermFromAutocomplete(term) {
    // Vérifier si le terme n'est pas déjà sélectionné
    if (selectedTermsData.find(selected => selected.id === term.id)) {
        return;
    }
    
    // Ajouter le terme à la sélection
    selectedTermsData.push(term);
    
    // Mettre à jour l'affichage
    updateSelectedTermsDisplay();
    
    // Vider le champ de recherche
    document.getElementById('termAutocomplete').value = '';
    
    // Cacher les résultats
    hideAutocompleteResults();
    
    // Feedback visuel
    showAlert(`Terme "${term.pref_label}" ajouté`, 'success');
}

// Supprimer un terme sélectionné
function removeTerm(termId) {
    selectedTermsData = selectedTermsData.filter(term => term.id !== termId);
    updateSelectedTermsDisplay();
    showAlert('Terme supprimé', 'info');
}

// Mettre à jour l'affichage des termes sélectionnés
function updateSelectedTermsDisplay() {
    const container = document.getElementById('selectedTermsTags');
    
    if (selectedTermsData.length === 0) {
        container.innerHTML = '<div class="text-muted">Aucun terme sélectionné</div>';
        return;
    }
    
    container.innerHTML = selectedTermsData.map(term => `
        <span class="badge bg-primary me-2 mb-2 d-inline-flex align-items-center">
            <span class="me-1">${term.pref_label}</span>
            <button type="button" class="btn-close btn-close-white" 
                    onclick="removeTerm(${term.id})" 
                    aria-label="Supprimer ${term.pref_label}"></button>
        </span>
    `).join('');
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
    updateMainFormDisplay();
    
    // Fermer le modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('termModal'));
    modal.hide();
    
    showAlert(`${selectedTermsData.length} terme(s) sélectionné(s)`, 'success');
}

// Mettre à jour l'affichage des termes dans le formulaire principal
function updateMainFormDisplay() {
    const displayInput = document.getElementById('selected-terms-display');
    if (!displayInput) return;
    
    if (selectedTermsData.length === 0) {
        displayInput.value = '';
        displayInput.placeholder = 'Aucun terme sélectionné';
        return;
    }
    
    const termNames = selectedTermsData.map(term => term.pref_label).join(', ');
    displayInput.value = termNames;
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
    
    // Supprimer automatiquement après 3 secondes
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, 3000);
}

// Fonction pour charger les termes déjà associés au record (si en mode édition)
function loadExistingTerms(recordId) {
    if (!recordId) return;
    
    fetch(`/api/records/${recordId}/terms`)
        .then(response => response.json())
        .then(data => {
            selectedTermsData = data;
            updateSelectedTermsDisplay();
            updateMainFormDisplay();
        })
        .catch(error => {
            console.error('Erreur lors du chargement des termes existants:', error);
        });
}
</script>

<style>
.autocomplete-results {
    border-top: none !important;
    border-radius: 0 0 0.375rem 0.375rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.autocomplete-item {
    transition: background-color 0.2s ease;
}

.autocomplete-item:hover:not(.bg-light) {
    background-color: #f8f9fa !important;
}

.autocomplete-item.cursor-pointer {
    cursor: pointer;
}

.selected-terms-tags {
    min-height: 80px;
}

.selected-terms-tags .badge {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}

.selected-terms-tags .btn-close {
    font-size: 0.75rem;
    margin-left: 0.5rem;
}

.position-relative {
    position: relative;
}

#termAutocomplete:focus + .autocomplete-results {
    border-color: #86b7fe;
}
</style>
