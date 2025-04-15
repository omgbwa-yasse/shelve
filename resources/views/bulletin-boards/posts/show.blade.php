@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.index') }}">Tableaux d'affichage</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.show', $bulletinBoard['id']) }}">{{ $bulletinBoard->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.posts.index', $bulletinBoard['id']) }}">Publications</a></li>
                    <li class="breadcrumb-item active">{{ $post->name }}</li>
                </ol>
            </nav>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-{{ $post->status == 'published' ? 'success' : ($post->status == 'draft' ? 'warning' : 'secondary') }} me-2">
                            {{ ucfirst($post->status) }}
                        </span>
                        <span class="text-muted">Publication</span>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i> Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('bulletin-boards.posts.edit', [$bulletinBoard['id'], $post->id]) }}">
                                    <i class="fas fa-edit fa-fw me-1"></i> Modifier
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changeStatusModal">
                                    <i class="fas fa-toggle-on fa-fw me-1"></i> Changer le statut
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" id="copy-link">
                                    <i class="fas fa-link fa-fw me-1"></i> Copier le lien
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('bulletin-boards.posts.destroy', [$bulletinBoard['id'], $post->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette publication ?')">
                                        <i class="fas fa-trash fa-fw me-1"></i> Supprimer
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <h1 class="card-title mb-3">{{ $post->name }}</h1>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <i class="fas fa-calendar-alt fa-fw text-muted me-2"></i>
                                <strong>Date de début:</strong>
                                {{ $post->start_date->format('d/m/Y') }}
                            </div>

                            @if($post->end_date)
                                <div class="mb-3">
                                    <i class="fas fa-calendar-check fa-fw text-muted me-2"></i>
                                    <strong>Date de fin:</strong>
                                    {{ $post->end_date->format('d/m/Y') }}
                                </div>

                                <div class="mb-3">
                                    <i class="fas fa-hourglass-half fa-fw text-muted me-2"></i>
                                    <strong>Durée:</strong>
                                    @php
                                        $duration = $post->start_date->diff($post->end_date);
                                        echo $duration->days . ' jour' . ($duration->days > 1 ? 's' : '');
                                    @endphp
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <i class="fas fa-user fa-fw text-muted me-2"></i>
                                <strong>Créé par:</strong>
                                {{ $post->creator->name }}
                            </div>

                            <div class="mb-3">
                                <i class="fas fa-clock fa-fw text-muted me-2"></i>
                                <strong>Créé le:</strong>
                                {{ $post->created_at->format('d/m/Y à H:i') }}
                            </div>

                            <div class="mb-3">
                                <i class="fas fa-edit fa-fw text-muted me-2"></i>
                                <strong>Dernière modification:</strong>
                                {{ $post->updated_at->format('d/m/Y à H:i') }}
                            </div>

                            <div class="mb-3">
                                <i class="fas fa-info-circle fa-fw text-muted me-2"></i>
                                <strong>Statut actuel:</strong>
                                <span class="badge bg-{{ $post->status == 'published' ? 'success' : ($post->status == 'draft' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($post->status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Contenu</h5>
                        </div>
                        <div class="card-body">
                            <div class="post-content">
                                {!! nl2br(e($post->description)) !!}
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Pièces jointes</h5>
                            <button type="button" class="btn btn-primary btn-sm" id="addAttachmentButton" data-bs-toggle="modal" data-bs-target="#addAttachmentModal">
                                <i class="fas fa-plus me-1"></i> Ajouter des pièces jointes
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="attachments-list">
                                @if($post->attachments && $post->attachments->isNotEmpty())
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Fichier</th>
                                                    <th>Type</th>
                                                    <th>Taille</th>
                                                    <th>Ajouté par</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="attachments-table-body">
                                                @foreach($post->attachments as $attachment)
                                                    <tr id="attachment-{{ $attachment->id }}" class="attachment-row">
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div>
                                                                    {{ $attachment->name }}
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>{{ Str::upper(pathinfo($attachment->name, PATHINFO_EXTENSION)) }}</td>
                                                        <td>{{ number_format($attachment->size / 1024, 2) }} KB</td>
                                                        <td>{{ $attachment->creator->name }}</td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="{{ route('posts.attachments.download', $attachment->id) }}" class="btn btn-outline-primary" target="_blank">
                                                                    <i class="fas fa-download"></i>
                                                                </a>
                                                                <a href="{{ route('posts.attachments.preview', $attachment->id) }}" class="btn btn-outline-info" target="_blank">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <button type="button" class="btn btn-outline-danger delete-attachment" data-attachment-id="{{ $attachment->id }}">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info" id="no-attachments-message">
                                        <i class="fas fa-info-circle me-2"></i> Aucune pièce jointe n'est associée à cette publication.
                                        <button type="button" class="btn btn-link p-0 alert-link" data-bs-toggle="modal" data-bs-target="#addAttachmentModal">
                                            Ajouter des pièces jointes
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('bulletin-boards.posts.index', $bulletinBoard['id']) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Retour aux publications
                        </a>

                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-share-alt me-1"></i> Partager
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" id="copy-share-link"><i class="fas fa-link me-2"></i> Copier le lien</a></li>
                                <li><a class="dropdown-item" href="mailto:?subject={{ urlencode($post->name) }}&body={{ urlencode(route('bulletin-boards.posts.show', [$bulletinBoard['id'], $post->id])) }}"><i class="fas fa-envelope me-2"></i> Email</a></li>
                            </ul>
                        </div>

                        <a href="{{ route('bulletin-boards.posts.edit', [$bulletinBoard['id'], $post->id]) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i> Modifier
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de changement de statut -->
<div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeStatusModalLabel">Changer le statut de la publication</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('bulletin-boards.posts.toggleStatus', [$bulletinBoard['id'], $post->id]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Nouveau statut</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="status" id="modal-status-draft" value="draft" {{ $post->status == 'draft' ? 'checked' : '' }}>
                            <label class="btn btn-outline-warning" for="modal-status-draft">
                                <i class="fas fa-pencil-alt me-1"></i> Brouillon
                            </label>

                            <input type="radio" class="btn-check" name="status" id="modal-status-published" value="published" {{ $post->status == 'published' ? 'checked' : '' }}>
                            <label class="btn btn-outline-success" for="modal-status-published">
                                <i class="fas fa-check-circle me-1"></i> Publié
                            </label>

                            <input type="radio" class="btn-check" name="status" id="modal-status-cancelled" value="cancelled" {{ $post->status == 'cancelled' ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="modal-status-cancelled">
                                <i class="fas fa-ban me-1"></i> Annulé
                            </label>
                        </div>
                    </div>

                    <div class="form-text text-muted mb-3">
                        <i class="fas fa-info-circle me-1"></i> Changer le statut affectera la visibilité de la publication pour les autres utilisateurs.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Changer le statut</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal d'ajout de pièces jointes -->
<div class="modal fade" id="addAttachmentModal" tabindex="-1" aria-labelledby="addAttachmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAttachmentModalLabel">Ajouter des pièces jointes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-body">
                    <div id="upload-feedback" class="mb-3"></div>

                    <div class="mb-3">
                        <label for="attachment-name" class="form-label">Nom du fichier</label>
                        <input type="text" class="form-control" id="attachment-name" placeholder="Nom du fichier (optionnel)">
                        <div class="form-text">Si laissé vide, le nom original du fichier sera utilisé.</div>
                    </div>

                    <div id="dropzone" class="card p-4 mb-3">
                        <div class="text-center dropzone-area">
                            <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-muted"></i>
                            <p>Glissez et déposez vos fichiers ici, ou <span class="text-primary" id="browse-files">parcourir</span></p>
                            <p class="text-muted small">Formats supportés: PDF, images, documents, vidéos - Max 20MB</p>
                        </div>
                        <div id="preview-container" class="row mt-3 g-2"></div>
                        <input type="file" id="file-input" class="d-none" multiple>
                    </div>

                    <div id="upload-progress" class="progress mb-3 d-none">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <div id="upload-queue" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" id="upload-button" disabled>Téléverser</button>
                </div>
            </div>
        </div>
    </div>
    @endsection

    @section('styles')
    <style>
        #dropzone {
            border: 2px dashed #ccc;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        #dropzone:hover, #dropzone.dragover {
            border-color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.05);
            cursor: pointer;
        }
        #browse-files {
            text-decoration: underline;
            cursor: pointer;
        }
        .preview-item {
            position: relative;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
        }
        .preview-remove {
            position: absolute;
            top: 5px;
            right: 5px;
            cursor: pointer;
            color: #dc3545;
        }
        .file-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .btn-link {
            text-decoration: none;
        }
        .btn-link:hover {
            text-decoration: underline;
        }

        .post-content {
            white-space: pre-line;
        }

        .attachment-row {
            transition: background-color 0.3s;
        }
        .attachment-row-highlight {
            background-color: #f8f9fa;
            animation: highlight-row 1.5s ease-in-out;
        }
        @keyframes highlight-row {
            0% { background-color: #cff4fc; }
            100% { background-color: transparent; }
        }

        @media (max-width: 767.98px) {
            .btn-group-sm .btn {
                padding: 0.25rem 0.5rem;
            }
        }
    </style>
    @endsection

    @section('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const postId = {{ $post->id }};
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('file-input');
        const browseFilesBtn = document.getElementById('browse-files');
        const uploadButton = document.getElementById('upload-button');
        const previewContainer = document.getElementById('preview-container');
        const progressBar = document.querySelector('#upload-progress .progress-bar');
        const uploadProgress = document.getElementById('upload-progress');
        const uploadFeedback = document.getElementById('upload-feedback');
        const attachmentName = document.getElementById('attachment-name');

        // Copier le lien
        document.getElementById('copy-link').addEventListener('click', function(e) {
            e.preventDefault();
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(function() {
                alert('Lien copié dans le presse-papiers!');
            }, function() {
                alert('Impossible de copier le lien.');
            });
        });

        document.getElementById('copy-share-link').addEventListener('click', function(e) {
            e.preventDefault();
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(function() {
                alert('Lien copié dans le presse-papiers!');
            }, function() {
                alert('Impossible de copier le lien.');
            });
        });

        // Événements du dropzone
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            dropzone.classList.add('dragover');
        }

        function unhighlight() {
            dropzone.classList.remove('dragover');
        }

        // Gestion du clic sur "parcourir"
        browseFilesBtn.addEventListener('click', function() {
            fileInput.click();
        });

        // Clic sur la zone entière
        dropzone.addEventListener('click', function(e) {
            if (e.target !== browseFilesBtn && !browseFilesBtn.contains(e.target)) {
                fileInput.click();
            }
        });

        // Gestion du dépôt de fichiers
        dropzone.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        });

        // Gestion de la sélection de fichiers via input
        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });

        // Traitement des fichiers sélectionnés
        let filesToUpload = [];
        function handleFiles(files) {
            if (files.length === 0) return;

            const newFiles = Array.from(files).filter(file => {
                // Vérifier si le fichier n'est pas déjà dans la liste
                return !filesToUpload.some(f => f.name === file.name && f.size === file.size);
            });

            filesToUpload = [...filesToUpload, ...newFiles];

            if (filesToUpload.length > 0) {
                uploadButton.disabled = false;
            }

            for (const file of newFiles) {
                // Créer la prévisualisation
                addFilePreview(file);
            }
        }

        // Ajouter une prévisualisation de fichier
        function addFilePreview(file) {
            const reader = new FileReader();
            const fileSize = formatFileSize(file.size);
            const fileType = getFileType(file.name);
            const fileIcon = getFileIcon(fileType);

            const previewItem = document.createElement('div');
            previewItem.className = 'col-md-4 col-sm-6';
            previewItem.dataset.filename = file.name;

            const previewContent = `
                <div class="preview-item text-center">
                    <div class="preview-remove"><i class="fas fa-times-circle"></i></div>
                    <div class="file-icon"><i class="${fileIcon}"></i></div>
                    <div class="file-name text-truncate">${file.name}</div>
                    <div class="file-size text-muted small">${fileSize}</div>
                </div>
            `;

            previewItem.innerHTML = previewContent;
            previewContainer.appendChild(previewItem);

            // Ajouter événement pour supprimer le fichier de la liste
            const removeBtn = previewItem.querySelector('.preview-remove');
            removeBtn.addEventListener('click', function() {
                filesToUpload = filesToUpload.filter(f => f.name !== file.name);
                previewItem.remove();

                if (filesToUpload.length === 0) {
                    uploadButton.disabled = true;
                }
            });
        }

        // Déterminer le type de fichier
        function getFileType(filename) {
            const extension = filename.split('.').pop().toLowerCase();
            const imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
            const documentTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
            const videoTypes = ['mp4', 'avi', 'mov', 'wmv', 'mkv'];

            if (imageTypes.includes(extension)) return 'image';
            if (documentTypes.includes(extension)) return 'document';
            if (videoTypes.includes(extension)) return 'video';
            return 'file';
        }

        // Obtenir l'icône correspondant au type de fichier
        function getFileIcon(fileType) {
            switch(fileType) {
                case 'image': return 'fas fa-file-image text-info';
                case 'document': return 'fas fa-file-pdf text-danger';
                case 'video': return 'fas fa-file-video text-primary';
                default: return 'fas fa-file text-secondary';
            }
        }

        // Formater la taille du fichier
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Téléversement des fichiers
        uploadButton.addEventListener('click', function() {
            if (filesToUpload.length === 0) return;

            uploadFeedback.innerHTML = '';
            uploadProgress.classList.remove('d-none');
            uploadButton.disabled = true;

            const formData = new FormData();

            // Ajouter les fichiers au FormData
            filesToUpload.forEach((file, index) => {
                formData.append(`files[${index}]`, file);
            });

            // Ajouter le nom du fichier s'il est spécifié
            if (attachmentName.value.trim()) {
                formData.append('name', attachmentName.value.trim());
            }

            // Téléverser avec Ajax
            fetch(`/posts/${postId}/attachments/ajax-store`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur de téléversement');
                }
                return response.json();
            })
            .then(data => {
                // Succès
                uploadProgress.classList.add('d-none');

                uploadFeedback.innerHTML = `
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        ${filesToUpload.length > 1 ? 'Fichiers téléversés avec succès!' : 'Fichier téléversé avec succès!'}
                    </div>
                `;

                // Vider la liste des fichiers et la prévisualisation
                filesToUpload = [];
                previewContainer.innerHTML = '';

                // Mettre à jour la liste des pièces jointes
                refreshAttachmentsList();

                // Réinitialiser les champs
                attachmentName.value = '';
            })
            .catch(error => {
                // Erreur
                uploadProgress.classList.add('d-none');

                uploadFeedback.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Erreur lors du téléversement: ${error.message}
                    </div>
                `;
                uploadButton.disabled = false;
            });
        });

        // Mettre à jour la liste des pièces jointes
        function refreshAttachmentsList() {
            fetch(`/posts/${postId}/attachments/list`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('attachments-list').innerHTML = html;

                // Réattacher les événements de suppression
                attachDeleteEvents();

                // Fermer le modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addAttachmentModal'));
                if (modal) {
                    modal.hide();
                }
            })
            .catch(error => {
                console.error('Erreur lors de la mise à jour de la liste des pièces jointes:', error);
            });
        }

        // Attacher les événements de suppression aux boutons
        function attachDeleteEvents() {
            document.querySelectorAll('.delete-attachment').forEach(button => {
                button.addEventListener('click', function() {
                    const attachmentId = this.dataset.attachmentId;
                    if (confirm('Êtes-vous sûr de vouloir supprimer cette pièce jointe ?')) {
                        deleteAttachment(attachmentId);
                    }
                });
            });
        }

        // Initialiser les événements de suppression
        attachDeleteEvents();

        // Supprimer une pièce jointe
        function deleteAttachment(attachmentId) {
            fetch(`/posts/${postId}/attachments/${attachmentId}/ajax-destroy`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur lors de la suppression');
                }
                return response.json();
            })
            .then(data => {
                // Supprimer la ligne du tableau
                const row = document.getElementById(`attachment-${attachmentId}`);
                if (row) {
                    row.remove();
                }

                // Afficher un message de succès temporaire
                const successMessage = document.createElement('div');
                successMessage.className = 'alert alert-success alert-dismissible fade show';
                successMessage.innerHTML = `
                    <i class="fas fa-check-circle me-2"></i> Pièce jointe supprimée avec succès.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;

                document.querySelector('.card-body').insertBefore(successMessage, document.getElementById('attachments-list'));

                // Si plus de pièces jointes, afficher le message approprié
                if (document.querySelectorAll('#attachments-table-body tr').length === 0) {
                    document.getElementById('attachments-list').innerHTML = `
                        <div class="alert alert-info" id="no-attachments-message">
                            <i class="fas fa-info-circle me-2"></i> Aucune pièce jointe n'est associée à cette publication.
                            <button type="button" class="btn btn-link p-0 alert-link" data-bs-toggle="modal" data-bs-target="#addAttachmentModal">
                                Ajouter des pièces jointes
                            </button>
                        </div>
                    `;
                }

                // Effacer le message après 3 secondes
                setTimeout(() => {
                    successMessage.remove();
                }, 3000);
            })
            .catch(error => {
                console.error('Erreur lors de la suppression:', error);
                alert('Erreur lors de la suppression de la pièce jointe.');
            });
        }
    });
    </script>
    @endsection
