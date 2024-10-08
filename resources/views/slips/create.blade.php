@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Ajouter un versement</h1>
        <form id="slipForm" action="{{ route('slips.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" class="form-control" id="code" name="code" required maxlength="20">
            </div>
            <div class="mb-3">
                <label for="slip_status_id" class="form-label">Transferring Status</label>
                <select class="form-select" id="slip_status_id" name="slip_status_id" required>
                    @foreach ($slipStatuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
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
                <label for="officer_organisation_id" class="form-label">Officer Organisation</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="officer_organisation_name" readonly>
                    <input type="hidden" id="officer_organisation_id" name="officer_organisation_id" required>
                    <button class="btn btn-outline-secondary select-btn" data-type="officer_organisation" type="button">Select</button>
                </div>
            </div>
            <div class="mb-3">
                <label for="user_organisation_id" class="form-label">User Organisation</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="user_organisation_name" readonly>
                    <input type="hidden" id="user_organisation_id" name="user_organisation_id" required>
                    <button class="btn btn-outline-secondary select-btn" data-type="user_organisation" type="button">Select</button>
                </div>
            </div>
            <div class="mb-3">
                <label for="user_id" class="form-label">User</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="user_name" readonly>
                    <input type="hidden" id="user_id" name="user_id">
                    <button class="btn btn-outline-secondary select-btn" data-type="user" type="button">Select</button>
                </div>
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

            document.querySelector('#selectionModal .btn-close').addEventListener('click', function() {
                console.log('Close button clicked');
                modal.hide();
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
                    items = users.filter(user => user.organisations.some(org => org.id == userOrgId));
                } else {
                    items = [];
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

            if (currentType === 'user_organisation') {
                document.getElementById('user_id').value = '';
                document.getElementById('user_name').value = '';
            }

            modal.hide();
        }

        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            let items = currentType.includes('organisation') ? organisations : users;
            if (currentType === 'user') {
                const userOrgId = document.getElementById('user_organisation_id').value;
                if (userOrgId) {
                    items = users.filter(user => user.organisations.some(org => org.id == userOrgId));
                } else {
                    items = [];
                }
            }
            const filteredItems = items.filter(item => item.name.toLowerCase().includes(searchTerm));
            renderItems(filteredItems);
        });

        document.getElementById('slipForm').addEventListener('submit', function(e) {
            const requiredFields = ['officer_organisation_id', 'user_organisation_id'];
            let isValid = true;

            requiredFields.forEach(field => {
                if (!document.getElementById(field).value) {
                    alert(`Please select a ${field.replace('_', ' ')}`);
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
@endsection
