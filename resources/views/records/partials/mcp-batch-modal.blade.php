{{-- Modal pour le traitement MCP par lots --}}
<div class="modal fade" id="mcpBatchModal" tabindex="-1" aria-labelledby="mcpBatchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="mcpBatchModalLabel">
                    <i class="bi bi-robot me-2"></i>Traitement MCP par Lots
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="mcpBatchForm">
                    @csrf
                    
                    {{-- Sélection des records --}}
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-list-check me-2"></i>Sélection des Records
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="batchMethod" class="form-label">Méthode de sélection</label>
                                <select class="form-select" id="batchMethod" name="method" required>
                                    <option value="selected">Records sélectionnés ({{ __('selected_count') ?? '0' }})</option>
                                    <option value="filter">Selon les filtres actuels</option>
                                    <option value="all">Tous les records</option>
                                    <option value="custom">Sélection personnalisée</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="batchLimit" class="form-label">Limite de records</label>
                                <input type="number" class="form-control" id="batchLimit" name="limit" 
                                       value="50" min="1" max="1000" required>
                                <small class="form-text text-muted">Maximum 1000 records par lot</small>
                            </div>
                        </div>
                        
                        {{-- Filtres personnalisés (masqués par défaut) --}}
                        <div id="customFilters" class="mt-3" style="display: none;">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="filterOrganisation" class="form-label">Organisation</label>
                                    <select class="form-select" id="filterOrganisation" name="organisation_id">
                                        <option value="">Toutes</option>
                                        {{-- Les options seront remplies via AJAX --}}
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="filterLevel" class="form-label">Niveau</label>
                                    <select class="form-select" id="filterLevel" name="level_id">
                                        <option value="">Tous</option>
                                        {{-- Les options seront remplies via AJAX --}}
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="filterActivity" class="form-label">Activité</label>
                                    <select class="form-select" id="filterActivity" name="activity_id">
                                        <option value="">Toutes</option>
                                        {{-- Les options seront remplies via AJAX --}}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Fonctionnalités MCP --}}
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-gear me-2"></i>Fonctionnalités à Appliquer
                        </h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="title" 
                                           id="batchFeatureTitle" name="features[]">
                                    <label class="form-check-label" for="batchFeatureTitle">
                                        <i class="bi bi-magic text-primary me-1"></i>
                                        <strong>Reformulation Titre</strong>
                                        <small class="d-block text-muted">Applique les règles ISAD(G)</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="thesaurus" 
                                           id="batchFeatureThesaurus" name="features[]" checked>
                                    <label class="form-check-label" for="batchFeatureThesaurus">
                                        <i class="bi bi-tags text-success me-1"></i>
                                        <strong>Indexation Thésaurus</strong>
                                        <small class="d-block text-muted">Mots-clés automatiques</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="summary" 
                                           id="batchFeatureSummary" name="features[]">
                                    <label class="form-check-label" for="batchFeatureSummary">
                                        <i class="bi bi-file-text text-info me-1"></i>
                                        <strong>Résumé ISAD(G)</strong>
                                        <small class="d-block text-muted">Élément 3.3.1</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Recommandation :</strong> Commencez par l'indexation thésaurus qui est la plus rapide, 
                            puis testez avec un petit nombre de records avant un traitement massif.
                        </div>
                    </div>

                    {{-- Options de traitement --}}
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-sliders me-2"></i>Options de Traitement
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           id="batchAsync" name="async" checked>
                                    <label class="form-check-label" for="batchAsync">
                                        <strong>Traitement asynchrone</strong>
                                        <small class="d-block text-muted">Recommandé pour plus de 10 records</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           id="batchNotification" name="notifications" checked>
                                    <label class="form-check-label" for="batchNotification">
                                        <strong>Notifications</strong>
                                        <small class="d-block text-muted">Recevoir un rapport par email</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Estimation --}}
                    <div class="mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bi bi-speedometer2 me-2"></i>Estimation
                                </h6>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="fw-bold text-primary" id="estimatedRecords">0</div>
                                        <small class="text-muted">Records</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="fw-bold text-info" id="estimatedTime">0 min</div>
                                        <small class="text-muted">Durée estimée</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="fw-bold text-warning" id="estimatedCost">-</div>
                                        <small class="text-muted">Charge système</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Annuler
                </button>
                <button type="button" class="btn btn-info" id="batchPreviewBtn">
                    <i class="bi bi-eye me-1"></i>Prévisualiser
                </button>
                <button type="button" class="btn btn-primary" id="batchStartBtn">
                    <i class="bi bi-play me-1"></i>Démarrer le Traitement
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal de progression --}}
<div class="modal fade" id="mcpProgressModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-gear-fill me-2"></i>Traitement MCP en Cours
                </h5>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Traitement...</span>
                    </div>
                </div>
                <h6 id="progressTitle">Initialisation du traitement...</h6>
                <div class="progress mb-3">
                    <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%"></div>
                </div>
                <p class="mb-0" id="progressDetails">Préparation des tâches...</p>
                <small class="text-muted" id="progressStats">0 / 0 records traités</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" onclick="openMcpDashboard()">
                    <i class="bi bi-speedometer2 me-1"></i>Voir le Dashboard
                </button>
                <button type="button" class="btn btn-secondary" id="cancelProcessingBtn">
                    <i class="bi bi-stop me-1"></i>Interrompre
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeMcpBatchModal();
});

