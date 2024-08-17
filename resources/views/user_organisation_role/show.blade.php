@extends('layouts.app')
@section('content')

<div class="container">
    <h1>User Organisation Details</h1>
    <table class="table">
        <tbody>
            <tr>
                <th>ID</th>
                <td>{{ $userOrganisationRole->id }}</td>
            </tr>
            <tr>
                <th>User</th>
                <td>{{ $userOrganisationRole->user->name }}</td>
            </tr>
            <tr>
                <th>Organisation</th>
                <td>{{ $userOrganisationRole->organisation->name }}</td>
            </tr>
            <tr>
                <th>Active</th>
                <td>{{ $userOrganisationRole->active ? 'Yes' : 'No' }}</td>
            </tr>
        </tbody>
    </table>
    <a href="{{ route('user-organisation-role.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection
