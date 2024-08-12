@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Chariot</h1>
    <a href="{{ route('dolly.create') }}" class="btn btn-primary mb-3">Create Dolly</a>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dollies as $dolly)
            <tr>
                <td>{{ $dolly->name }}</td>
                <td>{{ $dolly->description }}</td>
                <td>{{ $dolly->type->name }}</td>
                <td>
                    <a href="{{ route('dolly.show', $dolly) }}" class="btn btn-info">View</a>
                    <a href="{{ route('dolly.edit', $dolly) }}" class="btn btn-warning">Edit</a>
                    <form action="{{ route('dolly.destroy', $dolly) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this dolly?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
