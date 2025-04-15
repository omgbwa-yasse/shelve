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
                            <button type="button" class="btn btn-primary btn-sm" id="addAttachmentButton">
                                <i class="fas fa-plus me-1"></i> Ajouter des pièces jointes
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="upload-container" class="mb-3 d-none">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                        <h6 class="mb-0">Téléversement de fichiers</h6>
                                        <button type="button" class="btn-close" id="close-uploader"></button>
                                    </div>
                                    <div class="card-body">
                                        <form id="upload-form" enctype="multipart/form-data">
                                            @csrf
                                            <input type="file" id="fileInput" name="files[]" multiple class="d-none">

                                            <div id="dropZone" class="p-4 border border-2 border-dashed rounded text-center mb-3">
                                                <div class="py-4">
                                                    <i class="bi bi-cloud-arrow-up fs-2 mb-2"></i>
                                                    <p class="mb-0">Glissez-déposez vos fichiers ici</p>
                                                    <p>ou</p>
                                                    <button type="button" class="btn btn-outline-primary btn-browse">Parcourir</button>
                                                </div>
                                            </div>

                                            <div id="fileList" class="mb-3"></div>

                                            <div class="progress mb-3 d-none" id="upload-progress">
                                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                            </div>

                                            <div class="d-flex justify-content-end">
                                                <button type="submit" class="btn btn-primary" id="upload-btn">Téléverser</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div id="attachments-list">
                                @if($post->attachments->isNotEmpty())
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
                        <a href="{{ route('bulletin-boards.posts.edit', [$bulletinBoard['id'], $post->id]) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i> Modifier
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
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

#dropZone {
    border-color: #dee2e6 !important;
    transition: all 0.3s;
}

#dropZone.dragover {
    background-color: #f8f9fa;
    border-color: #0d6efd !important;
}

.file-item {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    margin-bottom: 0.5rem;
    background-color: #f8f9fa;
    border-radius: 0.25rem;
    justify-content: space-between;
}

