@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>User Organisation Details</h1>
        <table class="table">
            <tr>
                <th>User ID</th>
                <td>{{ $userOrganisation->user_id }}</td>
            </tr>
            <tr>
                <th>Organisation ID</th>
                <td>{{ $userOrganisation->organisation_id }}</td>
            </tr>
            <tr>
                <th>Created At</th>
                <td>{{ $userOrganisation->created_at }}</td>
            </tr>
            <tr>
                <th>Updated At</th>
                <td>{{ $userOrganisation->updated_at }}</td>
            </tr>
        </table>
        <a href="{{ route('user-organisations.index') }}" class="btn btn-secondary">Back</a>
    </div>
@endsection
