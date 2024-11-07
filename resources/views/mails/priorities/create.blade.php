@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Mail Priority</h1>
        <form action="{{ route('mail-priority.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="duration" class="form-label">Duration</label>
                <input type="number" class="form-control" id="duration" name="duration" required>
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
        </form>
    </div>
@endsection
