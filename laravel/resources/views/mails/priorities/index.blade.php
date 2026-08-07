@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Mail priority</h1>
        <a href="{{ route('mail-priority.create') }}" class="btn btn-primary mb-3">Create New Priority</a>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Duration</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($mailPriorities as $priority)
                    <tr>
                        <td>{{ $priority->name }}</td>
                        <td>{{ $priority->duration }}</td>
                        <td>
                            <a href="{{ route('mail-priority.edit', $priority) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('mail-priority.destroy', $priority) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
