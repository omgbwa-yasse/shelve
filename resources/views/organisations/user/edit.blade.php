@extends('layouts.app')
@section('content')

<div class="container">
    <h1>Edit User Organisation</h1>
    <form action="{{ route('user-organisation.update', $userOrganisation->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="user_id" class="form-label">User</label>
            <select name="user_id" id="user_id" class="form-select">
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" {{ $userOrganisation->user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="organisation_id" class="form-label">Organisation</label>
            <select name="organisation_id" id="organisation_id" class="form-select">
                @foreach ($organisations as $organisation)
                    <option value="{{ $organisation->id }}" {{ $userOrganisation->organisation_id == $organisation->id ? 'selected' : '' }}>{{ $organisation->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="active" class="form-label">Active</label>
            <select name="active" id="active" class="form-select">
                <option value="1" {{ $userOrganisation->active ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ !$userOrganisation->active ? 'selected' : '' }}>No</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>

@endsection
