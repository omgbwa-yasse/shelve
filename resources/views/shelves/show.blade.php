@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Shelf Details</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $shelf->code }}</h5>
                <p class="card-text"><strong>Observation:</strong> {{ $shelf->observation }}</p>
                <p class="card-text"><strong>Face(s):</strong> {{ $shelf->face }}</p>
                <p class="card-text"><strong>Travée(s):</strong> {{ $shelf->ear }}</p>
                <p class="card-text"><strong>Tablette(s) :</strong> {{ $shelf->shelf }}</p>
                <p class="card-text"><strong>Longueur tablette :</strong> {{ $shelf->shelf_length }} Cm</p>
                <p class="card-text"><strong>Volumétrie :</strong> {{  ($shelf->face * $shelf->ear * $shelf->shelf * $shelf->shelf_length)/100 }} ml </p>
                <p class="card-text"><strong>Room:</strong> {{ $shelf->room->name }}</p>
                <a href="{{ route('shelves.index') }}" class="btn btn-secondary btn-sm">Back</a>
                <a href="{{ route('shelves.edit', $shelf->id) }}" class="btn btn-warning btn-sm">Edit</a>
                <form action="{{ route('shelves.destroy', $shelf->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this shelf?')">Delete</button>
                </form>
            </div>
        </div>
    </div>
@endsection
