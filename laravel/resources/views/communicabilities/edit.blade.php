
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Communicability</h1>
        <form action="{{ route('communicabilities.update', $communicability->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="code">Code</label>
                <input type="text" name="code" id="code" class="form-control" value="{{ $communicability->code }}" required>
            </div>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ $communicability->name }}" required>
            </div>
            <div class="form-group">
                <label for="duration">Duration (année)</label>
                <input type="number" name="duration" id="duration" class="form-control" value="{{ $communicability->duration }}" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control">{{ $communicability->description }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
