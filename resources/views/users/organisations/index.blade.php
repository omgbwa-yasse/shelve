@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>User Organisation Roles</h1>
        <a href="{{ route('user-organisation-role.create') }}" class="btn btn-primary mb-3">Create New</a>
        <table class="table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Organisation</th>
                    <th>Role</th>
                    <th>Creator</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($userOrganisationRoles as $userOrganisationRole)
                    <tr>
                        <td>{{ $userOrganisationRole->user->name }}</td>
                        <td>{{ $userOrganisationRole->organisation->name }}</td>
                        <td>{{ $userOrganisationRole->role->name }}</td>
                        <td>{{ $userOrganisationRole->creator->name }}</td>
                        <td>
                            <a href="{{ route('user-organisation-role.show',[ $userOrganisationRole->user->id,  $userOrganisationRole->organisation->id]) }}" class="btn btn-info btn-sm">View</a>
                            <a href="{{ route('user-organisation-role.edit', [ $userOrganisationRole->user->id,  $userOrganisationRole->organisation->id]) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('user-organisation-role.destroy', [ $userOrganisationRole->user->id,  $userOrganisationRole->organisation->id]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user organisation role?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
