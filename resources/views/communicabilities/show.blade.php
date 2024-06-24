@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ $communicability->name }}</h1>
        <p><strong>Code:</strong> {{ $communicability->code }}</p>
        <p><strong>Duration (année) :</strong> {{ $communicability->duration }}</p>
        <p><strong>Description:</strong> {{ $communicability->decription }}</p>
        <a href="{{ route('communicabilities.index') }}" class="btn btn-secondary">Back</a>
        <a href="{{ route('communicabilities.edit', $communicability->id) }}" class="btn btn-primary">Edit</a>
        <form action="{{ route('communicabilities.destroy', $communicability->id) }}" method="POST" style="display: inline-block;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this communicability?')">Delete</button>
        </form>
    </div>
@endsection
