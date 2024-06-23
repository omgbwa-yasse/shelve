@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Tous les contenants d'archives</h1>
        <a href="{{ route('containers.create') }}" class="btn btn-primary mb-3">Create Container</a>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Shelf</th>
                    <th>Status</th>
                    <th>Property</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($containers as $container)
                    <tr>
                        <td>{{ $container->id }}</td>
                        <td>{{ $container->code }}</td>
                        <td>{{ $container->shelf->code }}</td>
                        <td>{{ $container->status->name }}</td>
                        <td>{{ $container->property->name }}</td>
                        <td>
                            <a href="{{ route('containers.show', $container->id) }}" class="btn btn-info btn-sm">View</a>
                            <a href="{{ route('containers.edit', $container->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('containers.destroy', $container->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this container?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
