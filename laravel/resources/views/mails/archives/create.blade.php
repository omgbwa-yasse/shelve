@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Archiver des courriers</h1>
        <form action="{{ route('mail-archive.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="container_id" class="form-label">Container</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="containerInput" readonly required>
                    <input type="hidden" name="container_id" id="container_id" value="{{ old('container_id') }}">
                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#containerModal">
                        Sélectionner
                    </button>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Sélection des courriers</label>
                <button type="button" class="btn btn-outline-primary mb-2" data-bs-toggle="modal" data-bs-target="#mailModal">
                    Sélectionner des courriers
                </button>
                
                <div id="selectedMailsContainer" class="border rounded p-3 mb-3">
                    <div class="alert alert-info" id="noMailsSelected">
                        Aucun courrier sélectionné
                    </div>
                    <div id="selectedMailsList">
                        <!-- Les mails sélectionnés seront ajoutés ici dynamiquement -->
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" id="submitButton" disabled>Archiver</button>
        </form>
    </div>

    <!-- Container Modal -->
    <div class="modal fade" id="containerModal" tabindex="-1" aria-labelledby="containerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="containerModalLabel">Sélectionner un container</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="containerSearch" class="form-control mb-3" placeholder="Rechercher un container...">
                    <div id="containerList" class="list-group">
                        @foreach ($mailContainers as $container)
                            <button type="button" class="list-group-item list-group-item-action" data-id="{{ $container->id }}" data-name="{{ $container->code }} - {{ $container->name }}">
                                {{ $container->code }} - {{ $container->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mail Modal -->
    <div class="modal fade" id="mailModal" tabindex="-1" aria-labelledby="mailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mailModalLabel">Sélectionner des courriers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="mailSearch" class="form-control mb-3" placeholder="Rechercher un courrier...">
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAllMails">
                            <label class="form-check-label" for="selectAllMails">
                                Sélectionner tous les courriers
                            </label>
                        </div>
                    </div>
                    <div id="mailList" class="list-group mb-3">
                        @foreach ($mails as $mail)
                            <div class="list-group-item">
                                <div class="form-check">
                                    <input class="form-check-input mail-checkbox" type="checkbox" id="mail-{{ $mail->id }}" data-id="{{ $mail->id }}" data-name="{{ $mail->name }}">
                                    <label class="form-check-label" for="mail-{{ $mail->id }}">
                                        {{ $mail->name }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" id="addSelectedMails">Ajouter les courriers sélectionnés</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Setup pour le modal Container
            const containerInput = document.getElementById('containerInput');
            const containerIdInput = document.getElementById('container_id');
            const containerSearch = document.getElementById('containerSearch');
            const containerList = document.getElementById('containerList');
            const selectedMailsList = document.getElementById('selectedMailsList');
            const noMailsSelected = document.getElementById('noMailsSelected');
            const submitButton = document.getElementById('submitButton');
            
            // Gestion de la recherche de container
            containerSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                Array.from(containerList.children).forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
            
            // Sélection d'un container
            containerList.addEventListener('click', function(e) {
                if (e.target.tagName === 'BUTTON') {
                    containerInput.value = e.target.dataset.name;
                    containerIdInput.value = e.target.dataset.id;
                    bootstrap.Modal.getInstance(document.getElementById('containerModal')).hide();
                }
            });
            
            // Recherche de mails
            document.getElementById('mailSearch').addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                Array.from(document.getElementById('mailList').children).forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
            
            // Sélection de tous les mails
            document.getElementById('selectAllMails').addEventListener('change', function() {
                document.querySelectorAll('.mail-checkbox').forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
            
            // Ajout des mails sélectionnés
            document.getElementById('addSelectedMails').addEventListener('click', function() {
                const selectedCheckboxes = document.querySelectorAll('.mail-checkbox:checked');
                
                if (selectedCheckboxes.length > 0) {
                    selectedMailsList.innerHTML = ''; // Effacer les sélections existantes
                    noMailsSelected.style.display = 'none';
                    submitButton.disabled = false;
                    
                    selectedCheckboxes.forEach((checkbox, index) => {
                        const mailId = checkbox.dataset.id;
                        const mailName = checkbox.dataset.name;
                        
                        const mailEntry = document.createElement('div');
                        mailEntry.className = 'card mb-2';
                        mailEntry.innerHTML = `
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">${mailName}</h6>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-mail" data-id="${mailId}">
                                        <i class="bi bi-trash"></i> Retirer
                                    </button>
                                </div>
                                <input type="hidden" name="mails[${index}][id]" value="${mailId}">
                                <div class="form-group">
                                    <label>Type de document</label>
                                    <select name="mails[${index}][document_type]" class="form-select">
                                        <option value="original">Original</option>
                                        <option value="copy">Copie</option>
                                        <option value="duplicate">Duplicata</option>
                                    </select>
                                </div>
                            </div>
                        `;
                        
                        selectedMailsList.appendChild(mailEntry);
                    });
                    
                    // Ajouter les gestionnaires d'événements pour les boutons de suppression
                    document.querySelectorAll('.remove-mail').forEach(button => {
                        button.addEventListener('click', function() {
                            this.closest('.card').remove();
                            updateMailsDisplay();
                        });
                    });
                    
                    bootstrap.Modal.getInstance(document.getElementById('mailModal')).hide();
                    
                    // Décocher toutes les cases après l'ajout
                    document.querySelectorAll('.mail-checkbox').forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    document.getElementById('selectAllMails').checked = false;
                }
            });
            
            // Fonction pour mettre à jour l'affichage des mails sélectionnés
            function updateMailsDisplay() {
                const mailItems = selectedMailsList.querySelectorAll('.card');
                
                if (mailItems.length === 0) {
                    noMailsSelected.style.display = 'block';
                    submitButton.disabled = true;
                } else {
                    noMailsSelected.style.display = 'none';
                    submitButton.disabled = false;
                    
                    // Mettre à jour les indices dans les noms des champs
                    mailItems.forEach((item, index) => {
                        const idInput = item.querySelector('input[name^="mails["][name$="][id]"]');
                        const typeSelect = item.querySelector('select[name^="mails["][name$="][document_type]"]');
                        
                        const mailId = idInput.value;
                        const documentType = typeSelect.value;
                        
                        idInput.name = `mails[${index}][id]`;
                        typeSelect.name = `mails[${index}][document_type]`;
                    });
                }
            }
        });
    </script>
@endsection