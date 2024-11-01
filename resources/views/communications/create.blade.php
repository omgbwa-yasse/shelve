@extends('layouts.app')

@section('content')
    <div class="container-fluid ">
        <h1>{{ __('Fill a form') }}</h1>
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
                    <label for="user_id" class="form-label">{{ __('User') }}</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="user_name" readonly>
                        <input type="hidden" id="user_id" name="user_id" required>
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#userModal">
                            {{ __('Select') }}
                        </button>
                    </div>
                </div>
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

            <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
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
                    <ul class="list-group" id="userList">
                        @foreach ($users as $user)
                            <li class="list-group-item user-item" data-id="{{ $user->id }}" data-name="{{ $user->name }}">
                                {{ $user->name }}
                            </li>
                        @endforeach
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
            // User search functionality
            const userSearch = document.getElementById('userSearch');
            const userList = document.getElementById('userList');
            const userItems = userList.querySelectorAll('.user-item');

            userSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                userItems.forEach(item => {
                    const userName = item.textContent.toLowerCase();
                    item.style.display = userName.includes(searchTerm) ? '' : 'none';
                });
            });

            // User selection
            userItems.forEach(item => {
                item.addEventListener('click', function() {
                    document.getElementById('user_id').value = this.dataset.id;
                    document.getElementById('user_name').value = this.dataset.name;
                    document.getElementById('userModal').querySelector('.btn-close').click();
                });
            });

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
                    document.getElementById('user_organisation_id').value = this.dataset.id;
                    document.getElementById('organisation_name').value = this.dataset.name;
                    document.getElementById('organisationModal').querySelector('.btn-close').click();
                });
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
