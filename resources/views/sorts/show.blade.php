@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Sort Details</h1>
        <table class="table">
            <tr>
                <th>ID</th>
                <td>{{ $sort->id }}</td>
            </tr>
            <tr>
                <th>Code</th>
                <td>{{ $sort->code }}</td>
            </tr>
            <tr>
                <th>Name</th>
                <td>{{ $sort->name }}</td>
            </tr>
            <tr>
                <th>Description</th>
                <td>{{ $sort->description }}</td>
            </tr>
        </table>
        <a href="{{ route('sorts.index') }}" class="btn btn-secondary">Back</a>
        <a href="{{ route('sorts.edit', $sort->id) }}" class="btn btn-warning">Edit</a>
        <form action="{{ route('sorts.destroy', $sort->id) }}" method="POST" style="display: inline-block;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this sort?')">Delete</button>
        </form>
    </div>
@endsection
