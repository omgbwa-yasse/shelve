@extends('layouts.app')

@section('content')

<div class="container">
    <h1>Create New Container Property</h1>
    <form action="{{ route('container-property.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="width" class="form-label">Width</label>
            <input type="number" class="form-control" id="width" name="width" step="0.01" required>
        </div>
        <div class="mb-3">
            <label for="length" class="form-label">Length</label>
            <input type="number" class="form-control" id="length" name="length" step="0.01" required>
        </div>
        <div class="mb-3">
            <label for="depth" class="form-label">Depth</label>
            <input type="number" class="form-control" id="depth" name="depth" step="0.01" required>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>


@endsection
