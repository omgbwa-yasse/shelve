@extends('layouts.app')

@section('content')
    <h1>Add Room to {{ $organisation->name }}</h1>

    <form action="{{ route('organisations.rooms.store', $organisation) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="room_id">Room</label>
            <select name="room_id" id="room_id" class="form-control">
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}">{{ $room->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Add Room</button>
    </form>
@endsection
