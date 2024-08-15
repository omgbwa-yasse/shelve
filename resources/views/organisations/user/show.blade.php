@extends('layouts.app')
@section('content')

<div class="container">
    <h1>User Organisation Details</h1>
    <table class="table">
        <tbody>
            <tr>
                <th>ID</th>
                <td>{{ $userOrganisation->id }}</td>
            </tr>
            <tr>
                <th>User</th>
                <td>{{ $userOrganisation->user->name }}</td>
            </tr>
            <tr>
                <th>Organisation</th>
                <td>{{ $userOrganisation->organisation->name }}</td>
            </tr>
            <tr>
                <th>Active</th>
                <td>{{ $userOrganisation->active ? 'Yes' : 'No' }}</td>
            </tr>
        </tbody>
    </table>
    <a href="{{ route('user-organisation.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection
