@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('User Role Details') }}</div>

                <div class="card-body">
                    <div class="form-group">
                        <label for="user_id">User</label>
                        <input type="text" class="form-control" id="user_id" value="{{ $userRole->user->name }}" readonly>
                    </div>

                    <div class="form-group">
                        <label for="role_id">Role</label>
                        <input type="text" class="form-control" id="role_id" value="{{ $userRole->role->name }}" readonly>
                    </div>

                    <a href="{{ route('user_roles.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
