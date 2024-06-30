@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Communication Statuses</h1>
        <a href="{{ route('communication-status.create') }}" class="btn btn-primary mb-3">Create New Status</a>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($statuses as $status)
                    <tr>
                        <td>{{ $status->id }}</td>
                        <td>{{ $status->name }}</td>
                        <td>{{ $status->description }}</td>
                        <td>
                            <a href="{{ route('communication-status.show', $status->id) }}" class="btn btn-info">Show</a>
                            <a href="{{ route('communication-status.edit', $status->id) }}" class="btn btn-warning">Edit</a>
                            <form action="{{ route('communication-status.destroy', $status->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this status?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
