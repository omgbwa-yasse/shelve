@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Building</h1>
        <form action="{{ route('buildings.update', $building->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $building->name }}" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description">{{ $building->description }}</textarea>
            </div>
            <div class="mb-3">
                <label for="visibility" class="form-label">Visibilité</label>
                <select class="form-control" id="visibility" name="visibility" required>
                    @foreach($visibilityOptions as $value => $label)
                        <option value="{{ $value }}" {{ $building->visibility == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
