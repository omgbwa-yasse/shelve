@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>User Organisations</h1>
        <a href="{{ route('user-organisations.create') }}" class="btn btn-primary mb-3">Create New User Organisation</a>
        <table class="table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Organisation ID</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($userOrganisations as $userOrganisation)
                    <tr>
                        <td>{{ $userOrganisation->user->name }}</td>
                        <td>{{ $userOrganisation->organisation->name}}</td>
                        <td>
                        <a href="{{ route('user-organisations.show', $userOrganisation->id ) }}" class="btn btn-info">View</a>
                        <a href="{{ route('user-organisations.edit', $userOrganisation->id) }}" class="btn btn-primary">Edit</a>
                        <form action="{{ route('user-organisations.destroy',  $userOrganisation->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user organisation?')">Delete</button>
                        </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
