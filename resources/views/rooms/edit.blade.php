@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Room</h1>
        <form action="{{ route('rooms.update', $room->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" class="form-control" id="code" name="code" value="{{ $room->code }}" required>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $room->name }}" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description">{{ $room->description }}</textarea>
            </div>
            <div class="mb-3">
                <label for="floor_id" class="form-label">Floor</label>
                <select class="form-select" id="floor_id" name="floor_id" required>
                    @foreach ($floors as $floor)
                        <option value="{{ $floor->id }}" {{ $floor->id == $room->floor_id ? 'selected' : '' }}>
                            {{ $floor->building->name }} - {{ $floor->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
