@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Ajouter un versement</h1>

        <!-- J4qi supprimer le choix du service qui émet le verserment et les statuts // cela va être gérer dans le controler   -->

        <form id="slipForm" action="{{ route('slips.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" class="form-control" id="code" name="code" required maxlength="20">
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required maxlength="200">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description"></textarea>
            </div>
            <div class="mb-3">
                <label for="current_organisation" class="form-label">Service d'archives</label>
                <input type="text" class="form-control" id="current_organisation" value="{{ $currentOrganisation->name }}" readonly>
                <small class="form-text text-muted">Votre organisation courante sera utilisée automatiquement.</small>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="user_organisation_id" class="form-label">Service versant</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="user_organisation_name" readonly>
                            <input type="hidden" id="user_organisation_id" name="user_organisation_id" required>
                            <button class="btn btn-outline-secondary select-btn" data-type="user_organisation" type="button">Select</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Responsable versement</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="user_name" readonly>
                            <input type="hidden" id="user_id" name="user_id">
                            <button class="btn btn-outline-secondary select-btn" data-type="user" type="button">Select</button>
                        </div>
                        <small class="form-text text-muted" id="user_help_text" style="display: none;">
                            <i class="fas fa-check-circle text-success"></i>
                            Utilisateurs chargés pour l'organisation sélectionnée
                        </small>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="slip_status_id" class="form-label">Statut</label>
                <select class="form-control" id="slip_status_id" name="slip_status_id" required>
                    <option value="">Sélectionner un statut</option>
                    @foreach($slipStatuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_received" name="is_received" value="1">
                    <label class="form-check-label" for="is_received">Reçu</label>
                </div>
            </div>
            <div class="mb-3" id="received_date_group" style="display: none;">
                <label for="received_date" class="form-label">Date de réception</label>
                <input type="date" class="form-control" id="received_date" name="received_date">
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_approved" name="is_approved" value="1">
                    <label class="form-check-label" for="is_approved">Approuvé</label>
                </div>
            </div>
            <div class="mb-3" id="approved_date_group" style="display: none;">
                <label for="approved_date" class="form-label">Date d'approbation</label>
                <input type="date" class="form-control" id="approved_date" name="approved_date">
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
            <button type="reset" class="btn btn-danger">Annuler</button>
        </form>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="selectionModal" tabindex="-1" aria-labelledby="selectionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="selectionModalLabel">Select Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="searchInput" class="form-control mb-3" placeholder="Search...">
                    <div id="itemList" class="list-group"></div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .modal-body {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>

    <script>
        let organisations = @json($organisations);
        let users = @json($users);
        let currentType = '';
        let modal;

        document.addEventListener('DOMContentLoaded', function() {
            modal = new bootstrap.Modal(document.getElementById('selectionModal'));

            document.querySelectorAll('.select-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const type = this.getAttribute('data-type');
                    console.log('Select button clicked for:', type);
                    openModal(type);
                });
            });

            // Gérer l'affichage des champs de date
            document.getElementById('is_received').addEventListener('change', function() {
                const receivedDateGroup = document.getElementById('received_date_group');
                receivedDateGroup.style.display = this.checked ? 'block' : 'none';
                if (!this.checked) {
                    document.getElementById('received_date').value = '';
                }
            });

            document.getElementById('is_approved').addEventListener('change', function() {
                const approvedDateGroup = document.getElementById('approved_date_group');
                approvedDateGroup.style.display = this.checked ? 'block' : 'none';
                if (!this.checked) {
                    document.getElementById('approved_date').value = '';
                }
            });
        });

        function openModal(type) {
            console.log('Opening modal for:', type);
            currentType = type;
            const modalTitle = document.getElementById('selectionModalLabel');
            const itemList = document.getElementById('itemList');
            const searchInput = document.getElementById('searchInput');

            modalTitle.textContent = `Select ${type.replace('_', ' ').charAt(0).toUpperCase() + type.replace('_', ' ').slice(1)}`;
            itemList.innerHTML = '';
            searchInput.value = '';

            let items = type.includes('organisation') ? organisations : users;
            if (type === 'user') {
                const userOrgId = document.getElementById('user_organisation_id').value;
                if (userOrgId) {
                    // Filtrer les utilisateurs qui appartiennent à l'organisation sélectionnée
                    items = users.filter(user => {
                        // Utiliser d'abord la propriété marquée par AJAX si elle existe
                        if (user.hasOwnProperty('belongsToSelectedOrg')) {
                            return user.belongsToSelectedOrg;
                        }
                        // Sinon utiliser la logique existante basée sur les relations
                        return user.organisations && user.organisations.some(org => org.id == userOrgId);
                    });
                } else {
                    items = [];
                    itemList.innerHTML = '<p class="text-center text-warning">Veuillez d\'abord sélectionner une organisation</p>';
                    modal.show();
                    return;
                }
            }

            console.log('Items to render:', items);
            renderItems(items);
            modal.show();
        }

        function renderItems(items) {
            const itemList = document.getElementById('itemList');
            itemList.innerHTML = '';
            if (items.length === 0) {
                itemList.innerHTML = '<p class="text-center">No items available</p>';
                return;
            }
            items.forEach(item => {
                const listItem = document.createElement('button');
                listItem.className = 'list-group-item list-group-item-action';
                listItem.textContent = item.name;
                listItem.onclick = () => selectItem(item);
                itemList.appendChild(listItem);
            });
        }

        function selectItem(item) {
            console.log('Item selected:', item);
            const idField = document.getElementById(`${currentType}_id`);
            const nameField = document.getElementById(`${currentType}_name`);

            idField.value = item.id;
            nameField.value = item.name;

            // Si on sélectionne une organisation utilisatrice, charger ses utilisateurs via AJAX
            if (currentType === 'user_organisation') {
                document.getElementById('user_id').value = '';
                document.getElementById('user_name').value = '';

                // Charger les utilisateurs de cette organisation
                loadUsersForOrganisation(item.id);
            }

            modal.hide();
        }

        // Fonction pour charger les utilisateurs d'une organisation via AJAX
        function loadUsersForOrganisation(organisationId) {
            // Afficher un indicateur de chargement
            const userButton = document.querySelector('[data-type="user"]');
            const originalText = userButton.textContent;
            userButton.textContent = 'Loading...';
            userButton.disabled = true;

            // Utiliser la route existante pour récupérer les utilisateurs d'une organisation
            fetch(`/organisations/${organisationId}/users`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(organisationUsers => {
                // Mettre à jour la liste globale des utilisateurs pour cette organisation
                users = users.map(user => {
                    // Marquer les utilisateurs qui appartiennent à cette organisation
                    const belongsToOrg = organisationUsers.some(orgUser => orgUser.id === user.id);
                    return { ...user, belongsToSelectedOrg: belongsToOrg };
                });

                console.log('Users loaded for organisation:', organisationUsers);

                // Restaurer le bouton
                userButton.textContent = originalText;
                userButton.disabled = false;

                // Afficher le message d'aide
                const helpText = document.getElementById('user_help_text');
                if (helpText) {
                    helpText.style.display = 'block';
                }

                // Afficher une notification si aucun utilisateur trouvé
                if (organisationUsers.length === 0) {
                    alert('Aucun utilisateur trouvé pour cette organisation.');
                    if (helpText) {
                        helpText.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Error loading users:', error);

                // Restaurer le bouton en cas d'erreur
                userButton.textContent = originalText;
                userButton.disabled = false;

                // Fallback: utiliser la logique existante basée sur les relations
                console.log('Fallback to existing logic');
            });
        }

        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            let items = currentType.includes('organisation') ? organisations : users;
            if (currentType === 'user') {
                const userOrgId = document.getElementById('user_organisation_id').value;
                if (userOrgId) {
                    items = users.filter(user => {
                        // Filtrer d'abord par organisation
                        let belongsToOrg = false;
                        if (user.hasOwnProperty('belongsToSelectedOrg')) {
                            belongsToOrg = user.belongsToSelectedOrg;
                        } else {
                            belongsToOrg = user.organisations && user.organisations.some(org => org.id == userOrgId);
                        }

                        // Puis filtrer par terme de recherche
                        const matchesSearch = user.name.toLowerCase().includes(searchTerm);

                        return belongsToOrg && matchesSearch;
                    });
                } else {
                    items = [];
                }
            } else {
                // Pour les organisations, filtrer simplement par nom
                items = items.filter(item => item.name.toLowerCase().includes(searchTerm));
            }
            renderItems(items);
        });

        document.getElementById('slipForm').addEventListener('submit', function(e) {
            const requiredFields = ['user_organisation_id', 'slip_status_id'];
            let isValid = true;

            requiredFields.forEach(field => {
                const fieldElement = document.getElementById(field);
                if (!fieldElement.value) {
                    alert(`Please select a ${field.replace('_', ' ')}`);
                    isValid = false;
                }
            });

            // Validation des dates si les checkboxes sont cochées
            if (document.getElementById('is_received').checked && !document.getElementById('received_date').value) {
                alert('Please enter the received date');
                isValid = false;
            }

            if (document.getElementById('is_approved').checked && !document.getElementById('approved_date').value) {
                alert('Please enter the approved date');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
@endsection
