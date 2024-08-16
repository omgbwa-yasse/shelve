@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Edit Role') }}</div>

                <div class="card-body">
                    <form action="{{ route('roles.update', $role->id) }}" method="POST" id="roleForm">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $role->name }}" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ $role->description }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('roleForm').addEventListener('submit', function(event) {
        var name = document.getElementById('name').value;
        var description = document.getElementById('description').value;

        if (name.trim() === '') {
            alert('Please enter a name for the role.');
            event.preventDefault();
        }
    });
</script>
@endsection
