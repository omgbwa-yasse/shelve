@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create User Role</h1>
        <form action="{{ route('user-roles.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="role_id" class="form-label">Role ID</label>
                <input type="number" name="role_id" id="role_id" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="user_id" class="form-label">User ID</label>
                <input type="number" name="user_id" id="user_id" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
        </form>
    </div>
@endsection
