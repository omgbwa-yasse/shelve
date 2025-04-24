@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Créer Courrier sortant</h1>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('mail-send.store') }}" method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="row">

                <h5 class="card-title mb-4">Informations générales</h5>

                <div class="col-md-4 mb-3">
                    <label for="code" class="form-label">Code</label>
                    <input type="text" id="code" name="code" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="date" class="form-label">Date du courrier</label>
                    <input type="date" id="date" name="date" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="typology_id" class="form-label">Typologie</label>
                    <select name="typology_id" id="typology_id" class="form-select" required>
                        <option value="">Choisir une typologie</option>
                        @foreach($typologies as $typology)
                            <option value="{{ $typology->id }}">{{ $typology->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Nom du courrier</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="document_type" class="form-label">Type de document</label>
                    <select name="document_type" id="document_type" class="form-select" required>
                        <option value="">Choisir le type de document</option>
                        <option value="original">Original</option>
                        <option value="duplicate">Duplicata</option>
                        <option value="copy">Copie</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="action_id" class="form-label">Action</label>
                    <select name="action_id" id="action_id" class="form-select" required>
                        <option value="">Choisir une action</option>
                        @foreach($mailActions as $action)
                            <option value="{{ $action->id }}">{{ $action->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="priority_id" class="form-label">Priorité</label>
                    <select name="priority_id" id="priority_id" class="form-select" required>
                        <option value="">Choisir une priorité</option>
                        @foreach($priorities as $priority)
                            <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                        @endforeach
                    </select>
                </div>



            </div>


            <div class="mb-12">
                <label class="form-label">Pièces jointes</label>
                <div class="drop-zone border rounded p-3" id="dropZone">
                    <div class="text-center">
                        <i class="bi bi-cloud-upload fs-3"></i>
                        <p class="mb-2">Glissez-déposez vos fichiers ici ou</p>
                        <input type="file"
                               class="d-none"
                               id="fileInput"
                               name="attachments[]"
                               multiple
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <button type="button"
                                class="btn btn-outline-primary btn-browse">
                            Parcourir
                        </button>
                    </div>
                    <div id="fileList" class="mt-3"></div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send"></i> Créer le courrier sortant
                </button>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <style>
        .drop-zone {
            min-height: 150px;
            border: 2px dashed #ccc !important;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .drop-zone.dragover {
            background-color: #f8f9fa;
            border-color: #0d6efd !important;
        }
        .file-item {
            display: flex;
            align-items: center;
            padding: 5px;
            margin: 5px 0;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .file-item .delete-btn {
            margin-left: auto;
        }
        .file-progress {
            width: 100%;
            height: 4px;
            margin-top: 5px;
            background-color: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
        }
        .file-progress-bar {
            height: 100%;
            background-color: #0d6efd;
            transition: width 0.3s ease;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('fileInput');
            const btnBrowse = document.querySelector('.btn-browse');
            const maxFileSize = 10 * 1024 * 1024; // 10MB en bytes
            const maxFiles = 5; // Nombre maximum de fichiers

            // Stockage des fichiers sélectionnés
            let selectedFiles = new DataTransfer();

            // Event listeners
            dropZone.addEventListener('drop', handleDrop);
            dropZone.addEventListener('dragover', handleDragOver);
            dropZone.addEventListener('dragleave', handleDragLeave);
            fileInput.addEventListener('change', function() {
                handleFiles(this.files);
            });
            btnBrowse.addEventListener('click', () => fileInput.click());

            function handleDrop(e) {
                e.preventDefault();
                const files = e.dataTransfer.files;
                dropZone.classList.remove('dragover');
                handleFiles(files);
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
                        alert(`Le fichier "${file.name}" est trop volumineux. Taille maximum: 10MB`);
                        return;
                    }

                    // Ajouter le fichier à notre DataTransfer
                    selectedFiles.items.add(file);

                    // Mettre à jour l'input file avec les fichiers sélectionnés
                    fileInput.files = selectedFiles.files;

                    // Créer un nouvel élément de fichier et l'ajouter à la liste
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
            <span>${file.name}</span>
            <span class="text-muted ms-2">(${size})</span>
            <button type="button" class="btn btn-link text-danger delete-btn p-0" data-filename="${file.name}">
                <i class="bi bi-x-lg"></i>
            </button>
        `;

                // Ajouter un écouteur d'événements pour supprimer le fichier
                fileItem.querySelector('.delete-btn').addEventListener('click', function() {
                    const fileName = this.dataset.filename;
                    // Supprimer le fichier de notre DataTransfer
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
                return 'bi-file-earmark';
            }

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            // Validation du formulaire avant soumission
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                let totalSize = 0;
                const fileList = document.getElementById('fileList');
                const fileItems = fileList.querySelectorAll('.file-item');

                if (fileItems.length === 0) {
                    const confirmed = confirm('Aucun fichier n\'a été sélectionné. Voulez-vous continuer sans pièce jointe ?');
                    if (!confirmed) {
                        e.preventDefault();
                        return;
                    }
                }

                Array.from(selectedFiles.files).forEach(file => {
                    totalSize += file.size;
                });

                if (totalSize > maxFileSize * maxFiles) {
                    e.preventDefault();
                    alert('La taille totale des fichiers dépasse la limite autorisée.');
                    return;
                }
            });








                const recipientOrganisationSelect = document.getElementById('recipient_organisation_id');
                const recipientUserSelect = document.getElementById('recipient_user_id');

                    recipientUserSelect.disabled = true;

                    recipientOrganisationSelect.addEventListener('change', function() {
                        const organisationId = this.value;

                        if (!organisationId) {
                            recipientUserSelect.disabled = true;
                            recipientUserSelect.innerHTML = '<option value="">Sélectionner un utilisateur</option>';
                            return;
                        }

                        recipientUserSelect.disabled = false;

                        recipientUserSelect.innerHTML = '<option value="">Chargement en cours...</option>';

                        fetch(`/mails/organisations/${organisationId}/users`)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Erreur lors de la récupération des utilisateurs');
                                }
                                return response.json();
                            })
                            .then(users => {
                                recipientUserSelect.innerHTML = '<option value="">Sélectionner un utilisateur</option>';
                                users.forEach(user => {
                                    const option = document.createElement('option');
                                    option.value = user.id;
                                    option.textContent = user.name;
                                    recipientUserSelect.appendChild(option);
                                });
                            })
                            .catch(error => {
                                console.error('Erreur:', error);
                                recipientUserSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                            });
                    });



        });
    </script>
@endpush
