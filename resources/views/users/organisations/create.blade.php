@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create User Organisation Role</h1>
        <form action="{{ route('user-organisation-role.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="user_id">{{ __('User') }}</label>
                <select name="user_id" id="user_id" class="form-control">
                    @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="organisation_id">{{ __('Organisation') }}</label>
                <select name="organisation_id" id="organisation_id" class="form-control">
                    @foreach($organisations as $organisation)
                    <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="role_id">{{ __('Role') }}</label>
                <select name="role_id" id="role_id" class="form-control">
                    @foreach($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
        </form>
    </div>
@endsection
