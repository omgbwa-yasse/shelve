@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Transferring Status</h1>
        <form action="{{ route('transferring-status.update', $transferringStatus->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $transferringStatus->name }}" required maxlength="50">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description">{{ $transferringStatus->description }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
