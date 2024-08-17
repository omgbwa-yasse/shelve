@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>User Roles</h1>
        <a href="{{ route('user-roles.create') }}" class="btn btn-primary mb-3">Create New User Role</a>
        <table class="table">
            <thead>
                <tr>
                    <th>Role ID</th>
                    <th>User ID</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($userRoles as $userRole)
                    <tr>
                        <td>{{ $userRole->role_id }}</td>
                        <td>{{ $userRole->user_id }}</td>
                        <td>
                            <a href="{{ route('user-roles.show', $userRole->id) }}" class="btn btn-info">View</a>
                            <a href="{{ route('user-roles.edit', $userRole->id) }}" class="btn btn-primary">Edit</a>
                            <form action="{{ route('user-roles.destroy', $userRole->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user role?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
