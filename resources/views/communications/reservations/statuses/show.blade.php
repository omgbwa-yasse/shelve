@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ $status->name }}</h1>
        <p>{{ $status->description }}</p>
        <a href="{{ route('communications.reservations.statuses.index') }}" class="btn btn-secondary btn-sm">Back</a>
        <a href="{{ route('communications.reservations.statuses.edit', $status) }}" class="btn btn-warning btn-sm">Edit</a>
        <form action="{{ route('communications.reservations.statuses.destroy', $status) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this status?')">Delete</button>
        </form>
    </div>
@endsection
