@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Créer Courrier sortant externe</h1>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('mails.send.external.store') }}" method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
            @csrf
            <h5 class="card-title mb-4">Informations générales</h5>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="code" class="form-label"><strong>Code existant</strong>, à défaut un nouveau sera créé</label>
                    <input type="text" id="code" name="code" class="form-control" value="{{ old('code') }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="date" class="form-label">Date du courrier</label>
                    <input type="date" id="date" name="date" class="form-control" value="{{ old('date') }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="typology_id" class="form-label">Typologie</label>
                    <select name="typology_id" id="typology_id" class="form-select" required>
                        <option value="">Choisir une typologie</option>
                        @foreach($typologies as $typology)
                            <option value="{{ $typology->id }}" {{ old('typology_id') == $typology->id ? 'selected' : '' }}>
                                {{ $typology->name }}
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

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label for="name" class="form-label">Nom/Objet du courrier</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="priority_id" class="form-label">Priorité</label>
                    <select name="priority_id" id="priority_id" class="form-select">
                        <option value="">Aucune priorité</option>
                        @foreach($priorities as $priority)
                            <option value="{{ $priority->id }}" {{ old('priority_id') == $priority->id ? 'selected' : '' }}>
                                {{ $priority->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>

            <h5 class="card-title mb-4 mt-4">Destinataire externe</h5>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="recipient_type" class="form-label">Type de destinataire</label>
                    <select name="recipient_type" id="recipient_type" class="form-select" required>
                        <option value="">Choisir le type de destinataire</option>
                        <option value="external_contact" {{ old('recipient_type') == 'external_contact' ? 'selected' : '' }}>Contact externe</option>
                        <option value="external_organization" {{ old('recipient_type') == 'external_organization' ? 'selected' : '' }}>Organisation externe</option>
                    </select>
                </div>
            </div>

            <!-- Section Contact externe -->
            <div id="external-contact-section" style="display: none;">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="external_recipient_id" class="form-label">Contact externe</label>
                        <select name="external_recipient_id" id="external_recipient_id" class="form-select">
                            <option value="">Sélectionner un contact externe</option>
                            @foreach($externalContacts as $contact)
                                <option value="{{ $contact->id }}" {{ old('external_recipient_id') == $contact->id ? 'selected' : '' }}>
                                    {{ $contact->first_name }} {{ $contact->last_name }}
                                    @if($contact->organization)
                                        ({{ $contact->organization->name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section Organisation externe -->
            <div id="external-organization-section" style="display: none;">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="external_recipient_organization_id" class="form-label">Organisation externe</label>
                        <select name="external_recipient_organization_id" id="external_recipient_organization_id" class="form-select">
                            <option value="">Sélectionner une organisation externe</option>
                            @foreach($externalOrganizations as $organization)
                                <option value="{{ $organization->id }}" {{ old('external_recipient_organization_id') == $organization->id ? 'selected' : '' }}>
                                    {{ $organization->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <h5 class="card-title mb-4 mt-4">Informations complémentaires</h5>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="delivery_method" class="form-label">Méthode de livraison</label>
                    <input type="text" id="delivery_method" name="delivery_method" class="form-control" value="{{ old('delivery_method') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="tracking_number" class="form-label">Numéro de suivi</label>
                    <input type="text" id="tracking_number" name="tracking_number" class="form-control" value="{{ old('tracking_number') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="action_id" class="form-label">Action</label>
                    <select name="action_id" id="action_id" class="form-select">
                        <option value="">Aucune action</option>
                        @foreach($actions as $action)
                            <option value="{{ $action->id }}" {{ old('action_id') == $action->id ? 'selected' : '' }}>
                                {{ $action->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="deadline" class="form-label">Date limite</label>
                    <input type="date" id="deadline" name="deadline" class="form-control" value="{{ old('deadline') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="estimated_processing_time" class="form-label">Temps de traitement estimé (jours)</label>
                    <input type="number" id="estimated_processing_time" name="estimated_processing_time" class="form-control" min="1" value="{{ old('estimated_processing_time') }}">
                </div>
            </div>

            <h5 class="card-title mb-4 mt-4">Pièces jointes</h5>

            <div class="mb-3">
                <label for="attachments" class="form-label">Fichiers joints</label>
                <div id="dropZone" class="border border-dashed rounded p-4 text-center">
                    <input type="file" id="fileInput" name="attachments[]" multiple class="d-none" accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png">
                    <i class="bi bi-cloud-upload fs-1 text-muted"></i>
                    <div>
                        <p class="mb-2">Glissez-déposez vos fichiers ici ou</p>
                        <button type="button" class="btn btn-outline-primary btn-browse">
                            <i class="bi bi-folder2-open me-1"></i>
                            Parcourir
                        </button>
                    </div>
                    <small class="text-muted">Formats acceptés: PDF, DOC, DOCX, TXT, JPG, PNG (max 10MB par fichier, 5 fichiers max)</small>
                </div>
                <div id="fileList" class="mt-3"></div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('mails.send.external.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-save"></i> Créer le courrier
                </button>
            </div>
        </form>
    </div>

@push('styles')
<style>
    #dropZone {
        transition: all 0.3s ease;
        background-color: var(--bs-body-bg);
    }

    #dropZone.dragover {
        background-color: var(--bs-primary-bg-subtle);
        border-color: var(--bs-primary);
        transform: scale(1.02);
    }

    .file-item {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        margin-bottom: 8px;
        background-color: var(--bs-light);
        border: 1px solid var(--bs-border-color);
        border-radius: 6px;
        position: relative;
    }

    .file-item:hover {
        background-color: var(--bs-primary-bg-subtle);
    }

    .file-item .delete-btn {
        margin-left: auto;
        font-size: 0.8rem;
        padding: 2px 6px !important;
    }

    .file-item .delete-btn:hover {
        background-color: var(--bs-danger-bg-subtle);
        border-radius: 4px;
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

            // Event listeners pour les fichiers
            if (dropZone && fileInput) {
                dropZone.addEventListener('drop', handleDrop);
                dropZone.addEventListener('dragover', handleDragOver);
                dropZone.addEventListener('dragleave', handleDragLeave);
                fileInput.addEventListener('change', function() {
                    handleFiles(this.files);
                });
                btnBrowse.addEventListener('click', () => fileInput.click());
            }

            // Gestion des sections de destinataires
            const recipientTypeSelect = document.getElementById('recipient_type');
            const externalContactSection = document.getElementById('external-contact-section');
            const externalOrganizationSection = document.getElementById('external-organization-section');

            function toggleRecipientSections() {
                const selectedType = recipientTypeSelect.value;

                // Masquer toutes les sections
                externalContactSection.style.display = 'none';
                externalOrganizationSection.style.display = 'none';

                // Réinitialiser les valeurs
                document.getElementById('external_recipient_id').value = '';
                document.getElementById('external_recipient_organization_id').value = '';

                // Afficher la section appropriée
                if (selectedType === 'external_contact') {
                    externalContactSection.style.display = 'block';
                } else if (selectedType === 'external_organization') {
                    externalOrganizationSection.style.display = 'block';
                }
            }

            recipientTypeSelect.addEventListener('change', toggleRecipientSections);

            // Fonctions de gestion des fichiers
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

                    // Vérifier les doublons
                    let isDuplicate = false;
                    for (let i = 0; i < selectedFiles.files.length; i++) {
                        if (selectedFiles.files[i].name === file.name) {
                            isDuplicate = true;
                            break;
                        }
                    }

                    if (isDuplicate) {
                        alert(`Le fichier "${file.name}" est déjà sélectionné.`);
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

                // Validation des champs obligatoires
                const recipientType = document.getElementById('recipient_type').value;
                let recipientSelected = false;

                if (recipientType === 'external_contact') {
                    recipientSelected = document.getElementById('external_recipient_id').value !== '';
                } else if (recipientType === 'external_organization') {
                    recipientSelected = document.getElementById('external_recipient_organization_id').value !== '';
                }

                if (!recipientSelected) {
                    e.preventDefault();
                    alert('Veuillez sélectionner un destinataire.');
                    return;
                }

                // Vérification des fichiers
                Array.from(selectedFiles.files).forEach(file => {
                    totalSize += file.size;
                });

                if (totalSize > maxFileSize * maxFiles) {
                    e.preventDefault();
                    alert('La taille totale des fichiers dépasse la limite autorisée.');
                    return;
                }

                // Afficher un indicateur de chargement
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Envoi en cours...';
                submitBtn.disabled = true;
            });

            // Déclencher l'affichage initial
            toggleRecipientSections();
        });
    </script>
@endsection
