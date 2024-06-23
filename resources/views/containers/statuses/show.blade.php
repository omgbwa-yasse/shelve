@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Container Status Details</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $containerStatus->name }}</h5>
                <p class="card-text">{{ $containerStatus->description }}</p>
                <a href="{{ route('container-status.index') }}" class="btn btn-secondary btn-sm">Back</a>
                <a href="{{ route('container-status.edit', $containerStatus->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('container-status.destroy', $containerStatus->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this container status?')">Delete</button>
                            </form>
            </div>
        </div>
    </div>
@endsection
