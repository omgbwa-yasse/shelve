@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Créer Courrier sortant</h1>

        <form action="{{ route('mail-send.store') }}" method="POST" class="needs-validation" novalidate>
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="mailInput" class="form-label">Mail</label>
                    <div class="input-group">
                        <input type="text" id="mailInput" class="form-control" readonly required>
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#mailModal">
                            <i class="bi bi-search"></i> Sélectionner
                        </button>
                    </div>
                    <input type="hidden" name="mail_id" id="selectedMailId">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="organisation_received_id" class="form-label">Organisation de réception</label>
                    <div class="input-group">
                        <input type="text" id="selectedOrganisation" class="form-control" readonly required>
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#organisationModal">
                            <i class="bi bi-building"></i> Choisir
                        </button>
                    </div>
                    <input type="hidden" name="organisation_received_id" id="organisation_received_id">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="user_received_id" class="form-label">Utilisateur récepteur</label>
                    <div class="input-group">
                        <input type="text" id="selectedUser" class="form-control" readonly required>
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#userModal">
                            <i class="bi bi-person"></i> Sélectionner
                        </button>
                    </div>
                    <input type="hidden" name="user_received_id" id="user_received_id">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="document_type_id" class="form-label">Nature de la copie</label>
                    <select name="document_type_id" id="document_type_id" class="form-select" required>
                        <option value="">Choisir la nature de la copie</option>
                        @foreach($documentTypes as $documentType)
                            <option value="{{ $documentType->id }}" {{ old('document_type_id') == $documentType->id ? 'selected' : '' }}>
                                {{ $documentType->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="action_id" class="form-label">Action</label>
                    <select name="action_id" id="action_id" class="form-select" required>
                        <option value="">Choisir une action</option>
                        @foreach($mailActions as $action)
                            <option value="{{ $action->id }}" {{ old('action_id') == $action->id ? 'selected' : '' }}>
                                {{ $action->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-send"></i> Créer le courrier sortant
            </button>
        </form>
    </div>

    <!-- Modal pour la sélection du mail -->
    <div class="modal fade" id="mailModal" tabindex="-1" aria-labelledby="mailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mailModalLabel">Sélectionner un mail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="mailSearch" class="form-control mb-3" placeholder="Rechercher un mail...">
                    <div id="mailList" class="list-group">
                        @foreach($mails as $mail)
                            <a href="#" class="list-group-item list-group-item-action" data-id="{{ $mail->id }}" data-code="{{ $mail->code }}" data-name="{{ $mail->name }}">
                                {{ $mail->code }} : {{ $mail->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour la sélection de l'organisation -->
    <div class="modal fade" id="organisationModal" tabindex="-1" aria-labelledby="organisationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="organisationModalLabel">Sélectionner une organisation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="organisationSearch" class="form-control mb-3" placeholder="Rechercher une organisation...">
                    <div id="organisationList" class="list-group">
                        @foreach($organisations as $organisation)
                            <a href="#" class="list-group-item list-group-item-action" data-id="{{ $organisation->id }}">
                                {{ $organisation->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour la sélection de l'utilisateur -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">Sélectionner un utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="userSearch" class="form-control mb-3" placeholder="Rechercher un utilisateur...">
                    <div id="userList" class="list-group">
                        @foreach($users as $user)
                            <a href="#" class="list-group-item list-group-item-action" data-id="{{ $user->id }}">
                                {{ $user->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fonction générique pour filtrer les éléments d'une liste
            function filterList(searchInput, listItems) {
                const filter = searchInput.value.toLowerCase();
                listItems.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(filter) ? '' : 'none';
                });
            }

            // Gestionnaire pour le modal des mails
            const mailModal = document.getElementById('mailModal');
            const mailSearch = document.getElementById('mailSearch');
            const mailList = document.getElementById('mailList');
            const mailItems = mailList.querySelectorAll('.list-group-item');
            const mailInput = document.getElementById('mailInput');
            const selectedMailId = document.getElementById('selectedMailId');

            mailSearch.addEventListener('input', () => filterList(mailSearch, mailItems));

            mailItems.forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    mailInput.value = `${item.dataset.code} : ${item.dataset.name}`;
                    selectedMailId.value = item.dataset.id;
                    bootstrap.Modal.getInstance(mailModal).hide();
                });
            });

            // Gestionnaire pour le modal des organisations
            const organisationModal = document.getElementById('organisationModal');
            const organisationSearch = document.getElementById('organisationSearch');
            const organisationList = document.getElementById('organisationList');
            const organisationItems = organisationList.querySelectorAll('.list-group-item');
            const selectedOrganisation = document.getElementById('selectedOrganisation');
            const organisationReceivedId = document.getElementById('organisation_received_id');

            organisationSearch.addEventListener('input', () => filterList(organisationSearch, organisationItems));

            organisationItems.forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    selectedOrganisation.value = item.textContent.trim();
                    organisationReceivedId.value = item.dataset.id;
                    bootstrap.Modal.getInstance(organisationModal).hide();
                });
            });

            // Gestionnaire pour le modal des utilisateurs
            const userModal = document.getElementById('userModal');
            const userSearch = document.getElementById('userSearch');
            const userList = document.getElementById('userList');
            const userItems = userList.querySelectorAll('.list-group-item');
            const selectedUser = document.getElementById('selectedUser');
            const userReceivedId = document.getElementById('user_received_id');

            userSearch.addEventListener('input', () => filterList(userSearch, userItems));

            userItems.forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    selectedUser.value = item.textContent.trim();
                    userReceivedId.value = item.dataset.id;
                    bootstrap.Modal.getInstance(userModal).hide();
                });
            });

            // Validation du formulaire
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    </script>
@endsection



