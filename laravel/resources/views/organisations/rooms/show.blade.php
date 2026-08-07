@extends('layouts.app')

@section('content')
    <h1>Room Details for {{ $organisation->name }}</h1>

    <p><strong>Room Name:</strong> {{ $room->name }}</p>
    <p><strong>Room ID:</strong> {{ $room->id }}</p>

    <a href="{{ route('organisations.rooms.edit', [$organisation, $room]) }}" class="btn btn-secondary">Edit</a>
    <a href="{{ route('organisations.rooms.index', $organisation) }}" class="btn btn-primary">Back to Rooms</a>
@endsection
