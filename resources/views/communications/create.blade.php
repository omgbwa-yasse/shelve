@extends('layouts.app')

@section('content')
    <div class="container-fluid ">
        <h1>{{ __('Fill a form') }}</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('transactions.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="code" class="form-label">{{ __('Code') }}</label>
                <input type="text" class="form-control" id="code" name="code" required>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">{{ __('Object') }}</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">{{ __('Description') }}</label>
                <textarea class="form-control" id="content" name="content"></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="user_organisation_id" class="form-label">{{ __('User organization') }}</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="organisation_name" readonly>
                        <input type="hidden" id="user_organisation_id" name="user_organisation_id" required>
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#organisationModal">
                            {{ __('Select') }}
                        </button>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="user_id" class="form-label">{{ __('User') }}</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="user_name" readonly>
                        <input type="hidden" id="user_id" name="user_id" required>
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#userModal" disabled id="userSelectButton">
                            {{ __('Select') }}
                        </button>
                    </div>
                    <small class="text-muted">{{ __('Please select an organization first') }}</small>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="return_date" class="form-label">{{ __('Return Date') }}</label>
                    <input type="date" class="form-control" id="return_date" name="return_date" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="status_id" class="form-label">{{ __('Status') }}</label>
                    <select class="form-select" id="status_id" name="status_id" required>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" id="submitBtn">{{ __('Create') }}</button>
        </form>
    </div>

    <!-- User Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">{{ __('Select User') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control mb-3" id="userSearch" placeholder="{{ __('Search users') }}">
                    <div id="userLoading" class="text-center" style="display: none;">
                        <div class="spinner-border text-primary" aria-hidden="true"></div>
                        <span class="ms-2">{{ __('Loading users...') }}</span>
                    </div>
                    <ul class="list-group" id="userList">
                        <!-- Les utilisateurs seront chargés dynamiquement via AJAX -->
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Organisation Modal -->
    <div class="modal fade" id="organisationModal" tabindex="-1" aria-labelledby="organisationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="organisationModalLabel">{{ __('Select Organization') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control mb-3" id="organisationSearch" placeholder="{{ __('Search organizations') }}">
                    <ul class="list-group" id="organisationList">
                        @foreach ($organisations as $organisation)
                            <li class="list-group-item organisation-item" data-id="{{ $organisation->id }}" data-name="{{ $organisation->name }}">
                                {{ $organisation->name }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let organisationUsers = []; // Stocker les utilisateurs de l'organisation sélectionnée

            // Organisation search functionality
            const organisationSearch = document.getElementById('organisationSearch');
            const organisationList = document.getElementById('organisationList');
            const organisationItems = organisationList.querySelectorAll('.organisation-item');

            organisationSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                organisationItems.forEach(item => {
                    const organisationName = item.textContent.toLowerCase();
                    item.style.display = organisationName.includes(searchTerm) ? '' : 'none';
                });
            });

            // Organisation selection
            organisationItems.forEach(item => {
                item.addEventListener('click', function() {
                    const organisationId = this.dataset.id;
                    const organisationName = this.dataset.name;

                    document.getElementById('user_organisation_id').value = organisationId;
                    document.getElementById('organisation_name').value = organisationName;
                    document.getElementById('organisationModal').querySelector('.btn-close').click();

                    // Réinitialiser la sélection utilisateur
                    document.getElementById('user_id').value = '';
                    document.getElementById('user_name').value = '';

                    // Charger les utilisateurs de cette organisation
                    loadOrganisationUsers(organisationId);

                    // Activer le bouton de sélection utilisateur
                    const userSelectButton = document.getElementById('userSelectButton');
                    userSelectButton.disabled = false;
                    userSelectButton.parentElement.nextElementSibling.textContent = '';
                });
            });

            // Fonction pour charger les utilisateurs d'une organisation via AJAX
            function loadOrganisationUsers(organisationId) {
                const userLoading = document.getElementById('userLoading');
                const userList = document.getElementById('userList');

                console.log('Chargement des utilisateurs pour l\'organisation ID:', organisationId);

                userLoading.style.display = 'block';
                userList.innerHTML = '';

                fetch(`/api/organisations/${organisationId}/users`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                    .then(response => {
                        console.log('Réponse du serveur:', response);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(users => {
                        console.log('Utilisateurs reçus:', users);
                        organisationUsers = users;
                        updateUserList(users);
                        userLoading.style.display = 'none';
                    })
                    .catch(error => {
                        console.error('Erreur lors du chargement des utilisateurs:', error);
                        userLoading.style.display = 'none';
                        userList.innerHTML = '<li class="list-group-item text-danger">{{ __("Error loading users") }}: ' + error.message + '</li>';
                    });
            }

            // Fonction pour mettre à jour la liste des utilisateurs
            function updateUserList(users) {
                const userList = document.getElementById('userList');
                userList.innerHTML = '';

                if (users.length === 0) {
                    userList.innerHTML = '<li class="list-group-item text-muted">{{ __("No users found in this organization") }}</li>';
                    return;
                }

                users.forEach(user => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item user-item';
                    li.dataset.id = user.id;
                    li.dataset.name = user.name;
                    li.textContent = user.name;
                    li.style.cursor = 'pointer';

                    li.addEventListener('click', function() {
                        document.getElementById('user_id').value = this.dataset.id;
                        document.getElementById('user_name').value = this.dataset.name;
                        document.getElementById('userModal').querySelector('.btn-close').click();
                    });

                    userList.appendChild(li);
                });
            }

            // User search functionality
            const userSearch = document.getElementById('userSearch');
            userSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const filteredUsers = organisationUsers.filter(user =>
                    user.name.toLowerCase().includes(searchTerm)
                );
                updateUserList(filteredUsers);
            });

            // Réinitialiser la recherche utilisateur quand la modal s'ouvre
            document.getElementById('userModal').addEventListener('show.bs.modal', function() {
                userSearch.value = '';
                updateUserList(organisationUsers);
            });

            // Validation du formulaire avant soumission
            document.querySelector('form').addEventListener('submit', function(e) {
                const requiredFields = {
                    'code': 'Code',
                    'name': 'Objet',
                    'user_organisation_id': 'Organisation utilisateur',
                    'user_id': 'Utilisateur',
                    'return_date': 'Date de retour',
                    'status_id': 'Statut'
                };

                let errors = [];

                for (let fieldName in requiredFields) {
                    const field = document.getElementById(fieldName);
                    if (!field || !field.value.trim()) {
                        errors.push(`Le champ "${requiredFields[fieldName]}" est obligatoire`);
                    }
                }

                if (errors.length > 0) {
                    e.preventDefault();
                    alert('Erreurs de validation:\n' + errors.join('\n'));
                    return false;
                }

                console.log('Données du formulaire à envoyer:');
                const formData = new FormData(this);
                for (let [key, value] of formData.entries()) {
                    console.log(key + ': ' + value);
                }
            });
        });


    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ... autre code JavaScript ...

            // Set min date for return_date input
            const returnDateInput = document.getElementById('return_date');
            const today = new Date().toISOString().split('T')[0];
            returnDateInput.setAttribute('min', today);

            returnDateInput.addEventListener('input', function() {
                if (this.value < today) {
                    this.value = today;
                    alert("{{ __('The return date cannot be earlier than today.') }}");
                }
            });
        });
    </script>
@endsection
