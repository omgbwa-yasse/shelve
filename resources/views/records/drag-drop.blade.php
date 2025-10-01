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
                    <div class="mt-2">
                        <small class="badge bg-light text-dark">
                            <i class="bi bi-robot me-1"></i>Provider: <strong>{{ $ai_provider ?? 'ollama' }}</strong>
                        </small>
                        <small class="badge bg-light text-dark ms-2">
                            <i class="bi bi-cpu me-1"></i>Modèle: <strong>{{ $ai_model ?? 'gemma3:4b' }}</strong>
                        </small>
                    </div>
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
                        <div class="d-flex align-items-center gap-3">
                            <div class="input-group input-group-sm" title="Nombre maximum de caractères à garder par fichier avant envoi à l'IA">
                                <span class="input-group-text">Max caractères/fichier</span>
                                <input type="number" id="per-file-char-limit" class="form-control" min="200" max="100000" step="500" value="200">
                            </div>
                            <div class="input-group input-group-sm" title="Nombre de pages de PDF à prendre en compte (depuis la première page)">
                                <span class="input-group-text">Pages PDF</span>
                                <input type="number" id="pdf-page-count" class="form-control" min="1" max="2000" step="1" placeholder="Toutes">
                            </div>
                            <span class="text-muted small" title="Limite applicative par fichier">
                                Max fichier: {{ (int)($app_upload_max_file_size_mb ?? 50) }} Mo
                            </span>
                            <button type="button" id="clear-files" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Effacer tout
                            </button>
                        </div>
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
    // Configuration IA depuis la base de données
    const AI_PROVIDER = '{{ $ai_provider ?? "ollama" }}';
    const AI_MODEL = '{{ $ai_model ?? "gemma3:4b" }}';

    console.log('Configuration IA active:', { provider: AI_PROVIDER, model: AI_MODEL });

    // Configuration
    const MAX_FILES = 10;
    const MAX_FILE_SIZE = ({{ (int)($app_upload_max_file_size_mb ?? 50) }}) * 1024 * 1024; // MB from app setting
    // Server limits (used for preflight check)
    const SERVER_POST_MAX = `{{ $server_post_max ?? '' }}`;
    const SERVER_UPLOAD_MAX = `{{ $server_upload_max_filesize ?? '' }}`;
    const SERVER_MAX_FILE_UPLOADS = `{{ $server_max_file_uploads ?? '' }}`;

    function parseSizeToBytes(v) {
        if (!v) return 0;
        const m = String(v).trim().match(/^(\d+)([KMG])?$/i);
        if (!m) return parseInt(v, 10) || 0;
        const n = parseInt(m[1], 10);
        const unit = (m[2] || '').toUpperCase();
        if (unit === 'K') return n * 1024;
        if (unit === 'M') return n * 1024 * 1024;
        if (unit === 'G') return n * 1024 * 1024 * 1024;
        return n;
    }
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
        // Préflight: estimer la taille POST totale (fichiers + overhead)
        try {
            const totalBytes = selectedFiles.reduce((s, f) => s + (f.size || 0), 0);
            // overhead approximatif pour multipart; conservateur
            const overhead = selectedFiles.length * 1024;
            const estimatedPost = totalBytes + overhead;
            const postMax = parseSizeToBytes(SERVER_POST_MAX);
            const uploadMax = parseSizeToBytes(SERVER_UPLOAD_MAX);
            const maxUploads = parseInt(SERVER_MAX_FILE_UPLOADS || '20', 10);
            if (maxUploads && selectedFiles.length > maxUploads) {
                showAlert('warning', `Vous avez sélectionné ${selectedFiles.length} fichiers, au-delà de la limite serveur (${maxUploads}).`);
            }
            if (uploadMax && selectedFiles.some(f => f.size > uploadMax)) {
                showAlert('warning', `Au moins un fichier dépasse la limite serveur upload_max_filesize (${SERVER_UPLOAD_MAX}).`);
            }
            if (postMax && estimatedPost > postMax) {
                showAlert('warning', `La taille totale estimée (${formatFileSize(estimatedPost)}) dépasse post_max_size serveur (${SERVER_POST_MAX}). Le téléversement risque d'échouer. Réduisez le nombre ou la taille des fichiers.`);
            }
        } catch (e) {}
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

        console.log(`Traitement avec ${AI_PROVIDER} / ${AI_MODEL}`);

        try {
            const formData = new FormData();
            selectedFiles.forEach((file, index) => {
                // Utiliser files[] pour une compatibilité maximale côté PHP
                formData.append('files[]', file);
            });
            // Ajouter l'option de limite par fichier si précisée
            const perFileLimitInput = document.getElementById('per-file-char-limit');
            if (perFileLimitInput && perFileLimitInput.value) {
                formData.append('per_file_char_limit', perFileLimitInput.value);
            }
            // Ajouter l'option de nombre de pages PDF à traiter
            const pdfPageCountInput = document.getElementById('pdf-page-count');
            if (pdfPageCountInput && pdfPageCountInput.value) {
                formData.append('pdf_page_count', pdfPageCountInput.value);
            }

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

            showProgress(`Traitement par l'IA (${AI_PROVIDER} / ${AI_MODEL})...`, 70);

            const result = await response.json();

            if (!response.ok || !result.success) {
                if (response.status === 422 && result.errors) {
                    const messages = [];
                    Object.keys(result.errors).forEach(key => {
                        const arr = result.errors[key];
                        if (Array.isArray(arr)) {
                            arr.forEach(m => messages.push(m));
                        } else if (arr) {
                            messages.push(String(arr));
                        }
                    });
                    showAlert('error', messages.join('<br>') || (result.message || 'Validation des fichiers échouée'));
                    hideProgress();
                    return;
                }
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
        const isDefaultActivity = suggestions.is_default_activity || false;

        aiSuggestions.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="ai-suggestion p-3 rounded mb-3">
                        <h6><i class="bi bi-card-text me-2"></i>Titre suggéré</h6>
                        <p class="mb-0">${suggestions.title || 'Non défini'}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="ai-suggestion p-3 rounded mb-3 ${isDefaultActivity ? 'border border-warning' : ''}">
                        <h6>
                            <i class="bi bi-briefcase me-2"></i>Activité suggérée
                            ${isDefaultActivity ? '<i class="bi bi-exclamation-triangle text-warning ms-2" title="Activité par défaut utilisée"></i>' : ''}
                        </h6>
                        <p class="mb-0">
                            ${suggestions.activity_suggestion?.name || 'Non définie'}
                            ${suggestions.activity_suggestion?.code ? `<br><small class="text-muted">(${suggestions.activity_suggestion.code})</small>` : ''}
                        </p>
                        ${isDefaultActivity ?
                            `<small class="text-warning d-block mt-2">
                                <i class="bi bi-info-circle me-1"></i>
                                L'IA n'a pas trouvé d'activité correspondante dans les activités de votre organisation.
                                L'activité ci-dessus sera utilisée par défaut. Vous pourrez la modifier après création.
                            </small>` : ''
                        }
                        ${suggestions.activity_suggestion?.confidence && !isDefaultActivity ?
                            `<div class="mt-2">
                                <small class="text-muted">Confiance: ${Math.round(suggestions.activity_suggestion.confidence * 100)}%</small>
                                <div class="confidence-bar" style="width: ${suggestions.activity_suggestion.confidence * 100}%"></div>
                            </div>` : ''
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
