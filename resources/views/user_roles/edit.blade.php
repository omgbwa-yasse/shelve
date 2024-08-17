@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit User Role</h1>
        <form action="{{ route('user-roles.update', $userRole->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="role_id" class="form-label">Role ID</label>
                <input type="number" name="role_id" id="role_id" class="form-control" value="{{ $userRole->role_id }}" required>
            </div>
            <div class="mb-3">
                <label for="user_id" class="form-label">User ID</label>
                <input type="number" name="user_id" id="user_id" class="form-control" value="{{ $userRole->user_id }}" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