function initializeMcpBatchModal() {
    const form = document.getElementById('mcpBatchForm');
    const methodSelect = document.getElementById('batchMethod');
    const customFilters = document.getElementById('customFilters');
    const previewBtn = document.getElementById('batchPreviewBtn');
    const startBtn = document.getElementById('batchStartBtn');
    
    // Gérer l'affichage des filtres personnalisés
    methodSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customFilters.style.display = 'block';
            loadFilterOptions();
        } else {
            customFilters.style.display = 'none';
        }
        updateEstimation();
    });
    
    // Mettre à jour l'estimation quand les paramètres changent
    form.addEventListener('change', updateEstimation);
    
    // Gestionnaires des boutons
    previewBtn.addEventListener('click', previewBatchProcessing);
    startBtn.addEventListener('click', startBatchProcessing);
    
    // Initialiser l'estimation
    updateEstimation();
}

function updateEstimation() {
    const method = document.getElementById('batchMethod').value;
    const limit = parseInt(document.getElementById('batchLimit').value) || 0;
    const features = document.querySelectorAll('input[name="features[]"]:checked');
    
    let estimatedRecords = 0;
    
    switch(method) {
        case 'selected':
            estimatedRecords = getSelectedRecordsCount();
            break;
        case 'filter':
            estimatedRecords = getFilteredRecordsCount();
            break;
        case 'all':
            estimatedRecords = getTotalRecordsCount();
            break;
        case 'custom':
            estimatedRecords = limit; // Estimation approximative
            break;
    }
    
    estimatedRecords = Math.min(estimatedRecords, limit);
    
    // Estimation du temps (approximative)
    const timePerRecord = features.length * 2; // 2 secondes par fonctionnalité
    const estimatedMinutes = Math.ceil((estimatedRecords * timePerRecord) / 60);
    
    // Charge système
    let systemLoad = 'Faible';
    if (estimatedRecords > 100) systemLoad = 'Moyenne';
    if (estimatedRecords > 500) systemLoad = 'Élevée';
    
    // Mettre à jour l'affichage
    document.getElementById('estimatedRecords').textContent = estimatedRecords;
    document.getElementById('estimatedTime').textContent = estimatedMinutes + ' min';
    document.getElementById('estimatedCost').textContent = systemLoad;
    
    // Désactiver le bouton si aucune fonctionnalité sélectionnée
    const hasFeatures = features.length > 0;
    document.getElementById('batchStartBtn').disabled = !hasFeatures;
    document.getElementById('batchPreviewBtn').disabled = !hasFeatures;
}

function getSelectedRecordsCount() {
    // Compter les checkboxes cochées dans la liste des records
    return document.querySelectorAll('.record-checkbox:checked').length;
}

function getFilteredRecordsCount() {
    // Cette fonction devrait être adaptée selon votre logique de filtres
    return parseInt(document.querySelector('.records-count')?.textContent) || 0;
}

function getTotalRecordsCount() {
    // Nombre total de records - à adapter selon votre interface
    return parseInt(document.querySelector('.total-records')?.textContent) || 1000;
}

function loadFilterOptions() {
    // Charger les options des filtres via AJAX
    Promise.all([
        fetch('/api/organisations').then(r => r.json()),
        fetch('/api/levels').then(r => r.json()),
        fetch('/api/activities').then(r => r.json())
    ]).then(([organisations, levels, activities]) => {
        populateSelect('filterOrganisation', organisations);
        populateSelect('filterLevel', levels);
        populateSelect('filterActivity', activities);
    }).catch(console.error);
}

