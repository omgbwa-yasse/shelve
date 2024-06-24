@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ $category->name }}</h1>
        <p>{{ $category->description }}</p>
        <a href="{{ route('term-categories.index') }}" class="btn btn-secondary btn-sm">Back</a>
        <a href="{{ route('term-categories.edit', $category->id) }}" class="btn btn-secondary btn-sm">Edit</a>
        <form action="{{ route('term-categories.destroy', $category->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this term category?')">Delete</button>
        </form>
    </div>
@endsection
