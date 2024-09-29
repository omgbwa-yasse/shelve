@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>User Organisation Role Details</h1>
        <table class="table">
            <tbody>
                <tr>
                    <th>User</th>
                    <td>{{ $userOrganisationRole->user->name }}</td>
                </tr>
                <tr>
                    <th>Organisation</th>
                    <td>{{ $userOrganisationRole->organisation->name }}</td>
                </tr>
                <tr>
                    <th>Role</th>
                    <td>{{ $userOrganisationRole->role->name }}</td>
                </tr>
                <tr>
                    <th>Creator</th>
                    <td>{{ $userOrganisationRole->creator->name }}</td>
                </tr>
            </tbody>
        </table>
        <a href="{{ route('user-organisation-role.index') }}" class="btn btn-secondary">Back</a>
    </div>
@endsection

