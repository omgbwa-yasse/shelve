@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Floor</h1>
        <form action="{{ route('floors.update', [$floor->building, $floor]) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $floor->name }}" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description">{{ $floor->description }}</textarea>
            </div>
            <div class="mb-3">
                <label for="building_id" class="form-label">Building</label>
                <select class="form-select" id="building_id" name="building_id" required>
                    @foreach ($buildings as $building)
                        <option value="{{ $building->id }}" {{ $building->id == $floor->building_id ? 'selected' : '' }}>
                            {{ $building->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
