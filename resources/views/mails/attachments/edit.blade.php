@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Attachment</h1>
        <form action="{{ route('mail-attachment.update', [$mail, $attachment]) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="path" class="form-label">Path</label>
                <input type="text" class="form-control" id="path" name="path" value="{{ $attachment->path }}" required>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $attachment->name }}" required>
            </div>
            <div class="mb-3">
                <label for="crypt" class="form-label">Crypt</label>
                <input type="text" class="form-control" id="crypt" name="crypt" value="{{ $attachment->crypt }}" required>
            </div>
            <div class="mb-3">
                <label for="size" class="form-label">Size</label>
                <input type="number" class="form-control" id="size" name="size" value="{{ $attachment->size }}" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Attachment</button>
        </form>
    </div>
@endsection
