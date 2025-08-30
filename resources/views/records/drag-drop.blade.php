@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-cloud-upload me-2"></i>
                        {{ __('Drag & Drop - Création automatique de records') }}
                    </h4>
                </div>

                <div class="card-body">
                    <!-- Zone de progression globale -->
                    <div id="global-progress" class="mb-4" style="display: none;">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bi bi-gear-fill me-2 text-primary"></i>
                                    Traitement en cours...
                                </h6>
                                <div class="progress mb-2">
                                    <div id="global-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
                                         role="progressbar" style="width: 0%"></div>
                                </div>
                                <small id="progress-text" class="text-muted">Initialisation...</small>
                            </div>
                        </div>
                    </div>

                    <!-- Zone de Drag & Drop -->
                    <div id="drop-zone" class="border-dashed border-3 border-light rounded p-5 text-center mb-4"
                         style="min-height: 300px; background-color: #f8f9fa; transition: all 0.3s;">
                        <div id="drop-zone-content">
                            <i class="bi bi-cloud-upload display-1 text-muted mb-3"></i>
                            <h4 class="text-muted mb-3">Glissez-déposez vos fichiers ici</h4>
                            <p class="text-muted mb-4">
                                Ou cliquez pour parcourir vos fichiers<br>
                                <small>Formats supportés: PDF, Images (JPG, PNG), Documents (DOC, DOCX, TXT)</small>
                            </p>
                            <button type="button" id="browse-files" class="btn btn-outline-primary btn-lg">
                                <i class="bi bi-folder2-open me-2"></i>Parcourir les fichiers
                            </button>
                        </div>

                        <!-- Input file caché -->
                        <input type="file" id="file-input" multiple
                               accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.txt,.rtf,.odt"
                               style="display: none;">
                    </div>

                    <!-- Zone des fichiers sélectionnés -->
                    <div id="files-preview" class="mb-4" style="display: none;">
                        <h5 class="mb-3">
                            <i class="bi bi-files me-2"></i>
                            Fichiers sélectionnés (<span id="files-count">0</span>)
                        </h5>
                        <div id="files-list" class="row"></div>
                    </div>

                    <!-- Boutons d'action -->
                    <div id="action-buttons" class="d-flex justify-content-between" style="display: none;">
                        <button type="button" id="clear-files" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>Effacer tout
                        </button>
                        <button type="button" id="process-files" class="btn btn-primary btn-lg">
                            <i class="bi bi-magic me-2"></i>Traiter avec l'IA
                        </button>
                    </div>

                    <!-- Zone de résultats IA -->
                    <div id="ai-results" class="mt-4" style="display: none;">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Suggestions de l'IA
                                </h5>
                            </div>
                            <div class="card-body" id="ai-suggestions">
                                <!-- Contenu généré dynamiquement -->
                            </div>
                            <div class="card-footer">
                                <div class="d-flex justify-content-between">
                                    <button type="button" id="reject-suggestions" class="btn btn-outline-danger">
                                        <i class="bi bi-x-lg me-2"></i>Rejeter
                                    </button>
                                    <button type="button" id="accept-suggestions" class="btn btn-success">
                                        <i class="bi bi-check-lg me-2"></i>Ouvrir le record
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-message">
                <!-- Message dynamique -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" id="modal-confirm" class="btn btn-primary">Confirmer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.border-dashed {
    border-style: dashed !important;
}

.drop-zone-active {
    border-color: var(--bs-primary) !important;
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.file-preview {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    transition: all 0.3s;
}

.file-preview:hover {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.1);
}

.file-icon {
    font-size: 2rem;
}

.ai-suggestion {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-left: 4px solid var(--bs-primary);
}

