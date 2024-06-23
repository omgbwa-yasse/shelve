@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Shelf</h1>
        <form action="{{ route('shelves.update', $shelf->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" class="form-control" id="code" name="code" value="{{ $shelf->code }}" required>
            </div>
            <div class="mb-3">
                <label for="observation" class="form-label">Observation</label>
                <textarea class="form-control" id="observation" name="observation">{{ $shelf->observation }}</textarea>
            </div>
            <div class="mb-3">
                <label for="face" class="form-label">Nombre de face</label>
                <select class="form-select" id="face" name="face" required>
                    <option value="1" {{ $shelf->face == 1 ? 'selected' : '' }}>1</option>
                    <option value="2" {{ $shelf->face == 2 ? 'selected' : '' }}>2</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="ear" class="form-label">Nombre de travée</label>
                <input type="text" class="form-control" id="ear" name="ear" value="{{ $shelf->ear }}" required>
            </div>
            <div class="mb-3">
                <label for="shelf" class="form-label">Nombre de Tablette </label>
                <input type="text" class="form-control" id="shelf" name="shelf" value="{{ $shelf->shelf }}" required>
            </div>
            <div class="mb-3">
                <label for="shelf_length" class="form-label">Longueur d'une tablette</label>
                <input type="number" class="form-control" id="shelf_length" name="shelf_length" value="{{ $shelf->shelf_length }}" required>
            </div>
            <div class="mb-3">
                <label for="room_id" class="form-label">Salle </label>
                <select class="form-select" id="room_id" name="room_id" required>
                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}" {{ $room->id == $shelf->room_id ? 'selected' : '' }}>
                            {{ $room->name }} - {{ $room->floor->name }} ({{ $room->floor->building->name }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