.border-dashed {
    border-style: dashed !important;
}
</style>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const postId = {{ $post->id }};
        const addAttachmentButton = document.getElementById('addAttachmentButton');
        const uploadContainer = document.getElementById('upload-container');
        const closeUploader = document.getElementById('close-uploader');
        const uploadForm = document.getElementById('upload-form');
        const progressBar = document.querySelector('#upload-progress .progress-bar');
        const progressContainer = document.getElementById('upload-progress');
        const attachmentsTableBody = document.getElementById('attachments-table-body');
        const noAttachmentsMessage = document.getElementById('no-attachments-message');
        const uploadBtn = document.getElementById('upload-btn');

        // Afficher le formulaire d'upload
        addAttachmentButton.addEventListener('click', function() {
            uploadContainer.classList.remove('d-none');
        });

        // Masquer le formulaire d'upload
        closeUploader.addEventListener('click', function() {
            uploadContainer.classList.add('d-none');
            document.getElementById('fileInput').value = '';
            progressContainer.classList.add('d-none');
            progressBar.style.width = '0%';
            document.getElementById('fileList').innerHTML = '';
        });

        // Drag & Drop Zone
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const btnBrowse = document.querySelector('.btn-browse');
        const maxFileSize = 20 * 1024 * 1024; // 20MB
        const maxFiles = 10;

        // Stockage des fichiers sélectionnés
        let selectedFiles = new DataTransfer();

        // Event listeners pour drag & drop
        dropZone.addEventListener('drop', handleDrop);
        dropZone.addEventListener('dragover', handleDragOver);
        dropZone.addEventListener('dragleave', handleDragLeave);
        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });
        btnBrowse.addEventListener('click', () => fileInput.click());

        function handleDrop(e) {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        }

        function handleDragOver(e) {
            e.preventDefault();
            dropZone.classList.add('dragover');
        }

        function handleDragLeave(e) {
            e.preventDefault();
            dropZone.classList.remove('dragover');
        }

        function handleFiles(files) {
            if (selectedFiles.files.length + files.length > maxFiles) {
                alert(`Vous ne pouvez pas télécharger plus de ${maxFiles} fichiers.`);
                return;
            }

            Array.from(files).forEach(file => {
                if (file.size > maxFileSize) {
                    alert(`Le fichier "${file.name}" est trop volumineux. Taille maximum: 20MB`);
                    return;
                }

                selectedFiles.items.add(file);
                fileInput.files = selectedFiles.files;

                const fileItem = createFileItem(file);
                document.getElementById('fileList').appendChild(fileItem);
            });
        }

        function createFileItem(file) {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';

            const icon = getFileIcon(file.type);
            const size = formatFileSize(file.size);

            fileItem.innerHTML = `
                <i class="bi ${icon} me-2"></i>
                <div>
                    <div class="fw-medium">${file.name}</div>
                    <small class="text-muted">${size}</small>
                </div>
                <button type="button" class="btn btn-link text-danger delete-btn p-0" data-filename="${file.name}">
                    <i class="bi bi-x-lg"></i>
                </button>
            `;

            fileItem.querySelector('.delete-btn').addEventListener('click', function() {
                const fileName = this.dataset.filename;
                const dt = new DataTransfer();

                for (let i = 0; i < selectedFiles.files.length; i++) {
                    const f = selectedFiles.files[i];
                    if (f.name !== fileName) {
                        dt.items.add(f);
                    }
                }

                selectedFiles = dt;
                fileInput.files = selectedFiles.files;
                fileItem.remove();
            });

            return fileItem;
        }

        function getFileIcon(fileType) {
            if (fileType.startsWith('image/')) return 'bi-file-earmark-image';
            if (fileType.includes('pdf')) return 'bi-file-earmark-pdf';
            if (fileType.includes('word')) return 'bi-file-earmark-word';
            if (fileType.includes('sheet')) return 'bi-file-earmark-excel';
            if (fileType.includes('video')) return 'bi-file-earmark-play';
            return 'bi-file-earmark';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Soumission du formulaire d'upload
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();

            if (selectedFiles.files.length === 0) {
                alert('Veuillez sélectionner au moins un fichier');
                return;
            }

            let totalSize = 0;
            Array.from(selectedFiles.files).forEach(file => {
                totalSize += file.size;
            });

            if (totalSize > maxFileSize * maxFiles) {
                alert('La taille totale des fichiers dépasse la limite autorisée.');
                return;
            }

            const formData = new FormData();
            for (let i = 0; i < selectedFiles.files.length; i++) {
                formData.append('files[]', selectedFiles.files[i]);
            }
            formData.append('_token', document.querySelector('input[name="_token"]').value);

            // Afficher la barre de progression
            progressContainer.classList.remove('d-none');
            uploadBtn.disabled = true;

            // Envoyer les fichiers via AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('POST', `/posts/${postId}/attachments/ajax-store`, true);

            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    progressBar.style.width = percentComplete + '%';
                    progressBar.setAttribute('aria-valuenow', percentComplete);
                }
            });

            xhr.onload = function() {
                uploadBtn.disabled = false;

                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Recharger la liste des pièces jointes
                            refreshAttachmentsList();

                            // Réinitialiser le formulaire
                            uploadForm.reset();
                            progressContainer.classList.add('d-none');
                            uploadContainer.classList.add('d-none');
                            document.getElementById('fileList').innerHTML = '';
                            selectedFiles = new DataTransfer();
                        } else {
                            alert('Erreur: ' + response.message);
                        }
                    } catch (e) {
                        alert('Une erreur est survenue lors de l'analyse de la réponse');
                    }
                } else {
                    alert('Une erreur est survenue lors du téléversement');
                }
            };

            xhr.send(formData);
        });

        // Rafraîchir la liste des pièces jointes
        function refreshAttachmentsList() {
            fetch(`/posts/${postId}/attachments/list`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau');
                    }
                    return response.text();
                })
                .then(html => {
                    // Mise à jour de l'interface selon la réponse
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    if (doc.querySelector('.table-responsive')) {
                        // Des pièces jointes existent
                        if (noAttachmentsMessage) {
                            document.getElementById('attachments-list').innerHTML = html;
                        } else {
                            const existingTable = document.querySelector('#attachments-list .table-responsive');
                            if (existingTable) {
                                existingTable.outerHTML = doc.querySelector('.table-responsive').outerHTML;
                            } else {
                                document.getElementById('attachments-list').innerHTML = html;
                            }
                        }
                    } else {
                        // Aucune pièce jointe
                        document.getElementById('attachments-list').innerHTML = html;
                    }

                    // Réattacher les écouteurs d'événements pour les boutons de suppression
                    attachDeleteListeners();
                })
                .catch(error => {
                    console.error('Erreur lors du rafraîchissement des pièces jointes:', error);
                    alert('Impossible de rafraîchir la liste des pièces jointes');
                });
        }

        // Attacher les écouteurs d'événements pour les boutons de suppression
        function attachDeleteListeners() {
            document.querySelectorAll('.delete-attachment').forEach(button => {
                button.addEventListener('click', function() {
                    const attachmentId = this.dataset.attachmentId;
                    if (confirm('Êtes-vous sûr de vouloir supprimer cette pièce jointe ?')) {
                        deleteAttachment(attachmentId);
                    }
                });
            });
        }

        // Fonction pour supprimer une pièce jointe
        function deleteAttachment(attachmentId) {
            fetch(`/posts/${postId}/attachments/${attachmentId}/ajax-destroy`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Supprimer la ligne du tableau ou rafraîchir la liste
                    const row = document.getElementById(`attachment-${attachmentId}`);
                    if (row) {
                        row.remove();

                        // Si plus aucune pièce jointe, afficher le message
                        if (!document.querySelectorAll('#attachments-table-body tr').length) {
                            refreshAttachmentsList();
                        }
                    } else {
                        refreshAttachmentsList();
                    }
                } else {
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur lors de la suppression:', error);
                alert('Une erreur est survenue lors de la suppression de la pièce jointe');
            });
        }

        // Initialiser les écouteurs d'événements pour les boutons de suppression
        attachDeleteListeners();
    });
</script>
@endpush
