@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Créer Courrier entrant</h1>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('mail-received.store') }}" method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="row">

                <h5 class="card-title mb-4">Informations générales</h5>

                <div class="col-md-4 mb-3">
                    <label for="reference" class="form-label">Référence</label>
                    <input type="text" id="reference" name="reference" class="form-control" required>
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

            <div class="row">
                <!-- Type d'expéditeur -->
                <div class="col-12 mb-4">
                    <h5 class="mt-3 mb-3">Type d'expéditeur</h5>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="sender_type" id="sender_type_internal" value="internal" checked>
                        <label class="form-check-label" for="sender_type_internal">Interne</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="sender_type" id="sender_type_external_contact" value="external_contact">
                        <label class="form-check-label" for="sender_type_external_contact">Contact externe</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="sender_type" id="sender_type_external_organization" value="external_organization">
                        <label class="form-check-label" for="sender_type_external_organization">Organisation externe</label>
                    </div>
                </div>
            </div>

            <div class="row" id="internal_sender">
                <div class="col-md-6 mb-3">
                    <label for="sender_organisation_id" class="form-label">Organisation d'envoi</label>
                    <select name="sender_organisation_id" id="sender_organisation_id" class="form-select sender-field internal-field" required>
                        <option value="">Choisir une organisation</option>
                        @foreach($senderOrganisations as $organisation)
                            <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="sender_user_id" class="form-label">Utilisateur expéditeur</label>
                    <select name="sender_user_id" id="sender_user_id" class="form-select sender-field internal-field" required>
                        <option value="">Choisir un utilisateur</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row d-none" id="external_contact_sender">
                <div class="col-md-12 mb-3">
                    <label for="external_sender_id" class="form-label">Contact externe expéditeur</label>
                    <select name="external_sender_id" id="external_sender_id" class="form-select sender-field external-contact-field">
                        <option value="">Choisir un contact externe</option>
                        @foreach($externalContacts as $contact)
                            <option value="{{ $contact->id }}" data-organization="{{ $contact->external_organization_id }}">
                                {{ $contact->full_name }} {{ $contact->organization ? '(' . $contact->organization->name . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row d-none" id="external_organization_sender">
                <div class="col-md-12 mb-3">
                    <label for="external_sender_organization_id" class="form-label">Organisation externe expéditrice</label>
                    <select name="external_sender_organization_id" id="external_sender_organization_id" class="form-select sender-field external-org-field">
                        <option value="">Choisir une organisation externe</option>
                        @foreach($externalOrganizations as $org)
                            <option value="{{ $org->id }}">{{ $org->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-12">
                <label for="fileInput" class="form-label">Pièces jointes</label>
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
                    <i class="bi bi-inbox"></i> Créer le courrier entrant
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

            // Gestion des types d'expéditeurs
            const senderTypeRadios = document.querySelectorAll('input[name="sender_type"]');
            const internalSenderDiv = document.getElementById('internal_sender');
            const externalContactSenderDiv = document.getElementById('external_contact_sender');
            const externalOrganizationSenderDiv = document.getElementById('external_organization_sender');

            // Tous les champs d'expéditeurs
            const senderFields = document.querySelectorAll('.sender-field');

            function handleSenderTypeChange() {
                const selectedType = document.querySelector('input[name="sender_type"]:checked').value;

                // Cacher toutes les sections
                internalSenderDiv.classList.add('d-none');
                externalContactSenderDiv.classList.add('d-none');
                externalOrganizationSenderDiv.classList.add('d-none');

                // Réinitialiser tous les champs
                senderFields.forEach(field => {
                    field.removeAttribute('required');
                    field.value = '';
                });

                // Afficher la section appropriée et rendre les champs requis
                if (selectedType === 'internal') {
                    internalSenderDiv.classList.remove('d-none');
                    document.querySelectorAll('.internal-field').forEach(field => {
                        field.setAttribute('required', 'required');
                    });
                } else if (selectedType === 'external_contact') {
                    externalContactSenderDiv.classList.remove('d-none');
                    document.querySelectorAll('.external-contact-field').forEach(field => {
                        field.setAttribute('required', 'required');
                    });
                } else if (selectedType === 'external_organization') {
                    externalOrganizationSenderDiv.classList.remove('d-none');
                    document.querySelectorAll('.external-org-field').forEach(field => {
                        field.setAttribute('required', 'required');
                    });
                }
            }

            // Écouteurs d'événements pour les boutons radio
            senderTypeRadios.forEach(radio => {
                radio.addEventListener('change', handleSenderTypeChange);
            });

            // Initialiser l'état par défaut
            handleSenderTypeChange();

            // Gestion du drag & drop
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
                form.classList.add('was-validated');
            });
        });
    </script>
@endpush