function populateSelect(selectId, options) {
    const select = document.getElementById(selectId);
    const currentValue = select.value;
    
    // Garder l'option "Tous"
    const defaultOption = select.firstElementChild;
    select.innerHTML = '';
    select.appendChild(defaultOption);
    
    options.forEach(option => {
        const optionElement = document.createElement('option');
        optionElement.value = option.id;
        optionElement.textContent = option.name;
        if (option.id == currentValue) optionElement.selected = true;
        select.appendChild(optionElement);
    });
}

function previewBatchProcessing() {
    const formData = new FormData(document.getElementById('mcpBatchForm'));
    const params = new URLSearchParams(formData);
    
    showMcpNotification('Génération de l\'aperçu...', 'info');
    
    fetch('/api/mcp/batch/preview?' + params.toString(), {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.message);
        }
        showBatchPreview(data);
    })
    .catch(error => {
        showMcpNotification('Erreur lors de la prévisualisation: ' + error.message, 'error');
    });
}

function startBatchProcessing() {
    const formData = new FormData(document.getElementById('mcpBatchForm'));
    const data = Object.fromEntries(formData.entries());
    
    // Convertir les features en array
    data.features = formData.getAll('features[]');
    
    if (data.features.length === 0) {
        showMcpNotification('Veuillez sélectionner au moins une fonctionnalité', 'error');
        return;
    }
    
    // Fermer la modal de configuration
    bootstrap.Modal.getInstance(document.getElementById('mcpBatchModal')).hide();
    
    // Afficher la modal de progression
    const progressModal = new bootstrap.Modal(document.getElementById('mcpProgressModal'));
    progressModal.show();
    
    // Lancer le traitement
    fetch('/api/mcp/batch/process', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.message);
        }
        
        if (data.status === 'queued') {
            // Traitement asynchrone - surveiller la progression
            monitorBatchProgress(data.batch_id || 'unknown');
        } else {
            // Traitement synchrone terminé
            showBatchResults(data);
        }
    })
    .catch(error => {
        progressModal.hide();
        showMcpNotification('Erreur lors du lancement: ' + error.message, 'error');
    });
}

function monitorBatchProgress(batchId) {
    const progressInterval = setInterval(() => {
        fetch(`/api/mcp/batch/status/${batchId}`)
        .then(response => response.json())
        .then(data => {
            updateProgressDisplay(data);
            
            if (data.status === 'completed' || data.status === 'failed') {
                clearInterval(progressInterval);
                setTimeout(() => {
                    bootstrap.Modal.getInstance(document.getElementById('mcpProgressModal')).hide();
                    showBatchResults(data);
                }, 2000);
            }
        })
        .catch(console.error);
    }, 2000); // Vérifier toutes les 2 secondes
    
    // Arrêter la surveillance après 30 minutes
    setTimeout(() => clearInterval(progressInterval), 30 * 60 * 1000);
}

function updateProgressDisplay(data) {
    const progress = data.progress || 0;
    const total = data.total || 1;
    const processed = data.processed || 0;
    
    const percentage = Math.round((processed / total) * 100);
    
    document.getElementById('progressBar').style.width = percentage + '%';
    document.getElementById('progressBar').textContent = percentage + '%';
    
    document.getElementById('progressTitle').textContent = data.message || 'Traitement en cours...';
    document.getElementById('progressStats').textContent = `${processed} / ${total} records traités`;
    
    if (data.current_action) {
        document.getElementById('progressDetails').textContent = data.current_action;
    }
}

function showBatchResults(data) {
    const message = data.message || 'Traitement terminé';
    const type = data.error ? 'error' : 'success';
    
    showMcpNotification(message, type);
    
    // Optionnel: afficher un résumé détaillé
    if (data.summary) {
        console.log('Résumé du traitement:', data.summary);
    }
    
    // Recharger la page si nécessaire
    if (type === 'success') {
        setTimeout(() => {
            window.location.reload();
        }, 3000);
    }
}

function showBatchPreview(data) {
    // Afficher l'aperçu dans une modale ou une section dédiée
    showMcpNotification(`Aperçu généré: ${data.estimated_records} records seront traités`, 'info');
}

function openMcpDashboard() {
    window.open('/admin/mcp', '_blank');
}

function cancelBatchProcessing() {
    if (confirm('Êtes-vous sûr de vouloir interrompre le traitement en cours ?')) {
        // Implémenter l'annulation si nécessaire
        showMcpNotification('Annulation du traitement...', 'info');
    }
}
</script>
@endpush