@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <!-- Header simple -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Créer un courrier entrant</h1>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <!-- Main Form Card -->
        <div class="card">
            <div class="card-body">
                <form action="{{ route('mail-received.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <!-- Première colonne -->
                        <div class="col-md-6">
                             <div class="mb-3">
                                <label class="form-label">Courrier associé</label>
                                <div class="input-group">
                                    <input type="text"
                                           id="mailInput"
                                           class="form-control"
                                           readonly
                                           required>
                                    <button class="btn btn-outline-secondary"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#mailModal">
                                        <i class="bi bi-search"></i>
                                        Sélectionner
                                    </button>
                                    <input type="hidden" name="mail_id" id="selectedMailId">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description"
                                          class="form-control"
                                          rows="3">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        <!-- Deuxième colonne -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Organisation d'envoi</label>
                                <div class="input-group">
                                    <input type="text"
                                           id="selectedOrganisation"
                                           class="form-control"
                                           readonly
                                           required>
                                    <button class="btn btn-outline-secondary"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#organisationModal">
                                        <i class="bi bi-search"></i>
                                        Sélectionner
                                    </button>
                                    <input type="hidden" name="organisation_send_id" id="organisation_send_id">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Utilisateur expéditeur</label>
                                <div class="input-group">
                                    <input type="text"
                                           id="selectedUser"
                                           class="form-control"
                                           readonly
                                           required>
                                    <button class="btn btn-outline-secondary"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#userModal">
                                        <i class="bi bi-search"></i>
                                        Sélectionner
                                    </button>
                                    <input type="hidden" name="user_send_id" id="user_send_id">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nature du document</label>
                                <select name="document_type_id" class="form-select" required>
                                    <option value="">Sélectionner une nature</option>
                                    @foreach($documentTypes as $type)
                                        <option value="{{ $type->id }}" {{ old('document_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Action requise</label>
                                <select name="action_id" class="form-select" required>
                                    <option value="">Sélectionner une action</option>
                                    @foreach($mailActions as $action)
                                        <option value="{{ $action->id }}" {{ old('action_id') == $action->id ? 'selected' : '' }}>
                                            {{ $action->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons de form -->
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Créer le courrier</button>
                        <a href="{{ route('mail-received.index') }}" class="btn btn-light ms-2">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal pour la sélection du mail -->
    <div class="modal fade" id="mailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sélectionner un courrier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" id="mailSearch" class="form-control" placeholder="Rechercher un courrier...">
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Code</th>
                                <th>Nom</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach($mails as $mail)
                                <tr>
                                    <td>{{ $mail->code }}</td>
                                    <td>{{ $mail->name }}</td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-sm btn-primary select-mail"
                                                data-id="{{ $mail->id }}"
                                                data-code="{{ $mail->code }}"
                                                data-name="{{ $mail->name }}">
                                            Sélectionner
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour la sélection de l'organisation -->
    <div class="modal fade" id="organisationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sélectionner une organisation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" id="organisationSearch" class="form-control" placeholder="Rechercher une organisation...">
                    </div>
                    <div class="list-group">
                        @foreach($organisations as $organisation)
                            <button type="button"
                                    class="list-group-item list-group-item-action select-organisation"
                                    data-id="{{ $organisation->id }}"
                                    data-name="{{ $organisation->name }}">
                                {{ $organisation->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour la sélection de l'utilisateur -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sélectionner un utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" id="userSearch" class="form-control" placeholder="Rechercher un utilisateur...">
                    </div>
                    <div class="list-group">
                        @foreach($users as $user)
                            <button type="button"
                                    class="list-group-item list-group-item-action select-user"
                                    data-id="{{ $user->id }}"
                                    data-name="{{ $user->name }}">
                                {{ $user->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Fonction de recherche générique
                function setupSearch(searchInput, items) {
                    searchInput.addEventListener('input', function() {
                        const searchText = this.value.toLowerCase();
                        items.forEach(item => {
                            const text = item.textContent.toLowerCase();
                            item.style.display = text.includes(searchText) ? '' : 'none';
                        });
                    });
                }

                // Configuration de la recherche pour les mails
                setupSearch(
                    document.getElementById('mailSearch'),
                    document.querySelectorAll('#mailModal tbody tr')
                );

                // Configuration de la recherche pour les organisations
                setupSearch(
                    document.getElementById('organisationSearch'),
                    document.querySelectorAll('.select-organisation')
                );

                // Configuration de la recherche pour les utilisateurs
                setupSearch(
                    document.getElementById('userSearch'),
                    document.querySelectorAll('.select-user')
                );

                // Sélection des mails
                document.querySelectorAll('.select-mail').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const code = this.dataset.code;
                        const name = this.dataset.name;

                        document.getElementById('mailInput').value = `${code} - ${name}`;
                        document.getElementById('selectedMailId').value = id;
                        bootstrap.Modal.getInstance(document.getElementById('mailModal')).hide();
                    });
                });

                // Sélection des organisations
                document.querySelectorAll('.select-organisation').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const name = this.dataset.name;

                        document.getElementById('selectedOrganisation').value = name;
                        document.getElementById('organisation_send_id').value = id;
                        bootstrap.Modal.getInstance(document.getElementById('organisationModal')).hide();
                    });
                });

                // Sélection des utilisateurs
                document.querySelectorAll('.select-user').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const name = this.dataset.name;

                        document.getElementById('selectedUser').value = name;
                        document.getElementById('user_send_id').value = id;
                        bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
                    });
                });
            });
        </script>
    @endpush
@endsection
