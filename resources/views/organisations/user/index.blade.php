@extends('layouts.app')
@section('content')

<div class="container">
    <h1>User Organisations</h1>
    <a href="{{ route('user-organisation.create') }}" class="btn btn-primary mb-3">Create New User Organisation</a>
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
            @foreach ($userOrganisations as $userOrganisation)
                <tr>
                    <td>{{ $userOrganisation->id }}</td>
                    <td>{{ $userOrganisation->user->name }}</td>
                    <td>{{ $userOrganisation->organisation->name }}</td>
                    <td>{{ $userOrganisation->active ? 'Yes' : 'No' }}</td>
                    <td>
                        <a href="{{ route('user-organisation.show', $userOrganisation->id) }}" class="btn btn-info">Show</a>
                        <a href="{{ route('user-organisation.edit', $userOrganisation->id) }}" class="btn btn-primary">Edit</a>
                        <form action="{{ route('user-organisation.destroy', $userOrganisation->id) }}" method="POST" style="display: inline-block;">
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
