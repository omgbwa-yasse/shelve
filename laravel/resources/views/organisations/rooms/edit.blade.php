@extends('layouts.app')

@section('content')
    <h1>Edit Room for {{ $organisation->name }}</h1>

    <form action="{{ route('organisations.rooms.update', [$organisation, $room]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="room_id">Room</label>
            <select name="room_id" id="room_id" class="form-control">
                @foreach($allRooms as $allRoom)
                    <option value="{{ $allRoom->id }}" {{ $room->id == $allRoom->id ? 'selected' : '' }}>{{ $allRoom->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update Room</button>
    </form>
@endsection
