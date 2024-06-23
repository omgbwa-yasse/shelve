@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Edit Mail Typology</h1>
    <form action="{{ route('mail-typology.update', $mailTypology->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $mailTypology->name }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description">{{ $mailTypology->description }}</textarea>
        </div>
        <div class="mb-3">
            <label for="class_id" class="form-label">Class</label>
            <select class="form-select" id="class_id" name="class_id" required>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}" {{ $class->id == $mailTypology->class_id ? 'selected' : '' }}>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
