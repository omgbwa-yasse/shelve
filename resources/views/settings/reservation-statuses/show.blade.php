@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ $reservationStatus->name }}</h1>
        <p>{{ $reservationStatus->description }}</p>
        <a href="{{ route('reservation-status.index') }}" class="btn btn-secondary btn-sm">Back</a>
        <a href="{{ route('reservation-status.edit', $reservationStatus) }}" class="btn btn-warning btn-sm">Edit</a>
        <form action="{{ route('reservation-status.destroy', $reservationStatus) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this status?')">Delete</button>
        </form>
    </div>
@endsection
