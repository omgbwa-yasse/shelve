@extends('layouts.app')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const searchButton = document.getElementById('searchButton');
        const table = document.getElementById('usersTable');
        const rows = table.getElementsByTagName('tr');

        function filterTable() {
            const filter = searchInput.value.toLowerCase();
            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;
                for (let j = 0; j < cells.length; j++) {
                    const cell = cells[j];
                    if (cell.innerHTML.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
                row.style.display = found ? '' : 'none';
            }
        }

        searchButton.addEventListener('click', filterTable);
        searchInput.addEventListener('keyup', filterTable);

        // Check for flash messages and display alerts
        @if(session('success'))
        showAlert('success', "{{ session('success') }}");
        @endif

        @if(session('error'))
        showAlert('danger', "{{ session('error') }}");
        @endif
    });

    function confirmDelete(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            const form = document.getElementById('deleteUserForm');
            form.action = `/users/${userId}`;
            form.submit();
        }
    }

    function showAlert(type, message) {
        const alertPlaceholder = document.getElementById('alertPlaceholder');
        const wrapper = document.createElement('div');
        wrapper.innerHTML = [
            `<div class="alert alert-${type} alert-dismissible" role="alert">`,
            `   <div>${message}</div>`,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
            '</div>'
        ].join('');
        alertPlaceholder.append(wrapper);

        // Auto-dismiss the alert after 5 seconds
        setTimeout(() => {
            const alert = bootstrap.Alert.getOrCreateInstance(wrapper.firstElementChild);
            alert.close();
        }, 5000);
    }
</script>
@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="">
                <!-- Alert placeholder -->
                <div id="alertPlaceholder"></div>

                <div class="">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">{{ __('User Management') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <a href="{{ route('users.create') }}" class="btn btn-success">
                                <i class="bi bi-person-plus"></i> Create New User
                            </a>
                            <div class="input-group w-auto">
                                <input type="text" class="form-control" placeholder="Search users..." id="searchInput">
                                <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover" id="usersTable">
                                <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Surname</th>
                                    <th>Birthday</th>
                                    <th>Email</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->surname }}</td>
                                        <td>{{ $user->birthday }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('users.show', $user->id) }}" class="btn btn-info btn-sm">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $user->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
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
    </div>

    <!-- Delete User Form -->
    <form id="deleteUserForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@section('scripts')

@endsection
