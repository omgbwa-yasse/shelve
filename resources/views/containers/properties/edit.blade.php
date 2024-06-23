@extends('layouts.app')

@section('content')

<div class="container">
    <h1>Edit Container Property</h1>
    <form action="{{ route('container-property.update', $containerProperty->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $containerProperty->name }}" required>
        </div>
        <div class="mb-3">
            <label for="width" class="form-label">Width</label>
            <input type="number" class="form-control" id="width" name="width" step="0.01" value="{{ $containerProperty->width }}" required>
        </div>
        <div class="mb-3">
            <label for="length" class="form-label">Length</label>
            <input type="number" class="form-control" id="length" name="length" step="0.01" value="{{ $containerProperty->length }}" required>
        </div>
        <div class="mb-3">
            <label for="depth" class="form-label">Depth</label>
            <input type="number" class="form-control" id="depth" name="depth" step="0.01" value="{{ $containerProperty->depth }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>


@endsection
