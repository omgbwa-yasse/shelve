@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>User Role Details</h1>
        <table class="table">
            <tr>
                <th>Role ID</th>
                <td>{{ $userRole->role_id }}</td>
            </tr>
            <tr>
                <th>User ID</th>
                <td>{{ $userRole->user_id }}</td>
            </tr>
            <tr>
                <th>Created At</th>
                <td>{{ $userRole->created_at }}</td>
            </tr>
            <tr>
                <th>Updated At</th>
                <td>{{ $userRole->updated_at }}</td>
            </tr>
        </table>
        <a href="{{ route('user-roles.update', $userRole->user_id) }}" class="btn btn-secondary"> Modifier mon profil</a>
    </div>
@endsection
