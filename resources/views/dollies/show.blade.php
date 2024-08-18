@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $dolly->name }}</h1>
    <p>{{ $dolly->description }}</p>
    <p>Type: {{ $dolly->type->name }}</p>
    <a href="{{ route('dolly.edit', $dolly) }}" class="btn btn-warning">Edit</a>
    <form action="{{ route('dolly.destroy', $dolly) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this dolly?')">Delete</button>
    </form>

</div>
@endsection
