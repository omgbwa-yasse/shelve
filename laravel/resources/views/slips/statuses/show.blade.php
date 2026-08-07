@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Transferring Status Details</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $transferringStatus->name }}</h5>
                <p class="card-text">{{ $transferringStatus->description }}</p>
                <a href="{{ route('transferring-status.index') }}" class="btn btn-secondary btn-sm">Back</a>
                <a href="{{ route('transferring-status.edit', $transferringStatus->id) }}" class="btn btn-warning btn-sm">Edit</a>
                <form action="{{ route('transferring-status.destroy', $transferringStatus) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this term category?')">Delete</button>
                </form>
            </div>
        </div>
    </div>
@endsection
