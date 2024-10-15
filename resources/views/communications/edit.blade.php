@extends('layouts.app')
<style>
    .form-section {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .form-section h4 {
        margin-bottom: 20px;
        color: #0056b3;
    }
    .btn-floating {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 100;
    }
</style>
@section('content')
    <div class="container">
        <h1>{{ __('Edit Record') }}</h1>
        <form action="{{ route('transactions.update', $communication->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="code" class="form-label">{{ __('Code') }}</label>
                <input type="text" class="form-control" id="code" name="code" value="{{ $communication->code }}" required>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">{{ __('Subject') }}</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $communication->name }}" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">{{ __('Description') }}</label>
                <input type="text" class="form-control" id="content" name="content" value="{{ $communication->content }}" required>
            </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="user_id" class="form-label">{{ __('User') }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="user_name" value="{{ $communication->user->name ?? '' }}" readonly>
                            <input type="hidden" id="user_id" name="user_id" value="{{ $communication->user_id }}">
                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#userModal">{{ __('Select') }}</button>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="user_organisation_id" class="form-label">{{ __('User Organization') }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="organisation_name" value="{{ $communication->userOrganisation->name ?? '' }}" readonly>
                            <input type="hidden" id="user_organisation_id" name="user_organisation_id" value="{{ $communication->user_organisation_id }}">
                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#organisationModal">{{ __('Select') }}</button>
                        </div>
                    </div>
                </div>
               <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="return_date" class="form-label">{{ __('Return Date') }}</label>
                    <input type="date" class="form-control" id="return_date" name="return_date" value="{{ $communication->return_date }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="status_id" class="form-label">{{ __('Status') }}</label>
                    <select class="form-select" id="status_id" name="status_id" required>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->id }}" {{ $communication->status_id == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
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
                    <input type="text" id="userSearch" class="form-control mb-3" placeholder="{{ __('Search for a user...') }}">
                    <div id="userList" class="list-group">
                        @foreach ($users as $user)
                            <a href="#" class="list-group-item list-group-item-action {{ $communication->user_id == $user->id ? 'active' : '' }}" data-id="{{ $user->id }}" data-name="{{ $user->name }}">
                                {{ $user->name }}
                            </a>
                        @endforeach
                    </div>
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
                    <input type="text" id="organisationSearch" class="form-control mb-3" placeholder="{{ __('Search for an organization...') }}">
                    <div id="organisationList" class="list-group">
                        @foreach ($organisations as $organisation)
                            <a href="#" class="list-group-item list-group-item-action {{ $communication->user_organisation_id == $organisation->id ? 'active' : '' }}" data-id="{{ $organisation->id }}" data-name="{{ $organisation->name }}">
                                {{ $organisation->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function setupModal(modalId, inputId, hiddenInputId, listId, searchId) {
                const modal = document.getElementById(modalId);
                const input = document.getElementById(inputId);
                const hiddenInput = document.getElementById(hiddenInputId);
                const list = document.getElementById(listId);
                const search = document.getElementById(searchId);
                const items = list.querySelectorAll('.list-group-item');

                function filterItems() {
                    const filter = search.value.toLowerCase();
                    items.forEach(item => {
                        const text = item.textContent.toLowerCase();
                        item.style.display = text.includes(filter) ? '' : 'none';
                    });
                }

                search.addEventListener('input', filterItems);

                items.forEach(item => {
                    item.addEventListener('click', (e) => {
                        e.preventDefault();
                        input.value = item.dataset.name;
                        hiddenInput.value = item.dataset.id;
                        items.forEach(i => i.classList.remove('active'));
                        item.classList.add('active');
                        bootstrap.Modal.getInstance(modal).hide();
                    });
                });
            }

            setupModal('userModal', 'user_name', 'user_id', 'userList', 'userSearch');
            setupModal('organisationModal', 'organisation_name', 'user_organisation_id', 'organisationList', 'organisationSearch');
        });
    </script>
@endsection
