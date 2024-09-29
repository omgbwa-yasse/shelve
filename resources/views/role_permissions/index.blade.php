@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Role Permissions') }}</div>

                <div class="card-body">
                    <a href="{{ route('role_permissions.create') }}" class="btn btn-primary mb-3">Associer une permission à un role</a>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Permission</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rolePermissions as $rolePermission)
                            <tr>
                                <td>{{ $rolePermission->role->name }}</td>
                                <td>{{ $rolePermission->permission->name ??'' }}</td>
                                <td>
                                    <a href="{{ route('role_permissions.show',[$rolePermission->role, $rolePermission->role]) }}" class="btn btn-info">Show</a>
                                    <a href="{{ route('role_permissions.edit', [$rolePermission->role, $rolePermission->role]) }}" class="btn btn-primary">Edit</a>
                                    <form action="{{ route('role_permissions.destroy', [$rolePermission->role, $rolePermission->role]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this role permission?')">Delete</button>
                                    </form>
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
@endsection
