@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Task Statuses</h1>
        <a href="{{ route('taskstatus.create') }}" class="btn btn-primary mb-3">Create New Task Status</a>
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
            @foreach($taskStatuses as $taskStatus)
                <tr>
                    <td>{{ $taskStatus->id }}</td>
                    <td>{{ $taskStatus->name }}</td>
                    <td>{{ $taskStatus->description }}</td>
                    <td>
                        <a href="{{ route('taskstatus.edit', $taskStatus) }}" class="btn btn-primary">Edit</a>
                        <form action="{{ route('taskstatus.destroy', $taskStatus) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
