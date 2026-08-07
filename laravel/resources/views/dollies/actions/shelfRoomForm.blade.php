@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="room">
            <h1>Changer de salle  : {{ $dolly->name }}</h1>
            <p>{{ $dolly->description }}</p>
            <form action="" method="GET">
            @csrf
            <div class="mb-3">
                <div class="select-with-search">
                    <select name="room_id" id="room_id" class="form-select" required>
                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}">
                            {{ $room->code }}, {{ $room->floor->name }}, {{ $room->floor->building->name }}
                        </option>
                    @endforeach
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Changer</button>
    </form>
@endsection
