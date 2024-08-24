@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Floors</h1>
        <a href="{{ route('floors.create') }}" class="btn btn-primary mb-3">Create Floor</a>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Building</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($floors as $floor)
                    <tr>
                        <td>{{ $floor->id }}</td>
                        <td>{{ $floor->name }}</td>
                        <td>{{ $floor->description }}</td>
                        <td>{{ $floor->building->name }}</td>
                        <td>
                            <a href="{{ route('floors.show', $floor->id) }}" class="btn btn-info btn-sm">View</a>
                            <a href="{{ route('floors.edit', $floor->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('floors.destroy', $floor->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this floor?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection
