@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Add Attachment to Mail #{{ $mail->id }}</h1>
        <form action="{{ route('mail-attachment.store', $mail) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="path" class="form-label">Path</label>
                <input type="text" class="form-control" id="path" name="path" required>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="crypt" class="form-label">Crypt</label>
                <input type="text" class="form-control" id="crypt" name="crypt" required>
            </div>
            <div class="mb-3">
                <label for="size" class="form-label">Size</label>
                <input type="number" class="form-control" id="size" name="size" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Attachment</button>
        </form>
    </div>
@endsection
