@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('User Roles') }}</div>

                <div class="card-body">
                    <a href="{{ route('user_roles.create') }}" class="btn btn-primary mb-3">Assign Role to User</a>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($userRoles as $userRole)
                            <tr>
                                <td>{{ $userRole->user->name }}</td>
                                <td>{{ $userRole->role->name }}</td>
                                <td>
                                    <a href="{{ route('user_roles.edit', $userRole->id) }}" class="btn btn-primary">Edit</a>
                                    <form action="{{ route('user_roles.destroy', [$userRole->user_id, $userRole->role_id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to remove this role from the user?')">Remove</button>
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
