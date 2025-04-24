@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h1 class="h3 mb-4">Créer un courrier entrant</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('mail-received.store') }}" method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
            @csrf

            <div class="card mb-4">
                <div class="card-body">

                    <h5 class="card-title mb-4">Informations générales</h5>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="reference" class="form-label">Référence courrier</label>
                            <input type="text" id="reference" name="reference" class="form-control" value="{{ old('reference') }}" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="date" class="form-label">Date du courrier</label>
                            <input type="date" id="date" name="date" class="form-control" value="{{ old('date') }}" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="typology_id" class="form-label">Typologie</label>
                            <select name="typology_id" id="typology_id" class="form-select" required>
                                <option value="">Sélectionner une typologie</option>
                                @foreach($typologies as $typology)
                                    <option value="{{ $typology->id }}" {{ old('typology_id') == $typology->id ? 'selected' : '' }}>
                                        {{ $typology->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                    </div>

                    <div class="row">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom du courrier</label>
                            <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Expéditeur et classification</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="sender_organisation_id" class="form-label">Organisation d'envoi</label>
                            <select name="sender_organisation_id" id="sender_organisation_id" class="form-select" required>
                                <option value="">Sélectionner une organisation</option>
                                <!--  Ici se charge automatique les organisations  -->
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="sender_user_id" class="form-label">Utilisateur expéditeur</label>
                            <select name="sender_user_id" id="sender_user_id" class="form-select" required>
                                <option value="">Sélectionner un utilisateur</option>
                                <!--  Ici se charge automatique les utilisateurs  -->
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="action_id" class="form-label">Action requise</label>
                            <select name="action_id" id="action_id" class="form-select" required>
                                <option value="">Sélectionner une action</option>
                                @foreach($mailActions as $action)
                                    <option value="{{ $action->id }}" {{ old('action_id') == $action->id ? 'selected' : '' }}>
                                        {{ $action->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="priority_id" class="form-label">Priorité</label>
                            <select name="priority_id" id="priority_id" class="form-select" required>
                                <option value="">Sélectionner une priorité</option>
                                @foreach($priorities as $priority)
                                    <option value="{{ $priority->id }}" {{ old('priority_id') == $priority->id ? 'selected' : '' }}>
                                        {{ $priority->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="document_type" class="form-label">Type de document</label>
                            <select name="document_type" id="document_type" class="form-select" required>
                                <option value="">Choisir le type de document</option>
                                <option value="original" {{ old('document_type') == 'original' ? 'selected' : '' }}>Original</option>
                                <option value="duplicate" {{ old('document_type') == 'duplicate' ? 'selected' : '' }}>Duplicata</option>
                                <option value="copy" {{ old('document_type') == 'copy' ? 'selected' : '' }}>Copie</option>
                            </select>
                        </div>


                    </div>

                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Pièces jointes</h5>

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
                            <button type="button" class="btn btn-outline-primary btn-browse">
                                Parcourir
                            </button>
                        </div>
                        <div id="fileList" class="mt-3"></div>
                    </div>
                    <small class="text-muted mt-2">
                        Formats acceptés: PDF, Word, Excel, Images (JPG, PNG) - Max 10MB par fichier, 5 fichiers maximum
                    </small>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-2"></i>Créer le courrier
                </button>
                <a href="{{ route('mail-received.index') }}" class="btn btn-light">
                    <i class="bi bi-x-lg me-2"></i>Annuler
                </a>
            </div>
        </form>
    </div>

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
                padding: 8px;
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
                const maxFileSize = 10 * 1024 * 1024; // 10MB
                const maxFiles = 5;

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
                            alert(`Le fichier "${file.name}" est trop volumineux. Taille maximum: 10MB`);
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
                    return 'bi-file-earmark';
                }

                function formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                }

                // Validation du formulaire
                const form = document.querySelector('form');
                form.addEventListener('submit', function(e) {
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                    }

                    let totalSize = 0;
                    Array.from(selectedFiles.files).forEach(file => {
                        totalSize += file.size;
                    });

                    if (totalSize > maxFileSize * maxFiles) {
                        e.preventDefault();
                        alert('La taille totale des fichiers dépasse la limite autorisée.');
                        return;
                    }

                    form.classList.add('was-validated');
                });





            /*

                Chargement des organisations

            */
            const sendOrganisationSelect = document.getElementById('sender_organisation_id');
            console.log(sendOrganisationSelect);
            fetch(`/mails/organisations/list`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur lors de la récupération des organisations');
                        }
                        return response.json();
                    })
                    .then(organisations => {
                        sendOrganisationSelect.innerHTML = '<option value="">Sélectionner une organisation </option>';

                        organisations.forEach(organisation => {
                            const option = document.createElement('option');
                            option.value = organisation.id;
                            option.textContent = organisation.name;
                            sendOrganisationSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        sendOrganisationSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                    });







            /*

                Chargement des utilisateurs

            */

            const sendUserSelect = document.getElementById('sender_user_id');

            sendUserSelect.disabled = true;

            sendOrganisationSelect.addEventListener('change', function() {
                const organisationId = this.value;

                if (!organisationId) {
                    sendUserSelect.disabled = true;
                    sendUserSelect.innerHTML = '<option value="">Sélectionner un utilisateur</option>';
                    return;
                }

                sendUserSelect.disabled = false;

                sendUserSelect.innerHTML = '<option value="">Chargement en cours...</option>';

                fetch(`/mails/organisations/${organisationId}/users`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur lors de la récupération des utilisateurs');
                        }
                        return response.json();
                    })
                    .then(users => {
                        sendUserSelect.innerHTML = '<option value="">Sélectionner un utilisateur</option>';
                        users.forEach(user => {
                            const option = document.createElement('option');
                            option.value = user.id;
                            option.textContent = user.name;
                            sendUserSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        sendUserSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                    });
                });
            });

        </script>
    @endpush
@endsection
