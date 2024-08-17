@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Edit Role Permission') }}</div>

                <div class="card-body">
                    <form action="{{ route('role_permissions.update', [$rolePermission->role, $rolePermission->permission]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="role_id">Role</label>
                            <select class="form-control" id="role_id" name="role_id" required>
                                <option value="">Select Role</option>
                                @foreach ($roles as $role)
                                <option value="{{ $role->id }}" {{ $rolePermission->role_id == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="permission_id">Permission</label>
                            <select class="form-control" id="permission_id" name="permission_id" required>
                                <option value="">Select Permission</option>
                                @foreach ($permissions as $permission)
                                <option value="{{ $permission->id }}" {{ $rolePermission->permission_id == $permission->id ? 'selected' : '' }}>{{ $permission->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('role_permissions.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