.confidence-bar {
    height: 8px;
    background: linear-gradient(90deg, #dc3545 0%, #ffc107 50%, #198754 100%);
    border-radius: 4px;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuration
    const MAX_FILES = 10;
    const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB
    const ALLOWED_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain',
        'application/rtf',
        'application/vnd.oasis.opendocument.text'
    ];
    const ALLOWED_EXTENSIONS = [
        'pdf','jpg','jpeg','png','gif','doc','docx','txt','rtf','odt'
    ];

    // Elements DOM
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');
    const browseBtn = document.getElementById('browse-files');
    const filesPreview = document.getElementById('files-preview');
    const filesList = document.getElementById('files-list');
    const filesCount = document.getElementById('files-count');
    const actionButtons = document.getElementById('action-buttons');
    const clearBtn = document.getElementById('clear-files');
    const processBtn = document.getElementById('process-files');
    const globalProgress = document.getElementById('global-progress');
    const progressBar = document.getElementById('global-progress-bar');
    const progressText = document.getElementById('progress-text');
    const aiResults = document.getElementById('ai-results');
    const aiSuggestions = document.getElementById('ai-suggestions');

    // État
    let selectedFiles = [];
    let currentAiResponse = null;

    // Event Listeners
    browseBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', handleFileSelect);
    clearBtn.addEventListener('click', clearAllFiles);
    processBtn.addEventListener('click', processFiles);

    // Drag & Drop
    dropZone.addEventListener('dragover', handleDragOver);
    dropZone.addEventListener('dragleave', handleDragLeave);
    dropZone.addEventListener('drop', handleDrop);

    // Fonctions utilitaires
    function handleDragOver(e) {
        e.preventDefault();
        dropZone.classList.add('drop-zone-active');
    }

    function handleDragLeave(e) {
        e.preventDefault();
        if (!dropZone.contains(e.relatedTarget)) {
            dropZone.classList.remove('drop-zone-active');
        }
    }

    function handleDrop(e) {
        e.preventDefault();
        dropZone.classList.remove('drop-zone-active');
        const files = Array.from(e.dataTransfer.files);
        addFiles(files);
    }

    function handleFileSelect(e) {
        const files = Array.from(e.target.files);
        addFiles(files);
    }

    function addFiles(files) {
        // Validation des fichiers
        const validFiles = files.filter(file => {
            if (selectedFiles.length >= MAX_FILES) {
                showAlert('warning', `Maximum ${MAX_FILES} fichiers autorisés`);
                return false;
            }

            if (file.size > MAX_FILE_SIZE) {
                showAlert('error', `Le fichier "${file.name}" est trop volumineux (max 50MB)`);
                return false;
            }

            // Vérifier le type MIME OU l'extension en fallback (certains navigateurs ne renseignent pas correctement file.type)
            const hasValidMime = file.type && ALLOWED_TYPES.includes(file.type);
            const ext = (file.name.split('.').pop() || '').toLowerCase();
            const hasValidExt = ALLOWED_EXTENSIONS.includes(ext);
            if (!hasValidMime && !hasValidExt) {
                showAlert('error', `Type de fichier non supporté: "${file.name}"`);
                return false;
            }

            return true;
        });

        // Ajouter les fichiers valides
        selectedFiles.push(...validFiles);
        updateFilesDisplay();
    }

    function updateFilesDisplay() {
        filesCount.textContent = selectedFiles.length;

        if (selectedFiles.length === 0) {
            filesPreview.style.display = 'none';
            actionButtons.style.display = 'none';
            return;
        }

        filesPreview.style.display = 'block';
        actionButtons.style.display = 'flex';

        // Générer la preview des fichiers
        filesList.innerHTML = selectedFiles.map((file, index) => {
            const icon = getFileIcon(file.type);
            const size = formatFileSize(file.size);

            return `
                <div class="col-md-4 mb-3">
                    <div class="file-preview p-3">
                        <div class="d-flex align-items-center">
                            <i class="bi ${icon} file-icon text-primary me-3"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 text-truncate" title="${file.name}">${file.name}</h6>
                                <small class="text-muted">${size}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="removeFile(${index})">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function removeFile(index) {
        selectedFiles.splice(index, 1);
        updateFilesDisplay();
    }

    function clearAllFiles() {
        selectedFiles = [];
        fileInput.value = '';
        updateFilesDisplay();
        hideAiResults();
    }

    async function processFiles() {
        if (selectedFiles.length === 0) {
            showAlert('warning', 'Aucun fichier sélectionné');
            return;
        }

        showProgress('Préparation des fichiers...', 10);

        try {
            const formData = new FormData();
            selectedFiles.forEach((file, index) => {
                // Utiliser files[] pour une compatibilité maximale côté PHP
                formData.append('files[]', file);
            });

            // Ajouter le token CSRF
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            if (token) {
                formData.append('_token', token);
            }

            showProgress('Envoi des fichiers...', 30);

            const response = await fetch('{{ route("records.drag-drop.process") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            showProgress('Traitement par l\'IA...', 70);

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Erreur lors du traitement');
            }

            showProgress('Finalisation...', 100);

            setTimeout(() => {
                hideProgress();
                displayAiResults(result);
            }, 500);

        } catch (error) {
            console.error('Erreur:', error);
            hideProgress();
            showAlert('error', error.message || 'Erreur lors du traitement des fichiers');
        }
    }

    function displayAiResults(result) {
        currentAiResponse = result;
        const suggestions = result.ai_suggestions || {};

        aiSuggestions.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="ai-suggestion p-3 rounded mb-3">
                        <h6><i class="bi bi-card-text me-2"></i>Titre suggéré</h6>
                        <p class="mb-0">${suggestions.title || 'Non défini'}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="ai-suggestion p-3 rounded mb-3">
                        <h6><i class="bi bi-briefcase me-2"></i>Activité suggérée</h6>
                        <p class="mb-0">${suggestions.activity_suggestion?.name || 'Non définie'}</p>
                        ${suggestions.activity_suggestion?.confidence ?
                            `<div class="confidence-bar mt-2" style="width: ${suggestions.activity_suggestion.confidence * 100}%"></div>` : ''
                        }
                    </div>
                </div>
            </div>

            <div class="ai-suggestion p-3 rounded mb-3">
                <h6><i class="bi bi-file-text me-2"></i>Description générée</h6>
                <p class="mb-0">${suggestions.content || 'Non définie'}</p>
            </div>

            ${suggestions.keywords && suggestions.keywords.length > 0 ? `
                <div class="ai-suggestion p-3 rounded mb-3">
                    <h6><i class="bi bi-tags me-2"></i>Mots-clés suggérés</h6>
                    <div class="d-flex flex-wrap gap-2">
                        ${suggestions.keywords.map(keyword =>
                            `<span class="badge bg-primary">${keyword}</span>`
                        ).join('')}
                    </div>
                </div>
            ` : ''}
        `;

        aiResults.style.display = 'block';
        aiResults.scrollIntoView({ behavior: 'smooth' });

        // Handlers pour les boutons
        document.getElementById('reject-suggestions').onclick = rejectSuggestions;
        document.getElementById('accept-suggestions').onclick = acceptSuggestions;
    }

    function rejectSuggestions() {
        hideAiResults();
    }

    function acceptSuggestions() {
        if (!currentAiResponse?.record_id) {
            showAlert('error', 'Aucun record trouvé à finaliser');
            return;
        }

        window.location.href = `/repositories/records/${currentAiResponse.record_id}`;
    }

    function hideAiResults() {
        aiResults.style.display = 'none';
        currentAiResponse = null;
    }

    function showProgress(message, percent) {
        progressText.textContent = message;
        progressBar.style.width = `${percent}%`;
        progressBar.setAttribute('aria-valuenow', percent);
        globalProgress.style.display = 'block';
    }

    function hideProgress() {
        globalProgress.style.display = 'none';
    }

    function showAlert(type, message) {
        // Créer une alerte Bootstrap
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';

        const alert = document.createElement('div');
        alert.className = `alert ${alertClass} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Insérer l'alerte en haut de la carte
        const cardBody = document.querySelector('.card-body');
        cardBody.insertBefore(alert, cardBody.firstChild);

        // Auto-suppression après 5 secondes
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }

    function getFileIcon(mimeType) {
        if (mimeType.includes('pdf')) return 'bi-file-pdf';
        if (mimeType.includes('image')) return 'bi-file-image';
        if (mimeType.includes('word') || mimeType.includes('document')) return 'bi-file-word';
        if (mimeType.includes('text')) return 'bi-file-text';
        return 'bi-file';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Exposition globale pour les boutons inline
    window.removeFile = removeFile;
});
</script>
@endpush
