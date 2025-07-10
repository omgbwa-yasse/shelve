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

        <form action="{{ route('mail-incoming.store') }}" method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="row">
                <h5 class="card-title mb-4">Informations générales</h5>
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
                <div class="col-md-4 mb-3">
                    <label for="document_type" class="form-label">Type de document</label>
                    <select name="document_type" id="document_type" class="form-select" required>
                        <option value="">Choisir le type de document</option>
                        <option value="original">Original</option>
                        <option value="duplicate">Duplicata</option>
                        <option value="copy">Copie</option>
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

            <!-- Section Expéditeur -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Expéditeur</h5>

                    <div class="mb-3">
                        <label for="sender_type" class="form-label">Type d'expéditeur</label>
                        <select name="sender_type" id="sender_type" class="form-select" required>
                            <option value="">Choisir le type d'expéditeur</option>
                            <option value="external_contact">Contact externe</option>
                            <option value="external_organization">Organisation externe</option>
                            <option value="organisation">Organisation interne</option>
                        </select>
                    </div>

                    <div id="external_contact_section" class="mb-3" style="display: none;">
                        <label for="external_sender_id" class="form-label">Contact externe</label>
                        <select name="external_sender_id" id="external_sender_id" class="form-select">
                            <option value="">Choisir un contact externe</option>
                            @foreach($externalContacts as $contact)
                                <option value="{{ $contact->id }}">
                                    {{ $contact->full_name }}
                                    @if($contact->organization)
                                        ({{ $contact->organization->name }})
                                    @endif
                                    @if($contact->email)
                                        - {{ $contact->email }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="external_organization_section" class="mb-3" style="display: none;">
                        <label for="external_sender_organization_id" class="form-label">Organisation externe</label>
                        <select name="external_sender_organization_id" id="external_sender_organization_id" class="form-select">
                            <option value="">Choisir une organisation externe</option>
                            @foreach($externalOrganizations as $organization)
                                <option value="{{ $organization->id }}">
                                    {{ $organization->name }}
                                    @if($organization->city)
                                        - {{ $organization->city }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="organisation_section" class="mb-3" style="display: none;">
                        <label for="sender_organisation_id" class="form-label">Organisation interne</label>
                        <select name="sender_organisation_id" id="sender_organisation_id" class="form-select">
                            <option value="">Choisir une organisation</option>
                            @foreach($senderOrganisations as $organisation)
                                <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section Réception -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Informations de réception</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="delivery_method" class="form-label">Méthode de réception</label>
                            <select name="delivery_method" id="delivery_method" class="form-select">
                                <option value="">Choisir une méthode</option>
                                <option value="courrier">Courrier postal</option>
                                <option value="email">Email</option>
                                <option value="fax">Fax</option>
                                <option value="en_main_propre">En main propre</option>
                                <option value="porteur">Porteur</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="tracking_number" class="form-label">Numéro de suivi</label>
                            <input type="text" name="tracking_number" id="tracking_number" class="form-control" placeholder="Numéro de suivi (optionnel)">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="received_at" class="form-label">Date de réception confirmée</label>
                        <input type="datetime-local" name="received_at" id="received_at" class="form-control">
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

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-inbox"></i> Créer le courrier entrant
                </button>
                <a href="{{ route('mail-incoming.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
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

            // Gestion du changement de type d'expéditeur
            const senderTypeSelect = document.getElementById('sender_type');
            const externalContactSection = document.getElementById('external_contact_section');
            const externalOrganizationSection = document.getElementById('external_organization_section');
            const organisationSection = document.getElementById('organisation_section');

            senderTypeSelect.addEventListener('change', function() {
                // Masquer toutes les sections
                externalContactSection.style.display = 'none';
                externalOrganizationSection.style.display = 'none';
                organisationSection.style.display = 'none';

                // Réinitialiser les valeurs
                document.getElementById('external_sender_id').value = '';
                document.getElementById('external_sender_organization_id').value = '';
                document.getElementById('sender_organisation_id').value = '';

                // Afficher la section appropriée
                switch(this.value) {
                    case 'external_contact':
                        externalContactSection.style.display = 'block';
                        break;
                    case 'external_organization':
                        externalOrganizationSection.style.display = 'block';
                        break;
                    case 'organisation':
                        organisationSection.style.display = 'block';
                        break;
                }
            });

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
        });
    </script>
@endpush
