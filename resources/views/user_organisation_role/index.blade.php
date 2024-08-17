@extends('layouts.app')
@section('content')

<div class="container">
    <h1>User Organisations</h1>
    <a href="{{ route('user-organisation-role.create') }}" class="btn btn-primary mb-3">Create New User Organisation</a>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Organisation</th>
                <th>Active</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($userOrganisationRoles as $userOrganisationRole)
                <tr>
                    <td>{{ $userOrganisationRole->id }}</td>
                    <td>{{ $userOrganisationRole->user->name }}</td>
                    <td>{{ $userOrganisationRole->organisation->name }}</td>
                    <td>{{ $userOrganisationRole->active ? 'Yes' : 'No' }}</td>
                    <td>
                        <a href="{{ route('user-organisation-role.show', $userOrganisationRole->id) }}">View</a>
                        <a href="{{ route('user-organisation-role.edit', $userOrganisationRole->id) }}" class="btn btn-primary">Edit</a>
                        <form action="{{ route('user-organisation-role.destroy', $userOrganisationRole->id) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
