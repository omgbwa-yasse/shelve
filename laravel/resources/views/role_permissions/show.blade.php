@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Show Role Permission') }}</div>

                <div class="card-body">
                    <div class="form-group">
                        <label for="role_id">Role</label>
                        <input type="text" class="form-control" id="role_id" value="{{ $rolePermission }}" readonly>
                    </div>

                    <div class="form-group">
                        <label for="permission_id">Permission</label>
                        <input type="text" class="form-control" id="permission_id" value="{{ $rolePermission }}" readonly>
                    </div>

                    <a href="{{ route('role_permissions.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
