@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Mail Priority</h1>
        <form action="{{ route('mail-priority.update', $mailPriority) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $mailPriority->name }}" required>
            </div>
            <div class="mb-3">
                <label for="duration" class="form-label">Duration</label>
                <input type="number" class="form-control" id="duration" name="duration" value="{{ $mailPriority->duration }}" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
