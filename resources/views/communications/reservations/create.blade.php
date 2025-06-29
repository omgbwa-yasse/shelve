@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Nouvelle réservation</h1>

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

        <form action="{{ route('communications.reservations.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}" required>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Objet</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Description</label>
                <textarea class="form-control" id="content" name="content">{{ old('content') }}</textarea>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Statut</label>
                <select name="status" id="status" class="form-select" required>
                    @foreach ($statuses as $status)
                        <option value="{{ $status['value'] }}" {{ old('status') === $status['value'] ? 'selected' : '' }}>{{ $status['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="user_organisation_id" class="form-label">{{ __('User Organisation') }}</label>
                    <select name="user_organisation_id" id="user_organisation_id" class="form-select" required>
                        <option value="">{{ __('Select an organization') }}</option>
                        @foreach ($organisations as $organisation)
                            <option value="{{ $organisation->id }}" {{ old('user_organisation_id') == $organisation->id ? 'selected' : '' }}>{{ $organisation->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="user_id" class="form-label">{{ __('User') }}</label>
                    <select name="user_id" id="user_id" class="form-select" required disabled>
                        <option value="">{{ __('Please select an organization first') }}</option>
                    </select>
                    <small class="text-muted">{{ __('Please select an organization first') }}</small>
                    <div id="userLoading" class="text-center mt-2" style="display: none;">
                        <div class="spinner-border spinner-border-sm text-primary" aria-hidden="true"></div>
                        <span class="ms-2">{{ __('Loading users...') }}</span>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Ajouter</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const organisationSelect = document.getElementById('user_organisation_id');
            const userSelect = document.getElementById('user_id');
            const userLoading = document.getElementById('userLoading');
            let organisationUsers = [];
            let lastLoadedOrganisationId = null;

            // Fonction pour charger les utilisateurs d'une organisation via AJAX
            function loadOrganisationUsers(organisationId) {
                if (!organisationId) {
                    userSelect.disabled = true;
                    userSelect.innerHTML = '<option value="">{{ __("Please select an organization first") }}</option>';
                    return;
                }

                userLoading.style.display = 'block';
                userSelect.disabled = true;
                userSelect.innerHTML = '<option value="">{{ __("Loading...") }}</option>';

                fetch(`/api/organisations/${organisationId}/users`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(users => {
                    organisationUsers = users;
                    updateUserSelect(users);
                    userLoading.style.display = 'none';
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des utilisateurs:', error);
                    userLoading.style.display = 'none';
                    userSelect.innerHTML = '<option value="">{{ __("Error loading users") }}</option>';
                });
            }

            // Fonction pour mettre à jour le select des utilisateurs
            function updateUserSelect(users) {
                userSelect.innerHTML = '<option value="">{{ __("Select a user") }}</option>';

                if (users.length === 0) {
                    userSelect.innerHTML = '<option value="">{{ __("No users found in this organization") }}</option>';
                    userSelect.disabled = true;
                    return;
                }

                users.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = user.name;
                    userSelect.appendChild(option);
                });

                userSelect.disabled = false;
            }

            // Écouter les changements d'organisation
            organisationSelect.addEventListener('change', function() {
                const organisationId = this.value;
                if (lastLoadedOrganisationId !== organisationId) {
                    loadOrganisationUsers(organisationId);
                    lastLoadedOrganisationId = organisationId;
                }
            });

            // Validation du formulaire
            document.querySelector('form').addEventListener('submit', function(e) {
                const requiredFields = {
                    'code': 'Code',
                    'name': 'Objet',
                    'user_organisation_id': 'Organisation utilisateur',
                    'user_id': 'Utilisateur'
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
            });
        });
    </script>
@endsection
