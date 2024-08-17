@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit User Organisation</h1>
        <form action="{{ route('user-organisations.update', $userOrganisation->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="user_id" class="form-label">User ID</label>
                <input type="number" name="user_id" id="user_id" class="form-control" value="{{ $userOrganisation->user_id }}" required>
            </div>
            <div class="mb-3">
                <label for="organisation_id" class="form-label">Organisation ID</label>
                <input type="number" name="organisation_id" id="organisation_id" class="form-control" value="{{ $userOrganisation->organisation_id }}" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
