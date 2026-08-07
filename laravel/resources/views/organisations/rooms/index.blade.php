@extends('layouts.app')

@section('content')
    <h1>Rooms for {{ $organisation->name }}</h1>



    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <ul>
        @foreach($rooms as $room)
            <li>
                {{ $room->name }}
                <form action="{{ route('organisations.rooms.destroy', [$organisation, $room]) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                </form>
            </li>
        @endforeach
    </ul>
@endsection
