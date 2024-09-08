@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Room Details</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $room->name }}</h5>
                <p class="card-text"><strong>Code:</strong> {{ $room->code }}</p>
                <p class="card-text"><strong>Description:</strong> {{ $room->description }}</p>
                <p class="card-text"><strong>Niveau/bâtiment :</strong>  {{ $room->floor->name }} ({{ $room->floor->building->name }})</p>
                <p class="card-text"><strong>Type de local :</strong>
                    @if( $room->type->name == "archives")
                        Salle d'archives
                    @elseif($room->type->name == "producer")
                        Local tampon (service producteur)
                     @endif
                </p>
                <a href="{{ route('rooms.index') }}" class="btn btn-secondary btn-sm">Back</a>
                <a href="{{ route('rooms.edit', $room->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('rooms.destroy', $room->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this room?')">Delete</button>
                    </form>
            </div>
        </div>
    </div>
@endsection
