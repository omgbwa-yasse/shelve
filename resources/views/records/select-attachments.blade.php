@extends('layouts.app')

@section('title', __('Analyser des documents avec IA'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-robot"></i>
                        {{ __('Analyse de documents avec IA') }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('records.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i>
                            {{ __('Retour aux records') }}
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info-circle"></i> {{ __('Comment ça marche ?') }}</h5>
                        <p>{{ __('Sélectionnez ou téléchargez vos documents numériques ci-dessous. Le système MCP analysera automatiquement leur contenu et créera directement un record archivistique avec :') }}</p>
                        <ul>
                            <li>{{ __('Une description archivistique générée automatiquement') }}</li>
                            <li>{{ __('Une indexation thésaurus intelligente') }}</li>
                            <li>{{ __('Tous les fichiers attachés au record créé') }}</li>
                        </ul>
                        <p><strong>{{ __('Formats supportés :') }}</strong> PDF, TXT, DOCX, RTF, ODT</p>
                    </div>

                    <!-- Zone de téléchargement -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-upload"></i>
                                {{ __('Télécharger de nouveaux documents') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="upload-area" id="upload-area">
                                <div class="upload-content">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                    <h5>{{ __('Glissez-déposez vos fichiers ici') }}</h5>
                                    <p class="text-muted">{{ __('ou') }}</p>
                                    <button type="button" class="btn btn-primary" id="browse-btn">
                                        <i class="fas fa-folder-open"></i>
                                        {{ __('Parcourir les fichiers') }}
                                    </button>
                                    <input type="file"
                                           id="file-input"
                                           multiple
                                           accept=".pdf,.txt,.docx,.doc,.rtf,.odt"
                                           style="display: none;">
                                    <div class="mt-2">
                                        <small class="text-muted">{{ __('Formats acceptés: PDF, TXT, DOCX, DOC, RTF, ODT') }}</small>
                                        <br>
                                        <small class="text-muted">{{ __('Taille maximum: 10MB par fichier') }}</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Prévisualisation des fichiers à télécharger -->
                            <div id="upload-preview" class="mt-3" style="display: none;">
                                <h6>{{ __('Fichiers à télécharger:') }}</h6>
                                <div id="upload-list" class="list-group"></div>
                                <button type="button" class="btn btn-success mt-2" id="upload-files-btn">
                                    <i class="fas fa-upload"></i>
                                    {{ __('Télécharger les fichiers') }}
                                </button>
                                <button type="button" class="btn btn-secondary mt-2" id="clear-files-btn">
                                    <i class="fas fa-times"></i>
                                    {{ __('Effacer') }}
                                </button>
                            </div>

                            <!-- Barre de progression -->
                            <div id="upload-progress" class="mt-3" style="display: none;">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%">0%</div>
                                </div>
                                <small id="upload-status" class="text-muted"></small>
                            </div>
                        </div>
                    </div>

                    <!-- Documents existants -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-file-alt"></i>
                                {{ __('Documents existants') }}
                                @if($attachments->count() > 0)
                                    <span class="badge bg-secondary ms-2">{{ $attachments->count() }}</span>
                                @endif
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($attachments->count() > 0)
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="select-all-existing">
                                            <label class="form-check-label" for="select-all-existing">
                                                {{ __('Sélectionner tous les documents existants') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="col-1">
                                                    <input type="checkbox" id="select-all-checkbox">
                                                </th>
                                                <th class="col-1">{{ __('Type') }}</th>
                                                <th class="col-5">{{ __('Nom du fichier') }}</th>
                                                <th class="col-2">{{ __('Taille') }}</th>
                                                <th class="col-2">{{ __('Créateur') }}</th>
                                                <th class="col-1">{{ __('Date') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="existing-attachments">
                                            @foreach($attachments as $attachment)
                                            <tr>
                                                <td>
                                                    <input type="checkbox"
                                                           name="attachment_ids[]"
                                                           value="{{ $attachment->id }}"
                                                           class="attachment-checkbox existing-attachment-checkbox">
                                                </td>
                                                <td>
                                                    @php
                                                        $extension = strtoupper(pathinfo($attachment->name, PATHINFO_EXTENSION));
                                                        $iconClass = match($extension) {
                                                            'PDF' => 'fas fa-file-pdf text-danger',
                                                            'TXT' => 'fas fa-file-alt text-secondary',
                                                            'DOCX', 'DOC' => 'fas fa-file-word text-primary',
                                                            'RTF' => 'fas fa-file-alt text-info',
                                                            'ODT' => 'fas fa-file-alt text-success',
                                                            default => 'fas fa-file text-muted'
                                                        };
                                                    @endphp
                                                    <i class="{{ $iconClass }}"></i>
                                                    <small>{{ $extension }}</small>
                                                </td>
                                                <td>
                                                    <strong>{{ $attachment->name }}</strong>
                                                    @if($attachment->thumbnail_path)
                                                        <br><small class="text-muted">{{ __('Aperçu disponible') }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small>{{ \App\Helpers\FileHelper::formatBytes($attachment->size ?? 0) }}</small>
                                                </td>
                                                <td>
                                                    <small>{{ $attachment->creator->name ?? __('Inconnu') }}</small>
                                                </td>
                                                <td>
                                                    <small>{{ $attachment->created_at->format('d/m/Y H:i') }}</small>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if($attachments->hasPages())
                                    <div class="d-flex justify-content-center">
                                        {{ $attachments->links() }}
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">{{ __('Aucun document trouvé') }}</h5>
                                    <p class="text-muted">{{ __('Téléchargez vos premiers documents ci-dessus pour commencer l\'analyse') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <form id="analyze-form" action="{{ route('records.create-from-mcp') }}" method="POST">
                        @csrf

                        <!-- Champs cachés pour les nouveaux attachments -->
                        <div id="new-attachments-inputs"></div>

                        <!-- Indicateur de progression des étapes MCP -->
                        <div id="mcp-progress" class="card mt-4" style="display: none;">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-cogs"></i>
                                    {{ __('Traitement automatique en cours...') }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="progress-steps">
                                    <div class="step" id="step-1">
                                        <div class="step-number">1</div>
                                        <div class="step-content">
                                            <div class="step-title">{{ __('Réception des documents') }}</div>
                                            <div class="step-description">{{ __('Envoi des fichiers au serveur MCP') }}</div>
                                        </div>
                                        <div class="step-status">
                                            <i class="fas fa-clock text-muted"></i>
                                        </div>
                                    </div>

                                    <div class="step" id="step-2">
                                        <div class="step-number">2</div>
                                        <div class="step-content">
                                            <div class="step-title">{{ __('Analyse des documents') }}</div>
                                            <div class="step-description">{{ __('Extraction et analyse du contenu') }}</div>
                                        </div>
                                        <div class="step-status">
                                            <i class="fas fa-clock text-muted"></i>
                                        </div>
                                    </div>

                                    <div class="step" id="step-3">
                                        <div class="step-number">3</div>
                                        <div class="step-content">
                                            <div class="step-title">{{ __('Création du record') }}</div>
                                            <div class="step-description">{{ __('Génération des métadonnées archivistiques') }}</div>
                                        </div>
                                        <div class="step-status">
                                            <i class="fas fa-clock text-muted"></i>
                                        </div>
                                    </div>

                                    <div class="step" id="step-4">
                                        <div class="step-number">4</div>
                                        <div class="step-content">
                                            <div class="step-title">{{ __('Finalisation') }}</div>
                                            <div class="step-description">{{ __('Association des attachments et indexation') }}</div>
                                        </div>
                                        <div class="step-status">
                                            <i class="fas fa-clock text-muted"></i>
                                        </div>
                                    </div>

                                    <div class="step" id="step-5">
                                        <div class="step-number">5</div>
                                        <div class="step-content">
                                            <div class="step-title">{{ __('Redirection') }}</div>
                                            <div class="step-description">{{ __('Vers la page de visualisation du record') }}</div>
                                        </div>
                                        <div class="step-status">
                                            <i class="fas fa-clock text-muted"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <div class="progress">
                                        <div id="overall-progress" class="progress-bar bg-success" role="progressbar" style="width: 0%">0%</div>
                                    </div>
                                    <div class="text-center mt-2">
                                        <small id="current-step-text" class="text-muted">{{ __('Préparation...') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit"
                                    class="btn btn-success btn-lg"
                                    id="analyze-btn"
                                    disabled>
                                <i class="fas fa-magic"></i>
                                {{ __('Créer un record automatiquement') }}
                            </button>
                            <div id="selection-info" class="mt-2">
                                <small class="text-muted">{{ __('Sélectionnez ou téléchargez au moins un document pour créer automatiquement un record') }}</small>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.upload-area {
    border: 2px dashed #007bff;
    border-radius: 10px;
    padding: 40px;
    text-align: center;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-area:hover {
    border-color: #0056b3;
    background-color: #e3f2fd;
}

.upload-area.dragover {
    border-color: #28a745;
    background-color: #d4edda;
    transform: scale(1.02);
}

.upload-content {
    pointer-events: none;
}

.attachment-checkbox:checked + td {
    background-color: #e7f3ff;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

#analyze-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.upload-item {
    display: flex;
    align-items: center;
    padding: 10px;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    margin-bottom: 5px;
    background-color: #fff;
}

.upload-item .file-info {
    flex-grow: 1;
    margin-left: 10px;
}

.upload-item .file-size {
    color: #6c757d;
    font-size: 0.9em;
}

.upload-item .remove-file {
    color: #dc3545;
    cursor: pointer;
    margin-left: 10px;
}

.upload-item .remove-file:hover {
    color: #c82333;
}

.progress {
    height: 25px;
}

.progress-bar {
    transition: width 0.3s ease;
}

/* Styles pour les étapes MCP */
.progress-steps {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.step {
    display: flex;
    align-items: center;
    padding: 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
}

.step.active {
    border-color: #007bff;
    background-color: #e3f2fd;
    transform: scale(1.02);
}

.step.completed {
    border-color: #28a745;
    background-color: #d4edda;
}

.step.error {
    border-color: #dc3545;
    background-color: #f8d7da;
}

.step-number {
    min-width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #6c757d;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 15px;
    transition: all 0.3s ease;
}

.step.active .step-number {
    background-color: #007bff;
    animation: pulse 1.5s infinite;
}

.step.completed .step-number {
    background-color: #28a745;
}

.step.error .step-number {
    background-color: #dc3545;
}

.step-content {
    flex-grow: 1;
}

.step-title {
    font-weight: bold;
    font-size: 1.1em;
    margin-bottom: 5px;
}

.step-description {
    font-size: 0.9em;
    color: #6c757d;
}

.step.active .step-description {
    color: #495057;
}

.step-status {
    margin-left: 15px;
}

.step.active .step-status i {
    color: #007bff !important;
    animation: spin 1s linear infinite;
}

.step.completed .step-status i {
    color: #28a745 !important;
}

.step.error .step-status i {
    color: #dc3545 !important;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(0, 123, 255, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
    }
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

#mcp-progress {
    border: 2px solid #007bff;
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const uploadArea = document.getElementById('upload-area');
    const fileInput = document.getElementById('file-input');
    const browseBtn = document.getElementById('browse-btn');
    const uploadPreview = document.getElementById('upload-preview');
    const uploadList = document.getElementById('upload-list');
    const uploadFilesBtn = document.getElementById('upload-files-btn');
    const clearFilesBtn = document.getElementById('clear-files-btn');
    const uploadProgress = document.getElementById('upload-progress');
    const progressBar = document.querySelector('.progress-bar');
    const uploadStatus = document.getElementById('upload-status');
    const newAttachmentsInputs = document.getElementById('new-attachments-inputs');

    const selectAllExistingCheckbox = document.getElementById('select-all-existing');
    const existingAttachmentCheckboxes = document.querySelectorAll('.existing-attachment-checkbox');
    const analyzeBtn = document.getElementById('analyze-btn');
    const selectionInfo = document.getElementById('selection-info');

    let filesToUpload = [];
    let uploadedAttachments = [];

    // Parcourir les fichiers
    browseBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', handleFileSelect);

    // Glisser-déposer
    uploadArea.addEventListener('click', () => fileInput.click());
    uploadArea.addEventListener('dragover', handleDragOver);
    uploadArea.addEventListener('dragleave', handleDragLeave);
    uploadArea.addEventListener('drop', handleDrop);

    // Gestion des fichiers
    uploadFilesBtn.addEventListener('click', uploadFiles);
    clearFilesBtn.addEventListener('click', clearFiles);

    // Sélection des attachments existants
    if (selectAllExistingCheckbox) {
        selectAllExistingCheckbox.addEventListener('change', function() {
            existingAttachmentCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateAnalyzeButton();
        });
    }

    existingAttachmentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateAnalyzeButton);
    });

    function handleDragOver(e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    }

    function handleDragLeave(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
    }

    function handleDrop(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const files = Array.from(e.dataTransfer.files);
        addFiles(files);
    }

    function handleFileSelect(e) {
        const files = Array.from(e.target.files);
        addFiles(files);
    }

    function addFiles(files) {
        const validExtensions = ['pdf', 'txt', 'docx', 'doc', 'rtf', 'odt'];
        const maxSize = 10 * 1024 * 1024; // 10MB

        files.forEach(file => {
            const extension = file.name.split('.').pop().toLowerCase();

            if (!validExtensions.includes(extension)) {
                alert(`Le fichier ${file.name} n'est pas dans un format supporté.`);
                return;
            }

            if (file.size > maxSize) {
                alert(`Le fichier ${file.name} est trop volumineux (maximum 10MB).`);
                return;
            }

            // Vérifier si le fichier n'est pas déjà dans la liste
            if (!filesToUpload.find(f => f.name === file.name && f.size === file.size)) {
                filesToUpload.push(file);
            }
        });

        updateUploadPreview();
        updateAnalyzeButton();
    }

    function updateUploadPreview() {
        if (filesToUpload.length === 0) {
            uploadPreview.style.display = 'none';
            return;
        }

        uploadPreview.style.display = 'block';
        uploadList.innerHTML = '';

        filesToUpload.forEach((file, index) => {
            const extension = file.name.split('.').pop().toUpperCase();
            const iconClass = getFileIcon(extension);

            const listItem = document.createElement('div');
            listItem.className = 'upload-item';
            listItem.innerHTML = `
                <i class="${iconClass}"></i>
                <div class="file-info">
                    <strong>${file.name}</strong>
                    <div class="file-size">${formatFileSize(file.size)}</div>
                </div>
                <i class="fas fa-times remove-file" onclick="removeFile(${index})"></i>
            `;
            uploadList.appendChild(listItem);
        });
    }

    function removeFile(index) {
        filesToUpload.splice(index, 1);
        updateUploadPreview();
        updateAnalyzeButton();
    }

    function clearFiles() {
        filesToUpload = [];
        uploadedAttachments = [];
        newAttachmentsInputs.innerHTML = '';
        updateUploadPreview();
        updateAnalyzeButton();
        fileInput.value = '';
    }

    async function uploadFiles() {
        if (filesToUpload.length === 0) return;

        uploadProgress.style.display = 'block';
        uploadFilesBtn.disabled = true;

        let uploadedCount = 0;
        const totalFiles = filesToUpload.length;

        for (let i = 0; i < filesToUpload.length; i++) {
            const file = filesToUpload[i];

            try {
                uploadStatus.textContent = `Téléchargement de ${file.name}...`;

                const formData = new FormData();
                formData.append('file', file);
                formData.append('name', file.name);
                @if($record ?? false)
                formData.append('record_id', '{{ $record->id }}');
                @endif
                formData.append('_token', '{{ csrf_token() }}');

                @if($record ?? false)
                const response = await fetch('{{ route("records.attachments.store", ["record" => $record->id ?? 0]) }}', {
                @else
                const response = await fetch('{{ route("attachments.upload-temp") }}', {
                @endif
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    const result = await response.json();

                    if (result.success || result.attachment) {
                        const attachment = result.attachment || result;
                        uploadedAttachments.push(attachment);

                        // Ajouter un input caché pour l'attachment téléchargé
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'attachment_ids[]';
                        hiddenInput.value = attachment.id;
                        hiddenInput.className = 'new-attachment-input';
                        newAttachmentsInputs.appendChild(hiddenInput);

                        uploadedCount++;
                        console.log('Fichier téléchargé avec succès:', attachment);
                    } else {
                        throw new Error(result.error || `Erreur lors du téléchargement de ${file.name}`);
                    }
                } else {
                    const errorData = await response.json().catch(() => ({ error: 'Erreur de serveur' }));
                    throw new Error(errorData.error || `Erreur HTTP ${response.status} lors du téléchargement de ${file.name}`);
                }
            } catch (error) {
                console.error('Erreur de téléchargement:', error);
                alert(`Erreur lors du téléchargement de ${file.name}: ${error.message}`);
            }

            // Mettre à jour la barre de progression
            const progress = ((i + 1) / totalFiles) * 100;
            progressBar.style.width = progress + '%';
            progressBar.textContent = Math.round(progress) + '%';
        }

        uploadStatus.textContent = `${uploadedCount}/${totalFiles} fichiers téléchargés avec succès`;
        uploadFilesBtn.disabled = false;

        // Nettoyer la liste des fichiers à télécharger
        filesToUpload = [];
        updateUploadPreview();
        updateAnalyzeButton();

        // Masquer la barre de progression après 2 secondes
        setTimeout(() => {
            uploadProgress.style.display = 'none';
            progressBar.style.width = '0%';
            progressBar.textContent = '0%';
        }, 2000);
    }

    function updateAnalyzeButton() {
        const existingSelected = document.querySelectorAll('.existing-attachment-checkbox:checked').length;
        const newAttachments = document.querySelectorAll('.new-attachment-input').length;
        const pendingFiles = filesToUpload.length;
        const totalSelected = existingSelected + newAttachments;

        if (totalSelected === 0 && pendingFiles === 0) {
            analyzeBtn.disabled = true;
            selectionInfo.innerHTML = '<small class="text-muted">Sélectionnez ou téléchargez au moins un document pour créer automatiquement un record</small>';
        } else if (pendingFiles > 0) {
            analyzeBtn.disabled = true;
            selectionInfo.innerHTML = `<small class="text-warning">${pendingFiles} fichier(s) en attente de téléchargement</small>`;
        } else if (totalSelected > 20) {
            analyzeBtn.disabled = true;
            selectionInfo.innerHTML = `<small class="text-danger">Maximum 20 documents autorisés (${totalSelected} sélectionnés)</small>`;
        } else {
            analyzeBtn.disabled = false;
            selectionInfo.innerHTML = `<small class="text-success">${totalSelected} document(s) sélectionné(s) - Le MCP créera automatiquement le record</small>`;
        }

        // Mettre à jour le checkbox "Sélectionner tout"
        if (selectAllExistingCheckbox) {
            selectAllExistingCheckbox.checked = existingSelected === existingAttachmentCheckboxes.length && existingSelected > 0;
            selectAllExistingCheckbox.indeterminate = existingSelected > 0 && existingSelected < existingAttachmentCheckboxes.length;
        }
    }

    function getFileIcon(extension) {
        const icons = {
            'PDF': 'fas fa-file-pdf text-danger',
            'TXT': 'fas fa-file-alt text-secondary',
            'DOCX': 'fas fa-file-word text-primary',
            'DOC': 'fas fa-file-word text-primary',
            'RTF': 'fas fa-file-alt text-info',
            'ODT': 'fas fa-file-alt text-success'
        };
        return icons[extension] || 'fas fa-file text-muted';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Rendre les fonctions globales pour les onclick
    window.removeFile = removeFile;

    // Soumission du formulaire avec indicateur de chargement et suivi des étapes
    document.getElementById('analyze-form').addEventListener('submit', function(e) {
        e.preventDefault(); // Empêcher la soumission normale

        // Afficher le suivi des étapes
        document.getElementById('mcp-progress').style.display = 'block';
        analyzeBtn.style.display = 'none';

        // Simuler le processus étape par étape
        simulateMcpProcess();
    });

    // Fonction pour simuler le processus MCP avec les étapes
    async function simulateMcpProcess() {
        const steps = [
            { id: 'step-1', title: 'Réception des documents', duration: 2000 },
            { id: 'step-2', title: 'Analyse des documents', duration: 4000 },
            { id: 'step-3', title: 'Création du record', duration: 3000 },
            { id: 'step-4', title: 'Finalisation', duration: 2000 },
            { id: 'step-5', title: 'Redirection', duration: 1000 }
        ];

        let currentStepIndex = 0;

        for (let i = 0; i < steps.length; i++) {
            const step = steps[i];

            // Activer l'étape courante
            activateStep(step.id, step.title);

            // Mettre à jour la barre de progression globale
            const progressPercent = ((i + 1) / steps.length) * 100;
            updateOverallProgress(progressPercent, step.title);

            // Attendre la durée de l'étape
            await new Promise(resolve => setTimeout(resolve, step.duration));

            // Marquer l'étape comme terminée
            completeStep(step.id);
        }

        // Toutes les étapes terminées, soumettre le formulaire
        submitFormToMcp();
    }

    function activateStep(stepId, title) {
        // Désactiver toutes les étapes
        document.querySelectorAll('.step').forEach(step => {
            step.classList.remove('active');
        });

        // Activer l'étape courante
        const currentStep = document.getElementById(stepId);
        currentStep.classList.add('active');
        currentStep.querySelector('.step-status i').className = 'fas fa-spinner text-primary';

        // Mettre à jour le texte de l'étape courante
        document.getElementById('current-step-text').textContent = `En cours: ${title}...`;
    }

    function completeStep(stepId) {
        const step = document.getElementById(stepId);
        step.classList.remove('active');
        step.classList.add('completed');
        step.querySelector('.step-status i').className = 'fas fa-check text-success';
    }

    function updateOverallProgress(percent, currentTitle) {
        const progressBar = document.getElementById('overall-progress');
        progressBar.style.width = percent + '%';
        progressBar.textContent = Math.round(percent) + '%';

        if (percent === 100) {
            document.getElementById('current-step-text').textContent = 'Processus terminé avec succès !';
        }
    }

    async function submitFormToMcp() {
        try {
            // Préparer les données du formulaire
            const formData = new FormData(document.getElementById('analyze-form'));

            // Envoyer la requête au serveur en AJAX
            const response = await fetch(document.getElementById('analyze-form').action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const result = await response.json();

                if (result.success && result.record_id) {
                    // Redirection vers la page de visualisation du record
                    setTimeout(() => {
                        window.location.href = `{{ route('records.show', '') }}/${result.record_id}`;
                    }, 1000);
                } else {
                    throw new Error(result.error || 'Erreur lors de la création du record');
                }
            } else {
                const errorData = await response.json().catch(() => ({ error: 'Erreur de serveur' }));
                throw new Error(errorData.error || `Erreur HTTP ${response.status}`);
            }
        } catch (error) {
            console.error('Erreur lors de la soumission:', error);

            // Marquer la dernière étape en erreur
            const lastActiveStep = document.querySelector('.step.active');
            if (lastActiveStep) {
                lastActiveStep.classList.remove('active');
                lastActiveStep.classList.add('error');
                lastActiveStep.querySelector('.step-status i').className = 'fas fa-times text-danger';
            }

            document.getElementById('current-step-text').textContent = 'Erreur: ' + error.message;
            document.getElementById('overall-progress').className = 'progress-bar bg-danger';

            // Réafficher le bouton pour permettre une nouvelle tentative
            setTimeout(() => {
                analyzeBtn.style.display = 'block';
                analyzeBtn.innerHTML = '<i class="fas fa-magic"></i> {{ __("Créer un record automatiquement") }}';
                analyzeBtn.disabled = false;
                document.getElementById('mcp-progress').style.display = 'none';
                // Réinitialiser les étapes
                resetSteps();
            }, 3000);
        }
    }

    function resetSteps() {
        document.querySelectorAll('.step').forEach(step => {
            step.classList.remove('active', 'completed', 'error');
            step.querySelector('.step-status i').className = 'fas fa-clock text-muted';
        });

        document.getElementById('overall-progress').style.width = '0%';
        document.getElementById('overall-progress').textContent = '0%';
        document.getElementById('overall-progress').className = 'progress-bar bg-success';
        document.getElementById('current-step-text').textContent = 'Préparation...';
    }

    // Initialiser l'état du bouton
    updateAnalyzeButton();
});
</script>
@endsection
