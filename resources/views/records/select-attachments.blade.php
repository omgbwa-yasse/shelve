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
                        <p>{{ __('Téléchargez ou sélectionnez plusieurs documents numériques ci-dessous. L\'IA analysera leur contenu et proposera automatiquement :') }}</p>
                        <ul>
                            <li>{{ __('Une description structurée de record archivistique') }}</li>
                            <li>{{ __('Une indexation thésaurus appropriée') }}</li>
                            <li>{{ __('Des métadonnées archivistiques suggérées') }}</li>
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

                    <form id="analyze-form" action="{{ route('records.analyze-attachments') }}" method="POST">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="model_name" class="form-label">{{ __('Modèle IA') }}</label>
                                <select name="model_name" id="model_name" class="form-control">
                                    <option value="llama3">Llama 3 (Recommandé)</option>
                                    <option value="mistral">Mistral (Français)</option>
                                    <option value="phi3">Phi 3 (Rapide)</option>
                                    <option value="codellama">Code Llama (Technique)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="template" class="form-label">{{ __('Niveau de détail') }}</label>
                                <select name="record_options[template]" id="template" class="form-control">
                                    <option value="basic">{{ __('Basique') }}</option>
                                    <option value="detailed" selected>{{ __('Détaillé') }}</option>
                                    <option value="full">{{ __('Complet') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Champs cachés pour les nouveaux attachments -->
                        <div id="new-attachments-inputs"></div>

                        <div class="mt-4">
                            <button type="submit"
                                    class="btn btn-success btn-lg"
                                    id="analyze-btn"
                                    disabled>
                                <i class="fas fa-robot"></i>
                                {{ __('Analyser les documents sélectionnés') }}
                            </button>
                            <div id="selection-info" class="mt-2">
                                <small class="text-muted">{{ __('Sélectionnez ou téléchargez au moins un document pour commencer l\'analyse') }}</small>
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
                @if($record)
                formData.append('record_id', '{{ $record->id }}');
                @endif
                formData.append('_token', '{{ csrf_token() }}');

                @if($record)
                const response = await fetch('{{ route("records.attachments.store", ["record" => $record->id]) }}', {
                @else
                const response = await fetch('/api/attachments/upload', {
                @endif
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    const result = await response.json();
                    uploadedAttachments.push(result.attachment);

                    // Ajouter un input caché pour l'attachment téléchargé
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'attachment_ids[]';
                    hiddenInput.value = result.attachment.id;
                    hiddenInput.className = 'new-attachment-input';
                    newAttachmentsInputs.appendChild(hiddenInput);

                    uploadedCount++;
                } else {
                    throw new Error(`Erreur lors du téléchargement de ${file.name}`);
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
            selectionInfo.innerHTML = '<small class="text-muted">Sélectionnez ou téléchargez au moins un document pour commencer l\'analyse</small>';
        } else if (pendingFiles > 0) {
            analyzeBtn.disabled = true;
            selectionInfo.innerHTML = `<small class="text-warning">${pendingFiles} fichier(s) en attente de téléchargement</small>`;
        } else if (totalSelected > 20) {
            analyzeBtn.disabled = true;
            selectionInfo.innerHTML = `<small class="text-danger">Maximum 20 documents autorisés (${totalSelected} sélectionnés)</small>`;
        } else {
            analyzeBtn.disabled = false;
            selectionInfo.innerHTML = `<small class="text-success">${totalSelected} document(s) sélectionné(s)</small>`;
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

    // Soumission du formulaire avec indicateur de chargement
    document.getElementById('analyze-form').addEventListener('submit', function() {
        analyzeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyse en cours...';
        analyzeBtn.disabled = true;
    });

    // Initialiser l'état du bouton
    updateAnalyzeButton();
});
</script>
@endsection
